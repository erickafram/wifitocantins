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
     * Endpoint para MikroTik registrar MACs automaticamente
     */
    public function registerMac(Request $request)
    {
        try {
            // Verificar token
            $token = $request->get('token');
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if ($token !== $expectedToken) {
                return response()->json(['error' => 'Token inválido'], 401);
            }
            
            $mac = $request->get('mac');
            $ip = $request->get('ip');
            $hostname = $request->get('hostname', '');
            
            if (!$mac || !$ip) {
                return response()->json(['error' => 'MAC e IP obrigatórios'], 400);
            }
            
            // Registrar MAC no banco
            MikrotikMacReport::updateOrCreate(
                [
                    'mac_address' => strtoupper($mac),
                    'ip_address' => $ip
                ],
                [
                    'hostname' => $hostname,
                    'reported_at' => now(),
                    'last_seen' => now()
                ]
            );
            
            Log::info('📡 MAC registrado pelo MikroTik', [
                'mac' => $mac,
                'ip' => $ip,
                'hostname' => $hostname
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'MAC registrado com sucesso',
                'mac' => $mac,
                'ip' => $ip
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Erro ao registrar MAC', [
                'error' => $e->getMessage(),
                'mac' => $request->get('mac'),
                'ip' => $request->get('ip')
            ]);
            
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

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

            // 🚀 CONSULTA ULTRA-RÁPIDA - Buscar usuários pagos ativos E usuários com vouchers ativos
            $paidUsers = User::whereIn('status', ['connected', 'active'])
                           ->where('expires_at', '>', now())
                           ->whereNotNull('mac_address')
                           ->with(['payments' => function($query) {
                               $query->where('status', 'completed')
                                     ->latest()
                                     ->limit(1);
                           }])
                           ->get(['id', 'mac_address', 'ip_address', 'expires_at', 'connected_at']);

            // Buscar usuários expirados que devem ser removidos
            // Incluir status 'expired' também para garantir remoção
            $expiredUsers = User::whereIn('status', ['connected', 'active', 'expired'])
                              ->where('expires_at', '<=', now())
                              ->whereNotNull('mac_address')
                              ->get(['id', 'mac_address', 'ip_address', 'expires_at']);

            // Atualizar status dos usuários expirados para 'expired'
            if ($expiredUsers->count() > 0) {
                $expiredUserIds = $expiredUsers->pluck('id')->toArray();
                User::whereIn('id', $expiredUserIds)->update([
                    'status' => 'expired',
                    'connected_at' => null
                ]);
                
                Log::info('👥 Usuários expirados atualizados', [
                    'count' => count($expiredUserIds),
                    'user_ids' => $expiredUserIds
                ]);
            }

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

            $format = $request->query('format', 'json');

            if ($format === 'routeros') {
                $lines = [];
                $lines[] = 'STATUS|success|'.now()->toISOString();

                foreach ($paidUsers as $user) {
                    $lines[] = implode('|', [
                        'LIBERATE',
                        $user->mac_address,
                        $user->ip_address ?? '',
                        optional($user->expires_at)->toISOString() ?? '',
                        (string) $user->id,
                    ]);
                }

                foreach ($expiredUsers as $user) {
                    $lines[] = implode('|', [
                        'REMOVE',
                        $user->mac_address,
                        $user->ip_address ?? '',
                        optional($user->expires_at)->toISOString() ?? '',
                        (string) $user->id,
                    ]);
                }

                $lines[] = implode('|', ['TOTAL', (string) $paidUsers->count(), (string) $expiredUsers->count()]);

            Log::info('⚡ MikroTik consulta ULTRA-RÁPIDA (RouterOS)', [
                'mikrotik_ip' => $request->ip(),
                'liberate_count' => $paidUsers->count(),
                'remove_count' => $expiredUsers->count(),
                'interval' => '10_segundos',
                'format' => 'routeros',
            ]);

                return response(implode("\n", $lines)."\n", 200)
                    ->header('Content-Type', 'text/plain');
            }

            $response = [
                'success' => true,
                'timestamp' => now()->toISOString(),
                'liberate_macs' => $paidUsers->map(function ($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expires_at' => $user->expires_at->toISOString(),
                        'user_id' => $user->id,
                        'action' => 'LIBERATE',
                    ];
                })->toArray(),
                'remove_macs' => $expiredUsers->map(function ($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expired_at' => $user->expires_at->toISOString(),
                        'user_id' => $user->id,
                        'action' => 'REMOVE',
                    ];
                })->toArray(),
                'total_liberate' => $paidUsers->count(),
                'total_remove' => $expiredUsers->count(),
            ];

            // 🚀 Log da consulta ultra-rápida (10s)
            Log::info('⚡ MikroTik consulta ULTRA-RÁPIDA', [
                'mikrotik_ip' => $request->ip(),
                'liberate_count' => $paidUsers->count(),
                'remove_count' => $expiredUsers->count(),
                'interval' => '10_segundos',
                'format' => 'json',
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
