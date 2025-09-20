<?php

echo "=== TESTE WEBHOOK MANUAL ===\n\n";

// Simular webhook do pagamento 50
$webhookData = [
    'event' => 'OPENPIX:CHARGE_COMPLETED',
    'charge' => [
        'correlationID' => 'TXN_1758389524_0A8A4334',  // Transaction ID do pagamento 50
        'status' => 'COMPLETED',
        'globalID' => 'Q2hhcmdlOjY4Y2VlNGZjODdhOGU1MmE0YzUwMWU4NA==',
        'value' => 5,
        'customer' => [
            'correlationID' => 'user-52'
        ]
    ],
    'pix' => [
        'time' => date('Y-m-d\TH:i:s.000\Z'),
        'status' => 'CONFIRMED',
        'value' => 5,
        'endToEndId' => 'E0000020820250920173417867332525'
    ]
];

echo "Dados do webhook:\n";
echo json_encode($webhookData, JSON_PRETTY_PRINT) . "\n\n";

// Testar webhook endpoint
echo "=== ENVIANDO WEBHOOK ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.tocantinstransportewifi.com.br/webhook/woovi');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Woovi-Webhook/1.0',
    'X-Webhook-Signature: test'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";
if ($error) {
    echo "Erro cURL: " . $error . "\n";
}

// Testar outros endpoints
echo "\n=== TESTANDO OUTROS ENDPOINTS ===\n";

$endpoints = [
    '/webhook/woovi/created',
    '/webhook/woovi/completed', 
    '/webhook/woovi/unified'
];

foreach ($endpoints as $endpoint) {
    echo "\nTestando: $endpoint\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.tocantinstransportewifi.com.br' . $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Woovi-Webhook/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP: $httpCode | Response: " . substr($response, 0, 100) . "\n";
}

echo "\n=== FIM DOS TESTES ===\n";
