<?php
/**
 * 🔍 TESTE SIMPLES DE SCOPE SANTANDER PIX
 * 
 * Execute: php testar_scope_santander_simples.php
 * 
 * Este script faz uma requisição OAuth e mostra claramente
 * se o scope está vazio ou presente.
 */

echo "\n";
echo "==========================================\n";
echo "🔍 TESTE DE SCOPE SANTANDER PIX\n";
echo "==========================================\n\n";

// Carregar Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Obter configurações
$clientId = config('wifi.payment_gateways.pix.client_id');
$clientSecret = config('wifi.payment_gateways.pix.client_secret');
$environment = config('wifi.payment_gateways.pix.environment', 'sandbox');
$certificatePath = storage_path('app/' . config('wifi.payment_gateways.pix.certificate_path'));
$certificatePassword = config('wifi.payment_gateways.pix.certificate_password', '');

echo "📋 Configurações:\n";
echo "   Client ID: " . substr($clientId, 0, 10) . "..." . substr($clientId, -4) . "\n";
echo "   Ambiente: $environment\n";

$baseUrl = $environment === 'production' 
    ? 'https://trust-pix.santander.com.br'
    : 'https://trust-pix-h.santander.com.br';

echo "   Base URL: $baseUrl\n\n";

// Verificar certificado
if (!file_exists($certificatePath)) {
    echo "❌ Certificado não encontrado: $certificatePath\n";
    exit(1);
}

echo "🔐 Fazendo requisição OAuth...\n\n";

// Preparar autenticação Basic
$basicAuth = base64_encode($clientId . ':' . $clientSecret);

// Fazer requisição
$ch = curl_init($baseUrl . '/auth/oauth/v2/token');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $basicAuth,
        'Content-Type: application/x-www-form-urlencoded',
    ],
    CURLOPT_POSTFIELDS => http_build_query([
        'grant_type' => 'client_credentials',
        'scope' => 'cob.write cob.read pix.write pix.read',
    ]),
    CURLOPT_SSLCERT => $certificatePath,
    CURLOPT_SSLCERTPASSWD => $certificatePassword,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Status HTTP: $httpCode\n\n";

if ($httpCode === 200) {
    echo "✅ Token obtido com sucesso!\n\n";
    
    $data = json_decode($response, true);
    
    if (!isset($data['access_token'])) {
        echo "❌ Token não encontrado na resposta\n";
        echo "Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        exit(1);
    }
    
    $accessToken = $data['access_token'];
    
    echo "🔑 Token JWT (primeiros 80 caracteres):\n";
    echo "   " . substr($accessToken, 0, 80) . "...\n\n";
    
    // Decodificar JWT
    echo "🔍 Decodificando JWT...\n\n";
    
    $parts = explode('.', $accessToken);
    
    if (count($parts) !== 3) {
        echo "❌ Token JWT inválido\n";
        exit(1);
    }
    
    // Decodificar payload (segunda parte)
    $payload = $parts[1];
    
    // Adicionar padding se necessário
    $remainder = strlen($payload) % 4;
    if ($remainder) {
        $payload .= str_repeat('=', 4 - $remainder);
    }
    
    // Decodificar base64url (substituir - por + e _ por /)
    $payload = str_replace(['-', '_'], ['+', '/'], $payload);
    $decoded = base64_decode($payload);
    $payloadData = json_decode($decoded, true);
    
    echo "==========================================\n";
    echo "📋 PAYLOAD DO TOKEN JWT:\n";
    echo "==========================================\n";
    echo json_encode($payloadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Análise do scope
    echo "==========================================\n";
    echo "🎯 ANÁLISE DO SCOPE:\n";
    echo "==========================================\n";
    
    $scope = $payloadData['scope'] ?? '';
    
    if (empty($scope)) {
        echo "\033[0;31m❌ PROBLEMA CONFIRMADO: SCOPE VAZIO!\033[0m\n\n";
        echo "   Valor atual: \"" . ($scope ?: '(vazio)') . "\"\n";
        echo "   Valor esperado: \"cob.write cob.read pix.write pix.read\"\n\n";
        echo "\033[1;33m⚠️  ESTE É O PROBLEMA!\033[0m\n";
        echo "   O token não possui permissões para acessar a API PIX.\n";
        echo "   A aplicação 'STARLINK QR CODE' precisa ter a\n";
        echo "   'API Pix - Geração de QRCode' habilitada no portal.\n\n";
    } else {
        echo "\033[0;32m✅ SCOPE PRESENTE!\033[0m\n";
        echo "   Scope: $scope\n\n";
    }
    
    // Verificar outros campos
    echo "==========================================\n";
    echo "📊 OUTROS CAMPOS RELEVANTES:\n";
    echo "==========================================\n";
    
    $aud = $payloadData['aud'] ?? 'N/A';
    $iss = $payloadData['iss'] ?? 'N/A';
    $clientIdToken = $payloadData['clientId'] ?? 'N/A';
    $exp = $payloadData['exp'] ?? null;
    
    echo "   Audience (aud): $aud\n";
    echo "   Issuer (iss): $iss\n";
    echo "   Client ID: $clientIdToken\n";
    
    if ($exp) {
        $expiresAt = date('Y-m-d H:i:s', $exp);
        echo "   Expira em: $expiresAt\n";
    }
    
    echo "\n";
    
    if ($aud === 'Santander Open API') {
        echo "\033[1;33m⚠️  Audience é genérico (não específico para PIX)\033[0m\n\n";
    }
    
    echo "==========================================\n";
    echo "📸 COPIE ESTA SAÍDA E ENVIE AO SANTANDER\n";
    echo "==========================================\n\n";
    
    echo "Informações para o suporte:\n";
    echo "  • Aplicação: STARLINK QR CODE\n";
    echo "  • Client ID: $clientId\n";
    echo "  • Problema: Token OAuth sem scope PIX\n";
    echo "  • Evidência: Campo 'scope' está " . (empty($scope) ? "VAZIO" : "presente") . " no JWT\n";
    echo "  • Ambiente: $environment\n\n";
    
} else {
    echo "\033[0;31m❌ Erro ao obter token!\033[0m\n\n";
    echo "Status HTTP: $httpCode\n";
    
    if ($error) {
        echo "Erro cURL: $error\n";
    }
    
    echo "Resposta:\n";
    $errorData = json_decode($response, true);
    if ($errorData) {
        echo json_encode($errorData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
    echo "\n";
}

echo "==========================================\n";
echo "✅ TESTE CONCLUÍDO\n";
echo "==========================================\n\n";

