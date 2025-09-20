<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGIR PROCESSAMENTO DE WEBHOOKS ===\n";

// 1. Verificar pagamentos pendentes há mais de 5 minutos
$pendingPayments = \App\Models\Payment::where('status', 'pending')
    ->where('created_at', '<', now()->subMinutes(5))
    ->with('user')
    ->get();

echo "📋 Pagamentos pendentes há mais de 5 minutos: " . $pendingPayments->count() . "\n\n";

foreach ($pendingPayments as $payment) {
    echo "🔍 Verificando pagamento ID: {$payment->id}\n";
    echo "   MAC: {$payment->user->mac_address}\n";
    echo "   Valor: R$ " . number_format($payment->amount, 2) . "\n";
    echo "   Criado: {$payment->created_at}\n";
    echo "   Gateway ID: " . ($payment->gateway_payment_id ?: 'N/A') . "\n";
    
    // Se tem gateway_payment_id, tentar verificar na Woovi
    if ($payment->gateway_payment_id) {
        try {
            echo "   🔄 Consultando status na Woovi...\n";
            
            // Usar o serviço Woovi para verificar status
            $wooviService = new \App\Services\WooviPixService();
            $status = $wooviService->checkPaymentStatus($payment->gateway_payment_id);
            
            if ($status && $status['status'] === 'COMPLETED') {
                echo "   ✅ Pagamento confirmado na Woovi! Aprovando...\n";
                
                // Aprovar pagamento
                \Illuminate\Support\Facades\DB::beginTransaction();
                
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now()
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
                
            } else {
                echo "   ⏳ Ainda pendente na Woovi\n";
            }
            
        } catch (\Exception $e) {
            echo "   ❌ Erro ao verificar na Woovi: {$e->getMessage()}\n";
        }
    }
    
    echo "\n";
}

echo "=== FIM ===\n";
