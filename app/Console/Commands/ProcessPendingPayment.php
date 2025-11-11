<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessPendingPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:process-pending {transaction_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processar manualmente um pagamento pendente que já foi confirmado pela Woovi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactionId = $this->argument('transaction_id');
        
        $this->info("🔄 Processando pagamento pendente: {$transactionId}");
        $this->newLine();

        try {
            DB::beginTransaction();

            // Buscar pagamento
            $payment = Payment::where('transaction_id', $transactionId)->first();
            
            if (!$payment) {
                $this->error("❌ Pagamento não encontrado com transaction_id: {$transactionId}");
                return 1;
            }
            
            $this->info("📋 Pagamento encontrado:");
            $this->line("   ID: {$payment->id}");
            $this->line("   User ID: {$payment->user_id}");
            $this->line("   Status Atual: {$payment->status}");
            $this->line("   Valor: R$ {$payment->amount}");
            $this->newLine();
            
            if ($payment->status === 'completed') {
                $this->warn("✅ Pagamento já está marcado como concluído!");
                return 0;
            }
            
            // Atualizar pagamento
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'payment_data' => json_encode([
                    'processed_manually' => true,
                    'processed_at' => now()->toISOString(),
                    'reason' => 'Webhook validation failed, processed manually via artisan command',
                ]),
            ]);
            
            $this->info("✅ Pagamento atualizado para 'completed'");
            $this->newLine();
            
            // Buscar usuário
            $user = User::find($payment->user_id);
            
            if ($user) {
                $this->info("👤 Usuário encontrado:");
                $this->line("   Nome: {$user->name}");
                $this->line("   Email: {$user->email}");
                $this->line("   MAC: {$user->mac_address}");
                $this->line("   IP: {$user->ip_address}");
                $this->newLine();
                
                // Atualizar status do usuário
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addHours(24), // 24 horas de acesso
                ]);
                
                $this->info("✅ Status do usuário atualizado para 'connected'");
                $this->info("✅ Acesso válido até: " . now()->addHours(24)->format('d/m/Y H:i:s'));
                $this->newLine();
                
                // Criar sessão WiFi
                DB::table('wifi_sessions')->insert([
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
                    'started_at' => now(),
                    'session_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("✅ Sessão WiFi criada");
                $this->newLine();
            } else {
                $this->warn("⚠️ Usuário não encontrado!");
                $this->newLine();
            }
            
            DB::commit();
            
            $this->info("🎉 PROCESSAMENTO CONCLUÍDO COM SUCESSO!");
            $this->newLine();
            $this->info("📝 Resumo:");
            $this->line("   - Pagamento marcado como 'completed'");
            $this->line("   - Usuário marcado como 'connected'");
            $this->line("   - Sessão WiFi criada");
            $this->line("   - Acesso liberado por 24 horas");
            $this->newLine();
            
            if ($user) {
                $this->warn("⚠️ IMPORTANTE: Libere o MAC address no MikroTik:");
                $this->line("   MAC: {$user->mac_address}");
                $this->line("   IP: {$user->ip_address}");
                
                // Tentar liberar automaticamente via API
                $this->newLine();
                $this->info("🔄 Tentando liberar acesso no MikroTik...");
                
                try {
                    $response = \Illuminate\Support\Facades\Http::post(config('wifi.mikrotik.api_url') . '/api/mikrotik/allow', [
                        'mac_address' => $user->mac_address,
                        'ip_address' => $user->ip_address,
                    ]);
                    
                    if ($response->successful()) {
                        $this->info("✅ Acesso liberado no MikroTik automaticamente!");
                    } else {
                        $this->warn("⚠️ Não foi possível liberar automaticamente. Libere manualmente.");
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️ Erro ao tentar liberar automaticamente: " . $e->getMessage());
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ ERRO: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
