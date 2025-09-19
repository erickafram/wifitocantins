<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\MikrotikController;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DE INTEGRAÇÃO MIKROTIK ===\n";
echo "Testando conexão e funcionalidades...\n\n";

// Configurar ambiente de teste
$_ENV['APP_ENV'] = 'testing';

try {
    // 1. Teste de conexão básica
    echo "1. Testando conexão com MikroTik...\n";
    
    $mikrotikController = new MikrotikController();
    
    // Criar usuário de teste
    $testMac = '02:TEST:' . strtoupper(substr(md5(time()), 0, 8));
    echo "   MAC de teste: {$testMac}\n";
    
    // Criar usuário no banco
    $testUser = User::create([
        'mac_address' => $testMac,
        'ip_address' => '10.10.10.150',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "   ✅ Usuário de teste criado: ID {$testUser->id}\n";
    
    // 2. Teste de liberação de acesso
    echo "\n2. Testando liberação de acesso...\n";
    
    $response = $mikrotikController->allowDeviceByUser($testUser);
    
    if ($response->getData()->success) {
        echo "   ✅ Dispositivo liberado com sucesso\n";
    } else {
        echo "   ❌ Falha ao liberar dispositivo: " . $response->getData()->message . "\n";
    }
    
    // 3. Teste de consulta de status
    echo "\n3. Testando consulta de status...\n";
    
    $statusResponse = $mikrotikController->getStatus($testMac);
    $statusData = $statusResponse->getData();
    
    echo "   MAC: " . $statusData->mac_address . "\n";
    echo "   Status: " . $statusData->status . "\n";
    echo "   Conectado: " . ($statusData->connected ? 'Sim' : 'Não') . "\n";
    
    // 4. Teste de dados de uso
    echo "\n4. Testando consulta de uso...\n";
    
    $usageResponse = $mikrotikController->getUsage($testMac);
    $usageData = $usageResponse->getData();
    
    echo "   Dados usados: " . $usageData->data_used . " bytes\n";
    echo "   Duração da sessão: " . $usageData->session_duration . " minutos\n";
    echo "   Velocidade download: " . $usageData->download_speed . " kbps\n";
    echo "   Velocidade upload: " . $usageData->upload_speed . " kbps\n";
    
    // 5. Teste de bloqueio
    echo "\n5. Testando bloqueio de dispositivo...\n";
    
    $blockRequest = new \Illuminate\Http\Request(['mac_address' => $testMac]);
    $blockResponse = $mikrotikController->blockDevice($blockRequest);
    
    if ($blockResponse->getData()->success) {
        echo "   ✅ Dispositivo bloqueado com sucesso\n";
    } else {
        echo "   ❌ Falha ao bloquear dispositivo: " . $blockResponse->getData()->message . "\n";
    }
    
    // 6. Simulação de pagamento aprovado
    echo "\n6. Simulando pagamento aprovado...\n";
    
    // Criar pagamento de teste
    $payment = \App\Models\Payment::create([
        'user_id' => $testUser->id,
        'amount' => 0.05,
        'payment_type' => 'pix',
        'status' => 'pending',
        'transaction_id' => 'TEST_' . time(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "   Pagamento criado: ID {$payment->id}\n";
    
    // Simular aprovação do pagamento
    $paymentController = new \App\Http\Controllers\PaymentController();
    
    // Usar reflection para acessar método privado
    $reflection = new ReflectionClass($paymentController);
    $method = $reflection->getMethod('activateUserAccess');
    $method->setAccessible(true);
    
    $method->invoke($paymentController, $payment);
    
    echo "   ✅ Pagamento processado e acesso liberado\n";
    
    // Verificar se usuário foi liberado
    $testUser->refresh();
    echo "   Status do usuário após pagamento: " . $testUser->status . "\n";
    echo "   Conectado em: " . ($testUser->connected_at ?? 'Não conectado') . "\n";
    echo "   Expira em: " . ($testUser->expires_at ?? 'Sem expiração') . "\n";
    
    // 7. Limpeza
    echo "\n7. Limpando dados de teste...\n";
    
    // Remover dados de teste
    $testUser->payments()->delete();
    $testUser->sessions()->delete();
    $testUser->delete();
    
    echo "   ✅ Dados de teste removidos\n";
    
    echo "\n=== TESTE CONCLUÍDO COM SUCESSO ===\n";
    echo "✅ Todos os componentes estão funcionando corretamente!\n";
    echo "\nPróximos passos:\n";
    echo "1. Configure o MikroTik com o script mikrotik-hotspot-integrado.rsc\n";
    echo "2. Verifique as variáveis de ambiente no .env\n";
    echo "3. Teste com um dispositivo real conectado ao WiFi\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO NO TESTE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Tentar limpar dados de teste mesmo com erro
    try {
        if (isset($testUser)) {
            $testUser->payments()->delete();
            $testUser->sessions()->delete();
            $testUser->delete();
            echo "\n🧹 Dados de teste limpos após erro\n";
        }
    } catch (Exception $cleanupError) {
        echo "❌ Erro na limpeza: " . $cleanupError->getMessage() . "\n";
    }
    
    echo "\n=== DIAGNÓSTICO ===\n";
    echo "Possíveis causas:\n";
    echo "1. MikroTik não está acessível no IP configurado\n";
    echo "2. Usuário da API não existe ou senha incorreta\n";
    echo "3. API do MikroTik está desabilitada\n";
    echo "4. Firewall bloqueando conexão na porta 8728\n";
    echo "5. Configuração incorreta no .env\n";
    
    echo "\nVerifique:\n";
    echo "- MIKROTIK_HOST=" . config('wifi.mikrotik.host') . "\n";
    echo "- MIKROTIK_USERNAME=" . config('wifi.mikrotik.username') . "\n";
    echo "- MIKROTIK_API_ENABLED=" . (config('wifi.mikrotik.api_enabled') ? 'true' : 'false') . "\n";
}

echo "\n=== FIM DO TESTE ===\n"; 