<?php

echo "🔍 DIAGNÓSTICO RÁPIDO DO SISTEMA\n";
echo "===============================\n\n";

// 1. Verificar se usuário conectado existe
echo "1. 📊 Verificando usuários conectados...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tocantinstransportewifi", "root", "password");
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'connected'");
    $connected = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'connected' AND expires_at > NOW()");
    $active = $stmt->fetch()['total'];
    
    echo "   ✅ Usuários conectados: {$connected}\n";
    echo "   ✅ Usuários ativos: {$active}\n\n";
    
    // 2. Verificar últimos pagamentos
    echo "2. 💳 Verificando pagamentos recentes...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments WHERE status = 'completed' AND paid_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $recentPayments = $stmt->fetch()['total'];
    echo "   ✅ Pagamentos na última hora: {$recentPayments}\n\n";
    
    // 3. Testar endpoint
    echo "3. 🌐 Testando endpoint de sincronização...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer mikrotik-sync-2024']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "   ✅ Endpoint OK: HTTP {$httpCode}\n";
        echo "   📊 MACs para liberar: " . count($data['allow_users']) . "\n";
        echo "   📊 IPs para bypass: " . count($data['ip_bindings']) . "\n";
        if (!empty($data['allow_users'])) {
            echo "   📋 Primeiros MACs: " . implode(', ', array_slice($data['allow_users'], 0, 3)) . "\n";
        }
    } else {
        echo "   ❌ Endpoint falhou: HTTP {$httpCode}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n✅ DIAGNÓSTICO CONCLUÍDO!\n";
?>
