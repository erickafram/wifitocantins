<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use App\Models\MikrotikMacReport;
use App\Models\Session;
use App\Support\HotspotIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DriverVoucherController extends Controller
{
    /**
     * Exibe a página de ativação de voucher
     */
    public function showActivate(Request $request)
    {
        // Capturar IP e MAC do contexto do Mikrotik (igual usuários pagantes)
        $ipAddress = HotspotIdentity::resolveClientIp($request);
        $macAddress = $request->query('mac') ?? $request->query('mac-address');
        
        if ($macAddress) {
            $macAddress = HotspotIdentity::resolveRealMac($macAddress, $ipAddress);
        }

        // Verificar se já tem MAC na sessão
        if (!$macAddress && session('mikrotik_mac')) {
            $macAddress = session('mikrotik_mac');
        }
        if (!$ipAddress && session('mikrotik_ip')) {
            $ipAddress = session('mikrotik_ip');
        }

        // Se veio do Mikrotik, salvar na sessão
        if ($request->query('source') === 'mikrotik' && $macAddress) {
            session(['mikrotik_mac' => $macAddress]);
            session(['mikrotik_ip' => $ipAddress]);
            session(['mikrotik_context_verified' => true]);
        }

        // Se não tem MAC, redirecionar para a raiz para capturar do Mikrotik
        if (!$macAddress) {
            return redirect('/?redirect=voucher')
                ->with('error', 'Conecte-se ao Wi-Fi Tocantins Transporte para ativar o voucher.');
        }
        
        return view('portal.voucher.activate', [
            'ip_address' => $ipAddress,
            'mac_address' => $macAddress,
        ]);
    }

    /**
     * Busca voucher por CPF/documento ou código
     */
    public function searchVoucher(Request $request)
    {
        $request->validate([
            'search_term' => 'required|string|max:50',
        ]);

        // MAC e IP vêm da sessão (capturados do Mikrotik) ou do request
        $macAddress = $request->input('mac_address') ?? session('mikrotik_mac');
        $ipAddress = $request->input('ip_address') ?? session('mikrotik_ip');

        $searchTerm = trim($request->search_term);
        
        // Limpar CPF/documento (remover pontos, traços, etc)
        $cleanedTerm = preg_replace('/\D/', '', $searchTerm);
        
        // Buscar por código do voucher (formato WIFI-XXXX-XXXX)
        $voucher = Voucher::where('code', strtoupper($searchTerm))->first();
        
        // Se não encontrou por código, buscar por documento (CPF)
        if (!$voucher && strlen($cleanedTerm) >= 11) {
            // Buscar por documento exato ou parcial
            $voucher = Voucher::where('driver_document', 'LIKE', '%' . $cleanedTerm . '%')
                ->orWhere('driver_document', 'LIKE', '%' . $searchTerm . '%')
                ->first();
        }
        
        // Se ainda não encontrou, tentar buscar pelo termo original no documento
        if (!$voucher) {
            $voucher = Voucher::where('driver_document', $searchTerm)->first();
        }

        if (!$voucher) {
            return back()
                ->with('error', 'Nenhum voucher encontrado para este CPF ou código. Verifique os dados e tente novamente.')
                ->withInput();
        }

        // Verificar se o voucher está ativo
        $voucherStatus = $this->getVoucherStatus($voucher, $macAddress);

        return view('portal.voucher.activate', [
            'ip_address' => $ipAddress,
            'mac_address' => $macAddress,
            'voucher' => $voucher,
            'voucherStatus' => $voucherStatus,
            'searched' => true,
        ]);
    }

    /**
     * Obtém o status detalhado do voucher
     */
    private function getVoucherStatus(Voucher $voucher, ?string $currentMac = null): array
    {
        $status = [
            'can_activate' => true,
            'message' => null,
            'type' => 'success', // success, warning, error, info
            'time_remaining' => null,
            'next_activation' => null,
            'hours_available_today' => null,
            'is_active_session' => false,
            'active_device' => null,
        ];

        // 1. Verificar se voucher está desativado
        if (!$voucher->is_active) {
            $status['can_activate'] = false;
            $status['type'] = 'error';
            $status['message'] = 'Este voucher está desativado. Entre em contato com o administrador.';
            return $status;
        }

        // 2. Verificar se expirou
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            $status['can_activate'] = false;
            $status['type'] = 'error';
            $status['message'] = 'Este voucher expirou em ' . $voucher->expires_at->format('d/m/Y') . '.';
            return $status;
        }

        // 3. Verificar se já está em uso em outro dispositivo
        $driverPhone = $voucher->driver_phone;
        if ($driverPhone) {
            $activeUser = User::where('driver_phone', $driverPhone)
                ->where('voucher_id', $voucher->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($activeUser) {
                $timeRemaining = $activeUser->expires_at->diff(now());
                $hoursRemaining = $timeRemaining->h + ($timeRemaining->days * 24);
                $minutesRemaining = $timeRemaining->i;
                
                $status['time_remaining'] = [
                    'hours' => $hoursRemaining,
                    'minutes' => $minutesRemaining,
                    'expires_at' => $activeUser->expires_at,
                ];
                $status['is_active_session'] = true;
                $status['active_device'] = $activeUser->mac_address;

                // Se é o mesmo dispositivo, apenas informar
                if ($currentMac && strtoupper($activeUser->mac_address) === strtoupper($currentMac)) {
                    $status['can_activate'] = false;
                    $status['type'] = 'info';
                    $status['message'] = "Voucher já está ativo neste dispositivo.";
                } else {
                    // Dispositivo diferente - bloquear
                    $status['can_activate'] = false;
                    $status['type'] = 'warning';
                    $status['message'] = "Voucher em uso em outro dispositivo.\nAguarde o término da sessão atual.";
                }
                return $status;
            }
        }

        // 4. Verificar intervalo entre ativações
        if ($driverPhone) {
            $lastUsedUser = User::where('driver_phone', $driverPhone)
                ->where('voucher_id', $voucher->id)
                ->whereNotNull('voucher_activated_at')
                ->orderBy('voucher_activated_at', 'desc')
                ->first();

            if ($lastUsedUser && $lastUsedUser->voucher_activated_at) {
                $intervalRequired = (float) ($voucher->activation_interval_hours ?? 24);
                $nextAvailableTime = $lastUsedUser->voucher_activated_at->copy()->addHours($intervalRequired);
                
                // 🔧 FIX: Verificar se a próxima ativação ainda está no futuro
                if ($nextAvailableTime->isFuture()) {
                    $timeUntilNext = now()->diff($nextAvailableTime);
                    $hoursRemaining = $timeUntilNext->h + ($timeUntilNext->days * 24);
                    $minutesRemaining = $timeUntilNext->i;

                    $status['can_activate'] = false;
                    $status['type'] = 'warning';
                    $status['next_activation'] = $nextAvailableTime;
                    $status['message'] = "Aguarde o intervalo entre ativações.\nPróxima ativação: " . $nextAvailableTime->format('d/m/Y H:i');
                    return $status;
                }
                // Se nextAvailableTime já passou, o voucher pode ser ativado normalmente
            }
        }

        // 5. Verificar horas disponíveis hoje
        $hoursAvailable = $voucher->getRemainingHoursToday();
        $status['hours_available_today'] = $hoursAvailable;

        if ($voucher->voucher_type === 'limited' && $hoursAvailable <= 0) {
            $nextDay = now()->addDay()->startOfDay();
            $status['can_activate'] = false;
            $status['type'] = 'warning';
            $status['next_activation'] = $nextDay;
            $status['message'] = "Limite diário atingido.\nDisponível novamente: " . $nextDay->format('d/m/Y H:i');
            return $status;
        }

        // Tudo OK - pode ativar
        $status['message'] = 'Voucher disponível para ativação.';

        return $status;
    }

    /**
     * Ativa um voucher para o motorista
     */
    public function activate(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:20',
        ]);

        // MAC e IP vêm da sessão (capturados do Mikrotik) ou do request
        $macAddress = $request->input('mac_address') ?? session('mikrotik_mac');
        $ipAddress = $request->input('ip_address') ?? session('mikrotik_ip');

        // Validar MAC e IP
        if (!$macAddress || !$ipAddress) {
            return back()->with('error', 'Não foi possível identificar seu dispositivo. Reconecte-se ao Wi-Fi e tente novamente.');
        }

        try {
            DB::beginTransaction();

            $voucherCode = strtoupper(trim($request->voucher_code));
            $macAddress = strtoupper($macAddress);
            $ipAddress = $ipAddress;

            // 1. Buscar voucher
            $voucher = Voucher::where('code', $voucherCode)->first();

            if (!$voucher) {
                return back()->with('error', 'Voucher não encontrado. Verifique o código e tente novamente.');
            }

            // 2. Usar telefone ou documento do voucher como identificador
            $driverPhone = $voucher->driver_phone;
            $driverIdentifier = $driverPhone ?? $voucher->driver_document ?? $voucher->code;
            
            if (!$driverIdentifier) {
                return back()->with('error', 'Este voucher não possui identificador válido. Entre em contato com o administrador.');
            }

            // 3. Validar voucher
            if (!$voucher->is_active) {
                return back()->with('error', 'Este voucher está desativado. Entre em contato com o administrador.');
            }

            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return back()->with('error', 'Este voucher expirou em ' . $voucher->expires_at->format('d/m/Y') . '.');
            }

            if (!$voucher->hasHoursAvailableToday()) {
                return back()->with('error', 'Este voucher já atingiu o limite de horas para hoje. Tente novamente amanhã.');
            }

            // 4. VALIDAÇÃO DE SEGURANÇA: Verificar se o voucher já está ativo em OUTRO dispositivo
            $activeUser = User::where(function($q) use ($driverPhone, $driverIdentifier) {
                    if ($driverPhone) {
                        $q->where('driver_phone', $driverPhone);
                    } else {
                        $q->where('driver_phone', $driverIdentifier);
                    }
                })
                ->whereNotNull('voucher_id')
                ->where('voucher_id', $voucher->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($activeUser) {
                DB::rollback();
                
                // Se o MAC for diferente, bloquear
                if ($activeUser->mac_address !== $macAddress) {
                    $timeRemaining = $activeUser->expires_at->diff(now());
                    $hoursRemaining = $timeRemaining->h;
                    $minutesRemaining = $timeRemaining->i;
                    
                    return back()->with('error', 
                        "🔒 VOUCHER JÁ ESTÁ EM USO!\n\n" .
                        "Este voucher está ativo em outro dispositivo.\n" .
                        "Tempo restante: {$hoursRemaining}h {$minutesRemaining}min\n" .
                        "Dispositivo registrado: " . substr($activeUser->mac_address, -8) . "\n\n" .
                        "⚠️ Por segurança, um voucher só pode ser usado em um dispositivo por vez.\n" .
                        "Aguarde o término da sessão atual para usar em outro dispositivo."
                    );
                }
                
                // Se for o mesmo MAC, apenas avisar que já está ativo
                $timeRemaining = $activeUser->expires_at->diff(now());
                $hoursRemaining = $timeRemaining->h;
                $minutesRemaining = $timeRemaining->i;
                
                return back()->with('warning', 
                    "⚠️ Voucher já está ativo!\n\n" .
                    "Você já tem um voucher ativo no momento.\n" .
                    "Tempo restante: {$hoursRemaining}h {$minutesRemaining}min\n" .
                    "Válido até: " . $activeUser->expires_at->format('d/m/Y H:i')
                );
            }

            // 5. VALIDAÇÃO DO INTERVALO ENTRE ATIVAÇÕES
            // Verificar se já usou o voucher e quanto tempo se passou desde a última ativação
            $lastUsedUser = User::where(function($q) use ($driverPhone, $driverIdentifier) {
                    if ($driverPhone) {
                        $q->where('driver_phone', $driverPhone);
                    } else {
                        $q->where('driver_phone', $driverIdentifier);
                    }
                })
                ->where('voucher_id', $voucher->id)
                ->whereNotNull('voucher_activated_at')
                ->orderBy('voucher_activated_at', 'desc')
                ->first();

            if ($lastUsedUser && $lastUsedUser->voucher_activated_at) {
                $intervalRequired = (float) ($voucher->activation_interval_hours ?? 24);
                $nextAvailableTime = $lastUsedUser->voucher_activated_at->copy()->addHours($intervalRequired);

                // 🔧 FIX: Verificar se a próxima ativação ainda está no futuro
                if ($nextAvailableTime->isFuture()) {
                    DB::rollback();

                    // Calcular tempo restante até poder ativar novamente
                    $timeUntilNext = now()->diff($nextAvailableTime);
                    $hoursRemaining = $timeUntilNext->h + ($timeUntilNext->days * 24);
                    $minutesRemaining = $timeUntilNext->i;
                    
                    // Formatar tempo de intervalo
                    $intervalFormatted = $intervalRequired >= 1 
                        ? floor($intervalRequired) . 'h' . ($intervalRequired != floor($intervalRequired) ? ' ' . round((($intervalRequired - floor($intervalRequired)) * 60)) . 'min' : '')
                        : round($intervalRequired * 60) . ' minutos';
                    
                    return back()->with('error', 
                        "⏰ AGUARDE O INTERVALO!\n\n" .
                        "Este voucher requer um intervalo de {$intervalFormatted} entre ativações.\n\n" .
                        "⏱️ Última ativação: " . $lastUsedUser->voucher_activated_at->format('d/m/Y H:i') . "\n" .
                        "🕐 Próxima ativação disponível: " . $nextAvailableTime->format('d/m/Y H:i') . "\n\n" .
                        "⏳ Aguarde mais: {$hoursRemaining}h {$minutesRemaining}min"
                    );
                }
                // Se nextAvailableTime já passou, o voucher pode ser ativado normalmente
            }

            // 6. Verificar se já usou o voucher hoje e atingiu o limite
            $existingExpiredUser = User::where(function($q) use ($driverPhone, $driverIdentifier) {
                    if ($driverPhone) {
                        $q->where('driver_phone', $driverPhone);
                    } else {
                        $q->where('driver_phone', $driverIdentifier);
                    }
                })
                ->where('voucher_id', $voucher->id)
                ->whereNotNull('voucher_activated_at')
                ->whereDate('voucher_last_connection', now()->toDateString())
                ->where('expires_at', '<=', now())
                ->first();

            if ($existingExpiredUser && !$voucher->hasHoursAvailableToday()) {
                DB::rollback();

                $nextAvailableTime = now()->addDay()->startOfDay();
                $hoursUntilReset = now()->diffInHours($nextAvailableTime);
                
                return back()->with('error', 
                    "❌ Limite diário atingido!\n\n" .
                    "Você já utilizou suas {$voucher->daily_hours} horas disponíveis hoje.\n\n" .
                    "Você poderá ativar novamente em: {$hoursUntilReset} horas\n" .
                    "Disponível a partir de: " . $nextAvailableTime->format('d/m/Y H:i')
                );
            }

            // 7. Criar ou atualizar usuário motorista
            // Primeiro buscar por driver_phone/identifier (motorista já cadastrado)
            $user = User::where('driver_phone', $driverIdentifier)->first();

            // Se não encontrou por driver_phone, buscar por MAC (dispositivo já usado por outro usuário)
            if (!$user) {
                $user = User::where('mac_address', $macAddress)->first();
            }

            if (!$user) {
                // Criar novo usuário motorista
                $user = User::create([
                    'name' => $voucher->driver_name ?? 'Motorista',
                    'phone' => $driverPhone ?? $driverIdentifier,
                    'driver_phone' => $driverIdentifier,
                    'mac_address' => $macAddress,
                    'ip_address' => $ipAddress,
                    'voucher_id' => $voucher->id,
                    'voucher_activated_at' => now(),
                    'status' => 'connected',
                    'role' => 'user',
                    'registered_at' => now(),
                ]);
            } else {
                // Atualizar usuário existente (pode ser motorista ou usuário comum que agora usa voucher)
                $user->update([
                    'name' => $voucher->driver_name ?? $user->name ?? 'Motorista',
                    'phone' => $driverPhone ?? $user->phone ?? $driverIdentifier,
                    'driver_phone' => $driverIdentifier,
                    'mac_address' => $macAddress,
                    'ip_address' => $ipAddress,
                    'voucher_id' => $voucher->id,
                    'voucher_activated_at' => now(),
                    'status' => 'connected',
                ]);
            }

            // 8. Calcular tempo de expiração baseado nas horas do voucher
            $hoursAvailable = $voucher->getRemainingHoursToday();
            
            // Adicionar tempo usando minutos para maior precisão
            $minutesToAdd = round($hoursAvailable * 60);
            $expiresAt = now()->addMinutes($minutesToAdd);

            // Para vouchers limitados, nunca passar de hoje às 23:59
            if ($voucher->voucher_type === 'limited') {
                $endOfDay = now()->endOfDay();
                if ($expiresAt->gt($endOfDay)) {
                    $expiresAt = $endOfDay;
                }
            }
            
            Log::info('📅 Calculando expiração', [
                'hours_available' => $hoursAvailable,
                'minutes_to_add' => $minutesToAdd,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            ]);

            // 9. Atualizar tempo de expiração
            $user->update([
                'connected_at' => now(),
                'expires_at' => $expiresAt,
                'voucher_last_connection' => now(),
                'voucher_daily_minutes_used' => 0, // Resetar contador diário
            ]);

            // 10. Registrar uso do voucher
            $voucher->recordUsage($hoursAvailable);

            // 11. Criar sessão de acesso
            Session::create([
                'user_id' => $user->id,
                'payment_id' => null, // Motorista não paga
                'started_at' => now(),
                'session_status' => 'active',
            ]);

            // 12. Registrar MAC no Mikrotik para liberação
            $this->registerMacInMikrotik($macAddress, $ipAddress, $user->id);

            // 13. Tentar liberar acesso imediatamente no Mikrotik
            $this->liberateAccessOnMikrotik($user);

            DB::commit();

            // Formatar tempo concedido
            $timeGranted = $voucher->formatHours($hoursAvailable);

            Log::info('🎫 Voucher ativado para motorista', [
                'user_id' => $user->id,
                'voucher_code' => $voucherCode,
                'driver_phone' => $driverPhone,
                'mac_address' => $macAddress,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'hours_granted' => $hoursAvailable,
                'time_granted' => $timeGranted,
            ]);

            return redirect()->route('voucher.status', ['document' => $voucher->driver_document ?? $driverIdentifier])
                ->with('success', "Voucher ativado com sucesso!");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erro ao ativar voucher', [
                'error' => $e->getMessage(),
                'voucher_code' => $request->voucher_code ?? null,
                'phone' => $request->driver_phone ?? null,
            ]);

            return back()->with('error', 'Erro ao ativar voucher: ' . $e->getMessage());
        }
    }

    /**
     * Exibe o status do voucher do motorista
     */
    public function showStatus(Request $request)
    {
        $phone = $request->query('phone');
        $document = $request->query('document');
        
        return view('portal.voucher.status', [
            'phone' => $phone,
            'document' => $document,
        ]);
    }

    /**
     * Verifica o status do voucher via CPF/documento
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'driver_document' => 'required|string|max:20',
        ]);

        // Limpar CPF (remover pontos, traços, etc)
        $driverDocument = preg_replace('/\D/', '', $request->driver_document);

        // Primeiro buscar voucher pelo documento
        $voucher = Voucher::where('driver_document', 'LIKE', '%' . $driverDocument . '%')->first();
        
        if (!$voucher) {
            return back()->with('error', 'Nenhum voucher encontrado para este CPF.');
        }

        // Buscar usuário que usou este voucher
        $user = User::where('voucher_id', $voucher->id)
            ->whereNotNull('voucher_activated_at')
            ->orderBy('voucher_activated_at', 'desc')
            ->first();

        if (!$user) {
            return back()->with('error', 'Este voucher ainda não foi ativado. Vá para a página de ativação.');
        }

        $voucher = $user->voucher;
        $isActive = $user->expires_at && $user->expires_at->isFuture();
        
        // Calcular tempo restante
        $timeRemaining = null;
        if ($isActive) {
            $timeRemaining = [
                'total_minutes' => now()->diffInMinutes($user->expires_at),
                'hours' => now()->diffInHours($user->expires_at),
                'minutes' => now()->diff($user->expires_at)->i,
            ];
        }

        // Calcular horas disponíveis hoje
        $hoursAvailableToday = $voucher ? $voucher->getRemainingHoursToday() : 0;
        $hoursAvailableTodayFormatted = $voucher ? $voucher->formatHours($hoursAvailableToday) : '0h';
        
        // Formatar tempo restante
        $timeRemainingFormatted = null;
        if ($timeRemaining) {
            $totalHours = $timeRemaining['total_minutes'] / 60;
            $timeRemainingFormatted = $voucher->formatHours($totalHours);
        }

        return view('portal.voucher.status', [
            'document' => $driverDocument,
            'user' => $user,
            'voucher' => $voucher,
            'isActive' => $isActive,
            'timeRemaining' => $timeRemaining,
            'timeRemainingFormatted' => $timeRemainingFormatted,
            'hoursAvailableToday' => $hoursAvailableToday,
            'hoursAvailableTodayFormatted' => $hoursAvailableTodayFormatted,
        ]);
    }

    /**
     * Registra MAC na tabela do Mikrotik para liberação
     */
    private function registerMacInMikrotik($macAddress, $ipAddress, $userId)
    {
        try {
            MikrotikMacReport::updateOrCreate(
                [
                    'ip_address' => $ipAddress,
                    'mac_address' => $macAddress,
                ],
                [
                    'transaction_id' => 'VOUCHER_' . $userId,
                    'mikrotik_ip' => null,
                    'reported_at' => now(),
                ]
            );

            Log::info('✅ MAC registrado para liberação Mikrotik (Voucher)', [
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao registrar MAC no Mikrotik', [
                'error' => $e->getMessage(),
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Tenta liberar acesso imediatamente no Mikrotik
     */
    private function liberateAccessOnMikrotik(User $user)
    {
        try {
            // Usar o serviço de webhook do Mikrotik se disponível
            if (class_exists('\App\Services\MikrotikWebhookService')) {
                $webhookService = new \App\Services\MikrotikWebhookService;
                $liberado = $webhookService->liberarMacAddress($user->mac_address);

                if ($liberado) {
                    Log::info('🎉 Acesso liberado no Mikrotik via webhook (Voucher)', [
                        'user_id' => $user->id,
                        'mac_address' => $user->mac_address,
                    ]);
                    return true;
                }
            }

            // Fallback: tentar controller direto
            if (class_exists('\App\Http\Controllers\MikrotikController')) {
                $mikrotikController = new \App\Http\Controllers\MikrotikController;
                $result = $mikrotikController->allowDeviceByUser($user);

                if ($result) {
                    Log::info('✅ Acesso liberado no Mikrotik via controller (Voucher)', [
                        'user_id' => $user->id,
                        'mac_address' => $user->mac_address,
                    ]);
                    return true;
                }
            }

            Log::info('ℹ️ Liberação Mikrotik via sync automático (Voucher)', [
                'user_id' => $user->id,
                'note' => 'Será liberado no próximo sync (10s)',
            ]);

            return false;

        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao liberar no Mikrotik (Voucher)', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'mac_address' => $user->mac_address,
            ]);
            
            return false;
        }
    }

    /**
     * Desconecta o motorista (cancela voucher ativo)
     */
    public function disconnect(Request $request)
    {
        $request->validate([
            'driver_phone' => 'required|string|max:20',
        ]);

        $driverPhone = preg_replace('/\D/', '', $request->driver_phone);

        $user = User::where('driver_phone', $driverPhone)
            ->whereNotNull('voucher_id')
            ->first();

        if (!$user) {
            return back()->with('error', 'Usuário não encontrado.');
        }

        $user->update([
            'status' => 'offline',
            'expires_at' => now(),
        ]);

        Log::info('🔌 Motorista desconectado manualmente', [
            'user_id' => $user->id,
            'driver_phone' => $driverPhone,
        ]);

        return back()->with('success', 'Desconectado com sucesso.');
    }
}

