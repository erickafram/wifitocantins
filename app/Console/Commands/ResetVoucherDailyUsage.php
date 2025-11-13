<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ResetVoucherDailyUsage extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vouchers:reset-daily-usage';

    /**
     * The console command description.
     */
    protected $description = 'Reseta o uso diário de vouchers e remove usuários expirados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando reset de uso diário de vouchers...');

        try {
            // 1. Resetar contadores diários de vouchers limitados
            $vouchersToReset = Voucher::where('voucher_type', 'limited')
                ->where('is_active', true)
                ->where('daily_hours_used', '>', 0)
                ->whereDate('last_used_date', '<', now()->toDateString())
                ->get();

            foreach ($vouchersToReset as $voucher) {
                $voucher->resetDailyUsage();
                $this->info("✅ Voucher {$voucher->code} ({$voucher->driver_name}) resetado");
            }

            // 2. Finalizar sessões expiradas de vouchers e incrementar horas usadas
            $expiredVoucherUsers = User::whereIn('status', ['active', 'connected'])
                ->where('expires_at', '<=', now())
                ->whereNotNull('mac_address')
                ->get();

            foreach ($expiredVoucherUsers as $user) {
                // Verificar se é usuário de voucher (sem payment_id nas sessões)
                $hasVoucherSession = $user->sessions()
                    ->where('session_status', 'active')
                    ->whereNull('payment_id')
                    ->exists();

                if ($hasVoucherSession) {
                    // Buscar voucher do usuário
                    $voucher = Voucher::where('driver_name', $user->name)
                        ->where('is_active', true)
                        ->first();

                    // Finalizar sessões ativas
                    $activeSessions = $user->sessions()
                        ->where('session_status', 'active')
                        ->get();

                    foreach ($activeSessions as $session) {
                        $session->update([
                            'ended_at' => now(),
                            'session_status' => 'ended'
                        ]);

                        // Se encontrou voucher, incrementar horas usadas
                        if ($voucher) {
                            // Calcular horas usadas baseado na duração da sessão
                            $sessionDuration = $session->started_at->diffInHours(now());
                            $hoursToIncrement = max(1, ceil($sessionDuration)); // Mínimo 1 hora
                            
                            $voucher->endSession($hoursToIncrement);
                        }
                    }

                    // Atualizar status do usuário
                    $user->update([
                        'status' => 'expired',
                        'connected_at' => null
                    ]);

                    $this->info("⏰ Usuário {$user->name} ({$user->mac_address}) expirado");
                    if ($voucher) {
                        $this->info("   🎫 Voucher {$voucher->code}: {$voucher->daily_hours_used}h/{$voucher->daily_hours}h usadas hoje");
                    }
                }
            }

            // 3. Limpar usuários antigos de vouchers (mais de 7 dias expirados)
            $oldVoucherUsers = User::where('status', 'expired')
                ->where('expires_at', '<', now()->subDays(7))
                ->whereHas('sessions', function($query) {
                    $query->whereNull('payment_id');
                })
                ->get();

            foreach ($oldVoucherUsers as $user) {
                $user->sessions()->delete();
                $user->delete();
                $this->info("🗑️ Usuário antigo {$user->name} removido");
            }

            $this->info("✅ Reset concluído:");
            $this->info("   - {$vouchersToReset->count()} vouchers resetados");
            $this->info("   - {$expiredVoucherUsers->count()} usuários expirados");
            $this->info("   - {$oldVoucherUsers->count()} usuários antigos removidos");

            Log::info('🔄 Reset diário de vouchers executado', [
                'vouchers_reset' => $vouchersToReset->count(),
                'users_expired' => $expiredVoucherUsers->count(),
                'users_cleaned' => $oldVoucherUsers->count(),
            ]);

        } catch (\Exception $e) {
            $this->error('❌ Erro no reset de vouchers: ' . $e->getMessage());
            Log::error('❌ Erro no reset de vouchers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
