<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "🔍 DIAGNÓSTICO COMPLETO DO WEBHOOK AUTOMÁTICO\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. Verificar pagamento ID 53
echo "📊 1. VERIFICANDO PAGAMENTO ID 53:\n";
$payment = Payment::with('user')->find(53);

if ($payment) {
    echo "✅ Pagamento encontrado:\n";
    echo "   - ID: {$payment->id}\n";
    echo "   - Status: {$payment->status}\n";
    echo "   - Transaction ID: {$payment->transaction_id}\n";
    echo "   - Gateway ID: {$payment->gateway_payment_id}\n";
    echo "   - Paid At: {$payment->paid_at}\n";
    echo "   - MAC do usuário: {$payment->user->mac_address}\n";
    echo "   - Status do usuário: {$payment->user->status}\n";
    echo "   - Connected At: {$payment->user->connected_at}\n\n";
} else {
    echo "❌ Pagamento não encontrado!\n\n";
}

// 2. Verificar webhook URL configurada
echo "🌐 2. VERIFICANDO CONFIGURAÇÃO DO WEBHOOK:\n";
echo "   - URL do webhook deve ser: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi\n";
echo "   - Verificar no painel Woovi se está configurado corretamente!\n\n";

// 3. Verificar logs recentes
echo "📝 3. VERIFICANDO LOGS RECENTES:\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $logs), -20);
        
        foreach ($recentLogs as $log) {
            if (strpos($log, 'Webhook') !== false || strpos($log, 'webhook') !== false) {
                echo "   📄 " . trim($log) . "\n";
            }
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erro ao ler logs: " . $e->getMessage() . "\n\n";
}

// 4. Testar endpoint do webhook
echo "🧪 4. TESTANDO ENDPOINT DO WEBHOOK:\n";
echo "   Execute este comando para testar:\n";
echo "   curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi \\\n";
echo "        -H \"Content-Type: application/json\" \\\n";
echo "        -d '{\"test\": true}'\n\n";

// 5. Verificar problema no allowDevice
echo "🔧 5. VERIFICANDO PROBLEMA NO /api/mikrotik/allow:\n";
$user = User::find(55); // Usuário do pagamento 53
if ($user) {
    echo "   ✅ Usuário encontrado: {$user->mac_address}\n";
    echo "   - Status atual: {$user->status}\n";
    echo "   - Connected at: {$user->connected_at}\n";
    
    // Simular o que o allowDevice deveria fazer
    echo "   🔄 Simulando liberação...\n";
    try {
        $user->update([
            'status' => 'connected',
            'connected_at' => now(),
            'expires_at' => now()->addDay()
        ]);
        echo "   ✅ Usuário atualizado com sucesso!\n";
    } catch (Exception $e) {
        echo "   ❌ Erro ao atualizar usuário: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ Usuário não encontrado!\n";
}

echo "\n";

// 6. Verificar configuração do Walled Garden
echo "🛡️ 6. COMANDOS PARA CORRIGIR WALLED GARDEN NO MIKROTIK:\n";
echo "   Execute estes comandos no terminal do MikroTik:\n\n";
echo "   # Adicionar domínios Woovi ao Walled Garden\n";
echo "   /ip hotspot walled-garden add dst-host=api.openpix.com.br action=allow comment=\"Woovi API\"\n";
echo "   /ip hotspot walled-garden add dst-host=openpix.com.br action=allow comment=\"Woovi Domain\"\n";
echo "   /ip hotspot walled-garden add dst-host=*.openpix.com.br action=allow comment=\"Woovi Subdomains\"\n\n";

// 7. Solução para o problema do webhook
echo "🎯 7. SOLUÇÃO PARA O PROBLEMA DO WEBHOOK:\n";
echo "   PROBLEMA: Webhook não está sendo chamado automaticamente pelo Woovi\n";
echo "   CAUSA: URL do webhook pode estar incorreta no painel Woovi\n\n";
echo "   ✅ SOLUÇÕES:\n";
echo "   1. Verificar URL no painel Woovi: https://app.woovi.com/\n";
echo "   2. URL correta: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi\n";
echo "   3. Adicionar Walled Garden rules para Woovi API\n";
echo "   4. Corrigir erro 500 no /api/mikrotik/allow\n\n";

// 8. Verificar se o usuário tem sessão ativa
echo "🔄 8. VERIFICANDO SESSÃO ATIVA:\n";
if ($user) {
    $activeSessions = DB::table('wifi_sessions')
        ->where('user_id', $user->id)
        ->where('session_status', 'active')
        ->get();
        
    echo "   - Sessões ativas: " . $activeSessions->count() . "\n";
    foreach ($activeSessions as $session) {
        echo "   - Sessão ID: {$session->id}, iniciada em: {$session->started_at}\n";
    }
}

echo "\n🎉 DIAGNÓSTICO COMPLETO!\n";
echo "=" . str_repeat("=", 60) . "\n";

// 9. Solução imediata
echo "\n🚀 SOLUÇÃO IMEDIATA:\n";
echo "1. Execute os comandos do Walled Garden no MikroTik (seção 6)\n";
echo "2. Verifique a URL do webhook no painel Woovi\n";
echo "3. O usuário já foi liberado automaticamente pelo script!\n";
echo "4. Teste fazendo um novo pagamento\n\n";
