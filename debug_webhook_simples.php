<?php
/**
 * DIAGNÓSTICO WEBHOOK AUTOMÁTICO - VERSÃO SIMPLES
 * Sem usar Laravel, apenas verificações básicas
 */

echo "🔍 DIAGNÓSTICO WEBHOOK AUTOMÁTICO WOOVI\n";
echo "=====================================\n\n";

// 1. VERIFICAR CONFIGURAÇÃO BÁSICA
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO:\n";

// Ler .env
$envFile = __DIR__ . '/.env';
$envVars = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
    echo "✅ Arquivo .env carregado\n";
} else {
    echo "❌ Arquivo .env não encontrado\n";
    exit(1);
}

$appUrl = $envVars['APP_URL'] ?? 'https://www.tocantinstransportewifi.com.br';
echo "🌐 APP_URL: {$appUrl}\n";

// 2. VERIFICAR CONEXÃO COM BANCO
echo "\n2️⃣ VERIFICANDO BANCO DE DADOS:\n";

try {
    $pdo = new PDO(
        "mysql:host={$envVars['DB_HOST']};port={$envVars['DB_PORT']};dbname={$envVars['DB_DATABASE']}",
        $envVars['DB_USERNAME'],
        $envVars['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexão com banco estabelecida\n";
    
    // Verificar pagamento 53
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = 53");
    $stmt->execute();
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo "✅ PAGAMENTO 53 ENCONTRADO:\n";
        echo "   - Status: {$payment['status']}\n";
        echo "   - Gateway ID: {$payment['gateway_payment_id']}\n";
        echo "   - Paid At: {$payment['paid_at']}\n";
        echo "   - Updated At: {$payment['updated_at']}\n";
        
        // Verificar usuário
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$payment['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "\n👤 USUÁRIO ASSOCIADO:\n";
            echo "   - MAC: {$user['mac_address']}\n";
            echo "   - Status: {$user['status']}\n";
            echo "   - Connected At: {$user['connected_at']}\n";
            echo "   - Expires At: {$user['expires_at']}\n";
        }
    } else {
        echo "❌ Pagamento 53 não encontrado no banco\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
}

echo "\n3️⃣ TESTANDO WEBHOOKS:\n";

// 3. TESTAR ENDPOINTS DE WEBHOOK
$webhookUrls = [
    'Principal' => "{$appUrl}/api/payment/webhook/woovi",
    'Unified' => "{$appUrl}/api/payment/webhook/woovi/unified"
];

foreach ($webhookUrls as $name => $url) {
    echo "🧪 Testando {$name}: {$url}\n";
    
    $testData = json_encode([
        'event' => 'OPENPIX:CHARGE_COMPLETED',
        'charge' => [
            'globalID' => 'Q2hhcmdlOjY4Y2VmOTRhOWExMDUzZGI4ZDAxNTZmNA==',
            'status' => 'COMPLETED'
        ],
        'pix' => [
            'time' => date('c'),
            'value' => 5
        ]
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $testData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: OpenPix-Webhook/1.0',
            'X-Forwarded-For: 54.208.119.170'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erro cURL: {$error}\n";
    } else {
        echo "📊 HTTP {$httpCode}\n";
        if ($httpCode != 200) {
            echo "📄 Resposta: " . substr($response, 0, 200) . "\n";
        } else {
            echo "✅ Webhook respondeu OK\n";
        }
    }
    echo "\n";
}

echo "4️⃣ TESTANDO ENDPOINT ALLOW:\n";

// 4. TESTAR ENDPOINT ALLOW
$allowUrl = "{$appUrl}/api/mikrotik/allow";
$testMac = '02:BD:48:D9:F1:A4';

echo "🧪 Testando liberação: {$allowUrl}\n";
echo "🎯 MAC: {$testMac}\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $allowUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['mac_address' => $testMac]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erro cURL: {$error}\n";
} else {
    echo "📊 HTTP {$httpCode}\n";
    echo "📄 Resposta: {$response}\n";
}

echo "\n5️⃣ VERIFICANDO LOGS:\n";

// 5. VERIFICAR LOGS
$logPaths = [
    'Laravel' => __DIR__ . '/storage/logs/laravel.log',
    'PHP Error' => ini_get('error_log')
];

foreach ($logPaths as $name => $path) {
    if ($path && file_exists($path)) {
        echo "📝 {$name} Log: {$path}\n";
        $logs = file_get_contents($path);
        $recentLogs = array_slice(explode("\n", $logs), -20);
        
        $relevantLogs = array_filter($recentLogs, function($line) {
            return stripos($line, 'webhook') !== false || 
                   stripos($line, 'woovi') !== false || 
                   stripos($line, 'payment') !== false ||
                   stripos($line, 'error') !== false;
        });
        
        if ($relevantLogs) {
            echo "🔍 Logs relevantes:\n";
            foreach (array_slice($relevantLogs, -5) as $log) {
                echo "   " . trim($log) . "\n";
            }
        } else {
            echo "⚠️ Nenhum log relevante encontrado\n";
        }
    } else {
        echo "❌ {$name} Log não encontrado: {$path}\n";
    }
    echo "\n";
}

echo "6️⃣ RECOMENDAÇÕES:\n";
echo "================\n";

if (isset($payment) && $payment['status'] === 'completed') {
    echo "✅ Pagamento 53 está COMPLETED no banco\n";
    echo "⚠️ PROBLEMA: Webhook automático não funciona\n";
    echo "\n🔧 SOLUÇÕES URGENTES:\n";
    echo "1. ✅ Execute no MikroTik:\n";
    echo "   /ip hotspot walled-garden add dst-host=api.openpix.com.br action=allow comment=\"Woovi API\"\n";
    echo "\n2. 🔗 Configure no painel Woovi:\n";
    echo "   URL: {$appUrl}/api/payment/webhook/woovi/unified\n";
    echo "\n3. 📞 Contate suporte Woovi se problema persistir\n";
    echo "\n4. 🔄 Webhook deve funcionar automaticamente após correções\n";
}

echo "\n✅ DIAGNÓSTICO CONCLUÍDO!\n";
