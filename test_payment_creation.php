<?php
/**
 * Teste de Criação de Pagamento
 * Execute: php test_payment_creation.php
 */

require_once 'vendor/autoload.php';

// Carregar configurações Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "💳 TESTE CRIAÇÃO DE PAGAMENTO\n";
echo "=============================\n";

// Simular dados de uma requisição
$macAddress = '02:TEST:' . strtoupper(substr(md5(time()), 0, 8));
$amount = 5.99;
$ipAddress = '127.0.0.1';

echo "MAC Address: {$macAddress}\n";
echo "Valor: R$ " . number_format($amount, 2, ',', '.') . "\n";
echo "IP: {$ipAddress}\n\n";

try {
    DB::beginTransaction();
    
    // Buscar ou criar usuário
    echo "👤 Criando/buscando usuário...\n";
    $user = App\Models\User::where('mac_address', $macAddress)->first();
    
    if (!$user) {
        $user = App\Models\User::create([
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'status' => 'offline'
        ]);
        echo "✅ Usuário criado: ID {$user->id}\n";
    } else {
        echo "ℹ️ Usuário existente: ID {$user->id}\n";
    }
    
    // Gerar transaction ID
    $transactionId = 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    echo "Transaction ID: {$transactionId}\n\n";
    
    // Criar registro de pagamento
    echo "💰 Criando pagamento...\n";
    $payment = App\Models\Payment::create([
        'user_id' => $user->id,
        'amount' => $amount,
        'payment_type' => 'pix',
        'status' => 'pending',
        'transaction_id' => $transactionId
    ]);
    
    echo "✅ Pagamento criado: ID {$payment->id}\n\n";
    
    // Simular dados da Woovi
    echo "🔄 Simulando resposta da Woovi...\n";
    $wooviData = [
        'qr_code_text' => '00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/test-' . time() . '52040000530398654045.995802BR592357732545_ERICK_VINICIUS6009Sao_Paulo62290525test' . time() . '63043999',
        'correlation_id' => $transactionId,
        'woovi_id' => 'WOOVI_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8))
    ];
    
    // Atualizar payment com dados da Woovi
    echo "📝 Atualizando pagamento com dados da Woovi...\n";
    $payment->update([
        'pix_emv_string' => $wooviData['qr_code_text'],
        'pix_location' => $wooviData['correlation_id'],
        'gateway_payment_id' => $wooviData['woovi_id']
    ]);
    
    echo "✅ Pagamento atualizado!\n";
    echo "PIX EMV String: " . substr($wooviData['qr_code_text'], 0, 50) . "...\n";
    echo "PIX Location: {$wooviData['correlation_id']}\n";
    echo "Gateway Payment ID: {$wooviData['woovi_id']}\n\n";
    
    DB::commit();
    
    // Verificar se foi salvo corretamente
    echo "🔍 Verificando dados salvos...\n";
    $savedPayment = App\Models\Payment::find($payment->id);
    
    echo "ID: {$savedPayment->id}\n";
    echo "Status: {$savedPayment->status}\n";
    echo "Payment Type: {$savedPayment->payment_type}\n";
    echo "Transaction ID: {$savedPayment->transaction_id}\n";
    echo "PIX Location: " . ($savedPayment->pix_location ?? 'NULL') . "\n";
    echo "Gateway Payment ID: " . ($savedPayment->gateway_payment_id ?? 'NULL') . "\n";
    echo "PIX EMV String: " . (strlen($savedPayment->pix_emv_string ?? '') > 0 ? 'PRESENTE' : 'NULL') . "\n\n";
    
    // Testar busca por webhook
    echo "🔔 Testando busca para webhook...\n";
    $foundPayment = App\Models\Payment::where('pix_location', $wooviData['correlation_id'])
        ->orWhere('gateway_payment_id', $wooviData['woovi_id'])
        ->orWhere('transaction_id', $wooviData['correlation_id'])
        ->first();
    
    if ($foundPayment) {
        echo "✅ Pagamento encontrado para webhook: ID {$foundPayment->id}\n";
    } else {
        echo "❌ Pagamento NÃO encontrado para webhook!\n";
    }
    
    echo "\n✅ TESTE CONCLUÍDO COM SUCESSO!\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=============================\n"; 