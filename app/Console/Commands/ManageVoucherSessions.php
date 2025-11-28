<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VoucherSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ManageVoucherSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vouchers:manage-sessions';

    /**
     * The console command description.
     */
    protected $description = 'Gerencia sessões de vouchers, verificando tempo de uso e expirando sessões';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎫 Iniciando gerenciamento de sessões de vouchers...');

        $activeSessions = VoucherSession::active()->get();
        $expiredCount = 0;
        $updatedCount = 0;

        foreach ($activeSessions as $session) {
            try {
                // Atualiza o tempo usado
                $session->updateUsage();
                $updatedCount++;

                // Se a sessão expirou, desconecta o usuário do MikroTik
                if ($session->status === 'expired') {
                    $this->disconnectUser($session);
                    $expiredCount++;
                    
                    $this->info("⏰ Sessão expirada: {$session->voucher->driver_name} ({$session->mac_address})");
                }

            } catch (\Exception $e) {
                Log::error('❌ Erro ao processar sessão de voucher', [
                    'session_id' => $session->id,
                    'voucher_code' => $session->voucher->code,
                    'error' => $e->getMessage()
                ]);
                
                $this->error("Erro ao processar sessão {$session->id}: {$e->getMessage()}");
            }
        }

        // Reseta contadores diários para vouchers que não foram usados hoje
        $this->resetDailyCounters();

        $this->info("✅ Processamento concluído:");
        $this->info("   - {$updatedCount} sessões atualizadas");
        $this->info("   - {$expiredCount} sessões expiradas");

        Log::info('🎫 Gerenciamento de sessões de vouchers concluído', [
            'sessions_updated' => $updatedCount,
            'sessions_expired' => $expiredCount
        ]);

        return 0;
    }

    /**
     * Desconecta usuário do MikroTik
     */
    private function disconnectUser(VoucherSession $session): void
    {
        try {
            // Atualiza status do usuário
            $user = $session->user;
            $user->update([
                'status' => 'expired',
                'expires_at' => now()
            ]);

            // Chama API do MikroTik para desconectar
            $mikrotikController = new \App\Http\Controllers\MikrotikLiberacaoController();
            $mikrotikController->removerAcesso($session->mac_address, $session->ip_address);

            Log::info('🔌 Usuário desconectado do MikroTik por expiração de voucher', [
                'voucher_code' => $session->voucher->code,
                'driver_name' => $session->voucher->driver_name,
                'mac_address' => $session->mac_address,
                'hours_used' => round($session->minutes_used / 60, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao desconectar usuário do MikroTik', [
                'session_id' => $session->id,
                'mac_address' => $session->mac_address,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reseta contadores diários de vouchers
     */
    private function resetDailyCounters(): void
    {
        $vouchers = \App\Models\Voucher::where('is_active', true)
            ->whereNotNull('last_used_date')
            ->where('last_used_date', '<', today())
            ->get();

        $resetCount = 0;
        foreach ($vouchers as $voucher) {
            $voucher->resetDailyUsage();
            $resetCount++;
        }

        if ($resetCount > 0) {
            $this->info("🔄 {$resetCount} contadores diários resetados");
            Log::info('🔄 Contadores diários de vouchers resetados', [
                'vouchers_reset' => $resetCount
            ]);
        }
    }
}
