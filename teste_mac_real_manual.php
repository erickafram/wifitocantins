<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 === TESTE MANUAL DO MAC REAL ===\n\n";

// SIMULAR REQUEST COM MAC REAL VIA URL
$macReal = 'e4:84:d3:f4:7f:eb';
$ipReal = '189.72.217.241';

echo "1️⃣ TESTANDO CRIAÇÃO DE USUÁRIO COM MAC REAL:\n";

// Verificar se usuário já existe
$userExistente = DB::table('users')->where('mac_address', $macReal)->first();
if ($userExistente) {
    echo "⚠️ Usuário com MAC real já existe (ID: {$userExistente->id})\n";
    echo "   Removendo para teste limpo...\n";
    DB::table('users')->where('id', $userExistente->id)->delete();
    echo "   ✅ Removido!\n\n";
}

// Criar usuário com MAC real
$userId = DB::table('users')->insertGetId([
    'mac_address' => $macReal,
    'ip_address' => $ipReal,
    'status' => 'offline',
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✅ Usuário criado com MAC REAL!\n";
echo "   User ID: {$userId}\n";
echo "   MAC: {$macReal}\n";
echo "   IP: {$ipReal}\n\n";

echo "2️⃣ SIMULANDO PAGAMENTO COMPLETADO:\n";

// Criar pagamento
$paymentId = DB::table('payments')->insertGetId([
    'user_id' => $userId,
    'amount' => 0.05,
    'payment_type' => 'pix',
    'status' => 'completed',
    'paid_at' => now(),
    'created_at' => now(),
    'updated_at' => now()
]);

// Atualizar usuário como conectado
DB::table('users')->where('id', $userId)->update([
    'status' => 'connected',
    'connected_at' => now(),
    'expires_at' => now()->addHours(24)
]);

echo "✅ Pagamento simulado!\n";
echo "   Payment ID: {$paymentId}\n";
echo "   Status: completed\n";
echo "   Usuário: connected\n\n";

echo "3️⃣ TESTANDO ENDPOINT DE SYNC:\n";

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
    
    if (!empty($data['allow_users'])) {
        echo "   MACs para liberar:\n";
        foreach ($data['allow_users'] as $mac) {
            echo "     - {$mac}";
            if ($mac === $macReal) {
                echo " ← 🎯 MAC REAL ENCONTRADO!";
            }
            echo "\n";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Erro no endpoint: " . $e->getMessage() . "\n\n";
}

echo "4️⃣ RESULTADO FINAL:\n";
$userFinal = DB::table('users')->where('id', $userId)->first();
echo "   MAC no banco: {$userFinal->mac_address}\n";
echo "   Status: {$userFinal->status}\n";
echo "   Expires: {$userFinal->expires_at}\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "1. Execute no MikroTik: /import mikrotik-mac-real-definitivo.rsc\n";
echo "2. Conecte o dispositivo {$macReal} no WiFi\n";
echo "3. Acesse: https://www.tocantinstransportewifi.com.br/?mac={$macReal}\n";
echo "4. O sistema deve reconhecer o MAC real e dar acesso total!\n\n";

echo "🏁 === TESTE CONCLUÍDO ===\n";
