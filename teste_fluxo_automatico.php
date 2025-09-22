<?php
require_once 'vendor/autoload.php';

// Script para testar fluxo automático completo
use App\Models\User;
use App\Models\Payment;

echo "🧪 TESTANDO FLUXO AUTOMÁTICO COMPLETO\n";
echo "====================================\n\n";

// 1. Simular usuário conectando
$macTest = "4A:24:2C:27:7E:86";
$ipTest = "10.10.10.107";

echo "1. 🔍 Criando usuário de teste...\n";
$user = User::create([
    'name' => 'Teste Automático',
    'email' => 'teste@automatico.com',
    'phone' => '63999999999',
    'mac_address' => $macTest,
    'ip_address' => $ipTest,
    'status' => 'pending'
]);
echo "   ✅ Usuário criado: ID {$user->id}\n\n";

// 2. Simular pagamento
echo "2. 💳 Simulando pagamento...\n";
$payment = Payment::create([
    'user_id' => $user->id,
    'amount' => 5.99,
    'payment_method' => 'pix',
    'status' => 'completed',
    'paid_at' => now(),
    'transaction_id' => 'TEST_' . time()
]);
echo "   ✅ Pagamento criado: ID {$payment->id}\n\n";

// 3. Ativar acesso
echo "3. 🚀 Ativando acesso...\n";
$user->update([
    'status' => 'connected',
    'connected_at' => now(),
    'expires_at' => now()->addHours(24)
]);
echo "   ✅ Usuário ativado\n\n";

// 4. Testar endpoint de sincronização
echo "4. 🔄 Testando endpoint pending-users...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer mikrotik-sync-2024']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Endpoint respondeu: {$httpCode}\n";
    echo "   📊 Usuários para liberar: " . count($data['allow_users']) . "\n";
    
    if (in_array($macTest, $data['allow_users'])) {
        echo "   🎯 MAC de teste ENCONTRADO na lista!\n";
    } else {
        echo "   ❌ MAC de teste NÃO encontrado na lista\n";
        echo "   📋 MACs retornados: " . implode(', ', $data['allow_users']) . "\n";
    }
} else {
    echo "   ❌ Endpoint falhou: HTTP {$httpCode}\n";
    echo "   📄 Resposta: {$response}\n";
}

echo "\n5. 🧹 Limpando dados de teste...\n";
$payment->delete();
$user->delete();
echo "   ✅ Dados limpos\n\n";

echo "✅ TESTE CONCLUÍDO!\n";
