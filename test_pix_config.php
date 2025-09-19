<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTE DE CONFIGURAÇÃO PIX ===\n";

// 1. Verificar configurações PIX
echo "1. 🔧 Verificando configurações PIX...\n";

$pixKey = config('wifi.pix.key');
$merchantName = config('wifi.pix.merchant_name');
$merchantCity = config('wifi.pix.merchant_city');
$gateway = config('wifi.payment_gateways.pix.gateway');
$wooviAppId = config('wifi.payment_gateways.pix.woovi_app_id');

echo "   Chave PIX: " . ($pixKey ?: '❌ NÃO CONFIGURADA') . "\n";
echo "   Comerciante: " . ($merchantName ?: '❌ NÃO CONFIGURADO') . "\n";
echo "   Cidade: " . ($merchantCity ?: '❌ NÃO CONFIGURADA') . "\n";
echo "   Gateway: " . ($gateway ?: '❌ NÃO CONFIGURADO') . "\n";
echo "   Woovi App ID: " . ($wooviAppId ? 'CONFIGURADO ✅' : '❌ NÃO CONFIGURADO') . "\n";

echo "\n";

// 2. Testar serviço PIX QR Code
echo "2. 🎯 Testando geração de QR Code PIX...\n";

try {
    $pixService = new \App\Services\PixQRCodeService();
    $qrData = $pixService->generatePixQRCode(0.05, 'TEST_' . time());
    
    echo "   ✅ QR Code PIX gerado com sucesso!\n";
    echo "   💰 Valor: R$ {$qrData['amount']}\n";
    echo "   📊 Transaction ID: {$qrData['transaction_id']}\n";
    echo "   📱 EMV String: " . substr($qrData['emv_string'], 0, 50) . "...\n";
    
} catch (\Exception $e) {
    echo "   ❌ Erro ao gerar QR Code: {$e->getMessage()}\n";
}

echo "\n";

// 3. Testar serviço Woovi (se configurado)
echo "3. 🔗 Testando integração Woovi...\n";

if ($wooviAppId) {
    try {
        $wooviService = new \App\Services\WooviPixService();
        $result = $wooviService->createPixPayment(
            0.05,
            'Teste WiFi Tocantins',
            'TEST_WOOVI_' . time()
        );
        
        if ($result['success']) {
            echo "   ✅ Woovi integração funcionando!\n";
            echo "   💰 Valor: R$ " . number_format($result['amount'], 2) . "\n";
            echo "   🆔 Woovi ID: {$result['woovi_id']}\n";
            echo "   📱 QR Code gerado: " . (isset($result['qr_code_text']) ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "   ❌ Erro na Woovi: {$result['message']}\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Erro na integração Woovi: {$e->getMessage()}\n";
    }
} else {
    echo "   ⚠️ Woovi não configurada (usando PIX manual)\n";
}

echo "\n";

// 4. Testar criação de pagamento completo
echo "4. 💳 Testando criação de pagamento completo...\n";

try {
    // Criar usuário de teste
    $testUser = \App\Models\User::create([
        'mac_address' => '02:TEST:PIX:' . time(),
        'ip_address' => '127.0.0.1',
        'status' => 'offline'
    ]);
    
    // Criar pagamento
    $payment = \App\Models\Payment::create([
        'user_id' => $testUser->id,
        'amount' => 0.05,
        'payment_type' => 'pix',
        'status' => 'pending',
        'transaction_id' => 'TEST_PAYMENT_' . time()
    ]);
    
    echo "   ✅ Pagamento criado!\n";
    echo "   🆔 Payment ID: {$payment->id}\n";
    echo "   💰 Valor: R$ " . number_format($payment->amount, 2) . "\n";
    echo "   📱 MAC: {$testUser->mac_address}\n";
    
    // Limpeza
    $payment->delete();
    $testUser->delete();
    echo "   🧹 Dados de teste removidos\n";
    
} catch (\Exception $e) {
    echo "   ❌ Erro ao criar pagamento: {$e->getMessage()}\n";
}

echo "\n";

// 5. Verificar URLs da API
echo "5. 🌐 URLs da API de pagamento:\n";

$baseUrl = config('app.url');
echo "   Gerar PIX: {$baseUrl}/api/payment/pix\n";
echo "   Webhook Woovi: {$baseUrl}/api/payment/webhook/woovi\n";
echo "   Status PIX: {$baseUrl}/api/payment/pix/status\n";

echo "\n";

// 6. Exemplo de payload para testar
echo "6. 📋 Exemplo de payload para testar pagamento:\n";

$examplePayload = [
    'amount' => 0.05,
    'mac_address' => '02:TEST:DEVICE:MAC'
];

echo "   POST {$baseUrl}/api/payment/pix\n";
echo "   Content-Type: application/json\n";
echo "   Body: " . json_encode($examplePayload, JSON_PRETTY_PRINT) . "\n";

echo "\n=== RESUMO ===\n";

if ($pixKey && $merchantName && $merchantCity) {
    echo "✅ Configuração PIX: OK\n";
} else {
    echo "❌ Configuração PIX: INCOMPLETA\n";
}

if ($wooviAppId) {
    echo "✅ Woovi: CONFIGURADO\n";
} else {
    echo "⚠️ Woovi: NÃO CONFIGURADO (usando PIX manual)\n";
}

echo "\n🎯 Para resolver o erro 'Chave PIX não configurada':\n";
echo "1. As configurações PIX foram adicionadas\n";
echo "2. Execute: php artisan config:cache\n";
echo "3. Teste novamente o pagamento no portal\n";

echo "\n=== FIM DO TESTE ===\n"; 