<?php

echo "=== TESTANDO URL CORRETA DO WEBHOOK ===\n\n";

// Testar URL correta do webhook
$webhookData = [
    'event' => 'OPENPIX:CHARGE_COMPLETED',
    'charge' => [
        'correlationID' => 'TXN_1758389524_0A8A4334',
        'status' => 'COMPLETED'
    ]
];

$urls = [
    'https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi',
    'https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi/unified'
];

foreach ($urls as $url) {
    echo "Testando: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Woovi-Webhook/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
    if ($error) {
        echo "Erro: $error\n";
    }
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "=== FIM DOS TESTES ===\n";
