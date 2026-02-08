<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;
use App\Models\MikrotikMacReport;
use App\Models\MikrotikCommand;
use Carbon\Carbon;

class MikrotikApiController extends Controller
{
    /**
     * Verifica se um MAC address é randomizado (privado)
     * MACs randomizados têm o segundo bit do primeiro byte = 1
     * Exemplos: 02:xx, 06:xx, 0A:xx, 0E:xx, 12:xx, etc.
     */
    private function isRandomizedMac(string $mac): bool
    {
        $mac = strtoupper(trim($mac));
        if (strlen($mac) < 2) return true;
        
        $firstByte = hexdec(substr($mac, 0, 2));
        // Bit 1 (segundo bit) indica MAC localmente administrado (randomizado)
        return ($firstByte & 0x02) !== 0;
    }

    /**
     * Endpoint ULTRA-LEVE para MikroTik com pouca memória (hAP ac²)
     * Retorna apenas MACs em texto simples, sem JSON
     * 
     * IMPORTANTE: O MAC é do DISPOSITIVO, não da rede WiFi!
     * Então se o usuário paga na rede 2.4GHz e muda para 5GHz,
     * o MAC continua o mesmo e deve funcionar.
     */
    public function checkPaidUsersLite(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                Log::warning('🔒 MikroTik Lite: Token inválido', ['ip' => $request->ip()]);
                return response('ERROR:AUTH', 401)->header('Content-Type', 'text/plain');
            }

            // 🎯 Buscar MACs ativos - usuários que pagaram e ainda têm tempo
            // Status 'connected' = pagou e está ativo
            // Status 'active' = alternativo para ativo
            // Status 'temp_bypass' = bypass temporário de 3 min para abrir app do banco
            // IMPORTANTE: Liberamos TODOS os MACs, incluindo randomizados!
            // O usuário pagou com esse MAC, então deve funcionar.
            // LIMITE: 200 para suportar vários ônibus
            $activeMacs = User::whereIn('status', ['connected', 'active', 'temp_bypass'])
                ->where('expires_at', '>', now())
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->orderBy('expires_at', 'desc') // Priorizar quem expira depois
                ->limit(200) // Suporta vários ônibus
                ->pluck('mac_address')
                ->map(fn($mac) => strtoupper(trim($mac))) // Normalizar
                ->unique()
                ->values()
                ->toArray();

            // 🗑️ Buscar MACs expirados para remover
            // Apenas usuários que expiraram recentemente (últimas 24h)
            // LIMITE: 100 para suportar vários ônibus
            $expiredMacs = User::where('status', 'expired')
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->whereNotIn('mac_address', $activeMacs) // Não remover quem está ativo
                ->where('expires_at', '>', now()->subHours(24)) // Apenas últimas 24h
                ->where('expires_at', '<', now()) // Já expirou
                ->orderBy('expires_at', 'desc')
                ->limit(100) // Suporta vários ônibus
                ->pluck('mac_address')
                ->map(fn($mac) => strtoupper(trim($mac)))
                ->unique()
                ->values()
                ->toArray();

            // 🔄 Atualizar status de usuários que acabaram de expirar
            // Inclui temp_bypass que expirou (3 min sem pagar)
            $justExpired = User::whereIn('status', ['connected', 'active', 'temp_bypass'])
                ->where('expires_at', '<=', now())
                ->whereNotNull('mac_address')
                ->update([
                    'status' => 'expired',
                    'connected_at' => null
                ]);

            if ($justExpired > 0) {
                Log::info("⏰ MikroTik Lite: $justExpired usuários expiraram agora");
            }

            // 📝 Formato ultra-compacto: L:MAC = liberar, R:MAC = remover
            $output = "OK\n";
            foreach ($activeMacs as $mac) {
                $output .= "L:$mac\n";
            }
            foreach ($expiredMacs as $mac) {
                $output .= "R:$mac\n";
            }
            $output .= "END";

