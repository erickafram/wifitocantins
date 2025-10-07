<?php
/**
 * Teste final PagBank com email correto
 */

echo "\n╔══════════════════════════════════════════════════════╗\n";
echo "║         TESTE FINAL PAGBANK PIX                     ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

$token = "7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76";
$merchantEmail = "erickafram10@gmail.com";
$customerEmail = "cliente.wifi@tocantinstransportewifi.com.br"; // Email DIFERENTE
$baseUrl = 'https://sandbox.api.pagseguro.com'; // SANDBOX

echo "🔧 Configuração:\n";
echo "Ambiente: SANDBOX\n";
echo "Merchant Email: $merchantEmail\n";
echo "Customer Email: $customerEmail\n";
echo "Token: " . substr($token, 0, 30) . "...\n\n";

$referenceId = 'WIFI_' . time();
$amount = 10; // 10 centavos

$payload = [
    'reference_id' => $referenceId,
    'customer' => [
        'name' => 'Cliente WiFi Tocantins',
        'email' => $customerEmail, // Email DIFERENTE do vendedor
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
            'reference_id' => $referenceId,
            'name' => 'WiFi Tocantins Express',
            'quantity' => 1,
            'unit_amount' => $amount
        ]
    ],
    'qr_codes' => [
        [
            'amount' => [
                'value' => $amount
            ],
            'arrangements' => ['PAGBANK']
        ]
    ],
    'notification_urls' => [
        'https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank'
    ]
];

echo "💳 Criando pedido...\n";
echo "Reference ID: $referenceId\n";
echo "Valor: R$ 0,10\n\n";

$ch = curl_init($baseUrl . '/orders');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "📊 Resultado:\n";
echo "Status HTTP: $httpCode\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ ✅ ✅ SUCESSO! ✅ ✅ ✅\n\n";
    echo "Order ID: " . ($data['id'] ?? 'N/A') . "\n";
    echo "Reference ID: " . ($data['reference_id'] ?? 'N/A') . "\n";
    echo "Status: " . ($data['status'] ?? 'N/A') . "\n\n";
    
    if (isset($data['qr_codes'][0])) {
        $qrCode = $data['qr_codes'][0];
        
        echo "💰 QR CODE PIX:\n";
        echo str_repeat("=", 60) . "\n";
        echo "QR Code ID: " . ($qrCode['id'] ?? 'N/A') . "\n";
        echo "Valor: R$ " . number_format(($qrCode['amount']['value'] ?? 0) / 100, 2, ',', '.') . "\n\n";
        
        if (isset($qrCode['text'])) {
            echo "Código Copia e Cola:\n";
            echo $qrCode['text'] . "\n\n";
            
            $imageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrCode['text']);
            echo "URL da Imagem:\n";
            echo $imageUrl . "\n\n";
        }
        
        if (isset($qrCode['expiration_date'])) {
            echo "Expira em: " . $qrCode['expiration_date'] . "\n";
        }
        echo str_repeat("=", 60) . "\n\n";
    }
    
    echo "🎉 CONFIGURAÇÃO CORRETA PARA O .ENV:\n";
    echo str_repeat("=", 60) . "\n";
    echo "PIX_GATEWAY=pagbank\n";
    echo "PIX_ENVIRONMENT=sandbox\n";
    echo "PAGBANK_TOKEN=7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76\n";
    echo "PAGBANK_EMAIL=erickafram10@gmail.com\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "📝 PRÓXIMOS PASSOS:\n";
    echo "1. Edite o arquivo .env e faça as alterações acima\n";
    echo "2. Execute: php artisan config:clear\n";
    echo "3. Teste no portal novamente\n\n";
    
} else {
    echo "❌ ERRO:\n\n";
    
    if (isset($data['error_messages'])) {
        echo "Mensagens de erro:\n";
        foreach ($data['error_messages'] as $error) {
            echo "- " . ($error['description'] ?? 'Erro desconhecido') . "\n";
            if (isset($error['parameter_name'])) {
                echo "  Campo: " . $error['parameter_name'] . "\n";
            }
        }
    } else {
        echo "Resposta:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    echo "\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "                    FIM DO TESTE                        \n";
echo "═══════════════════════════════════════════════════════\n\n";

