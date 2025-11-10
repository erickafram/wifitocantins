<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payment;
use App\Models\MikrotikMacReport;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugQrCode extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'debug:qr-code {--mac=} {--test-payment}';

    /**
     * The console command description.
     */
    protected $description = 'Debug QR Code generation and MAC address flow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 DEBUG: Fluxo completo de MAC address e pagamento');
        $this->newLine();

        // 1. Simular MAC address
        $macAddress = $this->option('mac') ?: $this->generateMockMac();
        $ipAddress = '192.168.1.100';
        
        $this->info("📱 MAC Simulado: {$macAddress}");
        $this->info("🌐 IP Simulado: {$ipAddress}");
        $this->newLine();

        // 2. Simular registro de usuário
        $this->info('👤 Criando usuário de teste...');
        
        $user = User::create([
            'name' => 'Usuario Teste Debug',
            'email' => 'teste.debug.' . time() . '@example.com',
            'phone' => '63999999999',
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'password' => bcrypt('password'),
            'status' => 'pending'
        ]);

        $this->info("✅ Usuário criado: ID {$user->id}");
        $this->newLine();

        // 3. Simular criação de pagamento
        if ($this->option('test-payment')) {
            $this->info('💳 Criando pagamento de teste...');
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => 5.99,
                'payment_type' => 'pix',
                'status' => 'pending',
                'transaction_id' => 'TXN_DEBUG_' . time()
            ]);

            $this->info("✅ Pagamento criado: ID {$payment->id}");
            $this->newLine();

            // 4. Simular aprovação do pagamento
            $this->info('🎯 Simulando aprovação do pagamento...');
            
            $payment->update([
                'status' => 'completed',
                'paid_at' => now()
            ]);

            // 5. Simular ativação do acesso
            $this->info('🚀 Simulando ativação do acesso...');
            
            $paymentController = new PaymentController();
            $reflection = new \ReflectionClass($paymentController);
            $method = $reflection->getMethod('activateUserAccess');
            $method->setAccessible(true);
            
            try {
                $method->invoke($paymentController, $payment);
                $this->info('✅ Acesso ativado com sucesso!');
            } catch (\Exception $e) {
                $this->error('❌ Erro ao ativar acesso: ' . $e->getMessage());
            }
            $this->newLine();

            // 6. Verificar registros
            $this->info('📊 Verificando registros criados...');
            
            $user->refresh();
            $this->table(['Campo', 'Valor'], [
                ['User ID', $user->id],
                ['MAC Address', $user->mac_address],
                ['IP Address', $user->ip_address],
                ['Status', $user->status],
                ['Expires At', $user->expires_at],
                ['Payment Status', $payment->status],
                ['Transaction ID', $payment->transaction_id]
            ]);

            // 7. Verificar tabela mikrotik_mac_reports
            $macReports = MikrotikMacReport::where('mac_address', $macAddress)->get();
            
            if ($macReports->count() > 0) {
                $this->info("✅ MAC registrado na tabela mikrotik_mac_reports ({$macReports->count()} registros)");
                
                foreach ($macReports as $report) {
                    $this->line("   IP: {$report->ip_address} | MAC: {$report->mac_address} | Reported: {$report->reported_at}");
                }
            } else {
                $this->error("❌ MAC NÃO foi registrado na tabela mikrotik_mac_reports!");
            }
            $this->newLine();

            // 8. Logs do processo
            $this->info('📝 Verificando logs recentes...');
            $this->info('Execute: tail -f storage/logs/laravel.log | grep -E "(MAC|payment|activation)"');
        }

        $this->newLine();
        $this->info('🎯 Debug concluído!');
        
        if (!$this->option('test-payment')) {
            $this->info('💡 Use --test-payment para testar o fluxo completo de pagamento');
        }
    }

    /**
     * Gera MAC address fictício para teste
     */
    private function generateMockMac()
    {
        $hex = '0123456789ABCDEF';
        $mac = '';
        for ($i = 0; $i < 6; $i++) {
            if ($i > 0) $mac += ':';
            $mac += $hex[rand(0, 15)];
            $mac += $hex[rand(0, 15)];
        }
        return $mac;
    }
} 