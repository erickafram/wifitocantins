<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 === DIAGNÓSTICO PRODUÇÃO REAL ===\n\n";

echo "1️⃣ VERIFICANDO AMBIENTE:\n";
echo "   APP_ENV: " . env('APP_ENV') . "\n";
echo "   APP_URL: " . env('APP_URL') . "\n";
echo "   MIKROTIK_HOST: " . env('MIKROTIK_HOST') . "\n";
echo "   MIKROTIK_ENABLED: " . (config('wifi.mikrotik.enabled') ? 'true' : 'false') . "\n\n";

echo "2️⃣ VERIFICANDO SE CORREÇÕES FORAM APLICADAS:\n";
$portalController = file_get_contents(__DIR__ . '/app/Http/Controllers/PortalController.php');

if (strpos($portalController, 'MAC REAL capturado via URL') !== false) {
    echo "   ✅ Correções aplicadas no PortalController\n";
} else {
    echo "   ❌ Correções NÃO aplicadas no PortalController\n";
    echo "   🚨 PROBLEMA: Arquivo não foi atualizado em produção!\n\n";
    echo "   SOLUÇÃO: Faça upload do arquivo corrigido\n\n";
    exit;
}

echo "3️⃣ TESTANDO DETECÇÃO VIA URL:\n";
$macReal = '5C:CD:5B:2F:B9:3F';
$urlTeste = "https://www.tocantinstransportewifi.com.br/?mac={$macReal}";
echo "   URL de teste: {$urlTeste}\n";

// Simular request com MAC na URL
try {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (Test)\r\n"
        ]
    ]);
    
    $response = file_get_contents($urlTeste, false, $context);
    if ($response !== false) {
        echo "   ✅ Portal acessível via URL com MAC\n";
    } else {
        echo "   ❌ Erro ao acessar portal\n";
    }
} catch (Exception $e) {
    echo "   ⚠️ Erro na requisição: " . $e->getMessage() . "\n";
}

echo "\n4️⃣ VERIFICANDO ENDPOINT DE DETECÇÃO:\n";
try {
    $detectUrl = "https://www.tocantinstransportewifi.com.br/api/detect-device?mac={$macReal}";
    $response = file_get_contents($detectUrl);
    $data = json_decode($response, true);
    
    if ($data && isset($data['mac_address'])) {
        echo "   Resposta API: " . $data['mac_address'] . "\n";
        if ($data['mac_address'] === strtoupper($macReal)) {
            echo "   ✅ API funcionando - MAC real capturado!\n";
        } else {
            echo "   ❌ API retornou MAC incorreto\n";
        }
    } else {
        echo "   ❌ API não respondeu corretamente\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erro na API: " . $e->getMessage() . "\n";
}

echo "\n5️⃣ VERIFICANDO LOGS RECENTES:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $command = "tail -20 {$logFile} | grep -i 'MAC\\|DETECÇÃO'";
    $logs = shell_exec($command);
    if ($logs) {
        echo "   Logs encontrados:\n";
        echo "   " . str_replace("\n", "\n   ", trim($logs)) . "\n";
    } else {
        echo "   ⚠️ Nenhum log de MAC encontrado\n";
    }
}

echo "\n6️⃣ VERIFICANDO MIKROTIK:\n";
echo "   Seu IP atual: 192.168.10.197\n";
echo "   Gateway MikroTik: 192.168.10.1\n";
echo "   MAC real: {$macReal}\n\n";

// Testar se MikroTik está enviando parâmetros
if (isset($_GET['mac'])) {
    echo "   ✅ Parâmetro 'mac' detectado na URL: " . $_GET['mac'] . "\n";
} else {
    echo "   ❌ Parâmetro 'mac' NÃO encontrado na URL\n";
    echo "   🚨 PROBLEMA: MikroTik não está redirecionando com MAC\n";
}

if (isset($_GET['source'])) {
    echo "   ✅ Parâmetro 'source' detectado: " . $_GET['source'] . "\n";
} else {
    echo "   ❌ Parâmetro 'source' não encontrado\n";
}

echo "\n7️⃣ PRÓXIMOS PASSOS:\n";
if (!isset($_GET['mac'])) {
    echo "   1. 🔧 Aplicar script no MikroTik: mikrotik-mac-real-definitivo.rsc\n";
    echo "   2. 🔄 Aguardar redirecionamento automático\n";
    echo "   3. 🧪 Testar manualmente: {$urlTeste}\n";
} else {
    echo "   1. ✅ MikroTik configurado corretamente\n";
    echo "   2. 🧪 Testar processo de pagamento\n";
}

echo "\n🏁 === DIAGNÓSTICO CONCLUÍDO ===\n";
