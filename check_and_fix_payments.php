<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICAR E CORRIGIR PAGAMENTOS PENDENTES ===\n";

// Verificar pagamentos pendentes há mais de 3 minutos
$cutoffTime = now()->subMinutes(3);
$pendingPayments = \App\Models\Payment::where('status', 'pending')
    ->where('created_at', '<', $cutoffTime)
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();

echo "📋 Pagamentos pendentes há mais de 3 minutos: " . $pendingPayments->count() . "\n\n";

if ($pendingPayments->isEmpty()) {
    echo "✅ Nenhum pagamento pendente encontrado!\n";
    exit;
}

foreach ($pendingPayments as $payment) {
    echo "🔍 Verificando pagamento ID: {$payment->id}\n";
    echo "   MAC: {$payment->user->mac_address}\n";
    echo "   Valor: R$ " . number_format($payment->amount, 2) . "\n";
    echo "   Criado: {$payment->created_at} (" . $payment->created_at->diffForHumans() . ")\n";
    echo "   Gateway ID: " . ($payment->gateway_payment_id ?: 'N/A') . "\n";
    echo "   Transaction ID: " . ($payment->transaction_id ?: 'N/A') . "\n";
    
    // Se tem gateway_payment_id, tentar verificar na Woovi
    if ($payment->gateway_payment_id && config('wifi.payment_gateways.pix.woovi_app_id')) {
        try {
            echo "   🔄 Consultando status na Woovi...\n";
            
            $wooviService = new \App\Services\WooviPixService();
            
            // Tentar verificar o status do pagamento
            $status = $wooviService->checkPaymentStatus($payment->gateway_payment_id);
            
            if ($status) {
                echo "   📊 Status Woovi: " . ($status['status'] ?? 'unknown') . "\n";
                
                if (isset($status['status']) && $status['status'] === 'COMPLETED') {
                    echo "   ✅ Pagamento confirmado na Woovi! Aprovando...\n";
                    
                    // Aprovar pagamento
                    \Illuminate\Support\Facades\DB::beginTransaction();
                    
                    try {
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                            'payment_data' => $status
                        ]);
                        
                        $session = \App\Models\Session::create([
                            'user_id' => $payment->user_id,
                            'payment_id' => $payment->id,
                            'started_at' => now(),
                            'session_status' => 'active'
                        ]);
                        
                        $payment->user->update([
                            'status' => 'connected',
                            'connected_at' => now(),
                            'expires_at' => now()->addHours(24)
                        ]);
                        
                        \Illuminate\Support\Facades\DB::commit();
                        
                        echo "   🎉 Pagamento aprovado com sucesso!\n";
                        echo "   👤 Usuário {$payment->user->mac_address} liberado\n";
                        echo "   ⏰ Válido até: {$payment->user->expires_at}\n";
                        
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\DB::rollback();
                        echo "   ❌ Erro ao aprovar: {$e->getMessage()}\n";
                    }
                    
                } else {
                    echo "   ⏳ Ainda pendente na Woovi\n";
                }
                
            } else {
                echo "   ⚠️ Não foi possível verificar status na Woovi\n";
            }
            
        } catch (\Exception $e) {
            echo "   ❌ Erro ao verificar na Woovi: {$e->getMessage()}\n";
        }
        
    } else {
        echo "   ℹ️ Sem Gateway ID ou Woovi não configurada\n";
        
        // Para pagamentos muito antigos (mais de 10 minutos), perguntar se quer aprovar manualmente
        if ($payment->created_at < now()->subMinutes(10)) {
            echo "   ⚠️ Pagamento muito antigo - pode precisar de aprovação manual\n";
        }
    }
    
    echo "\n";
}

echo "=== RESUMO ===\n";
echo "✅ Verificação concluída\n";
echo "💡 Para aprovar um pagamento manualmente, use:\n";
echo "   php approve_payment.php [ID_DO_PAGAMENTO]\n";
echo "\n";
echo "🔄 Para executar sync manual no MikroTik:\n";
echo "   \$executarSyncMelhorado\n";

echo "\n=== FIM ===\n";
