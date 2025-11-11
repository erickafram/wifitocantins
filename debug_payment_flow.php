<?php
/**
 * DIAGNÓSTICO COMPLETO DO FLUXO DE PAGAMENTO
 * Verifica se usuários pagos estão sendo liberados no MikroTik
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🔍 DIAGNÓSTICO DO FLUXO DE PAGAMENTO - " . date('Y-m-d H:i:s') . "\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. VERIFICAR USUÁRIOS COM STATUS CONNECTED
echo "1️⃣ USUÁRIOS COM STATUS 'CONNECTED':\n";
echo "-" . str_repeat("-", 40) . "\n";

$connectedUsers = DB::table('users')
    ->where('status', 'connected')
    ->whereNotNull('mac_address')
    ->select('id', 'mac_address', 'connected_at', 'expires_at', 'status')
    ->get();

if ($connectedUsers->count() > 0) {
    foreach ($connectedUsers as $user) {
        $timeLeft = $user->expires_at ? 
            \Carbon\Carbon::parse($user->expires_at)->diffForHumans() : 'N/A';
        
        echo "✅ ID: {$user->id} | MAC: {$user->mac_address} | Expira: {$timeLeft}\n";
    }
} else {
    echo "❌ NENHUM usuário conectado encontrado!\n";
}

echo "\n";

// 2. VERIFICAR PAGAMENTOS COMPLETED
echo "2️⃣ PAGAMENTOS COMPLETED (ÚLTIMAS 24H):\n";
echo "-" . str_repeat("-", 40) . "\n";

$completedPayments = DB::table('payments')
    ->join('users', 'payments.user_id', '=', 'users.id')
    ->where('payments.status', 'completed')
    ->where('payments.created_at', '>=', now()->subHours(24))
    ->select(
        'payments.id as payment_id',
        'payments.user_id',
        'payments.amount',
        'payments.paid_at',
        'users.mac_address',
        'users.status as user_status',
        'users.connected_at',
        'users.expires_at'
    )
    ->orderBy('payments.paid_at', 'desc')
    ->get();

if ($completedPayments->count() > 0) {
    foreach ($completedPayments as $payment) {
        $userStatus = $payment->user_status === 'connected' ? '✅' : '❌';
        echo "{$userStatus} Payment ID: {$payment->payment_id} | User ID: {$payment->user_id}\n";
        echo "   💰 Valor: R$ {$payment->amount} | MAC: {$payment->mac_address}\n";
        echo "   📅 Pago: {$payment->paid_at} | Status: {$payment->user_status}\n";
        
        if ($payment->user_status !== 'connected') {
            echo "   ⚠️  PROBLEMA: Pagamento confirmado mas usuário não conectado!\n";
        }
        echo "\n";
    }
} else {
    echo "❌ NENHUM pagamento completed nas últimas 24h!\n";
}

echo "\n";

// 3. TESTAR ENDPOINT DE SYNC
echo "3️⃣ TESTANDO ENDPOINT DE SYNC:\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    $syncUrl = config('wifi.server_url', 'https://www.tocantinstransportewifi.com.br') . '/api/mikrotik-sync/pending-users';
    $token = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
    
    echo "🌐 URL: {$syncUrl}\n";
    echo "🔑 Token: {$token}\n\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json",
                "Accept: application/json"
            ],
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($syncUrl, false, $context);
    
    if ($response !== false) {
        echo "✅ RESPOSTA DO ENDPOINT:\n";
        echo $response . "\n\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['allow_users'])) {
            echo "👥 USUÁRIOS PARA LIBERAR:\n";
            foreach ($data['allow_users'] as $mac) {
                echo "   📱 {$mac}\n";
            }
        }
    } else {
        echo "❌ FALHA na comunicação com endpoint!\n";
        $error = error_get_last();
        echo "   Erro: " . ($error['message'] ?? 'Desconhecido') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO no teste: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. VERIFICAR CONFIGURAÇÕES
echo "4️⃣ VERIFICAÇÕES DE CONFIGURAÇÃO:\n";
echo "-" . str_repeat("-", 40) . "\n";

$configs = [
    'MIKROTIK_HOST' => config('wifi.mikrotik.host'),
    'MIKROTIK_USER' => config('wifi.mikrotik.user'),
    'MIKROTIK_SYNC_TOKEN' => config('wifi.mikrotik_sync_token'),
    'SERVER_URL' => config('wifi.server_url'),
    'PIX_KEY' => config('wifi.pix.key'),
];

foreach ($configs as $key => $value) {
    $status = $value ? '✅' : '❌';
    $displayValue = $key === 'MIKROTIK_SYNC_TOKEN' ? 
        (substr($value, 0, 8) . '...') : $value;
    echo "{$status} {$key}: {$displayValue}\n";
}

echo "\n";

// 5. SUGESTÕES DE CORREÇÃO
echo "5️⃣ SUGESTÕES DE CORREÇÃO:\n";
echo "-" . str_repeat("-", 40) . "\n";

if ($connectedUsers->count() === 0) {
    echo "🔧 1. Executar ativação manual dos usuários pagos\n";
    echo "   php approve_payment.php\n\n";
}

echo "🔧 2. Atualizar script MikroTik com MACs reais:\n";
foreach ($connectedUsers as $user) {
    echo "   \"{$user->mac_address}\";\n";
}

echo "\n🔧 3. Verificar se MikroTik está executando sync:\n";
echo "   /log print where topics~\"info\"\n";

echo "\n🔧 4. Forçar sync manual no MikroTik:\n";
echo "   \$executarSyncMelhorado\n";

echo "\n";

// 6. LOGS RECENTES
echo "6️⃣ LOGS RECENTES (ÚLTIMAS 2 HORAS):\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $logs), -50); // Últimas 50 linhas
        
        foreach ($recentLogs as $log) {
            if (stripos($log, 'payment') !== false || 
                stripos($log, 'webhook') !== false || 
                stripos($log, 'mikrotik') !== false) {
                echo $log . "\n";
            }
        }
    } else {
        echo "❌ Arquivo de log não encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao ler logs: " . $e->getMessage() . "\n";
}

echo "\n";
echo "🎯 DIAGNÓSTICO CONCLUÍDO!\n";
echo "=" . str_repeat("=", 60) . "\n";
