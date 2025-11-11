<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

echo "=== DIAGNÓSTICO PAGAMENTO 50 ===\n";

// Buscar pagamento 50
$payment = \App\Models\Payment::find(50);
if ($payment) {
    echo "=== PAGAMENTO 50 ===\n";
    echo "Status: " . $payment->status . "\n";
    echo "Gateway ID: " . $payment->gateway_payment_id . "\n";
    echo "Transaction ID: " . $payment->transaction_id . "\n";
    echo "Created: " . $payment->created_at . "\n";
    echo "Updated: " . $payment->updated_at . "\n";
    echo "Paid At: " . $payment->paid_at . "\n";
    
    echo "\n=== USUÁRIO ===\n";
    $user = $payment->user;
    echo "ID: " . $user->id . "\n";
    echo "MAC: " . $user->mac_address . "\n";
    echo "IP: " . $user->ip_address . "\n";
    echo "Status: " . $user->status . "\n";
    echo "Connected: " . $user->connected_at . "\n";
    echo "Expires: " . $user->expires_at . "\n";
    
    // Verificar se há webhook data
    if ($payment->payment_data) {
        echo "\n=== WEBHOOK DATA ===\n";
        $data = json_decode($payment->payment_data, true);
        if (isset($data['event'])) {
            echo "Event: " . $data['event'] . "\n";
        }
        if (isset($data['pix']['status'])) {
            echo "PIX Status: " . $data['pix']['status'] . "\n";
        }
    }
    
} else {
    echo "❌ Pagamento 50 não encontrado\n";
}

// Verificar últimos logs de webhook
echo "\n=== VERIFICAR LOGS DE WEBHOOK ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $webhookLines = array_filter($lines, function($line) {
        return strpos($line, 'webhook') !== false || 
               strpos($line, 'WEBHOOK') !== false ||
               strpos($line, '93bf75daa4ea433f970532856e8e1250') !== false;
    });
    
    if (!empty($webhookLines)) {
        echo "Logs de webhook encontrados:\n";
        foreach (array_slice($webhookLines, -10) as $line) {
            echo $line . "\n";
        }
    } else {
        echo "❌ Nenhum log de webhook encontrado\n";
    }
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

// Testar endpoint de webhook manualmente
echo "\n=== TESTAR WEBHOOK ENDPOINT ===\n";
try {
    $url = 'https://www.tocantinstransportewifi.com.br/webhook/woovi';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'webhook']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Test: true'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Response: " . $response . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao testar webhook: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
