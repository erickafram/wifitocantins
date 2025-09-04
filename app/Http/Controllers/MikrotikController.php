<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Device;

class MikrotikController extends Controller
{
    private $mikrotikHost;
    private $mikrotikUser;
    private $mikrotikPass;

    public function __construct()
    {
        $this->mikrotikHost = config('services.mikrotik.host', '192.168.1.1');
        $this->mikrotikUser = config('services.mikrotik.username', 'admin');
        $this->mikrotikPass = config('services.mikrotik.password', '');
    }

    /**
     * Obtém status do dispositivo
     */
    public function getStatus($macAddress)
    {
        try {
            // Buscar usuário no banco
            $user = User::where('mac_address', $macAddress)->first();
            
            if (!$user) {
                return response()->json([
                    'connected' => false,
                    'mac_address' => $macAddress,
                    'ip_address' => null,
                    'expires_at' => null,
                    'data_used' => 0,
                    'status' => 'offline'
                ]);
            }

            // Em produção, consultar MikroTik via API
            $mikrotikStatus = $this->getMikrotikDeviceStatus($macAddress);

            return response()->json([
                'connected' => $user->isConnected(),
                'mac_address' => $user->mac_address,
                'ip_address' => $user->ip_address,
                'expires_at' => $user->expires_at,
                'data_used' => $user->data_used,
                'status' => $user->status,
                'mikrotik_data' => $mikrotikStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter status MikroTik: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao consultar status do dispositivo'
            ], 500);
        }
    }

