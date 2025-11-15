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

            // 2. Usar telefone do voucher (cadastrado no admin)
            $driverPhone = $voucher->driver_phone;
            
            if (!$driverPhone) {
                return back()->with('error', 'Este voucher não possui telefone cadastrado. Entre em contato com o administrador.');
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
            $activeUser = User::where('driver_phone', $driverPhone)
                ->whereNotNull('voucher_id')
                ->where('voucher_id', $voucher->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($activeUser) {
                // Se o MAC for diferente, bloquear
                if ($activeUser->mac_address !== $macAddress) {
                    DB::commit();
                    
                    $timeRemaining = now()->diff($activeUser->expires_at);
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
                
                // Se for o mesmo MAC, apenas renovar
                DB::commit();

                $timeRemaining = now()->diff($activeUser->expires_at);
                $hoursRemaining = $timeRemaining->h;
                $minutesRemaining = $timeRemaining->i;
                
                return back()->with('warning', 
                    "⚠️ Voucher já está ativo!\n\n" .
                    "Você já tem um voucher ativo no momento.\n" .
                    "Tempo restante: {$hoursRemaining}h {$minutesRemaining}min\n" .
                    "Válido até: " . $activeUser->expires_at->format('d/m/Y H:i')
                );
            }

            // 5. Verificar se já usou o voucher hoje e atingiu o limite
            $existingExpiredUser = User::where('driver_phone', $driverPhone)
                ->where('voucher_id', $voucher->id)
                ->whereNotNull('voucher_activated_at')
                ->whereDate('voucher_last_connection', now()->toDateString())
                ->where('expires_at', '<=', now())
                ->first();

            if ($existingExpiredUser && !$voucher->hasHoursAvailableToday()) {
                DB::commit();

                $nextAvailableTime = now()->addDay()->startOfDay();
                $hoursUntilReset = now()->diffInHours($nextAvailableTime);
                
                return back()->with('error', 
                    "❌ Limite diário atingido!\n\n" .
                    "Você já utilizou suas {$voucher->daily_hours} horas disponíveis hoje.\n\n" .
                    "Você poderá ativar novamente em: {$hoursUntilReset} horas\n" .
                    "Disponível a partir de: " . $nextAvailableTime->format('d/m/Y H:i')
                );
            }

            // 6. Criar ou atualizar usuário motorista
            $user = User::where('driver_phone', $driverPhone)->first();

            if (!$user) {
                // Criar novo usuário motorista
                $user = User::create([
                    'name' => $voucher->driver_name ?? 'Motorista',
                    'phone' => $driverPhone,
                    'driver_phone' => $driverPhone,
                    'mac_address' => $macAddress,
                    'ip_address' => $ipAddress,
                    'voucher_id' => $voucher->id,
                    'voucher_activated_at' => now(),
                    'status' => 'connected',
                    'role' => 'user',
                    'registered_at' => now(),
                ]);
            } else {
                // Atualizar usuário existente
                $user->update([
                    'mac_address' => $macAddress,
                    'ip_address' => $ipAddress,
                    'voucher_id' => $voucher->id,
                    'voucher_activated_at' => now(),
                    'status' => 'connected',
                ]);
            }

            // 7. Calcular tempo de expiração baseado nas horas do voucher
            $hoursAvailable = $voucher->getRemainingHoursToday();
            $expiresAt = now()->addHours($hoursAvailable);

            // Para vouchers limitados, nunca passar de hoje às 23:59
            if ($voucher->voucher_type === 'limited') {
                $endOfDay = now()->endOfDay();
                if ($expiresAt->gt($endOfDay)) {
                    $expiresAt = $endOfDay;
                }
            }

            // 8. Atualizar tempo de expiração
            $user->update([
                'connected_at' => now(),
                'expires_at' => $expiresAt,
                'voucher_last_connection' => now(),
                'voucher_daily_minutes_used' => 0, // Resetar contador diário
            ]);

            // 9. Registrar uso do voucher
            $voucher->recordUsage($hoursAvailable);

            // 10. Criar sessão de acesso
            Session::create([
                'user_id' => $user->id,
                'payment_id' => null, // Motorista não paga
                'started_at' => now(),
                'session_status' => 'active',
            ]);

            // 11. Registrar MAC no Mikrotik para liberação
            $this->registerMacInMikrotik($macAddress, $ipAddress, $user->id);

            // 12. Tentar liberar acesso imediatamente no Mikrotik
            $this->liberateAccessOnMikrotik($user);

            DB::commit();

            Log::info('🎫 Voucher ativado para motorista', [
                'user_id' => $user->id,
                'voucher_code' => $voucherCode,
                'driver_phone' => $driverPhone,
                'mac_address' => $macAddress,
                'expires_at' => $expiresAt->toISOString(),
                'hours_granted' => $hoursAvailable,
            ]);

            return redirect()->route('voucher.status', ['phone' => $driverPhone])
                ->with('success', 'Voucher ativado com sucesso! Você tem ' . $hoursAvailable . ' horas de acesso hoje.');

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
        
        return view('portal.voucher.status', [
            'phone' => $phone,
        ]);
    }

    /**
     * Verifica o status do voucher via telefone
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'driver_phone' => 'required|string|max:20',
        ]);

        $driverPhone = preg_replace('/\D/', '', $request->driver_phone);

        $user = User::where('driver_phone', $driverPhone)
            ->whereNotNull('voucher_id')
            ->with('voucher')
            ->first();

        if (!$user) {
            return back()->with('error', 'Nenhum voucher ativo encontrado para este telefone.');
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

        return view('portal.voucher.status', [
            'phone' => $driverPhone,
            'user' => $user,
            'voucher' => $voucher,
            'isActive' => $isActive,
            'timeRemaining' => $timeRemaining,
            'hoursAvailableToday' => $hoursAvailableToday,
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

