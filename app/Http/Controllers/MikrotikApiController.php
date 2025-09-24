<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;
use App\Models\MikrotikMacReport;
use Carbon\Carbon;

class MikrotikApiController extends Controller
{
    /**
     * Endpoint ULTRA-RÁPIDO para MikroTik verificar MACs (consulta a cada 10s)
     */
    public function checkPaidUsers(Request $request)
    {
        try {
            // Verificar token de autorização
            $token = $request->bearerToken() ?? 
                     $request->get('token') ?? 
                     $request->header('Authorization');
            
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // 🚀 CONSULTA ULTRA-RÁPIDA - Buscar usuários pagos ativos
            $paidUsers = User::where('status', 'connected')
                           ->where('expires_at', '>', now())
                           ->whereNotNull('mac_address')
                           ->with(['payments' => function($query) {
                               $query->where('status', 'completed')
                                     ->latest()
                                     ->limit(1);
                           }])
                           ->get(['id', 'mac_address', 'ip_address', 'expires_at', 'connected_at']);

            // Buscar usuários expirados que devem ser bloqueados
            $expiredUsers = User::where('status', 'connected')
                              ->where('expires_at', '<=', now())
                              ->whereNotNull('mac_address')
                              ->get(['id', 'mac_address', 'ip_address', 'expires_at']);

            // Registrar MACs que devem ser liberados na tabela mikrotik_mac_reports
            foreach ($paidUsers as $user) {
                try {
                    MikrotikMacReport::updateOrCreate(
                        [
                            'ip_address' => $user->ip_address,
                            'mac_address' => $user->mac_address,
                        ],
                        [
                            'transaction_id' => 'AUTO_LIBERATED_' . $user->id,
                            'mikrotik_ip' => $request->ip(),
                            'reported_at' => now(),
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error('Erro ao registrar MAC para liberação', [
                        'user_id' => $user->id,
                        'mac' => $user->mac_address,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $response = [
                'success' => true,
                'timestamp' => now()->toISOString(),
                'liberate_macs' => $paidUsers->map(function($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expires_at' => $user->expires_at->toISOString(),
                        'user_id' => $user->id,
                        'action' => 'LIBERATE'
                    ];
                })->toArray(),
                'block_macs' => $expiredUsers->map(function($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expired_at' => $user->expires_at->toISOString(),
                        'user_id' => $user->id,
                        'action' => 'BLOCK'
                    ];
                })->toArray(),
                'total_liberate' => $paidUsers->count(),
                'total_block' => $expiredUsers->count()
            ];

            // 🚀 Log da consulta ultra-rápida (10s)
            Log::info('⚡ MikroTik consulta ULTRA-RÁPIDA', [
                'mikrotik_ip' => $request->ip(),
                'liberate_count' => $paidUsers->count(),
                'block_count' => $expiredUsers->count(),
                'interval' => '10_segundos'
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Erro no endpoint checkPaidUsers', [
                'error' => $e->getMessage(),
                'mikrotik_ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Endpoint para MikroTik reportar MAC real de um IP
     */
    public function reportMacAddress(Request $request)
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'mac' => 'required|string|size:17',
                'timestamp' => 'nullable|numeric'
            ]);

            $ipAddress = $request->ip;
            $macAddress = strtoupper($request->mac);
            $mikrotikIp = $request->ip();

            // Armazenar o mapeamento IP→MAC
            MikrotikMacReport::updateOrCreate(
                [
                    'ip_address' => $ipAddress,
                    'mac_address' => $macAddress,
                ],
                [
                    'mikrotik_ip' => $mikrotikIp,
                    'reported_at' => now(),
                ]
            );

            Log::info('MikroTik reportou MAC', [
                'ip' => $ipAddress,
                'mac' => $macAddress,
                'mikrotik_ip' => $mikrotikIp
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MAC reported successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao receber MAC do MikroTik', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to report MAC'
            ], 500);
        }
    }

    /**
     * Endpoint para MikroTik confirmar liberação de MAC
     */
    public function confirmMacLiberation(Request $request)
    {
        try {
            $request->validate([
                'mac_address' => 'required|string|size:17',
                'action' => 'required|in:liberated,blocked',
                'result' => 'required|in:success,failed'
            ]);

            $macAddress = strtoupper($request->mac_address);
            $action = $request->action;
            $result = $request->result;

            Log::info('MikroTik confirmou ação', [
                'mac_address' => $macAddress,
                'action' => $action,
                'result' => $result,
                'mikrotik_ip' => $request->ip()
            ]);

            // Se liberação foi bem-sucedida, não fazer nada
            // Se falhou, pode tentar novamente na próxima consulta

            return response()->json([
                'success' => true,
                'message' => 'Action confirmed'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na confirmação do MikroTik', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm action'
            ], 500);
        }
    }
}
