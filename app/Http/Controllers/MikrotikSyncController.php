<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use Carbon\Carbon;

class MikrotikSyncController extends Controller
{
    /**
     * Endpoint para MikroTik consultar usuários para liberar
     */
    public function getPendingUsers(Request $request)
    {
        try {
            // Verificar autenticação com token mais flexível
            $token = $request->bearerToken() ?? 
                     $request->get('token') ?? 
                     $request->get('authorization') ?? 
                     str_replace('Bearer ', '', $request->header('Authorization', ''));
            
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if ($token !== $expectedToken) {
                Log::warning('MikroTik Sync: Token inválido', [
                    'provided_token' => substr($token, 0, 8) . '...',
                    'expected_token' => substr($expectedToken, 0, 8) . '...',
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // BUSCA OTIMIZADA - Usuários que devem ser liberados
            $usersToAllow = User::where('status', 'connected')
                ->whereNotNull('mac_address')
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', now())
                ->select('id', 'mac_address', 'expires_at', 'connected_at')
                ->get();

            // BUSCA OTIMIZADA - Usuários que devem ser bloqueados (expirados)
            $usersToBlock = User::where('status', 'connected')
                ->whereNotNull('mac_address')
                ->where('expires_at', '<=', now())
                ->select('id', 'mac_address', 'expires_at')
                ->get();

            // Marcar usuários expirados como offline
            if ($usersToBlock->count() > 0) {
                User::whereIn('mac_address', $usersToBlock->pluck('mac_address'))
                    ->update(['status' => 'offline']);

                // Finalizar sessões
                foreach ($usersToBlock as $user) {
                    Session::where('user_id', $user->id)
                        ->where('session_status', 'active')
                        ->update([
                            'ended_at' => now(),
                            'session_status' => 'ended'
                        ]);
                }
            }

            // Extrair apenas os MACs para o MikroTik (formato simplificado)
            $allowMacs = $usersToAllow->pluck('mac_address')->toArray();
            $blockMacs = $usersToBlock->pluck('mac_address')->toArray();

            $response = [
                'success' => true,
                'timestamp' => now()->toISOString(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'allow_count' => count($allowMacs),
                'allow_users' => $allowMacs,
                'block_count' => count($blockMacs),
                'block_users' => $blockMacs,
                'detailed_users' => $usersToAllow->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'mac_address' => $user->mac_address,
                        'expires_at' => $user->expires_at->toISOString(),
                        'connected_at' => $user->connected_at ? $user->connected_at->toISOString() : null,
                        'time_left' => $user->expires_at->diffForHumans()
                    ];
                }),
                'stats' => [
                    'allow_count' => count($allowMacs),
                    'block_count' => count($blockMacs),
                    'total_active' => count($allowMacs)
                ]
            ];

            // Log detalhado para debug
            Log::info('MikroTik Sync Request Processado', [
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'allow_count' => count($allowMacs),
                'allow_macs' => $allowMacs,
                'block_count' => count($blockMacs),
                'block_macs' => $blockMacs,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Erro no sync MikroTik', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Endpoint para MikroTik reportar status de usuários
     */
    public function reportUserStatus(Request $request)
    {
        try {
            $request->validate([
                'mac_address' => 'required|string',
                'status' => 'required|in:connected,disconnected',
                'bytes_in' => 'nullable|integer',
                'bytes_out' => 'nullable|integer',
                'session_time' => 'nullable|integer'
            ]);

            $user = User::where('mac_address', $request->mac_address)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Atualizar dados de uso se fornecidos
            if ($request->has('bytes_in') || $request->has('bytes_out')) {
                $totalBytes = ($request->bytes_in ?? 0) + ($request->bytes_out ?? 0);
                $user->update(['data_used' => $totalBytes]);
            }

            // Atualizar sessão ativa se existir
            if ($request->status === 'connected' && $request->has('session_time')) {
                $activeSession = Session::where('user_id', $user->id)
                    ->where('session_status', 'active')
                    ->orderBy('started_at', 'desc')
                    ->first();

                if ($activeSession) {
                    $activeSession->update([
                        'data_used' => ($request->bytes_in ?? 0) + ($request->bytes_out ?? 0)
                    ]);
                }
            }

            Log::info('Status reportado pelo MikroTik', [
                'mac_address' => $request->mac_address,
                'status' => $request->status,
                'bytes_total' => ($request->bytes_in ?? 0) + ($request->bytes_out ?? 0)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao reportar status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Endpoint para MikroTik verificar se um usuário específico deve ter acesso
     */
    public function checkUserAccess(Request $request)
    {
        try {
            $request->validate([
                'mac_address' => 'required|string'
            ]);

            $user = User::where('mac_address', $request->mac_address)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'allow_access' => false,
                    'message' => 'Usuário não encontrado'
                ]);
            }

            $shouldAllow = $user->status === 'connected' && 
                          $user->expires_at && 
                          $user->expires_at > now();

            // Se expirou, atualizar status
            if (!$shouldAllow && $user->status === 'connected') {
                $user->update(['status' => 'offline']);
                
                // Finalizar sessões ativas
                Session::where('user_id', $user->id)
                    ->where('session_status', 'active')
                    ->update([
                        'ended_at' => now(),
                        'session_status' => 'ended'
                    ]);
            }

            return response()->json([
                'success' => true,
                'allow_access' => $shouldAllow,
                'mac_address' => $user->mac_address,
                'status' => $user->status,
                'expires_at' => $user->expires_at ? $user->expires_at->toISOString() : null,
                'message' => $shouldAllow ? 'Acesso permitido' : 'Acesso negado ou expirado'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar acesso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Endpoint de teste para verificar conectividade
     */
    public function ping(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Servidor acessível',
            'timestamp' => now()->toISOString(),
            'server_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Dashboard com estatísticas para o MikroTik
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'connected' => User::where('status', 'connected')->count(),
                    'pending' => User::where('status', 'pending')->count(),
                    'offline' => User::where('status', 'offline')->count()
                ],
                'payments' => [
                    'total' => Payment::count(),
                    'completed_today' => Payment::where('status', 'completed')
                        ->whereDate('paid_at', today())->count(),
                    'pending' => Payment::where('status', 'pending')->count(),
                    'revenue_today' => Payment::where('status', 'completed')
                        ->whereDate('paid_at', today())->sum('amount')
                ],
                'sessions' => [
                    'active' => Session::where('session_status', 'active')->count(),
                    'total_today' => Session::whereDate('started_at', today())->count()
                ],
                'system' => [
                    'server_time' => now()->toISOString(),
                    'timezone' => config('app.timezone'),
                    'version' => '1.0.0'
                ]
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno'
            ], 500);
        }
    }
} 