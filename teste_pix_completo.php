<?php
/**
 * Teste completo: Gerar QR Code PIX que aceita qualquer banco
 * Execute: php teste_pix_completo.php
 */

$token = "c75a2308-ec9d-4825-94fd-bacba8a7248344f58a634d1b857348dba39f6a5b6c957b2a-2890-4da4-9866-af24b6eee984";

echo "🔧 Teste Completo: QR Code PIX PagBank\n";
echo str_repeat("=", 70) . "\n\n";

// Teste 1: QR Code SEM arrangements (aceita qualquer banco)
echo "1️⃣ Gerando QR Code PIX PADRÃO (aceita qualquer banco):\n\n";

$payload = [
    'reference_id' => 'TEST_UNIVERSAL_' . time(),
    'customer' => [
        'name' => 'Cliente Teste',
        'email' => 'teste@exemplo.com.br',
        'tax_id' => '12345678909',
        'phones' => [
            [
                'country' => '55',
                'area' => '11',
                'number' => '999999999',
                'type' => 'MOBILE'
            ]
        ]
    ],
    'items' => [
        [
            'reference_id' => 'ITEM_TEST',
            'name' => 'Teste PIX Universal',
            'quantity' => 1,
            'unit_amount' => 100 // R$ 1,00
        ]
    ],
    'qr_codes' => [
        [
            'amount' => ['value' => 100]
            // SEM 'arrangements' = Aceita PIX de QUALQUER banco
        ]
    ]
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
$error = curl_error($ch);
curl_close($ch);

echo "   Status HTTP: $httpCode\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
} else {
    $data = json_decode($response, true);
    
    if ($httpCode === 201) {
        echo "   ✅ QR Code gerado com sucesso!\n\n";
        
        $qrCode = $data['qr_codes'][0] ?? null;
        
        if ($qrCode) {
            echo "   📋 Detalhes do QR Code:\n";
            echo "   ├─ Order ID: " . $data['id'] . "\n";
            echo "   ├─ QR Code ID: " . $qrCode['id'] . "\n";
            echo "   ├─ Valor: R$ 1,00\n";
            echo "   ├─ Validade: " . ($qrCode['expiration_date'] ?? 'N/A') . "\n";
            
            // Verificar se tem arrangements na resposta
            if (isset($qrCode['arrangements'])) {
                echo "   ├─ Arrangements: " . json_encode($qrCode['arrangements']) . "\n";
                echo "   └─ ⚠️ ATENÇÃO: QR Code restrito!\n";
            } else {
                echo "   └─ ✅ PIX UNIVERSAL (aceita qualquer banco)\n";
            }
            
            echo "\n   🔗 Código PIX (copia e cola):\n";
            echo "   " . ($qrCode['text'] ?? 'N/A') . "\n\n";
            
            // Analisar o código EMV
            $emvCode = $qrCode['text'] ?? '';
            if (strpos($emvCode, 'BR.COM.PAGBANK') !== false) {
                echo "   ℹ️ Código contém identificador PagBank\n";
            }
            if (strpos($emvCode, 'br.gov.bcb.pix') !== false) {
                echo "   ℹ️ Código contém identificador PIX padrão BCB\n";
            }
            
            echo "\n   📱 Teste este código em:\n";
            echo "   ├─ App do seu banco (qualquer banco)\n";
            echo "   ├─ App PagBank\n";
            echo "   └─ Qualquer app que suporte PIX\n";
        }
    } else {
        echo "   ❌ Erro ao gerar QR Code\n";
        echo "   Resposta: " . substr($response, 0, 300) . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";

// Teste 2: Comparação com arrangements
echo "\n2️⃣ Comparação: QR Code COM arrangements (apenas PagBank):\n\n";

$payloadRestricted = $payload;
$payloadRestricted['reference_id'] = 'TEST_RESTRICTED_' . time();
$payloadRestricted['qr_codes'][0]['arrangements'] = ['PAGBANK'];

$ch = curl_init('https://api.pagseguro.com/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payloadRestricted),
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

echo "   Status HTTP: $httpCode\n";

if ($httpCode === 201) {
    $data = json_decode($response, true);
    $qrCode = $data['qr_codes'][0] ?? null;
    
    echo "   ✅ QR Code gerado (RESTRITO)\n";
    echo "   ├─ Arrangements: " . json_encode($qrCode['arrangements'] ?? []) . "\n";
    echo "   └─ ⚠️ Aceita apenas contas PagBank\n";
} else {
    echo "   Resposta: " . substr($response, 0, 200) . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "\n📊 CONCLUSÃO:\n";
echo "   • SEM 'arrangements' = PIX universal (qualquer banco) ✅\n";
echo "   • COM 'arrangements' = Restrito ao PagBank ⚠️\n";
echo "\n   Use o código SEM arrangements no seu sistema!\n";
echo "\n" . str_repeat("=", 70) . "\n";
