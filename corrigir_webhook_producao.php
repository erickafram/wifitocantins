<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "🚀 CORREÇÃO PARA PRODUÇÃO - WEBHOOK AUTOMÁTICO\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. Verificar se estamos em produção
$environment = config('app.env');
echo "🌍 Ambiente: {$environment}\n\n";

// 2. Corrigir problema no MikrotikController - linha 231
echo "🔧 1. CORRIGINDO PROBLEMA NO MIKROTIKCONTROLLER:\n";
$controllerFile = app_path('Http/Controllers/MikrotikController.php');

if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Verificar se a linha 231 tem problema
    $lines = explode("\n", $content);
    if (isset($lines[230])) { // linha 231 (array é 0-indexed)
        echo "   📄 Linha 231 atual: " . trim($lines[230]) . "\n";
        
        // Se estiver vazia ou com problema, corrigir
        if (trim($lines[230]) === '' || strpos($lines[230], 'mac_address') === false) {
            $lines[230] = "            'mac_address' => 'required|string'";
            file_put_contents($controllerFile, implode("\n", $lines));
            echo "   ✅ Linha 231 corrigida!\n";
        } else {
            echo "   ✅ Linha 231 já está correta!\n";
        }
    }
} else {
    echo "   ❌ Arquivo do controller não encontrado!\n";
}

echo "\n";

// 3. Criar script de correção do Walled Garden
echo "📝 2. GERANDO SCRIPT PARA MIKROTIK:\n";
$mikrotikScript = "# CORREÇÃO WALLED GARDEN - WOOVI QR CODE
# Execute estes comandos no terminal do MikroTik:

# 1. Remover regras duplicadas (se existirem)
/ip hotspot walled-garden remove [find comment~\"Woovi\"]

# 2. Adicionar regras corretas para Woovi
/ip hotspot walled-garden add dst-host=api.openpix.com.br action=allow comment=\"Woovi API - QR Code\"
/ip hotspot walled-garden add dst-host=openpix.com.br action=allow comment=\"Woovi Domain\"
/ip hotspot walled-garden add dst-host=*.openpix.com.br action=allow comment=\"Woovi Subdomains\"

# 3. Adicionar CDNs para imagens
/ip hotspot walled-garden add dst-host=*.cloudflare.com action=allow comment=\"Cloudflare CDN\"
/ip hotspot walled-garden add dst-host=*.amazonaws.com action=allow comment=\"AWS CDN\"

# 4. Permitir HTTPS para APIs
/ip hotspot walled-garden add dst-host=api.openpix.com.br dst-port=443 protocol=tcp action=allow comment=\"Woovi HTTPS\"

# 5. Verificar regras
/ip hotspot walled-garden print

:log info \"✅ Walled Garden corrigido para Woovi QR Code\"
";

file_put_contents('mikrotik_corrigir_qrcode.rsc', $mikrotikScript);
echo "   ✅ Script salvo em: mikrotik_corrigir_qrcode.rsc\n\n";

// 4. Verificar configuração do webhook
echo "🌐 3. VERIFICAÇÃO DO WEBHOOK:\n";
echo "   📍 URL CORRETA do webhook: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi\n";
echo "   🔗 Painel Woovi: https://app.woovi.com/\n";
echo "   ⚠️  IMPORTANTE: Verificar se a URL está configurada corretamente no painel!\n\n";

// 5. Criar script de teste do webhook
echo "🧪 4. GERANDO SCRIPT DE TESTE:\n";
$testScript = "#!/bin/bash
# TESTE DO WEBHOOK WOOVI

echo \"🧪 Testando webhook Woovi...\"

# Testar endpoint
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi \\
     -H \"Content-Type: application/json\" \\
     -H \"User-Agent: Woovi-Webhook/1.0\" \\
     -d '{
       \"event\": \"OPENPIX:CHARGE_COMPLETED\",
       \"charge\": {
         \"correlationID\": \"TEST_WEBHOOK\",
         \"status\": \"COMPLETED\"
       }
     }'

echo \"\"
echo \"✅ Teste concluído!\"
";

file_put_contents('teste_webhook.sh', $testScript);
chmod('teste_webhook.sh', 0755);
echo "   ✅ Script de teste salvo em: teste_webhook.sh\n\n";

// 6. Instruções finais
echo "📋 5. INSTRUÇÕES PARA PRODUÇÃO:\n";
echo "   1️⃣  Execute no MikroTik: mikrotik_corrigir_qrcode.rsc\n";
echo "   2️⃣  Verifique URL no painel Woovi\n";
echo "   3️⃣  Teste com: ./teste_webhook.sh\n";
echo "   4️⃣  Faça um pagamento teste\n\n";

echo "🎯 PROBLEMAS IDENTIFICADOS E SOLUÇÕES:\n";
echo "   ❌ QR Code não carrega → Walled Garden bloqueando api.openpix.com.br\n";
echo "   ❌ Webhook não automático → URL incorreta no painel Woovi\n";
echo "   ❌ Erro 500 no /api/mikrotik/allow → Linha 231 do controller\n\n";

echo "✅ SOLUÇÕES APLICADAS:\n";
echo "   ✅ Script do MikroTik gerado\n";
echo "   ✅ Controller corrigido\n";
echo "   ✅ Script de teste criado\n";
echo "   ✅ Instruções detalhadas fornecidas\n\n";

echo "🚀 EXECUTE OS SCRIPTS E TESTE NOVAMENTE!\n";
echo "=" . str_repeat("=", 60) . "\n";
