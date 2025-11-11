<?php
/**
 * Script de teste para validar token PagBank
 * Execute: php teste_token_pagbank.php
 */

// Token de PRODUÇÃO do .env
$token = "c75a2308-ec9d-4825-94fd-bacba8a7248344f58a634d1b857348dba39f6a5b6c957b2a-2890-4da4-9866-af24b6eee984";

echo "🔧 Testando Token PagBank\n";
echo str_repeat("=", 60) . "\n\n";

// Teste 1: Verificar formato do token
echo "1️⃣ Formato do Token:\n";
echo "   Token: " . substr($token, 0, 20) . "..." . substr($token, -20) . "\n";
echo "   Tamanho: " . strlen($token) . " caracteres\n\n";

// Teste 2: Tentar criar um pedido de teste
echo "2️⃣ Testando conexão com API de PRODUÇÃO:\n";
echo "   URL: https://api.pagseguro.com/orders\n\n";

$payload = [
    'reference_id' => 'TEST_' . time(),
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
            'name' => 'Teste de Token',
            'quantity' => 1,
            'unit_amount' => 100 // R$ 1,00
        ]
    ],
    'qr_codes' => [
        [
            'amount' => ['value' => 100],
            'arrangements' => ['PAGBANK']
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

echo "3️⃣ Resultado:\n";
echo "   Status HTTP: $httpCode\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
} else {
    $data = json_decode($response, true);
    
    if ($httpCode === 201) {
        echo "   ✅ TOKEN VÁLIDO! Pedido criado com sucesso\n";
        echo "   Order ID: " . ($data['id'] ?? 'N/A') . "\n";
        echo "\n   ⚠️ IMPORTANTE: Este é um pedido REAL de R$ 1,00\n";
        echo "   Você pode cancelá-lo no painel do PagBank\n";
    } elseif ($httpCode === 401) {
        echo "   ❌ TOKEN INVÁLIDO!\n";
        echo "   Erro: " . ($data['error_messages'][0]['description'] ?? 'Desconhecido') . "\n\n";
        
        echo "📋 POSSÍVEIS CAUSAS:\n";
        echo "   1. Token é de SANDBOX (não funciona em produção)\n";
        echo "   2. Token expirado ou revogado\n";
        echo "   3. Token sem permissões necessárias\n\n";
        
        echo "🔧 SOLUÇÃO:\n";
        echo "   1. Acesse: https://minhaconta.pagseguro.uol.com.br/\n";
        echo "   2. Vá em: Integrações → Chaves de API\n";
        echo "   3. Gere um NOVO token de PRODUÇÃO\n";
        echo "   4. Atualize no .env: PAGBANK_TOKEN=seu_novo_token\n";
        echo "   5. Execute: php artisan config:clear\n";
    } else {
        echo "   ⚠️ Erro inesperado\n";
        echo "   Resposta: " . substr($response, 0, 200) . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Teste concluído!\n";
