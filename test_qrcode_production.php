<?php
/**
 * Teste rápido do QR Code no servidor de produção
 * Execute: php test_qrcode_production.php
 */

require_once 'vendor/autoload.php';

// Carregar configurações Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 TESTE QR CODE - PRODUÇÃO\n";
echo "==========================\n";

// Verificar configurações
echo "PIX Habilitado: " . (config('wifi.payment_gateways.pix.enabled') ? 'SIM' : 'NÃO') . "\n";
echo "Gateway: " . config('wifi.payment_gateways.pix.gateway') . "\n";
echo "Ambiente: " . config('wifi.payment_gateways.pix.environment') . "\n";
echo "App ID: " . substr(config('wifi.payment_gateways.pix.woovi_app_id'), 0, 20) . "...\n";
echo "\n";

// Testar conexão Woovi
echo "📡 TESTANDO CONEXÃO WOOVI...\n";
$wooviService = new App\Services\WooviPixService();
$connectionTest = $wooviService->testConnection();

if ($connectionTest['success']) {
    echo "✅ Conexão: OK\n";
    echo "Status Code: " . ($connectionTest['status_code'] ?? 'N/A') . "\n";
    
    // Testar criação de pagamento
    echo "\n💳 TESTANDO PAGAMENTO...\n";
    $testResult = $wooviService->createPixPayment(5.99, 'Teste Produção', 'TEST_PROD_' . time());
    
    if ($testResult['success']) {
        echo "✅ Pagamento criado!\n";
        echo "Woovi ID: " . $testResult['woovi_id'] . "\n";
        echo "Correlation ID: " . $testResult['correlation_id'] . "\n";
        
        // Verificar QR Code
        echo "\n🖼️ VERIFICANDO QR CODE...\n";
        if (!empty($testResult['qr_code_image'])) {
            if (!empty($testResult['qr_code_is_url']) && $testResult['qr_code_is_url']) {
                echo "✅ Tipo: URL da Woovi\n";
                echo "URL: " . $testResult['qr_code_image'] . "\n";
                
                // Testar se a URL é acessível
                $headers = @get_headers($testResult['qr_code_image']);
                if ($headers && strpos($headers[0], '200') !== false) {
                    echo "✅ URL acessível: SIM\n";
                } else {
                    echo "❌ URL acessível: NÃO\n";
                }
            } else {
                echo "✅ Tipo: Base64\n";
                echo "Tamanho: " . strlen($testResult['qr_code_image']) . " chars\n";
                
                // Validar base64
                $decoded = base64_decode($testResult['qr_code_image'], true);
                if ($decoded !== false) {
                    echo "✅ Base64 válido: SIM (" . strlen($decoded) . " bytes)\n";
                } else {
                    echo "❌ Base64 válido: NÃO\n";
                }
            }
        } else {
            echo "❌ QR Code image: AUSENTE\n";
        }
        
        // Mostrar EMV
        echo "\n📱 EMV CODE:\n";
        echo "Tamanho: " . strlen($testResult['qr_code_text']) . " chars\n";
        echo "Início: " . substr($testResult['qr_code_text'], 0, 50) . "...\n";
        
    } else {
        echo "❌ Erro no pagamento: " . $testResult['message'] . "\n";
    }
    
} else {
    echo "❌ Conexão falhou: " . $connectionTest['message'] . "\n";
}

echo "\n🎯 RESULTADO:\n";
if ($connectionTest['success'] && !empty($testResult['success'])) {
    echo "✅ Sistema funcionando corretamente!\n";
    echo "✅ Correções aplicadas com sucesso!\n";
} else {
    echo "❌ Ainda há problemas a resolver.\n";
}

echo "\n==========================\n";
echo "Teste concluído!\n"; 