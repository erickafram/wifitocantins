<?php
/**
 * Teste do Webhook Woovi
 * Execute: php test_webhook_woovi.php
 */

require_once 'vendor/autoload.php';

// Carregar configurações Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔔 TESTE WEBHOOK WOOVI\n";
echo "====================\n";

// Dados de exemplo de webhook da Woovi
$webhookData = [
    'event' => 'OPENPIX:CHARGE_COMPLETED',
    'charge' => [
        'correlationID' => 'TXN_1758293231_05AA9A08', // ID do pagamento 11 no banco
        'globalID' => 'Q2hhcmdlOjY3MTNhNzVkLWNiZjAtNDI5ZS1hY2NjLTc4ZGQ5OGUyYzNjYQ==',
        'value' => 599, // R$ 5,99 em centavos
        'status' => 'COMPLETED',
        'paidAt' => '2025-09-19T17:52:00.000Z',
        'payer' => [
            'name' => 'João da Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '63999999999'
        ]
    ]
];

echo "📋 Dados do webhook simulado:\n";
echo "Event: " . $webhookData['event'] . "\n";
echo "Correlation ID: " . $webhookData['charge']['correlationID'] . "\n";
echo "Global ID: " . $webhookData['charge']['globalID'] . "\n";
echo "Valor: R$ " . number_format($webhookData['charge']['value'] / 100, 2, ',', '.') . "\n";
echo "Status: " . $webhookData['charge']['status'] . "\n";
echo "\n";

// Testar processamento do webhook
echo "🔄 Processando webhook...\n";
$wooviService = new App\Services\WooviPixService();
$result = $wooviService->processWebhook($webhookData);

echo "✅ Resultado do processamento:\n";
echo "Success: " . ($result['success'] ? 'SIM' : 'NÃO') . "\n";
echo "Payment Approved: " . ($result['payment_approved'] ? 'SIM' : 'NÃO') . "\n";

if ($result['payment_approved']) {
    echo "Correlation ID: " . $result['correlation_id'] . "\n";
    echo "Woovi ID: " . $result['woovi_id'] . "\n";
    echo "Valor: R$ " . number_format($result['amount'], 2, ',', '.') . "\n";
    echo "Pago em: " . $result['paid_at'] . "\n";
    echo "Pagador: " . ($result['payer_name'] ?? 'N/A') . "\n";
    echo "\n";
    
    // Buscar pagamento no banco
    echo "🔍 Buscando pagamento no banco...\n";
    $payment = App\Models\Payment::where('pix_location', $result['correlation_id'])
        ->orWhere('gateway_payment_id', $result['woovi_id'])
        ->orWhere('transaction_id', $result['correlation_id'])
        ->first();
    
    if ($payment) {
        echo "✅ Pagamento encontrado!\n";
        echo "ID: " . $payment->id . "\n";
        echo "Status atual: " . $payment->status . "\n";
        echo "Valor: R$ " . number_format($payment->amount, 2, ',', '.') . "\n";
        echo "User ID: " . $payment->user_id . "\n";
        echo "Transaction ID: " . $payment->transaction_id . "\n";
        echo "PIX Location: " . $payment->pix_location . "\n";
        echo "Gateway Payment ID: " . $payment->gateway_payment_id . "\n";
        echo "\n";
        
        // Simular atualização do pagamento
        echo "💳 Simulando atualização do pagamento...\n";
        
        try {
            DB::beginTransaction();
            
            // Atualizar pagamento
            $payment->update([
                'status' => 'completed',
                'paid_at' => now()
            ]);
            
            // Criar sessão ativa se não existir
            $session = App\Models\Session::where('payment_id', $payment->id)->first();
            if (!$session) {
                $session = App\Models\Session::create([
                    'user_id' => $payment->user_id,
                    'payment_id' => $payment->id,
                    'started_at' => now(),
                    'session_status' => 'active'
                ]);
                echo "✅ Sessão criada: ID " . $session->id . "\n";
            } else {
                echo "ℹ️ Sessão já existe: ID " . $session->id . "\n";
            }
            
            // Liberar acesso do usuário
            $payment->user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => now()->addHours(24)
            ]);
            
            DB::commit();
            
            echo "✅ Pagamento atualizado com sucesso!\n";
            echo "✅ Usuário liberado para acesso!\n";
            echo "✅ Expira em: " . $payment->user->expires_at . "\n";
            
        } catch (\Exception $e) {
            DB::rollback();
            echo "❌ Erro ao atualizar pagamento: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Pagamento não encontrado no banco!\n";
        echo "Verifique se o correlation_id está correto.\n";
        
        // Listar alguns pagamentos para debug
        echo "\n📋 Últimos pagamentos no banco:\n";
        $payments = App\Models\Payment::orderBy('created_at', 'desc')->limit(5)->get();
        foreach ($payments as $p) {
            echo "ID: {$p->id} | Status: {$p->status} | TXN: {$p->transaction_id} | PIX: {$p->pix_location}\n";
        }
    }
    
} else {
    echo "ℹ️ Webhook processado, mas pagamento não foi aprovado.\n";
    if (isset($result['event'])) {
        echo "Evento: " . $result['event'] . "\n";
    }
}

echo "\n====================\n";
echo "Teste concluído!\n"; 