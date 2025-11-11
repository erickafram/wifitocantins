<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 === DIAGNÓSTICO DO MAC REAL ===\n\n";

// 1. VERIFICAR SE O MAC REAL ESTÁ NO BANCO
echo "1️⃣ VERIFICANDO MAC REAL NO BANCO:\n";
$macReal = 'e4:84:d3:f4:7f:eb';
$user = DB::table('users')->where('mac_address', $macReal)->first();

if ($user) {
    echo "✅ MAC REAL ENCONTRADO NO BANCO!\n";
    echo "   User ID: {$user->id}\n";
    echo "   Status: {$user->status}\n";
    echo "   IP: {$user->ip_address}\n\n";
} else {
    echo "❌ MAC REAL NÃO ENCONTRADO NO BANCO!\n";
    echo "   MAC procurado: {$macReal}\n\n";
    
    echo "📋 MACs existentes no banco:\n";
    $users = DB::table('users')->whereNotNull('mac_address')->get(['id', 'mac_address', 'status', 'ip_address']);
    foreach ($users as $u) {
        echo "   ID {$u->id}: {$u->mac_address} ({$u->status}) - IP: {$u->ip_address}\n";
    }
    echo "\n";
}

// 2. VERIFICAR PAGAMENTOS COMPLETADOS SEM USUÁRIOS CONECTADOS
echo "2️⃣ VERIFICANDO PAGAMENTOS COMPLETADOS:\n";
$paymentsCompleted = DB::table('payments')
    ->where('status', 'completed')
    ->orderBy('paid_at', 'desc')
    ->limit(5)
    ->get(['id', 'user_id', 'paid_at']);

foreach ($paymentsCompleted as $payment) {
    $user = DB::table('users')->where('id', $payment->user_id)->first();
    echo "💳 Payment ID {$payment->id} (User {$payment->user_id}):\n";
    echo "   Pago em: {$payment->paid_at}\n";
    echo "   Status usuário: {$user->status}\n";
    echo "   MAC: {$user->mac_address}\n";
    echo "   IP: {$user->ip_address}\n\n";
}

// 3. VERIFICAR LOGS DO WEBHOOK
echo "3️⃣ VERIFICANDO LOGS RECENTES:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = shell_exec("tail -20 {$logFile} | grep -i woovi");
    if ($logs) {
        echo "📝 Logs recentes do Woovi:\n";
        echo $logs . "\n";
    } else {
        echo "⚠️ Nenhum log do Woovi encontrado recentemente\n\n";
    }
}

// 4. TESTAR ENDPOINT DE SYNC
echo "4️⃣ TESTANDO ENDPOINT DE SYNC:\n";
try {
    $syncToken = config('wifi.mikrotik_sync_token');
    $serverUrl = config('wifi.server_url', 'https://www.tocantinstransportewifi.com.br');
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer {$syncToken}\r\n"
        ]
    ]);
    
    $response = file_get_contents("{$serverUrl}/api/mikrotik-sync/pending-users", false, $context);
    $data = json_decode($response, true);
    
    echo "✅ Resposta do endpoint sync:\n";
    echo "   Allow users: " . count($data['allow_users'] ?? []) . " MACs\n";
    echo "   Block users: " . count($data['block_users'] ?? []) . " MACs\n";
    
    if (!empty($data['allow_users'])) {
        echo "   MACs para liberar:\n";
        foreach ($data['allow_users'] as $mac) {
            echo "     - {$mac}\n";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao testar endpoint: " . $e->getMessage() . "\n\n";
}

echo "🏁 === DIAGNÓSTICO CONCLUÍDO ===\n";
