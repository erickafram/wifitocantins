<?php

// Consulta direta ao banco sem usar Laravel
$servername = "127.0.0.1";
$username = "tocantinstransportewifi";
$password = "@@2025@@Ekb";
$dbname = "tocantinstransportewifi";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DIAGNÓSTICO DIRETO DO BANCO ===\n\n";
    
    // Consultar pagamento 50
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = 50");
    $stmt->execute();
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo "=== PAGAMENTO 50 ===\n";
        echo "Status: " . $payment['status'] . "\n";
        echo "Gateway ID: " . $payment['gateway_payment_id'] . "\n";
        echo "Transaction ID: " . $payment['transaction_id'] . "\n";
        echo "Created: " . $payment['created_at'] . "\n";
        echo "Updated: " . $payment['updated_at'] . "\n";
        echo "Paid At: " . $payment['paid_at'] . "\n";
        echo "User ID: " . $payment['user_id'] . "\n";
        
        // Consultar usuário
        $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt2->execute([$payment['user_id']]);
        $user = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "\n=== USUÁRIO " . $user['id'] . " ===\n";
            echo "MAC: " . $user['mac_address'] . "\n";
            echo "IP: " . $user['ip_address'] . "\n";
            echo "Status: " . $user['status'] . "\n";
            echo "Connected: " . $user['connected_at'] . "\n";
            echo "Expires: " . $user['expires_at'] . "\n";
        }
        
        // Verificar se há webhook data
        if ($payment['payment_data']) {
            echo "\n=== WEBHOOK DATA ===\n";
            $data = json_decode($payment['payment_data'], true);
            if (isset($data['event'])) {
                echo "Event: " . $data['event'] . "\n";
            }
            if (isset($data['pix']['status'])) {
                echo "PIX Status: " . $data['pix']['status'] . "\n";
            }
            if (isset($data['pix']['time'])) {
                echo "PIX Time: " . $data['pix']['time'] . "\n";
            }
        } else {
            echo "\n❌ SEM WEBHOOK DATA - WEBHOOK NÃO PROCESSADO!\n";
        }
        
    } else {
        echo "❌ Pagamento 50 não encontrado\n";
    }
    
    // Verificar últimos pagamentos
    echo "\n=== ÚLTIMOS 5 PAGAMENTOS ===\n";
    $stmt = $pdo->query("SELECT id, user_id, status, gateway_payment_id, created_at, updated_at FROM payments ORDER BY id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Status: {$row['status']} | Gateway: {$row['gateway_payment_id']} | Updated: {$row['updated_at']}\n";
    }
    
    // Verificar se webhook endpoint está sendo chamado
    echo "\n=== TESTAR WEBHOOK ENDPOINT ===\n";
    $webhookData = [
        'event' => 'OPENPIX:CHARGE_COMPLETED',
        'charge' => [
            'correlationID' => 'TXN_1758389524_0A8A4334',
            'status' => 'COMPLETED'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.tocantinstransportewifi.com.br/webhook/woovi');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Woovi-Webhook/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
    if ($error) {
        echo "Erro: " . $error . "\n";
    }
    
} catch(PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