    /**
     * Libera acesso para dispositivo
     */
    public function allowDevice(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string'
        ]);

        try {
            $macAddress = $request->mac_address;
            
            // Buscar usuário
            $user = User::where('mac_address', $macAddress)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Liberar no MikroTik
            $mikrotikResult = $this->allowDeviceInMikrotik($macAddress);

            if ($mikrotikResult) {
                // Atualizar status no banco
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now()
                ]);

                // Registrar ou atualizar dispositivo
                $this->registerDevice($macAddress, $request);

                return response()->json([
                    'success' => true,
                    'message' => 'Dispositivo liberado com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao liberar dispositivo no MikroTik'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao liberar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Bloqueia dispositivo
     */
    public function blockDevice(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string'
        ]);

        try {
            $macAddress = $request->mac_address;
            
            // Bloquear no MikroTik
            $mikrotikResult = $this->blockDeviceInMikrotik($macAddress);

            if ($mikrotikResult) {
                // Atualizar status no banco
                $user = User::where('mac_address', $macAddress)->first();
                if ($user) {
                    $user->update([
                        'status' => 'offline'
                    ]);

                    // Finalizar sessões ativas
                    $user->sessions()->where('session_status', 'active')->update([
                        'ended_at' => now(),
                        'session_status' => 'ended'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Dispositivo bloqueado com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao bloquear dispositivo no MikroTik'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao bloquear dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obtém dados de uso do dispositivo
     */
    public function getUsage($macAddress)
    {
        try {
            $user = User::where('mac_address', $macAddress)->first();
            
            if (!$user) {
                return response()->json([
                    'data_used' => 0,
                    'session_duration' => 0,
                    'download_speed' => 0,
                    'upload_speed' => 0
                ]);
            }

            // Obter dados do MikroTik
            $mikrotikUsage = $this->getMikrotikUsageData($macAddress);

            // Calcular duração da sessão atual
            $currentSession = $user->sessions()
                ->where('session_status', 'active')
                ->orderBy('started_at', 'desc')
                ->first();

            $sessionDuration = 0;
            if ($currentSession) {
                $sessionDuration = now()->diffInMinutes($currentSession->started_at);
            }

            return response()->json([
                'data_used' => $user->data_used,
                'session_duration' => $sessionDuration,
                'download_speed' => $mikrotikUsage['download_speed'] ?? 0,
                'upload_speed' => $mikrotikUsage['upload_speed'] ?? 0,
                'total_sessions' => $user->sessions()->count(),
                'total_payments' => $user->payments()->where('status', 'completed')->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de uso: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao consultar dados de uso'
            ], 500);
        }
    }

    /**
     * Métodos privados para integração com MikroTik RouterOS API
     */

    /**
     * Conecta com MikroTik (simulado)
     */
    private function connectToMikrotik()
    {
        // Em produção, usar biblioteca como routeros-api ou similar
        // Para desenvolvimento, simular conexão
        return true;
    }

    /**
     * Obtém status do dispositivo no MikroTik
     */
    private function getMikrotikDeviceStatus($macAddress)
    {
        // Simular consulta ao MikroTik
        // Em produção: consultar hotspot users ou firewall rules
        
        return [
            'online' => rand(0, 1) ? true : false,
            'bytes_in' => rand(1000000, 100000000), // bytes
            'bytes_out' => rand(500000, 50000000),
            'session_time' => rand(300, 7200), // segundos
            'idle_time' => rand(0, 300)
        ];
    }

    /**
     * Libera dispositivo no MikroTik
     */
    private function allowDeviceInMikrotik($macAddress)
    {
        try {
            // Em produção: usar RouterOS API
            // Exemplo de comandos:
            // 1. Adicionar à lista de autorizados: /ip hotspot user add
            // 2. Ou remover regra de bloqueio: /ip firewall filter remove
            
            Log::info("Liberando dispositivo {$macAddress} no MikroTik");
            
            // Simular sucesso (95% das vezes)
            return rand(1, 100) <= 95;
            
        } catch (\Exception $e) {
            Log::error("Erro ao liberar {$macAddress} no MikroTik: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bloqueia dispositivo no MikroTik
     */
    private function blockDeviceInMikrotik($macAddress)
    {
        try {
            // Em produção: usar RouterOS API
            // Exemplo de comandos:
            // 1. Remover da lista de autorizados: /ip hotspot user remove
            // 2. Ou adicionar regra de bloqueio: /ip firewall filter add
            
            Log::info("Bloqueando dispositivo {$macAddress} no MikroTik");
            
            // Simular sucesso
            return true;
            
        } catch (\Exception $e) {
            Log::error("Erro ao bloquear {$macAddress} no MikroTik: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém dados de uso do MikroTik
     */
    private function getMikrotikUsageData($macAddress)
    {
        // Simular dados de uso
        return [
            'bytes_in' => rand(1000000, 100000000),
            'bytes_out' => rand(500000, 50000000),
            'download_speed' => rand(1000, 50000), // kbps
            'upload_speed' => rand(500, 25000), // kbps
            'session_time' => rand(300, 7200)
        ];
    }

    /**
     * Registra dispositivo na base de dados
     */
    private function registerDevice($macAddress, $request)
    {
        $device = Device::where('mac_address', $macAddress)->first();
        
        if (!$device) {
            Device::create([
                'mac_address' => $macAddress,
                'device_name' => $this->parseDeviceName($request->userAgent()),
                'device_type' => $this->detectDeviceType($request->userAgent()),
                'user_agent' => $request->userAgent(),
                'first_seen' => now(),
                'last_seen' => now(),
                'total_connections' => 1
            ]);
        } else {
            $device->updateLastSeen();
        }
    }

    /**
     * Extrai nome do dispositivo do User-Agent
     */
    private function parseDeviceName($userAgent)
    {
        if (preg_match('/iPhone/', $userAgent)) return 'iPhone';
        if (preg_match('/iPad/', $userAgent)) return 'iPad';
        if (preg_match('/Android/', $userAgent)) return 'Android Device';
        if (preg_match('/Windows/', $userAgent)) return 'Windows PC';
        if (preg_match('/Macintosh/', $userAgent)) return 'Mac';
        
        return 'Unknown Device';
    }

    /**
     * Detecta tipo do dispositivo
     */
    private function detectDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }
}
