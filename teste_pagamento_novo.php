<?php
/**
 * Teste: Criar um novo pagamento com R$ 1,00
 * Execute: php teste_pagamento_novo.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\PagBankPixService;

echo "🧪 Teste: Criar Pagamento R$ 1,00\n";
echo str_repeat("=", 70) . "\n\n";

$pagbankService = new PagBankPixService();

echo "1️⃣ Criando pedido PagBank com R$ 1,00...\n\n";

// Teste direto na API
$token = config('wifi.payment_gateways.pix.pagbank_token');

$payload = [
    'reference_id' => 'TEST_' . time(),
    'customer' => [
        'name' => 'Cliente Teste',
        'email' => 'teste@exemplo.com.br',
        'tax_id' => '12345678909',
        'phones' => [
            [
                'country' => '55',
                'area' => '63',
                'number' => '999999999',
                'type' => 'MOBILE'
            ]
        ]
    ],
    'items' => [
        [
            'reference_id' => 'ITEM_TEST',
            'name' => 'WiFi Tocantins Express - Teste',
            'quantity' => 1,
            'unit_amount' => 100 // R$ 1,00 em centavos
        ]
    ],
    'qr_codes' => [
        [
            'amount' => ['value' => 100] // R$ 1,00 em centavos
            // SEM arrangements = PIX universal
        ]
    ]
    // SEM notification_urls para teste local
];

$ch = curl_init('https://api.pagseguro.com/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = [
    'success' => $httpCode === 201,
    'http_code' => $httpCode,
    'response' => json_decode($response, true)
];

if ($httpCode === 201) {
    $data = $result['response'];
    $qrCode = $data['qr_codes'][0] ?? null;
    
    $result = [
        'success' => true,
        'order_id' => $data['id'],
        'reference_id' => $data['reference_id'],
        'amount' => 1.00,
        'status' => 'WAITING',
        'expires_at' => $qrCode['expiration_date'] ?? null,
        'qr_code_text' => $qrCode['text'] ?? null
    ];
} else {
    $result = [
        'success' => false,
        'message' => 'Erro HTTP ' . $httpCode . ': ' . $response
    ];
}

if ($result['success'] ?? false) {
    echo "✅ QR Code gerado com sucesso!\n\n";
    echo "📋 Detalhes:\n";
    echo "   Order ID: {$result['order_id']}\n";
    echo "   Reference ID: {$result['reference_id']}\n";
    echo "   Valor: R$ {$result['amount']}\n";
    echo "   Status: {$result['status']}\n";
    echo "   Expira em: {$result['expires_at']}\n\n";
    
    echo "🔗 Código PIX (primeiros 100 caracteres):\n";
    echo "   " . substr($result['qr_code_text'], 0, 100) . "...\n\n";
    
    echo "✅ SUCESSO! O sistema está funcionando corretamente.\n";
    echo "   Agora você pode testar pagando este QR Code em qualquer banco.\n";
} else {
    echo "❌ ERRO ao gerar QR Code:\n";
    echo "   " . ($result['message'] ?? 'Erro desconhecido') . "\n\n";
    
    if (isset($result['error_type'])) {
        echo "   Tipo: {$result['error_type']}\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
