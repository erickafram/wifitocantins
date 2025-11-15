<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\MikrotikMacReport;
use Symfony\Component\HttpFoundation\IpUtils;

class PortalController extends Controller
{
    /**
     * Exibe a página inicial do portal cativo
     */
    public function index(Request $request)
    {
        if (auth()->check() && ! in_array(auth()->user()->role, ['admin', 'manager'], true)) {
            return redirect()->route('portal.dashboard');
        }

        if ($this->shouldForceMikrotikRedirect($request)) {
            return $this->redirectToMikrotikLogin($request);
        }

        $clientInfo = $this->getClientInfo($request);
        $priceInfo = \App\Helpers\SettingsHelper::getPriceInfo();

        return view('portal.index', [
            'client_info' => $clientInfo,
            'company_name' => config('app.company_name', 'WiFi Tocantins Express'),
            'price' => $priceInfo['current_price'],
            'original_price' => $priceInfo['original_price'],
            'discount_percentage' => $priceInfo['discount_percentage'],
            'savings' => $priceInfo['savings'],
            'speed' => '100+ Mbps',
        ]);
    }

    private function shouldForceMikrotikRedirect(Request $request): bool
    {
        $mikrotikConfig = config('wifi.mikrotik', []);

        if (!($mikrotikConfig['enabled'] ?? false)) {
            return false;
        }

        if (!($mikrotikConfig['force_login_redirect'] ?? false)) {
            return false;
        }

        if ($request->has('skip_login') || $request->boolean('skip_login')) {
            return false;
        }

        if (app()->environment('local') && !($mikrotikConfig['force_login_redirect_local'] ?? false)) {
            return false;
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return false;
        }

        if ($this->requestHasMikrotikContext($request)) {
            return false;
        }

        if ($request->session()->get('mikrotik_context_verified')) {
            return false;
        }

        if (!$this->ipMatchesHotspotSubnets($request->ip())) {
            if (!($mikrotikConfig['force_login_redirect_outside_hotspot'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function redirectToMikrotikLogin(Request $request)
    {
        $loginUrl = config('wifi.mikrotik.login_url', 'http://login.tocantinswifi.local/login');

        $portalUrl = config('wifi.server_url', config('app.url'));
        $desiredUrl = $request->fullUrl();
        $destination = $portalUrl ?: $desiredUrl;

        $query = [
            'dst' => $destination,
            'return_url' => $desiredUrl,
            'from_portal' => 1,
        ];

        if ($request->has('device')) {
            $query['device'] = $request->get('device');
        }

        Log::info('🔁 Redirecionando usuário para login do MikroTik para capturar MAC/IP', [
            'login_url' => $loginUrl,
            'query' => $query,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $glue = Str::contains($loginUrl, '?') ? '&' : '?';

        return redirect()->away($loginUrl . $glue . http_build_query($query));
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

                if ($this->isLikelyMockMac($cleanMac)) {
                    Log::warning('🚨 MAC virtual/mock reportado - continuando busca', [
                        'mac_virtual' => $cleanMac,
                        'ip_externo' => $ip,
                        'ip_interno' => $internalIp,
                    ]);
                } else {
                    Log::info('🚀 MAC REAL obtido via REPORT do MikroTik', [
                        'mac' => $cleanMac,
                        'ip_externo' => $ip,
                        'ip_interno' => $internalIp,
                        'reportado_em' => $reportedMac->reported_at?->format('Y-m-d H:i:s')
                    ]);

                    $this->markMikrotikContextVerified($request);

                    return $cleanMac;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao consultar MACs reportados', ['error' => $e->getMessage()]);
        }

        // 1. PRIORIDADE: MAC VIA PARÂMETROS URL (MikroTik redirect)
        // 1. PRIORIDADE: MAC VIA PARÂMETROS URL (MikroTik redirect) - FILTRAR MOCKS
        $macViaUrl = $request->get('mac') ?: 
                    $request->get('mikrotik_mac') ?: 
                    $request->get('client_mac');

        if ($macViaUrl && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macViaUrl)) {
            $cleanMac = strtoupper(str_replace('-', ':', $macViaUrl));

            if ($this->isLikelyMockMac($cleanMac)) {
                Log::warning('🚨 MAC virtual/mock via URL - ignorado', [
                    'mac_virtual' => $cleanMac,
                    'ip' => $ip,
                ]);
            } else {
                Log::info('🎯 MAC REAL capturado via URL do MikroTik', [
                    'mac' => $cleanMac,
                    'ip' => $ip
                ]);

                $this->markMikrotikContextVerified($request);

                return $cleanMac;
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

            if ($this->isLikelyMockMac($cleanMac)) {
                Log::warning('🚨 MAC virtual/mock recebido via header MikroTik', [
                    'mac_virtual' => $cleanMac,
                    'ip' => $ip,
                ]);
            } else {
                Log::info('✅ MAC REAL obtido via header MikroTik', ['mac' => $cleanMac, 'ip' => $ip]);

                $this->markMikrotikContextVerified($request);

                return $cleanMac;
            }
        }

        // 3. TENTAR CONSULTAR DIRETAMENTE NO MIKROTIK POR IP
        $macFromMikrotik = $this->queryMacByIpFromMikrotik($ip);
        if ($macFromMikrotik && $macFromMikrotik !== null) {
            if ($this->isLikelyMockMac($macFromMikrotik)) {
                Log::warning('🚨 MAC virtual/mock retornado pela consulta ARP MikroTik', [
                    'mac_virtual' => $macFromMikrotik,
                    'ip' => $ip,
                ]);
            } else {
                Log::info('✅ MAC REAL obtido consultando MikroTik ARP', ['mac' => $macFromMikrotik, 'ip' => $ip]);

                $this->markMikrotikContextVerified($request);

                return strtoupper($macFromMikrotik);
            }
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
     * Extrai IP interno do hotspot dos headers (10.10.10.x)
     */
    private function getInternalIpFromHeaders(Request $request)
    {
        // O MikroTik envia o IP interno via X-Forwarded-For
        $forwardedFor = $request->header('X-Forwarded-For');

        if ($forwardedFor) {
            $ips = array_map('trim', explode(',', $forwardedFor));

            foreach ($ips as $candidateIp) {
                if ($this->ipMatchesHotspotSubnets($candidateIp)) {
                    return $candidateIp;
                }
            }
        }

        // Fallback: verificar se o IP atual já é interno
        $currentIp = $request->ip();
        if ($this->ipMatchesHotspotSubnets($currentIp)) {
            return $currentIp;
        }

        // Se não encontrou IP interno, retornar o IP atual
        return $currentIp;
    }

    private function requestHasMikrotikContext(Request $request): bool
    {
        if ($request->session()->get('mikrotik_context_verified')) {
            return true;
        }

        $macParams = array_filter([
            $request->get('mac'),
            $request->get('mikrotik_mac'),
            $request->get('client_mac'),
        ]);

        foreach ($macParams as $macCandidate) {
            $normalized = strtoupper(str_replace('-', ':', (string) $macCandidate));
            if (preg_match('/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/', $normalized) && !$this->isLikelyMockMac($normalized)) {
                return true;
            }
        }

        if ($request->boolean('captive') || $request->boolean('from_login') || $request->boolean('from_router')) {
            return true;
        }

        $source = Str::lower((string) $request->query('source', ''));
        if (in_array($source, ['mikrotik', 'captive-portal', 'hotspot'], true)) {
            return true;
        }

        $headers = [
            $request->header('X-Real-MAC'),
            $request->header('X-Mikrotik-MAC'),
            $request->header('X-Client-MAC'),
            $request->header('HTTP_X_REAL_MAC'),
            $request->header('HTTP_X_MIKROTIK_MAC'),
        ];

        foreach ($headers as $headerMac) {
            if ($headerMac && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $headerMac)) {
                $normalized = strtoupper(str_replace('-', ':', $headerMac));
                if (!$this->isLikelyMockMac($normalized)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function ipMatchesHotspotSubnets(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        $subnets = config('wifi.mikrotik.hotspot_subnets', []);

        foreach ($subnets as $subnet) {
            if (IpUtils::checkIp($ip, $subnet)) {
                return true;
            }
        }

        return false;
    }

    private function markMikrotikContextVerified(Request $request): void
    {
        $request->session()->put('mikrotik_context_verified', true);
    }

    private function isLikelyMockMac(string $mac): bool
    {
        return (bool) preg_match('/^(02:|00:00:00|FF:FF:FF)/i', $mac);
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
     * Valida e ativa voucher de motorista
     */
    public function validateVoucher(Request $request)
    {
        try {
            $request->validate([
                'voucher_code' => 'required|string',
            ]);

            $voucherCode = strtoupper(trim($request->voucher_code));
            
            // Busca voucher
            $voucher = \App\Models\Voucher::where('code', $voucherCode)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher inválido. Verifique o código e tente novamente.'
                ], 404);
            }

            // Valida voucher
            if (!$voucher->isValid()) {
                $reason = !$voucher->is_active ? 'Voucher desativado' : 
                         ($voucher->expires_at && $voucher->expires_at->isPast() ? 'Voucher expirado' : 
                         'Limite de horas diárias atingido');
                
                return response()->json([
                    'success' => false,
                    'message' => $reason
                ], 400);
            }

            // Obtém informações do cliente
            $clientInfo = $this->getClientInfo($request);
            $macAddress = $clientInfo['mac_address'];
            $ipAddress = $clientInfo['ip_address'];

            Log::info('🎫 Validando voucher de motorista', [
                'voucher' => $voucherCode,
                'driver' => $voucher->driver_name,
                'mac' => $macAddress,
                'ip' => $ipAddress,
                'type' => $voucher->voucher_type,
                'daily_hours' => $voucher->daily_hours,
                'hours_used' => $voucher->daily_hours_used,
            ]);

            // Calcular tempo de expiração baseado nas horas do voucher
            $hoursAvailable = $voucher->getRemainingHoursToday();
            $expiresAt = now()->addHours($hoursAvailable);

            // Para vouchers limitados, nunca passar de hoje às 23:59
            if ($voucher->voucher_type === 'limited') {
                $endOfDay = now()->endOfDay();
                if ($expiresAt->gt($endOfDay)) {
                    $expiresAt = $endOfDay;
                }
            }

            // Cria ou atualiza usuário com os campos do voucher
            $user = User::updateOrCreate(
                ['mac_address' => $macAddress],
                [
                    'name' => $voucher->driver_name,
                    'ip_address' => $ipAddress,
                    'device_name' => $clientInfo['device_type'],
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => $expiresAt,
                    'voucher_id' => $voucher->id,
                    'voucher_activated_at' => now(),
                    'voucher_last_connection' => now(),
                    'voucher_daily_minutes_used' => 0,
                ]
            );

            // Registra uso do voucher (apenas marca como usado, não incrementa horas)
            $voucher->recordUsage($hoursAvailable);

            // Registrar MAC na tabela Mikrotik
            \App\Models\MikrotikMacReport::updateOrCreate(
                [
                    'ip_address' => $ipAddress,
                    'mac_address' => $macAddress,
                ],
                [
                    'transaction_id' => 'VOUCHER_' . $user->id,
                    'mikrotik_ip' => null,
                    'reported_at' => now(),
                ]
            );

            // Libera acesso no Mikrotik
            $this->liberarAcessoMikrotik($macAddress, $ipAddress, $hoursAvailable);

            // Cria sessão WiFi
            $session = \App\Models\Session::create([
                'user_id' => $user->id,
                'payment_id' => null,
                'started_at' => now(),
                'session_status' => 'active'
            ]);

            Log::info('✅ Voucher validado e acesso liberado', [
                'voucher' => $voucherCode,
                'driver' => $voucher->driver_name,
                'hours_granted' => $hoursAvailable,
                'expires_at' => $user->expires_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bem-vindo, {$voucher->driver_name}! Acesso liberado.",
                'driver_name' => $voucher->driver_name,
                'hours_granted' => $hoursAvailable,
                'voucher_type' => $voucher->voucher_type,
                'expires_at' => $user->expires_at->format('Y-m-d H:i:s'),
                'remaining_hours_today' => $voucher->getRemainingHoursToday(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Código do voucher é obrigatório.'
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao validar voucher', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar voucher. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Libera acesso no Mikrotik (reutiliza lógica existente)
     */
    private function liberarAcessoMikrotik($macAddress, $ipAddress, $hours = 24)
    {
        try {
            // Buscar usuário pelo MAC
            $user = User::where('mac_address', $macAddress)->first();
            
            if (!$user) {
                Log::warning('⚠️ Usuário não encontrado para liberação Mikrotik', [
                    'mac' => $macAddress
                ]);
                return false;
            }

            // Tentar liberar via webhook service primeiro
            if (class_exists('\App\Services\MikrotikWebhookService')) {
                $webhookService = new \App\Services\MikrotikWebhookService;
                $liberado = $webhookService->liberarMacAddress($macAddress);
                
                if ($liberado) {
                    Log::info('🎉 Acesso liberado no Mikrotik via webhook (Voucher)', [
                        'user_id' => $user->id,
                        'mac' => $macAddress,
                        'hours' => $hours
                    ]);
                    return true;
                }
            }

            // Fallback: tentar controller MikrotikLiberacao
            if (class_exists('\App\Http\Controllers\MikrotikLiberacaoController')) {
                $mikrotikController = new \App\Http\Controllers\MikrotikLiberacaoController();
                $resultado = $mikrotikController->liberarAcessoImediato($user->id);
                
                if ($resultado) {
                    Log::info('✅ Acesso liberado no Mikrotik via controller (Voucher)', [
                        'user_id' => $user->id,
                        'mac' => $macAddress,
                        'hours' => $hours
                    ]);
                    return true;
                }
            }

            Log::info('ℹ️ Liberação será feita via sync automático do Mikrotik', [
                'mac' => $macAddress,
                'note' => 'Será liberado no próximo sync (10s)'
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao tentar liberar no Mikrotik, mas acesso será liberado no próximo sync', [
                'mac' => $macAddress,
                'error' => $e->getMessage()
            ]);
            // Não lançar exceção - o acesso será liberado via sync automático
            return true;
        }
    }
}