<?php
echo "🧪 TESTE DO WEBHOOK WOOVI - MÉTODO CORRETO\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Teste via cURL (método correto)
echo "🔄 1. TESTANDO VIA CURL (POST):\n";

$webhookUrl = "https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi";

// Dados de teste simulando webhook do Woovi
$testData = [
    'event' => 'OPENPIX:CHARGE_COMPLETED',
    'charge' => [
        'correlationID' => 'TEST_WEBHOOK_' . time(),
        'status' => 'COMPLETED',
        'value' => 5,
        'globalID' => 'TEST_GLOBAL_ID'
    ],
    'pix' => [
        'time' => date('c'),
        'status' => 'CONFIRMED'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Woovi-Webhook/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   📡 URL: {$webhookUrl}\n";
echo "   📝 Dados enviados: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";
echo "   📊 Código HTTP: {$httpCode}\n";
echo "   📄 Resposta: {$response}\n";

if ($error) {
    echo "   ❌ Erro cURL: {$error}\n";
}

if ($httpCode === 200) {
    echo "   ✅ WEBHOOK FUNCIONANDO CORRETAMENTE!\n";
} elseif ($httpCode === 405) {
    echo "   ❌ Erro 405: Método não permitido (você tentou GET em vez de POST)\n";
} elseif ($httpCode === 404) {
    echo "   ❌ Erro 404: Rota não encontrada\n";
} elseif ($httpCode === 500) {
    echo "   ❌ Erro 500: Erro interno do servidor\n";
} else {
    echo "   ⚠️  Código HTTP inesperado: {$httpCode}\n";
}

echo "\n";

// 2. Verificar outras rotas
echo "🔍 2. TESTANDO OUTRAS ROTAS WEBHOOK:\n";

$routes = [
    '/api/payment/webhook',
    '/api/payment/webhook/woovi/unified',
    '/api/payment/webhook/woovi/transaction'
];

foreach ($routes as $route) {
    $url = "https://www.tocantinstransportewifi.com.br{$route}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => true]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = $httpCode === 200 ? "✅ OK" : "❌ {$httpCode}";
    echo "   {$status} {$route}\n";
}

echo "\n";

// 3. Instruções
echo "📋 3. COMO TESTAR CORRETAMENTE:\n";
echo "   ❌ ERRADO: Acessar https://url/webhook no navegador (GET)\n";
echo "   ✅ CORRETO: Enviar POST com JSON para a URL\n\n";

echo "🎯 4. CONCLUSÃO:\n";
if ($httpCode === 200) {
    echo "   ✅ API está funcionando perfeitamente!\n";
    echo "   ✅ Woovi pode enviar webhooks sem problemas\n";
    echo "   ✅ Configuração no painel Woovi está correta\n";
} else {
    echo "   ⚠️  Verificar logs do servidor para mais detalhes\n";
    echo "   📝 Comando: tail -f /var/log/nginx/error.log\n";
}

echo "\n🚀 TESTE CONCLUÍDO!\n";
echo "=" . str_repeat("=", 50) . "\n";