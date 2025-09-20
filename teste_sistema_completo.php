<?php
/**
 * Teste completo do sistema de pagamento
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;

echo "🧪 TESTE COMPLETO DO SISTEMA\n";
echo str_repeat("=", 50) . "\n";

// 1. CRIAR USUÁRIO DE TESTE
$testUser = User::create([
    'name' => 'Usuario Teste',
    'email' => 'teste@teste.com',
    'phone' => '11999999999',
    'mac_address' => '02:TEST:MAC:ADDR',
    'status' => 'pending'
]);

echo "✅ Usuário criado: {$testUser->id}\n";

// 2. CRIAR PAGAMENTO DE TESTE
$testPayment = Payment::create([
    'user_id' => $testUser->id,
    'amount' => 5.99,
    'gateway' => 'woovi',
    'status' => 'pending',
    'gateway_payment_id' => 'test-' . time()
]);

echo "✅ Pagamento criado: {$testPayment->id}\n";

// 3. SIMULAR WEBHOOK (MARCAR COMO PAGO)
$testPayment->update(['status' => 'completed']);
$testUser->update(['status' => 'connected', 'connected_at' => now()]);

echo "✅ Pagamento confirmado simulado\n";

// 4. TESTAR ENDPOINT SYNC
$syncToken = config('wifi.mikrotik_sync_token');
$serverUrl = config('wifi.server_url');

echo "\n🔍 Testando endpoint sync...\n";
echo "URL: $serverUrl/api/mikrotik-sync/pending-users\n";
echo "Token: $syncToken\n";

try {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $syncToken\r\n"
        ]
    ]);
    
    $response = file_get_contents("$serverUrl/api/mikrotik-sync/pending-users", false, $context);
    $data = json_decode($response, true);
    
    echo "✅ Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    if (isset($data['allow_users']) && in_array('02:TEST:MAC:ADDR', $data['allow_users'])) {
        echo "🎯 MAC de teste encontrado na lista de liberação!\n";
    } else {
        echo "⚠️ MAC de teste NÃO encontrado na lista!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

// 5. LIMPEZA
$testPayment->delete();
$testUser->delete();

echo "\n🧹 Dados de teste removidos\n";
echo "✅ TESTE CONCLUÍDO!\n";
