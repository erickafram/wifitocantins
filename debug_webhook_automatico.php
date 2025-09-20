<?php
/**
 * DIAGNÓSTICO WEBHOOK AUTOMÁTICO WOOVI
 * Verifica por que webhooks só funcionam quando reenviados manualmente
 */

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "🔍 DIAGNÓSTICO WEBHOOK AUTOMÁTICO WOOVI\n";
echo "=====================================\n\n";

// 1. VERIFICAR PAGAMENTO 53
echo "1️⃣ VERIFICANDO PAGAMENTO 53:\n";
$payment = Payment::find(53);
if ($payment) {
    echo "✅ Pagamento encontrado:\n";
    echo "   - ID: {$payment->id}\n";
    echo "   - Status: {$payment->status}\n";
    echo "   - Gateway ID: {$payment->gateway_payment_id}\n";
    echo "   - Transaction ID: {$payment->transaction_id}\n";
    echo "   - Paid At: {$payment->paid_at}\n";
    echo "   - Updated At: {$payment->updated_at}\n";
    
    // Verificar usuário
    $user = User::find($payment->user_id);
    if ($user) {
        echo "\n👤 USUÁRIO ASSOCIADO:\n";
        echo "   - ID: {$user->id}\n";
        echo "   - MAC: {$user->mac_address}\n";
        echo "   - Status: {$user->status}\n";
        echo "   - Connected At: {$user->connected_at}\n";
        echo "   - Expires At: {$user->expires_at}\n";
    }
} else {
    echo "❌ Pagamento 53 não encontrado!\n";
}

echo "\n2️⃣ VERIFICANDO CONFIGURAÇÃO WEBHOOK:\n";

// 2. VERIFICAR URLs DE WEBHOOK
$appUrl = config('app.url');
echo "✅ APP_URL: {$appUrl}\n";

$webhookUrls = [
    'Principal' => "{$appUrl}/api/payment/webhook/woovi",
    'Unified' => "{$appUrl}/api/payment/webhook/woovi/unified",
    'Created' => "{$appUrl}/api/payment/webhook/woovi/created",
    'Transaction' => "{$appUrl}/api/payment/webhook/woovi/transaction"
];

foreach ($webhookUrls as $name => $url) {
    echo "🔗 {$name}: {$url}\n";
}

echo "\n3️⃣ TESTANDO CONECTIVIDADE WEBHOOK:\n";

// 3. TESTAR CONECTIVIDADE
foreach ($webhookUrls as $name => $url) {
    echo "🧪 Testando {$name}...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'event' => 'OPENPIX:CHARGE_COMPLETED',
        'charge' => ['globalID' => 'TEST'],
        'pix' => ['time' => date('c')]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: OpenPix-Webhook/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erro: {$error}\n";
    } else {
        echo "📊 HTTP {$httpCode}: " . substr($response, 0, 100) . "\n";
    }
}

echo "\n4️⃣ VERIFICANDO LOGS RECENTES:\n";

// 4. VERIFICAR LOGS
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = array_slice(explode("\n", $logs), -50);
    
    $webhookLogs = array_filter($recentLogs, function($line) {
        return strpos($line, 'webhook') !== false || 
               strpos($line, 'Woovi') !== false || 
               strpos($line, 'payment') !== false;
    });
    
    if ($webhookLogs) {
        echo "📝 LOGS RELACIONADOS AO WEBHOOK:\n";
        foreach (array_slice($webhookLogs, -10) as $log) {
            echo "   {$log}\n";
        }
    } else {
        echo "⚠️ Nenhum log de webhook encontrado recentemente\n";
    }
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

echo "\n5️⃣ VERIFICANDO CONFIGURAÇÃO WOOVI:\n";

// 5. VERIFICAR CONFIG WOOVI
$wooviConfig = [
    'APP_ID' => config('services.woovi.app_id') ? 'CONFIGURADO' : 'NÃO CONFIGURADO',
    'APP_SECRET' => config('services.woovi.app_secret') ? 'CONFIGURADO' : 'NÃO CONFIGURADO',
    'ENVIRONMENT' => config('services.woovi.environment', 'production'),
    'PIX_KEY' => config('services.woovi.pix_key', 'NÃO CONFIGURADO')
];

foreach ($wooviConfig as $key => $value) {
    echo "🔧 {$key}: {$value}\n";
}

echo "\n6️⃣ TESTANDO ENDPOINT /api/mikrotik/allow:\n";

// 6. TESTAR ENDPOINT ALLOW
$testMac = '02:BD:48:D9:F1:A4';
$allowUrl = "{$appUrl}/api/mikrotik/allow";

echo "🧪 Testando liberação para MAC: {$testMac}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $allowUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'mac_address' => $testMac
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

echo "\n7️⃣ RECOMENDAÇÕES:\n";
echo "================\n";

if ($payment && $payment->status === 'completed') {
    echo "✅ Pagamento está COMPLETED no banco\n";
    echo "⚠️ PROBLEMA: Webhook automático não está funcionando\n";
    echo "\n🔧 SOLUÇÕES:\n";
    echo "1. Verificar URL do webhook no painel Woovi\n";
    echo "2. Verificar se Woovi consegue acessar seu servidor\n";
    echo "3. Verificar logs do servidor web (nginx/apache)\n";
    echo "4. Testar webhook manualmente\n";
    
    echo "\n📝 URL CORRETA PARA WOOVI:\n";
    echo "   {$appUrl}/api/payment/webhook/woovi/unified\n";
}

echo "\n✅ DIAGNÓSTICO CONCLUÍDO!\n";
