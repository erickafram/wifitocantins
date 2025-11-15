<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ManageDriverVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:manage 
                            {--reset-daily : Resetar contadores diários de vouchers}
                            {--expire-old : Expirar sessões antigas de vouchers}
                            {--check-limits : Verificar limites diários}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerencia vouchers de motoristas (reset diário, expiração, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎫 Iniciando gerenciamento de vouchers de motoristas...');

        if ($this->option('reset-daily')) {
            $this->resetDailyCounters();
        }

        if ($this->option('expire-old')) {
            $this->expireOldSessions();
        }

        if ($this->option('check-limits')) {
            $this->checkDailyLimits();
        }

        // Se nenhuma opção foi passada, executar todas
        if (!$this->option('reset-daily') && !$this->option('expire-old') && !$this->option('check-limits')) {
            $this->resetDailyCounters();
            $this->expireOldSessions();
            $this->checkDailyLimits();
        }

        $this->info('✅ Gerenciamento de vouchers concluído!');
    }

    /**
     * Reseta contadores diários de vouchers (executar à meia-noite)
     */
    private function resetDailyCounters()
    {
        $this->info('🔄 Resetando contadores diários de vouchers...');

        // Resetar daily_hours_used para vouchers que foram usados em dias anteriores
        $vouchers = Voucher::where('voucher_type', 'limited')
            ->where('is_active', true)
            ->whereNotNull('last_used_date')
            ->where('last_used_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($vouchers as $voucher) {
            $voucher->update([
                'daily_hours_used' => 0,
            ]);
            $count++;
        }

        // Resetar voucher_daily_minutes_used para usuários motoristas
        $usersCount = User::whereNotNull('voucher_id')
            ->where('voucher_daily_minutes_used', '>', 0)
            ->update([
                'voucher_daily_minutes_used' => 0,
            ]);

        $this->info("   ✅ {$count} vouchers resetados");
        $this->info("   ✅ {$usersCount} motoristas resetados");

        Log::info('🔄 Contadores diários de vouchers resetados', [
            'vouchers_reset' => $count,
            'users_reset' => $usersCount,
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Expira sessões antigas de vouchers
     */
    private function expireOldSessions()
    {
        $this->info('⏰ Verificando sessões expiradas de vouchers...');

        // Buscar usuários motoristas com sessão expirada
        $expiredUsers = User::whereNotNull('voucher_id')
            ->where('status', 'connected')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredUsers as $user) {
            $user->update([
                'status' => 'expired',
                'connected_at' => null,
            ]);
            $count++;

            Log::info('⏰ Sessão de voucher expirada', [
                'user_id' => $user->id,
                'driver_phone' => $user->driver_phone,
                'voucher_code' => $user->voucher->code ?? 'N/A',
                'expired_at' => now()->toISOString(),
            ]);
        }

        $this->info("   ✅ {$count} sessões expiradas");
    }

    /**
     * Verifica limites diários e desconecta se necessário
     */
    private function checkDailyLimits()
    {
        $this->info('📊 Verificando limites diários de vouchers...');

        $users = User::whereNotNull('voucher_id')
            ->where('status', 'connected')
            ->with('voucher')
            ->get();

        $limitReachedCount = 0;

        foreach ($users as $user) {
            if (!$user->voucher) {
                continue;
            }

            // Verificar se o voucher ainda é válido
            if (!$user->voucher->isValid()) {
                $user->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                ]);
                $limitReachedCount++;

                Log::info('❌ Voucher atingiu limite diário', [
                    'user_id' => $user->id,
                    'driver_phone' => $user->driver_phone,
                    'voucher_code' => $user->voucher->code,
                    'daily_hours_used' => $user->voucher->daily_hours_used,
                    'daily_hours_limit' => $user->voucher->daily_hours,
                ]);
            }
        }

        $this->info("   ✅ {$limitReachedCount} usuários desconectados por limite");
    }
}

