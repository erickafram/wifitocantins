<?php

// Teste do endpoint de report real MAC
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\MikrotikSyncController;

// Simular request
$data = [
    'mac_address' => 'e4:84:d3:f4:7f:eb',
    'ip_address' => '10.10.10.100', 
    'token' => 'mikrotik-sync-2024',
    'transaction_id' => 'TXN_1758499536_B319D28A'
];

echo "🧪 TESTANDO ENDPOINT REPORT REAL MAC\n";
echo "=====================================\n";
echo "MAC: " . $data['mac_address'] . "\n";
echo "IP: " . $data['ip_address'] . "\n";
echo "Transaction: " . $data['transaction_id'] . "\n\n";

// Fazer request via cURL para o servidor
$url = 'http://www.tocantinstransportewifi.com.br/api/mikrotik-sync/report-real-mac';
$postData = http_build_query($data);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Host: www.tocantinstransportewifi.com.br'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

echo "📡 RESPOSTA DO SERVIDOR:\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ Erro cURL: $error\n";
} else {
    echo "✅ Resposta recebida:\n";
    echo $response . "\n";
    
    $json = json_decode($response, true);
    if ($json) {
        echo "\n📋 DADOS PROCESSADOS:\n";
        echo "Success: " . ($json['success'] ? 'SIM' : 'NÃO') . "\n";
        echo "User Found: " . ($json['user_found'] ?? 'N/A') . "\n";
        echo "Has Access: " . ($json['has_access'] ?? 'N/A') . "\n";
        echo "Message: " . ($json['message'] ?? 'N/A') . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
