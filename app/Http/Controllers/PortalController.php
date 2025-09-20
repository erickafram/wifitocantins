<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class PortalController extends Controller
{
    /**
     * Exibe a página inicial do portal cativo
     */
    public function index(Request $request)
    {
        // Detectar MAC address e outras informações do dispositivo
        $clientInfo = $this->getClientInfo($request);
        
        return view('portal.index', [
            'client_info' => $clientInfo,
            'company_name' => config('app.company_name', 'WiFi Tocantins Express'),
            'price' => config('wifi.pricing.default_price', 0.05),
            'speed' => '100+ Mbps'
        ]);
    }

    /**
     * API para detectar dispositivo
     */
    public function detectDevice(Request $request)
    {
        $clientInfo = $this->getClientInfo($request);
        
        return response()->json([
            'success' => true,
            'mac_address' => $clientInfo['mac_address'],
            'ip_address' => $clientInfo['ip_address'],
            'user_agent' => $clientInfo['user_agent']
        ]);
    }

    /**
     * Obtém informações do cliente/dispositivo
     */
    private function getClientInfo(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // PRODUÇÃO: Em hotspot MikroTik, o MAC vem via headers especiais
        $macAddress = $this->getMacAddressFromMikrotik($request, $ip);
        
        return [
            'ip_address' => $ip,
            'mac_address' => $macAddress,
            'user_agent' => $userAgent,
            'device_type' => $this->detectDeviceType($userAgent)
        ];
    }

    /**
     * Obtém MAC address real do MikroTik ou gera baseado no IP
     */
    private function getMacAddressFromMikrotik(Request $request, $ip)
    {
        // 1. TENTAR OBTER MAC DE HEADERS DO MIKROTIK
        $mikrotikMac = $request->header('X-Real-MAC') ?: 
                      $request->header('X-Mikrotik-MAC') ?: 
                      $request->header('X-Client-MAC');
        
        if ($mikrotikMac && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mikrotikMac)) {
            Log::info('MAC obtido do MikroTik via header', ['mac' => $mikrotikMac, 'ip' => $ip]);
            return strtoupper(str_replace('-', ':', $mikrotikMac));
        }

        // 2. TENTAR CONSULTAR DIRETAMENTE NO MIKROTIK POR IP
        $macFromMikrotik = $this->queryMacByIpFromMikrotik($ip);
        if ($macFromMikrotik) {
            Log::info('MAC obtido consultando MikroTik por IP', ['mac' => $macFromMikrotik, 'ip' => $ip]);
            return $macFromMikrotik;
        }

        // 3. FALLBACK: GERAR MAC CONSISTENTE BASEADO NO IP (PARA DESENVOLVIMENTO)
        $macAddress = $this->generateMacFromIp($ip);
        Log::info('MAC gerado como fallback baseado no IP', ['mac' => $macAddress, 'ip' => $ip]);
        
        return $macAddress;
    }

    /**
     * Consulta MAC address no MikroTik baseado no IP
     */
    private function queryMacByIpFromMikrotik($ip)
    {
        try {
            if (!config('wifi.mikrotik.enabled', false)) {
                return null;
            }

            // Consultar ARP table do MikroTik para obter MAC por IP
            $mikrotikController = new \App\Http\Controllers\MikrotikController();
            $macAddress = $mikrotikController->getMacByIp($ip);
            
            return $macAddress;
        } catch (\Exception $e) {
            Log::error('Erro ao consultar MAC no MikroTik', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Gera MAC fictício baseado no IP (para desenvolvimento)
     */
    private function generateMacFromIp($ip)
    {
        // Converter IP em MAC fictício para testes
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $mac = sprintf(
                '02:%02x:%02x:%02x:%02x:%02x',
                $parts[0] % 256,
                $parts[1] % 256,
                $parts[2] % 256,
                $parts[3] % 256,
                rand(0, 255)
            );
            return strtoupper($mac);
        }
        
        // Fallback para MAC aleatório
        return sprintf(
            '02:%02X:%02X:%02X:%02X:%02X',
            rand(0, 255),
            rand(0, 255),
            rand(0, 255),
            rand(0, 255),
            rand(0, 255)
        );
    }

    /**
     * Detecta tipo do dispositivo baseado no User-Agent
     */
    private function detectDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Processa acesso grátis via Instagram
     */
    public function instagramFreeAccess(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'source' => 'required|string'
        ]);

        try {
            // Verificar rate limiting por IP (máximo 3 tentativas por hora)
            $ipAttempts = \App\Models\Session::where('started_at', '>', now()->subHour())
                ->whereHas('user', function($query) use ($request) {
                    $query->where('ip_address', $request->ip());
                })
                ->whereNull('payment_id')
                ->count();

            if ($ipAttempts >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Muitas tentativas deste local. Aguarde 1 hora ou faça um pagamento.'
                ], 429);
            }

            // Verificar se já usou o acesso grátis recentemente (evitar spam)
            $user = User::where('mac_address', $request->mac_address)->first();
            
            if ($user) {
                $lastFreeAccess = $user->sessions()
                    ->where('session_status', 'active')
                    ->where('started_at', '>', now()->subHours(6))
                    ->whereNull('payment_id') // Sessões gratuitas não têm payment_id
                    ->first();

                if ($lastFreeAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você já usou o acesso grátis recentemente. Aguarde 6 horas ou faça um pagamento.'
                    ], 400);
                }
            }

            // Buscar ou criar usuário
            if (!$user) {
                $user = User::create([
                    'mac_address' => $request->mac_address,
                    'ip_address' => $request->ip(),
                    'device_name' => 'Instagram Free User',
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addMinutes(5) // 5 minutos grátis
                ]);
            } else {
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addMinutes(5)
                ]);
            }

            // Criar sessão gratuita
            $session = \App\Models\Session::create([
                'user_id' => $user->id,
                'payment_id' => null, // Sem pagamento - grátis
                'started_at' => now(),
                'session_status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Acesso grátis ativado por 5 minutos!',
                'session_id' => $session->id,
                'expires_at' => $user->expires_at->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no acesso grátis Instagram: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }
}
