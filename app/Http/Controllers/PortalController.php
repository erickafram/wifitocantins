<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\MikrotikMacReport;

class PortalController extends Controller
{
    /**
     * Exibe a página inicial do portal cativo
     */
    public function index(Request $request)
    {
        if (!$this->requestHasMikrotikContext($request)) {
            //return redirect()->away('http://login.tocantinswifi.local/login?dst=http%3A%2F%2Fwww.msftconnecttest.com%2Fredirect');
        }
 
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

        if (!$this->requestHasMikrotikContext($request) && !$clientInfo['mac_address']) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo não identificado. Conecte-se pela página inicial da rede Wi-Fi.'
            ], 400);
        }
 
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
        Log::info('🔍 INICIANDO DETECÇÃO DE MAC', [
            'ip' => $ip,
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all()
        ]);

        // 0. 🔥 PRIORIDADE MÁXIMA: CONSULTAR MACS REPORTADOS PELO MIKROTIK
        try {
            // Converter IP externo para IP interno do hotspot
            $internalIp = $this->getInternalIpFromHeaders($request);
            
            // Verificar se temos MAC reportado para este IP (interno ou externo)
            $reportedMac = MikrotikMacReport::getLatestMacForIp($internalIp);
            if (!$reportedMac && $internalIp !== $ip) {
                $reportedMac = MikrotikMacReport::getLatestMacForIp($ip);
            }
            
            if ($reportedMac) {
                $cleanMac = strtoupper($reportedMac->mac_address);
                
                // ✅ VERIFICAR SE É MAC REAL (não começa com 02: ou virtual)
                if (!preg_match('/^(02:|00:00:00|ff:ff:ff)/i', $cleanMac)) {
                    Log::info('🚀 MAC REAL WiFi obtido via REPORT do MikroTik', [
                        'mac' => $cleanMac, 
                        'ip_externo' => $ip,
                        'ip_interno' => $internalIp,
                        'reportado_em' => $reportedMac->reported_at->format('Y-m-d H:i:s'),
                        'tipo' => 'MAC_REAL_WIFI'
                    ]);
                    return $cleanMac;
                } else {
                    Log::warning('🚨 MAC virtual/mock reportado - continuando busca', [
                        'mac_virtual' => $cleanMac
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao consultar MACs reportados', ['error' => $e->getMessage()]);
        }

        // 1. PRIORIDADE: MAC VIA PARÂMETROS URL (MikroTik redirect) - FILTRAR MOCKS
        $macViaUrl = $request->get('mac') ?: 
                    $request->get('mikrotik_mac') ?: 
                    $request->get('client_mac');

        if ($macViaUrl && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macViaUrl)) {
            $cleanMac = strtoupper(str_replace('-', ':', $macViaUrl));
            
            // ✅ VERIFICAR SE É MAC REAL (não virtual/mock)
            if (!preg_match('/^(02:|00:00:00|ff:ff:ff)/i', $cleanMac)) {
                Log::info('🎯 MAC REAL WiFi capturado via URL do MikroTik', [
                    'mac' => $cleanMac, 
                    'ip' => $ip,
                    'tipo' => 'MAC_REAL_URL'
                ]);
                return $cleanMac;
            } else {
                Log::warning('🚨 MAC virtual/mock via URL - ignorado', [
                    'mac_virtual' => $cleanMac
                ]);
            }
        }

        // 2. TENTAR OBTER MAC DE HEADERS DO MIKROTIK
        $mikrotikMac = $request->header('X-Real-MAC') ?: 
                      $request->header('X-Mikrotik-MAC') ?: 
                      $request->header('X-Client-MAC') ?:
                      $request->header('HTTP_X_REAL_MAC') ?:
                      $request->header('HTTP_X_MIKROTIK_MAC');
        
        if ($mikrotikMac && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mikrotikMac)) {
            $cleanMac = strtoupper(str_replace('-', ':', $mikrotikMac));
            Log::info('✅ MAC REAL obtido via header MikroTik', ['mac' => $cleanMac, 'ip' => $ip]);
            return $cleanMac;
        }

        // 3. 🚀 CONSULTAR DIRETAMENTE NO MIKROTIK POR IP (MÉTODO MELHORADO)
        $macFromMikrotik = $this->queryMacByIpFromMikrotik($ip);
        if ($macFromMikrotik && $macFromMikrotik !== null) {
            Log::info('✅ MAC REAL obtido consultando MikroTik ARP', ['mac' => $macFromMikrotik, 'ip' => $ip]);
            return $macFromMikrotik;
        }

        // 4. ÚLTIMO RECURSO: GERAR MAC CONSISTENTE BASEADO NO IP 
        $macAddress = $this->generateMacFromIp($ip);
        Log::warning('⚠️ MAC MOCK gerado como fallback', [
            'mac_mock' => $macAddress, 
            'ip' => $ip,
            'nota' => 'MikroTik não enviou MAC real nem respondeu consulta ARP'
        ]);
        
        return $macAddress;
    }

    /**
     * Consulta MAC address no MikroTik baseado no IP
     */
    private function queryMacByIpFromMikrotik($ip)
    {
        try {
            // 🚀 NOVA ABORDAGEM: Consultar via API endpoint específico do MikroTik
            Log::info('🔍 Consultando MAC no MikroTik via API', ['ip' => $ip]);
            
            // Consultar endpoint especial que criamos no MikroTik
            $mikrotikHost = '10.10.10.1'; // IP interno do MikroTik
            $token = config('wifi.mikrotik.sync_token', 'mikrotik-sync-2024');
            
            // URL para consultar DHCP leases por IP
            $url = "http://{$mikrotikHost}/api/get-mac-by-ip?ip={$ip}&token={$token}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'method' => 'GET'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['mac_address']) && $data['mac_address']) {
                    $cleanMac = strtoupper(str_replace('-', ':', $data['mac_address']));
                    
                    // Verificar se é MAC real (não mock)
                    if (!preg_match('/^(02:|00:00:00|ff:ff:ff)/i', $cleanMac)) {
                        Log::info('✅ MAC REAL obtido via API MikroTik', [
                            'mac' => $cleanMac, 
                            'ip' => $ip,
                            'method' => 'dhcp_lease_api'
                        ]);
                        return $cleanMac;
                    }
                }
            }
            
            // 🔄 FALLBACK: Tentar método direto via SSH (se disponível)
            return $this->queryMacViaSshFallback($ip);
            
        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao consultar MAC no MikroTik via API', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 🆘 MÉTODO FALLBACK: Consultar MAC via logs recentes do MikroTik
     */
    private function queryMacViaSshFallback($ip)
    {
        try {
            // Consultar MACs reportados recentemente para este IP
            $recentMac = MikrotikMacReport::where('ip_address', $ip)
                                        ->where('reported_at', '>', now()->subMinutes(10))
                                        ->orderBy('reported_at', 'desc')
                                        ->first();
            
            if ($recentMac && !preg_match('/^(02:|00:00:00|ff:ff:ff)/i', $recentMac->mac_address)) {
                Log::info('📋 MAC REAL encontrado em reports recentes', [
                    'mac' => $recentMac->mac_address,
                    'ip' => $ip,
                    'reportado_em' => $recentMac->reported_at->format('Y-m-d H:i:s')
                ]);
                return strtoupper($recentMac->mac_address);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Erro no fallback de consulta MAC', ['error' => $e->getMessage()]);
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
     * Extrai IP interno do hotspot dos headers (10.10.10.x)
     */
    private function getInternalIpFromHeaders(Request $request)
    {
        // O MikroTik envia o IP interno via X-Forwarded-For
        $forwardedFor = $request->header('X-Forwarded-For');
        
        if ($forwardedFor) {
            $ips = array_map('trim', explode(',', $forwardedFor));
            
            // Procurar por IP da rede do hotspot (10.10.10.x)
            foreach ($ips as $ip) {
                if (preg_match('/^10\.10\.10\.\d+$/', $ip)) {
                    return $ip;
                }
            }
        }
        
        // Fallback: verificar se o IP atual já é interno
        $currentIp = $request->ip();
        if (preg_match('/^10\.10\.10\.\d+$/', $currentIp)) {
            return $currentIp;
        }
        
        // Se não encontrou IP interno, retornar o IP atual
        return $currentIp;
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

    /**
     * Verifica se a requisição vem de um contexto do MikroTik
     */
    private function requestHasMikrotikContext(Request $request): bool
    {
        $hasMacParam = $request->hasAny(['mac', 'mikrotik_mac', 'client_mac']);
        $hasLoginFlag = $request->boolean('from_login');
        $hasHeaders = $request->headers->has('X-Mikrotik-MAC') || $request->headers->has('X-Client-MAC');

        return $hasMacParam || $hasLoginFlag || $hasHeaders;
    }
}
