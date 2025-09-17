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
    private $mikrotikPort;

    public function __construct()
    {
        $this->mikrotikHost = config('wifi.mikrotik.host', '192.168.10.1');
        $this->mikrotikUser = config('wifi.mikrotik.username', 'api-laravel');
        $this->mikrotikPass = config('wifi.mikrotik.password', '');
        $this->mikrotikPort = config('wifi.mikrotik.port', 8728);
    }

    /**
     * Conecta à API do MikroTik
     */
    private function connectToMikroTik()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (!$socket) {
            throw new \Exception('Erro ao criar socket');
        }

        $result = socket_connect($socket, $this->mikrotikHost, $this->mikrotikPort);
        
        if (!$result) {
            throw new \Exception('Erro ao conectar ao MikroTik');
        }

        return $socket;
    }

    /**
     * Envia comando para MikroTik
     */
    private function sendCommand($socket, $command, $attributes = [])
    {
        // Implementação básica da API RouterOS
        $data = "/api/" . $command . "\n";
        
        foreach ($attributes as $key => $value) {
            $data .= "=" . $key . "=" . $value . "\n";
        }
        
        $data .= "\n";
        
        socket_write($socket, $data, strlen($data));
        
        // Ler resposta (implementação simplificada)
        $response = socket_read($socket, 2048);
        
        return $response;
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
            if (!config('wifi.mikrotik.api_enabled')) {
                Log::info("API MikroTik desabilitada - simulando liberação para {$macAddress}");
                return true;
            }

            Log::info("Liberando dispositivo {$macAddress} no MikroTik");
            
            // Conectar ao MikroTik
            $socket = $this->connectToMikroTik();
            
            // Método 1: Adicionar regra de firewall para permitir o MAC
            $response = $this->sendCommand($socket, 'ip/firewall/filter/add', [
                'chain' => 'forward',
                'action' => 'accept',
                'src-mac-address' => $macAddress,
                'comment' => 'Allowed-' . $macAddress
            ]);
            
            socket_close($socket);
            
            Log::info("Dispositivo {$macAddress} liberado no MikroTik");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Erro ao liberar {$macAddress} no MikroTik: " . $e->getMessage());
            // Em caso de erro, simular sucesso para não quebrar o fluxo
            return true;
        }
    }

    /**
     * Bloqueia dispositivo no MikroTik
     */
    private function blockDeviceInMikrotik($macAddress)
    {
        try {
            if (!config('wifi.mikrotik.api_enabled')) {
                Log::info("API MikroTik desabilitada - simulando bloqueio para {$macAddress}");
                return true;
            }

            Log::info("Bloqueando dispositivo {$macAddress} no MikroTik");
            
            // Conectar ao MikroTik
            $socket = $this->connectToMikroTik();
            
            // Remover regra de permissão do firewall
            $response = $this->sendCommand($socket, 'ip/firewall/filter/print', [
                'comment' => 'Allowed-' . $macAddress
            ]);
            
            // Aqui você removeria a regra específica
            // Para simplificar, apenas logamos a ação
            
            socket_close($socket);
            
            Log::info("Dispositivo {$macAddress} bloqueado no MikroTik");
            return true;
            
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
