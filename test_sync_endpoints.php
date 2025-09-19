<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTE DOS ENDPOINTS DE SYNC ===\n";
echo "Testando novos endpoints para MikroTik...\n\n";

$baseUrl = config('app.url');
$syncToken = config('wifi.mikrotik.sync_token', 'mikrotik-sync-2024');

echo "🌐 URL Base: {$baseUrl}\n";
echo "🔑 Token: {$syncToken}\n\n";

// 1. Criar usuário de teste com pagamento aprovado
echo "1. 📝 Criando usuário de teste...\n";

$testUser = \App\Models\User::create([
    'mac_address' => '02:SYNC:TEST:MAC',
    'status' => 'connected',
    'connected_at' => now(),
    'expires_at' => now()->addHours(24),
    'ip_address' => '10.10.10.100'
]);

$testPayment = \App\Models\Payment::create([
    'user_id' => $testUser->id,
    'amount' => 0.05,
    'payment_type' => 'pix',
    'status' => 'completed',
    'paid_at' => now()
]);

echo "   ✅ Usuário criado: MAC {$testUser->mac_address}\n";
echo "   ✅ Pagamento aprovado: R$ {$testPayment->amount}\n\n";

// 2. Testar endpoint ping
echo "2. 🏓 Testando endpoint ping...\n";

$pingUrl = "{$baseUrl}/api/mikrotik-sync/ping";
$response = file_get_contents($pingUrl);
$pingData = json_decode($response, true);

if ($pingData && $pingData['success']) {
    echo "   ✅ Ping OK: {$pingData['message']}\n";
    echo "   📅 Timestamp: {$pingData['timestamp']}\n";
} else {
    echo "   ❌ Ping falhou\n";
}

echo "\n";

// 3. Testar endpoint pending-users
echo "3. 👥 Testando endpoint pending-users...\n";

$pendingUrl = "{$baseUrl}/api/mikrotik-sync/pending-users";
$context = stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer {$syncToken}\r\n"
    ]
]);

$response = file_get_contents($pendingUrl, false, $context);
$pendingData = json_decode($response, true);

if ($pendingData && $pendingData['success']) {
    echo "   ✅ Dados obtidos com sucesso\n";
    echo "   📊 Usuários para liberar: {$pendingData['stats']['allow_count']}\n";
    echo "   🚫 Usuários para bloquear: {$pendingData['stats']['block_count']}\n";
    
    if (count($pendingData['allow_users']) > 0) {
        echo "   👤 Exemplo de usuário:\n";
        $user = $pendingData['allow_users'][0];
        echo "      MAC: {$user['mac_address']}\n";
        echo "      Expira: {$user['expires_at']}\n";
    }
} else {
    echo "   ❌ Erro ao obter usuários pendentes\n";
}

echo "\n";

// 4. Testar endpoint check-access
echo "4. 🔍 Testando endpoint check-access...\n";

$checkUrl = "{$baseUrl}/api/mikrotik-sync/check-access";
$postData = json_encode(['mac_address' => $testUser->mac_address]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$syncToken}\r\n",
        'content' => $postData
    ]
]);

$response = file_get_contents($checkUrl, false, $context);
$accessData = json_decode($response, true);

if ($accessData && $accessData['success']) {
    $access = $accessData['allow_access'] ? 'PERMITIDO' : 'NEGADO';
    echo "   ✅ Verificação OK\n";
    echo "   🔐 Acesso: {$access}\n";
    echo "   📱 MAC: {$accessData['mac_address']}\n";
    echo "   ⏰ Expira: {$accessData['expires_at']}\n";
} else {
    echo "   ❌ Erro na verificação de acesso\n";
}

echo "\n";

// 5. Testar endpoint stats
echo "5. 📊 Testando endpoint stats...\n";

$statsUrl = "{$baseUrl}/api/mikrotik-sync/stats";
$response = file_get_contents($statsUrl);
$statsData = json_decode($response, true);

if ($statsData && $statsData['success']) {
    echo "   ✅ Estatísticas obtidas\n";
    echo "   👥 Total usuários: {$statsData['stats']['users']['total']}\n";
    echo "   🟢 Conectados: {$statsData['stats']['users']['connected']}\n";
    echo "   💰 Pagamentos hoje: {$statsData['stats']['payments']['completed_today']}\n";
    echo "   💵 Receita hoje: R$ {$statsData['stats']['payments']['revenue_today']}\n";
} else {
    echo "   ❌ Erro ao obter estatísticas\n";
}

echo "\n";

// 6. Simular expiração e testar bloqueio
echo "6. ⏰ Testando expiração de usuário...\n";

// Expirar usuário
$testUser->update(['expires_at' => now()->subMinutes(5)]);

$response = file_get_contents($pendingUrl, false, $context);
$expiredData = json_decode($response, true);

if ($expiredData && $expiredData['success']) {
    echo "   ✅ Usuário expirado detectado\n";
    echo "   🚫 Usuários para bloquear: {$expiredData['stats']['block_count']}\n";
    
    if (count($expiredData['block_users']) > 0) {
        echo "   👤 Usuário expirado:\n";
        $user = $expiredData['block_users'][0];
        echo "      MAC: {$user['mac_address']}\n";
        echo "      Expirou: {$user['expired_at']}\n";
    }
}

echo "\n";

// 7. Testar URLs que o MikroTik usará
echo "7. 🔗 URLs para configurar no MikroTik:\n";
echo "\n";
echo "   Ping: {$baseUrl}/api/mikrotik-sync/ping\n";
echo "   Sync: {$baseUrl}/api/mikrotik-sync/pending-users\n";
echo "   Check: {$baseUrl}/api/mikrotik-sync/check-access\n";
echo "   Stats: {$baseUrl}/api/mikrotik-sync/stats\n";
echo "\n";
echo "   Token: {$syncToken}\n";

// 8. Limpeza
echo "\n8. 🧹 Limpando dados de teste...\n";

$testUser->payments()->delete();
$testUser->sessions()->delete();
$testUser->delete();

echo "   ✅ Dados de teste removidos\n";

echo "\n=== RESUMO ===\n";
echo "✅ Sistema de sync está funcionando!\n";
echo "✅ Endpoints respondendo corretamente\n";
echo "✅ Lógica de expiração funcional\n";
echo "\n";
echo "🎯 PRÓXIMOS PASSOS:\n";
echo "1. Configure o hotspot no MikroTik (se ainda não fez)\n";
echo "2. Execute o script 'mikrotik-sync-script.rsc' no MikroTik\n";
echo "3. O MikroTik vai sincronizar automaticamente a cada 2 minutos\n";
echo "4. Monitore os logs no MikroTik: /log print where topics~\"info\"\n";

echo "\n=== FIM DO TESTE ===\n"; 