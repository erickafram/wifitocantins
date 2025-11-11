<?php
/**
 * Diagnóstico completo dos problemas de liberação
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use Illuminate\Support\Facades\Log;

echo "🔍 DIAGNÓSTICO COMPLETO - PROBLEMAS DE LIBERAÇÃO\n";
echo str_repeat("=", 60) . "\n";

// 1. VERIFICAR PAGAMENTOS COMPLETADOS
echo "\n1️⃣ PAGAMENTOS COMPLETADOS:\n";
$completedPayments = Payment::where('status', 'completed')
    ->with('user')
    ->orderBy('updated_at', 'desc')
    ->take(10)
    ->get();

foreach ($completedPayments as $payment) {
    $user = $payment->user;
    echo "   💳 Payment ID: {$payment->id}\n";
    echo "   👤 Usuário: {$user->name} ({$user->email})\n";
    echo "   📱 MAC: " . ($user->mac_address ?: 'NÃO DEFINIDO') . "\n";
    echo "   🔄 Status User: {$user->status}\n";
    echo "   ⏰ Pago em: {$payment->updated_at}\n";
    echo "   " . str_repeat("-", 40) . "\n";
}

if ($completedPayments->isEmpty()) {
    echo "   ⚠️ NENHUM PAGAMENTO COMPLETADO ENCONTRADO!\n";
}

// 2. VERIFICAR USUÁRIOS CONECTADOS
echo "\n2️⃣ USUÁRIOS COM STATUS 'CONNECTED':\n";
$connectedUsers = User::where('status', 'connected')
    ->orderBy('connected_at', 'desc')
    ->get();

foreach ($connectedUsers as $user) {
    echo "   🟢 {$user->name} | MAC: " . ($user->mac_address ?: 'NÃO DEFINIDO') . " | Conectado: {$user->connected_at}\n";
}

if ($connectedUsers->isEmpty()) {
    echo "   ⚠️ NENHUM USUÁRIO COM STATUS 'CONNECTED'!\n";
}

// 3. TESTAR ENDPOINT SYNC
echo "\n3️⃣ TESTANDO ENDPOINT SYNC:\n";
try {
    $syncToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
    $serverUrl = config('wifi.server_url', 'https://www.tocantinstransportewifi.com.br');
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $syncToken\r\n"
        ]
    ]);
    
    $response = file_get_contents("$serverUrl/api/mikrotik-sync/pending-users", false, $context);
    $data = json_decode($response, true);
    
    echo "   📡 Response: " . $response . "\n";
    echo "   📊 Allow users: " . (isset($data['allow_users']) ? count($data['allow_users']) : 0) . "\n";
    echo "   🚫 Block users: " . (isset($data['block_users']) ? count($data['block_users']) : 0) . "\n";
    
    if (isset($data['allow_users']) && !empty($data['allow_users'])) {
        echo "   ✅ MACs para liberar: " . implode(', ', $data['allow_users']) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERRO no endpoint: " . $e->getMessage() . "\n";
}

// 4. VERIFICAR CONFIGURAÇÕES
echo "\n4️⃣ CONFIGURAÇÕES IMPORTANTES:\n";
echo "   🔧 Mikrotik Sync Token: " . config('wifi.mikrotik_sync_token') . "\n";
echo "   🌐 Server URL: " . config('wifi.server_url') . "\n";
echo "   💰 Preço atual: R$ " . config('wifi.pricing.amount') . "\n";
echo "   ⏱️ Duração sessão: " . config('wifi.pricing.session_duration_hours', 24) . "h\n";

// 5. VERIFICAR LOGS RECENTES
echo "\n5️⃣ LOGS RECENTES DE PAGAMENTO:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -20);
    
    foreach ($recentLogs as $log) {
        if (strpos($log, 'wooviWebhook') !== false || strpos($log, 'LIBERACAO') !== false) {
            echo "   📝 " . trim($log) . "\n";
        }
    }
} else {
    echo "   ⚠️ Arquivo de log não encontrado\n";
}

// 6. SUGESTÕES DE CORREÇÃO
echo "\n6️⃣ SUGESTÕES DE CORREÇÃO:\n";
echo "   🔧 1. Verificar se MAC é capturado corretamente no frontend\n";
echo "   🔧 2. Verificar se perfil 'pago-total' bypassa Walled Garden\n";
echo "   🔧 3. Verificar webhooks Woovi (adicionar mais eventos)\n";
echo "   🔧 4. Verificar se MikroTik está executando sync corretamente\n";
echo "   🔧 5. Verificar configuração de firewall no MikroTik\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ DIAGNÓSTICO CONCLUÍDO!\n";
