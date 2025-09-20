<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;

echo "🧪 TESTE DE PAGAMENTO COMPLETO\n";
echo "===============================\n\n";

// Simular um usuário com MAC real
$testMac = "02:C8:AD:28:EC:D7"; // MAC que está nos logs do MikroTik
$testIP = "192.168.10.150";

echo "📱 SIMULANDO USUÁRIO:\n";
echo "MAC: {$testMac}\n";
echo "IP: {$testIP}\n\n";

// Buscar/criar usuário
$user = User::where('mac_address', $testMac)->first();
if (!$user) {
    $user = User::create([
        'mac_address' => $testMac,
        'ip_address' => $testIP,
        'status' => 'offline'
    ]);
    echo "✅ Usuário criado no banco\n";
} else {
    echo "✅ Usuário já existe no banco\n";
    $user->update(['ip_address' => $testIP, 'status' => 'connected']);
}

// Criar pagamento
$payment = Payment::create([
    'user_id' => $user->id,
    'amount' => 0.05,
    'payment_type' => 'pix',
    'status' => 'pending',
    'transaction_id' => 'TEST-' . time()
]);

echo "💳 Pagamento criado: ID {$payment->id}\n";

// Simular confirmação do pagamento (webhook)
$payment->update(['status' => 'completed']);
$user->update(['status' => 'paid', 'paid_until' => now()->addHours(24)]);

echo "✅ Pagamento confirmado (simulado)\n";
echo "✅ Usuário marcado como pago\n\n";

echo "🔄 PRÓXIMOS PASSOS:\n";
echo "1. O MikroTik deve consultar o endpoint a cada 30s\n";
echo "2. Encontrar este usuário como 'pago'\n";
echo "3. Liberar automaticamente o IP {$testIP}\n\n";

echo "🧪 VERIFICAR NO MIKROTIK:\n";
echo "Execute: /log print where topics~\"HTTP-SYNC\"\n";
echo "Deve mostrar: Usuário {$testMac} sendo liberado\n\n";

// Verificar endpoint que o MikroTik consulta
echo "🌐 TESTANDO ENDPOINT DE SYNC:\n";
$url = "https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users";
echo "URL: {$url}\n";

try {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer mikrotik-sync-2024\r\n"
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    echo "✅ Endpoint respondeu: " . strlen($response) . " bytes\n";
    
    if (strpos($response, 'allow_users') !== false) {
        echo "✅ Resposta contém 'allow_users'\n";
    } else {
        echo "❌ Resposta não contém 'allow_users'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no endpoint: " . $e->getMessage() . "\n";
}

echo "\n📊 MONITORAMENTO:\n";
echo "- Logs MikroTik: /log print where topics~\"HTTP-SYNC\"\n";
echo "- Lista pagos: /ip firewall address-list print where list=\"usuarios-pagos\"\n";
echo "- Usuários ativos: /ip hotspot active print\n";
?>