            // 📊 Log para debug (apenas se houver ações)
            if (count($activeMacs) > 0 || count($expiredMacs) > 0) {
                Log::info('📡 MikroTik Lite sync', [
                    'mikrotik_ip' => $request->ip(),
                    'liberar' => count($activeMacs),
                    'remover' => count($expiredMacs),
                    'macs_liberar' => $activeMacs,
                    'macs_remover' => $expiredMacs,
                ]);
            }

            return response($output, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            Log::error('❌ MikroTik Lite erro: ' . $e->getMessage());
            return response('ERROR:INTERNAL', 500)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Limpar usuários expirados antigos (mais de 7 dias)
     * Muda status para 'cleaned' para não aparecer mais nas consultas
     */
    public function cleanExpiredUsers(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Limpar usuários expirados há mais de 7 dias
            $cleaned = User::where('status', 'expired')
                ->where('expires_at', '<', now()->subDays(7))
                ->update(['status' => 'cleaned']);

            Log::info('🧹 Usuários expirados limpos', ['count' => $cleaned]);

            return response()->json([
                'success' => true,
                'cleaned' => $cleaned,
                'message' => "$cleaned usuários marcados como limpos"
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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

            // 🚀 CONSULTA ULTRA-RÁPIDA - Buscar usuários pagos ativos
            // LIMITE: 200 para suportar vários ônibus
            $paidUsers = User::whereIn('status', ['connected', 'active'])
                           ->where('expires_at', '>', now())
                           ->whereNotNull('mac_address')
                           ->limit(200)
                           ->with(['payments' => function($query) {
                               $query->where('status', 'completed')
                                     ->latest()
                                     ->limit(1);
                           }])
                           ->get(['id', 'mac_address', 'ip_address', 'expires_at', 'connected_at']);

            // Coletar MACs que serão liberados para excluir da lista de remoção
            $liberateMacs = $paidUsers->pluck('mac_address')->toArray();

            // Buscar usuários expirados que devem ser removidos
            // EXCLUIR MACs que estão na lista de liberação (evita conflito)
            // LIMITE: 100 para suportar vários ônibus
            $expiredUsers = User::where('status', 'expired')
                              ->whereNotNull('mac_address')
                              ->whereNotIn('mac_address', $liberateMacs)
                              ->where('expires_at', '>', now()->subDays(7)) // Apenas últimos 7 dias
                              ->orderBy('expires_at', 'desc')
                              ->limit(100) // Suporta vários ônibus
                              ->get(['id', 'mac_address', 'ip_address', 'expires_at']);

            // Atualizar status dos usuários que expiraram AGORA para 'expired'
            $justExpired = User::whereIn('status', ['connected', 'active'])
                              ->where('expires_at', '<=', now())
                              ->whereNotNull('mac_address')
                              ->whereNotIn('mac_address', $liberateMacs)
                              ->get(['id', 'mac_address', 'ip_address', 'expires_at']);
            
            if ($justExpired->count() > 0) {
                $justExpiredIds = $justExpired->pluck('id')->toArray();
                User::whereIn('id', $justExpiredIds)->update([
                    'status' => 'expired',
                    'connected_at' => null
                ]);
                
                // Adicionar à lista de remoção
                $expiredUsers = $expiredUsers->merge($justExpired);
                
                Log::info('👥 Usuários expirados atualizados', [
                    'count' => count($justExpiredIds),
                    'user_ids' => $justExpiredIds
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
                $lines[] = 'STATUS|success|'.now()->format('Y-m-d\TH:i:s');

                foreach ($paidUsers as $user) {
                    $lines[] = implode('|', [
                        'LIBERATE',
                        $user->mac_address,
                        $user->ip_address ?? '',
                        optional($user->expires_at)->format('Y-m-d\TH:i:s') ?? '',
                        (string) $user->id,
                    ]);
                }

                foreach ($expiredUsers as $user) {
                    $lines[] = implode('|', [
                        'REMOVE',
                        $user->mac_address,
                        $user->ip_address ?? '',
                        optional($user->expires_at)->format('Y-m-d\TH:i:s') ?? '',
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
                'timestamp' => now()->format('Y-m-d\TH:i:s'),
                'liberate_macs' => $paidUsers->map(function ($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expires_at' => $user->expires_at->format('Y-m-d\TH:i:s'),
                        'user_id' => $user->id,
                        'action' => 'LIBERATE',
                    ];
                })->toArray(),
                'remove_macs' => $expiredUsers->map(function ($user) {
                    return [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                        'expired_at' => $user->expires_at->format('Y-m-d\TH:i:s'),
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

    /**
     * Endpoint de diagnóstico - verifica status de um MAC específico
     * Útil para debug quando usuário reclama que pagou mas não tem acesso
     */
    public function checkMacStatus(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $mac = strtoupper(trim($request->get('mac', '')));
            
            if (empty($mac) || strlen($mac) !== 17) {
                return response()->json([
                    'success' => false,
                    'error' => 'MAC address inválido. Formato: XX:XX:XX:XX:XX:XX'
                ], 400);
            }

            // Buscar usuário pelo MAC
            $user = User::where('mac_address', $mac)->first();
            
            if (!$user) {
                // Tentar buscar por MAC similar (case insensitive)
                $user = User::whereRaw('UPPER(mac_address) = ?', [$mac])->first();
            }

            // Buscar pagamentos relacionados
            $payments = [];
            if ($user) {
                $payments = Payment::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'amount', 'status', 'payment_type', 'created_at', 'paid_at']);
            }

            // Buscar no mikrotik_mac_reports
            $macReport = MikrotikMacReport::where('mac_address', $mac)->first();

            // Determinar se deveria estar liberado
            $shouldBeLiberated = false;
            $reason = 'MAC não encontrado no sistema';
            
            if ($user) {
                if (in_array($user->status, ['connected', 'active'])) {
                    if ($user->expires_at && $user->expires_at > now()) {
                        $shouldBeLiberated = true;
                        $reason = 'Usuário ativo com tempo válido';
                    } else {
                        $reason = 'Usuário ativo mas tempo expirado';
                    }
                } else {
                    $reason = 'Status do usuário: ' . $user->status;
                }
            }

            return response()->json([
                'success' => true,
                'mac_address' => $mac,
                'should_be_liberated' => $shouldBeLiberated,
                'reason' => $reason,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'ip_address' => $user->ip_address,
                    'connected_at' => $user->connected_at?->format('Y-m-d H:i:s'),
                    'expires_at' => $user->expires_at?->format('Y-m-d H:i:s'),
                    'time_remaining' => $user->expires_at && $user->expires_at > now() 
                        ? $user->expires_at->diffForHumans() 
                        : 'Expirado',
                ] : null,
                'payments' => $payments,
                'mac_report' => $macReport ? [
                    'ip_address' => $macReport->ip_address,
                    'mikrotik_ip' => $macReport->mikrotik_ip,
                    'reported_at' => $macReport->reported_at?->format('Y-m-d H:i:s'),
                ] : null,
                'debug' => [
                    'server_time' => now()->format('Y-m-d H:i:s'),
                    'timezone' => config('app.timezone'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no checkMacStatus', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint para forçar liberação imediata de um MAC
     * Útil quando o usuário pagou mas a sincronização ainda não rodou
     */
    public function forceLiberate(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $mac = strtoupper(trim($request->get('mac', '')));
            
            if (empty($mac) || strlen($mac) !== 17) {
                return response()->json([
                    'success' => false,
                    'error' => 'MAC address inválido'
                ], 400);
            }

            // Buscar usuário
            $user = User::where('mac_address', $mac)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não encontrado para este MAC'
                ], 404);
            }

            // Verificar se tem pagamento válido
            $hasValidPayment = Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('created_at', '>', now()->subHours(24))
                ->exists();

            if (!$hasValidPayment && !in_array($user->status, ['connected', 'active'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não tem pagamento válido nas últimas 24h'
                ], 400);
            }

            // Forçar status connected e expires_at
            $sessionDuration = config('wifi.pricing.session_duration_hours', 12);
            $expiresAt = now()->addHours($sessionDuration);

            $user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            Log::info('🔓 Liberação forçada de MAC', [
                'mac' => $mac,
                'user_id' => $user->id,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'requested_by' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MAC liberado com sucesso',
                'mac_address' => $mac,
                'user_id' => $user->id,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'note' => 'O MikroTik irá sincronizar na próxima consulta (máx 30 segundos)'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no forceLiberate', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de diagnóstico geral do sistema
     */
    public function diagnostics(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Estatísticas gerais
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'connected' => User::where('status', 'connected')->count(),
                    'active' => User::where('status', 'active')->count(),
                    'expired' => User::where('status', 'expired')->count(),
                    'with_valid_time' => User::whereIn('status', ['connected', 'active'])
                        ->where('expires_at', '>', now())
                        ->count(),
                ],
                'payments' => [
                    'total_today' => Payment::whereDate('created_at', today())->count(),
                    'completed_today' => Payment::whereDate('created_at', today())
                        ->where('status', 'completed')
                        ->count(),
                    'pending' => Payment::where('status', 'pending')
                        ->where('created_at', '>', now()->subHours(1))
                        ->count(),
                ],
                'mac_reports' => [
                    'total' => MikrotikMacReport::count(),
                    'last_hour' => MikrotikMacReport::where('reported_at', '>', now()->subHour())->count(),
                ],
            ];

            // Últimos usuários liberados
            $recentLiberated = User::whereIn('status', ['connected', 'active'])
                ->where('expires_at', '>', now())
                ->orderBy('connected_at', 'desc')
                ->limit(10)
                ->get(['id', 'mac_address', 'status', 'connected_at', 'expires_at']);

            return response()->json([
                'success' => true,
                'server_time' => now()->format('Y-m-d H:i:s'),
                'timezone' => config('app.timezone'),
                'stats' => $stats,
                'recent_liberated' => $recentLiberated,
                'config' => [
                    'session_duration_hours' => config('wifi.pricing.session_duration_hours', 12),
                    'sync_interval' => '30 segundos',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎛️ REMOTE ADMIN PANEL - Buscar comandos pendentes
     * Mikrotik chama este endpoint a cada 15 segundos para buscar comandos
     */
    public function getCommands(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response('ERROR:AUTH', 401)->header('Content-Type', 'text/plain');
            }

            // Buscar comandos pendentes
            $commands = MikrotikCommand::pending()
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();

            if ($commands->isEmpty()) {
                return response("OK\nEND", 200)->header('Content-Type', 'text/plain');
            }

            // Formato: CMD:ID:TYPE:MAC
            // Exemplo: CMD:1:liberate:AA:BB:CC:DD:EE:FF
            $output = "OK\n";
            foreach ($commands as $cmd) {
                $output .= "CMD:{$cmd->id}:{$cmd->command_type}:{$cmd->mac_address}\n";
            }
            $output .= "END";

            Log::info('🎛️ Comandos enviados para Mikrotik', [
                'mikrotik_ip' => $request->ip(),
                'commands_count' => $commands->count(),
                'commands' => $commands->pluck('command_type', 'id')->toArray(),
            ]);

            return response($output, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar comandos: ' . $e->getMessage());
            return response('ERROR:INTERNAL', 500)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * 🎛️ REMOTE ADMIN PANEL - Receber resultado de comando executado
     * Mikrotik reporta se o comando foi executado com sucesso ou falhou
     */
    public function commandResult(Request $request)
    {
        try {
            $token = $request->get('token') ?? $request->bearerToken();
            $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
            
            if (str_replace('Bearer ', '', $token) !== $expectedToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'command_id' => 'required|integer',
                'status' => 'required|in:executed,failed',
                'response' => 'nullable|string',
            ]);

            $command = MikrotikCommand::find($request->command_id);
            
            if (!$command) {
                return response()->json([
                    'success' => false,
                    'error' => 'Comando não encontrado'
                ], 404);
            }

            if ($request->status === 'executed') {
                $command->markAsExecuted($request->response);
            } else {
                $command->markAsFailed($request->response ?? 'Erro desconhecido');
            }

            Log::info('✅ Resultado de comando recebido', [
                'command_id' => $command->id,
                'type' => $command->command_type,
                'mac' => $command->mac_address,
                'status' => $request->status,
                'response' => $request->response,
                'mikrotik_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resultado registrado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar resultado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
