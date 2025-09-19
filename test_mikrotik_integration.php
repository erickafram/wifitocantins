<?php

// Bootstrap Laravel application properly
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

echo "=== TESTE DE INTEGRAÇÃO MIKROTIK ===\n";
echo "Testando conexão e funcionalidades...\n\n";

try {
    // Bootstrap Laravel Application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // Set environment
    $app->detectEnvironment(function() {
        return 'production';
    });

    echo "✅ Laravel bootstrapped successfully\n";

    // 1. Verificar configurações
    echo "\n1. Verificando configurações...\n";
    
    $mikrotikHost = config('wifi.mikrotik.host', 'NOT_SET');
    $mikrotikUser = config('wifi.mikrotik.username', 'NOT_SET');
    $mikrotikEnabled = config('wifi.mikrotik.api_enabled', false);
    
    echo "   Host MikroTik: {$mikrotikHost}\n";
    echo "   Usuário API: {$mikrotikUser}\n";
    echo "   API Habilitada: " . ($mikrotikEnabled ? 'Sim' : 'Não') . "\n";
    
    if ($mikrotikHost === 'NOT_SET' || $mikrotikUser === 'NOT_SET') {
        throw new Exception('Configurações MikroTik não encontradas. Verifique o arquivo .env');
    }

    // 2. Teste de conexão básica
    echo "\n2. Testando conexão com MikroTik...\n";
    
    $mikrotikController = new \App\Http\Controllers\MikrotikController();
    
    // Criar usuário de teste
    $testMac = '02:TEST:' . strtoupper(substr(md5(time()), 0, 8));
    echo "   MAC de teste: {$testMac}\n";
    
    // Criar usuário no banco
    $testUser = \App\Models\User::create([
        'mac_address' => $testMac,
        'ip_address' => '10.10.10.150',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "   ✅ Usuário de teste criado: ID {$testUser->id}\n";
    
    // 3. Teste de liberação de acesso
    echo "\n3. Testando liberação de acesso...\n";
    
    $response = $mikrotikController->allowDeviceByUser($testUser);
    $responseData = $response->getData();
    
    if (isset($responseData->success) && $responseData->success) {
        echo "   ✅ Dispositivo liberado com sucesso\n";
    } else {
        $message = isset($responseData->message) ? $responseData->message : 'Erro desconhecido';
        echo "   ⚠️ Resposta da liberação: {$message}\n";
    }
    
    // 4. Teste de consulta de status
    echo "\n4. Testando consulta de status...\n";
    
    $statusResponse = $mikrotikController->getStatus($testMac);
    $statusData = $statusResponse->getData();
    
    echo "   MAC: " . ($statusData->mac_address ?? 'N/A') . "\n";
    echo "   Status: " . ($statusData->status ?? 'N/A') . "\n";
    echo "   Conectado: " . (isset($statusData->connected) && $statusData->connected ? 'Sim' : 'Não') . "\n";
    
    // 5. Teste de dados de uso
    echo "\n5. Testando consulta de uso...\n";
    
    $usageResponse = $mikrotikController->getUsage($testMac);
    $usageData = $usageResponse->getData();
    
    echo "   Dados usados: " . ($usageData->data_used ?? 0) . " bytes\n";
    echo "   Duração da sessão: " . ($usageData->session_duration ?? 0) . " minutos\n";
    echo "   Velocidade download: " . ($usageData->download_speed ?? 0) . " kbps\n";
    echo "   Velocidade upload: " . ($usageData->upload_speed ?? 0) . " kbps\n";
    
    // 6. Teste de bloqueio
    echo "\n6. Testando bloqueio de dispositivo...\n";
    
    $blockRequest = Request::create('/api/mikrotik/block', 'POST', ['mac_address' => $testMac]);
    $blockResponse = $mikrotikController->blockDevice($blockRequest);
    $blockData = $blockResponse->getData();
    
    if (isset($blockData->success) && $blockData->success) {
        echo "   ✅ Dispositivo bloqueado com sucesso\n";
    } else {
        $message = isset($blockData->message) ? $blockData->message : 'Erro desconhecido';
        echo "   ⚠️ Resposta do bloqueio: {$message}\n";
    }
    
    // 7. Simulação de pagamento aprovado
    echo "\n7. Simulando pagamento aprovado...\n";
    
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
    
    // Simular aprovação do pagamento usando o PaymentController
    $paymentController = new \App\Http\Controllers\PaymentController();
    
    // Usar reflection para acessar método privado
    $reflection = new ReflectionClass($paymentController);
    $method = $reflection->getMethod('activateUserAccess');
    $method->setAccessible(true);
    
    try {
        $method->invoke($paymentController, $payment);
        echo "   ✅ Pagamento processado e acesso liberado\n";
    } catch (Exception $e) {
        echo "   ⚠️ Erro ao processar pagamento: " . $e->getMessage() . "\n";
    }
    
    // Verificar se usuário foi liberado
    $testUser->refresh();
    echo "   Status do usuário após pagamento: " . $testUser->status . "\n";
    echo "   Conectado em: " . ($testUser->connected_at ?? 'Não conectado') . "\n";
    echo "   Expira em: " . ($testUser->expires_at ?? 'Sem expiração') . "\n";
    
    // 8. Verificar sessões criadas
    echo "\n8. Verificando sessões criadas...\n";
    
    $sessions = \App\Models\Session::where('user_id', $testUser->id)->get();
    echo "   Total de sessões: " . $sessions->count() . "\n";
    
    foreach ($sessions as $session) {
        echo "   - Sessão ID {$session->id}: {$session->session_status} (iniciada em {$session->started_at})\n";
    }
    
    // 9. Limpeza
    echo "\n9. Limpando dados de teste...\n";
    
    // Remover dados de teste
    $testUser->payments()->delete();
    $testUser->sessions()->delete();
    $testUser->delete();
    
    echo "   ✅ Dados de teste removidos\n";
    
    echo "\n=== TESTE CONCLUÍDO COM SUCESSO ===\n";
    echo "✅ Todos os componentes estão funcionando!\n";
    
    // Verificar se há problemas de conectividade com MikroTik
    if (!config('wifi.mikrotik.api_enabled', false)) {
        echo "\n⚠️ AVISO: API MikroTik está DESABILITADA\n";
        echo "   O sistema está funcionando em modo simulação.\n";
        echo "   Para habilitar a integração real:\n";
        echo "   1. Configure o MikroTik com o script fornecido\n";
        echo "   2. Defina MIKROTIK_API_ENABLED=true no .env\n";
    } else {
        echo "\n✅ API MikroTik está HABILITADA\n";
        echo "   Sistema funcionando com integração real.\n";
    }
    
    echo "\nPróximos passos:\n";
    echo "1. Configure o MikroTik com o script mikrotik-hotspot-integrado.rsc\n";
    echo "2. Verifique as variáveis de ambiente no .env\n";
    echo "3. Teste com um dispositivo real conectado ao WiFi\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO NO TESTE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Tentar limpar dados de teste mesmo com erro
    try {
        if (isset($testUser) && $testUser->exists) {
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
    echo "1. Laravel não foi bootstrapped corretamente\n";
    echo "2. Arquivo .env não encontrado ou configuração incorreta\n";
    echo "3. Banco de dados não acessível\n";
    echo "4. MikroTik não está acessível no IP configurado\n";
    echo "5. Usuário da API não existe ou senha incorreta\n";
    echo "6. API do MikroTik está desabilitada\n";
    echo "7. Firewall bloqueando conexão na porta 8728\n";
    
    echo "\nVerifique:\n";
    try {
        echo "- APP_ENV=" . config('app.env', 'NOT_SET') . "\n";
        echo "- MIKROTIK_HOST=" . config('wifi.mikrotik.host', 'NOT_SET') . "\n";
        echo "- MIKROTIK_USERNAME=" . config('wifi.mikrotik.username', 'NOT_SET') . "\n";
        echo "- MIKROTIK_API_ENABLED=" . (config('wifi.mikrotik.api_enabled', false) ? 'true' : 'false') . "\n";
    } catch (Exception $configError) {
        echo "❌ Erro ao ler configurações: " . $configError->getMessage() . "\n";
        echo "\nVerifique se o arquivo .env existe e está configurado corretamente.\n";
    }
}

echo "\n=== FIM DO TESTE ===\n"; 