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
        $this->mikrotikUser = config('wifi.mikrotik.username', 'api-tocantins');
        $this->mikrotikPass = config('wifi.mikrotik.password', 'TocantinsWiFi2024!');
        $this->mikrotikPort = config('wifi.mikrotik.port', 8728);
    }

    /**
     * Conecta à API do MikroTik usando RouterOS API
     */
    private function connectToMikroTik()
    {
        if (!config('wifi.mikrotik.api_enabled', true)) {
            throw new \Exception('API MikroTik desabilitada');
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (!$socket) {
            throw new \Exception('Erro ao criar socket: ' . socket_strerror(socket_last_error()));
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);

        $result = socket_connect($socket, $this->mikrotikHost, $this->mikrotikPort);
        
        if (!$result) {
            socket_close($socket);
            throw new \Exception('Erro ao conectar ao MikroTik: ' . socket_strerror(socket_last_error()));
        }

        // Fazer login na API
        $this->loginToAPI($socket);

        return $socket;
    }

    /**
     * Faz login na API do RouterOS
     */
    private function loginToAPI($socket)
    {
        // Enviar comando de login
        $this->writeCommand($socket, '/login');
        $response = $this->readResponse($socket);

        if (!isset($response[0]) || $response[0] !== '!done') {
            throw new \Exception('Erro no handshake de login');
        }

        // Extrair challenge
        $challenge = '';
        foreach ($response as $line) {
            if (strpos($line, '=ret=') === 0) {
                $challenge = substr($line, 5);
                break;
            }
        }

        if (empty($challenge)) {
            throw new \Exception('Challenge não recebido');
        }

        // Calcular resposta MD5
        $md5 = md5(chr(0) . $this->mikrotikPass . pack('H*', $challenge));
        
        // Enviar credenciais
        $this->writeCommand($socket, '/login', [
            'name' => $this->mikrotikUser,
            'response' => '00' . $md5
        ]);

        $loginResponse = $this->readResponse($socket);
        
        if (!isset($loginResponse[0]) || $loginResponse[0] !== '!done') {
            throw new \Exception('Falha na autenticação: ' . implode(' ', $loginResponse));
        }
    }

    /**
     * Escreve comando para a API RouterOS
     */
    private function writeCommand($socket, $command, $attributes = [])
    {
        $data = $this->encodeLength(strlen($command)) . $command;
        
        foreach ($attributes as $key => $value) {
            $param = "=$key=$value";
            $data .= $this->encodeLength(strlen($param)) . $param;
        }
        
        $data .= $this->encodeLength(0); // End of command
        
        socket_write($socket, $data);
    }

    /**
     * Lê resposta da API RouterOS
     */
    private function readResponse($socket)
    {
        $response = [];
        
        while (true) {
            $length = $this->readLength($socket);
            if ($length === false) break;
            
            if ($length === 0) break;
            
            $data = socket_read($socket, $length);
            if ($data === false) break;
            
            $response[] = $data;
        }
        
        return $response;
    }

    /**
     * Codifica comprimento para protocolo RouterOS
     */
    private function encodeLength($length)
    {
        if ($length < 0x80) {
            return chr($length);
        } elseif ($length < 0x4000) {
            return pack('n', $length | 0x8000);
        } elseif ($length < 0x200000) {
            return pack('N', $length | 0xC00000) . substr(pack('N', $length), 1);
        } elseif ($length < 0x10000000) {
            return pack('N', $length | 0xE0000000);
        } else {
            return chr(0xF0) . pack('N', $length);
        }
    }

    /**
     * Decodifica comprimento do protocolo RouterOS
     */
    private function readLength($socket)
    {
        $byte = socket_read($socket, 1);
        if ($byte === false) return false;
        
        $firstByte = ord($byte);
        
        if ($firstByte < 0x80) {
            return $firstByte;
        } elseif ($firstByte < 0xC0) {
            $byte2 = socket_read($socket, 1);
            return (($firstByte & 0x7F) << 8) + ord($byte2);
        } elseif ($firstByte < 0xE0) {
            $bytes = socket_read($socket, 2);
            return (($firstByte & 0x1F) << 16) + (ord($bytes[0]) << 8) + ord($bytes[1]);
        } elseif ($firstByte < 0xF0) {
            $bytes = socket_read($socket, 3);
            return (($firstByte & 0x0F) << 24) + (ord($bytes[0]) << 16) + (ord($bytes[1]) << 8) + ord($bytes[2]);
        } else {
            $bytes = socket_read($socket, 4);
            return (ord($bytes[0]) << 24) + (ord($bytes[1]) << 16) + (ord($bytes[2]) << 8) + ord($bytes[3]);
        }
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

            // Consultar status real no MikroTik
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

            return $this->allowDeviceByUser($user);

        } catch (\Exception $e) {
            Log::error('Erro ao liberar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Libera acesso para um usuário específico
     */
    public function allowDeviceByUser(User $user)
    {
        try {
            // Liberar no MikroTik
            $mikrotikResult = $this->allowDeviceInMikrotik($user->mac_address);

            if ($mikrotikResult) {
                // Atualizar status no banco
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now()
                ]);

                // Registrar dispositivo
                $this->registerDevice($user->mac_address, null);

                Log::info("Dispositivo {$user->mac_address} liberado com sucesso");

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
        try {
            $socket = $this->connectToMikroTik();
            
            // Consultar usuários ativos do hotspot
            $this->writeCommand($socket, '/ip/hotspot/active/print', [
                '?mac-address' => $macAddress
            ]);
            
            $response = $this->readResponse($socket);
            socket_close($socket);
            
            // Processar resposta
            $isOnline = false;
            $bytesIn = 0;
            $bytesOut = 0;
            $sessionTime = 0;
            
            foreach ($response as $line) {
                if (strpos($line, '!re') === 0) {
                    $isOnline = true;
                } elseif (strpos($line, '=bytes-in=') === 0) {
                    $bytesIn = intval(substr($line, 10));
                } elseif (strpos($line, '=bytes-out=') === 0) {
                    $bytesOut = intval(substr($line, 11));
                } elseif (strpos($line, '=uptime=') === 0) {
                    $sessionTime = $this->parseUptime(substr($line, 8));
                }
            }
        
        return [
                'online' => $isOnline,
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'session_time' => $sessionTime,
                'idle_time' => 0
            ];
            
        } catch (\Exception $e) {
            Log::error("Erro ao consultar status do dispositivo {$macAddress}: " . $e->getMessage());
            return [
                'online' => false,
                'bytes_in' => 0,
                'bytes_out' => 0,
                'session_time' => 0,
                'idle_time' => 0
        ];
        }
    }

    /**
     * Obtém MAC address consultando ARP table do MikroTik por IP
     */
    public function getMacByIp($ipAddress)
    {
        try {
            if (!config('wifi.mikrotik.enabled', false)) {
                return null;
            }

            $socket = $this->connectToMikroTik();
            
            // Consultar ARP table
            $this->writeCommand($socket, '/ip/arp/print', [
                '?address' => $ipAddress
            ]);
            
            $response = $this->readResponse($socket);
            socket_close($socket);
            
            // Processar resposta para encontrar MAC
            foreach ($response as $line) {
                if (strpos($line, '=mac-address=') === 0) {
                    $macAddress = substr($line, 13);
                    if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress)) {
                        return strtoupper(str_replace('-', ':', $macAddress));
                    }
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Erro ao consultar MAC por IP {$ipAddress}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Libera dispositivo no MikroTik criando usuário do hotspot
     */
    private function allowDeviceInMikrotik($macAddress)
    {
        try {
            if (!config('wifi.mikrotik.api_enabled', true)) {
                Log::info("API MikroTik desabilitada - simulando liberação para {$macAddress}");
                return true;
            }

            Log::info("Liberando dispositivo {$macAddress} no MikroTik");
            
            $socket = $this->connectToMikroTik();
            
            // Primeiro, verificar se já existe usuário
            $this->writeCommand($socket, '/ip/hotspot/user/print', [
                '?name' => $macAddress
            ]);
            
            $response = $this->readResponse($socket);
            
            $userExists = false;
            foreach ($response as $line) {
                if (strpos($line, '!re') === 0) {
                    $userExists = true;
                    break;
                }
            }
            
            if (!$userExists) {
                // Criar usuário do hotspot
                $this->writeCommand($socket, '/ip/hotspot/user/add', [
                    'name' => $macAddress,
                    'mac-address' => $macAddress,
                    'profile' => 'default',
                    'comment' => 'Auto-created for paid access'
                ]);
                
                $addResponse = $this->readResponse($socket);
                
                // Verificar se foi criado com sucesso
                $success = false;
                foreach ($addResponse as $line) {
                    if (strpos($line, '!done') === 0) {
                        $success = true;
                        break;
                    }
                }
                
                if (!$success) {
                    Log::error("Falha ao criar usuário hotspot para {$macAddress}");
                    socket_close($socket);
                    return false;
                }
            } else {
                // Usuário já existe, apenas habilitá-lo
                $this->writeCommand($socket, '/ip/hotspot/user/set', [
                    'name' => $macAddress,
                    'disabled' => 'no'
                ]);
                
                $this->readResponse($socket);
            }
            
            socket_close($socket);
            
            Log::info("Dispositivo {$macAddress} liberado no MikroTik com sucesso");
            return true;
            
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
            if (!config('wifi.mikrotik.api_enabled', true)) {
                Log::info("API MikroTik desabilitada - simulando bloqueio para {$macAddress}");
                return true;
            }

            Log::info("Bloqueando dispositivo {$macAddress} no MikroTik");
            
            $socket = $this->connectToMikroTik();
            
            // Desabilitar usuário do hotspot
            $this->writeCommand($socket, '/ip/hotspot/user/set', [
                'name' => $macAddress,
                'disabled' => 'yes'
            ]);
            
            $response = $this->readResponse($socket);
            
            // Desconectar usuário ativo se estiver online
            $this->writeCommand($socket, '/ip/hotspot/active/remove', [
                'mac-address' => $macAddress
            ]);
            
            $this->readResponse($socket);
            
            socket_close($socket);
            
            Log::info("Dispositivo {$macAddress} bloqueado no MikroTik");
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
        try {
            $socket = $this->connectToMikroTik();
            
            $this->writeCommand($socket, '/ip/hotspot/active/print', [
                '?mac-address' => $macAddress
            ]);
            
            $response = $this->readResponse($socket);
            socket_close($socket);
            
            $bytesIn = 0;
            $bytesOut = 0;
            $sessionTime = 0;
            
            foreach ($response as $line) {
                if (strpos($line, '=bytes-in=') === 0) {
                    $bytesIn = intval(substr($line, 10));
                } elseif (strpos($line, '=bytes-out=') === 0) {
                    $bytesOut = intval(substr($line, 11));
                } elseif (strpos($line, '=uptime=') === 0) {
                    $sessionTime = $this->parseUptime(substr($line, 8));
                }
            }
            
            return [
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'download_speed' => $bytesIn > 0 ? round($bytesIn / max($sessionTime, 1) * 8 / 1000) : 0, // kbps
                'upload_speed' => $bytesOut > 0 ? round($bytesOut / max($sessionTime, 1) * 8 / 1000) : 0, // kbps
                'session_time' => $sessionTime
            ];
            
        } catch (\Exception $e) {
            Log::error("Erro ao obter dados de uso para {$macAddress}: " . $e->getMessage());
        return [
                'bytes_in' => 0,
                'bytes_out' => 0,
                'download_speed' => 0,
                'upload_speed' => 0,
                'session_time' => 0
            ];
        }
    }

    /**
     * Converte uptime do MikroTik para segundos
     */
    private function parseUptime($uptime)
    {
        // Formato: 1d2h3m4s ou 2h3m4s ou 3m4s ou 4s
        $seconds = 0;
        
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $seconds += intval($matches[1]) * 86400;
        }
        
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $seconds += intval($matches[1]) * 3600;
        }
        
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $seconds += intval($matches[1]) * 60;
        }
        
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds += intval($matches[1]);
        }
        
        return $seconds;
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
                'device_name' => $request ? $this->parseDeviceName($request->userAgent()) : 'Unknown Device',
                'device_type' => $request ? $this->detectDeviceType($request->userAgent()) : 'unknown',
                'user_agent' => $request ? $request->userAgent() : null,
                'first_seen' => now(),
                'last_seen' => now(),
                'total_connections' => 1
            ]);
        } else {
            $device->increment('total_connections');
            $device->update(['last_seen' => now()]);
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
