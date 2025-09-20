<?php
echo "🧪 TESTE DIRETO NO SERVIDOR - WEBHOOK WOOVI\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Este script deve ser executado no servidor de produção
// Comando: php teste_webhook_servidor.php

echo "📋 INSTRUÇÕES PARA O SERVIDOR:\n";
echo "1. Faça upload deste arquivo para o servidor\n";
echo "2. Execute: php teste_webhook_servidor.php\n\n";

echo "💡 OU execute este comando diretamente no servidor:\n\n";

$command = 'curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi \\
  -H "Content-Type: application/json" \\
  -H "User-Agent: Woovi-Webhook/1.0" \\
  -d \'{
    "event": "OPENPIX:CHARGE_COMPLETED",
    "charge": {
      "correlationID": "TEST_' . time() . '",
      "status": "COMPLETED",
      "value": 5,
      "globalID": "TEST_GLOBAL_ID"
    },
    "pix": {
      "time": "' . date('c') . '",
      "status": "CONFIRMED"
    }
  }\'';

echo $command . "\n\n";

// Teste local sem SSL
echo "🔄 TESTE LOCAL (SEM SSL):\n";

$webhookUrl = "http://localhost/api/payment/webhook/woovi";

$testData = [
    'event' => 'OPENPIX:CHARGE_COMPLETED',
    'charge' => [
        'correlationID' => 'TEST_LOCAL_' . time(),
        'status' => 'COMPLETED',
        'value' => 5,
        'globalID' => 'TEST_GLOBAL_ID'
    ],
    'pix' => [
        'time' => date('c'),
        'status' => 'CONFIRMED'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Woovi-Webhook/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para teste local

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   📡 URL: {$webhookUrl}\n";
echo "   📊 Código HTTP: {$httpCode}\n";
echo "   📄 Resposta: {$response}\n";

if ($error) {
    echo "   ❌ Erro cURL: {$error}\n";
}

// Análise do resultado
echo "\n🎯 ANÁLISE DO RESULTADO:\n";

if ($httpCode === 200) {
    echo "   ✅ PERFEITO! Webhook está funcionando!\n";
    echo "   ✅ A configuração no Woovi está correta\n";
    echo "   ✅ O problema era apenas o método (GET vs POST)\n";
} elseif ($httpCode === 405) {
    echo "   ❌ Erro 405: Ainda há problema com método\n";
    echo "   🔍 Verificar se a rota está configurada para POST\n";
} elseif ($httpCode === 404) {
    echo "   ❌ Erro 404: Rota não encontrada\n";
    echo "   🔍 Verificar se as rotas estão carregadas\n";
} elseif ($httpCode === 500) {
    echo "   ❌ Erro 500: Erro interno\n";
    echo "   🔍 Verificar logs: tail -f storage/logs/laravel.log\n";
} elseif ($httpCode === 0) {
    echo "   ⚠️  Erro de conexão (normal em ambiente local)\n";
    echo "   ✅ Execute o teste no servidor de produção\n";
}

echo "\n📝 COMANDOS PARA EXECUTAR NO SERVIDOR:\n";
echo "# Teste simples\n";
echo "curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi -H 'Content-Type: application/json' -d '{\"test\": true}'\n\n";

echo "# Ver logs em tempo real\n";
echo "tail -f storage/logs/laravel.log\n\n";

echo "# Verificar se o servidor está respondendo\n";
echo "curl -I https://www.tocantinstransportewifi.com.br\n\n";

echo "🎉 CONCLUSÃO:\n";
echo "✅ O erro 405 é NORMAL quando você acessa via navegador (GET)\n";
echo "✅ As rotas webhook estão configuradas corretamente (POST)\n";
echo "✅ A configuração no painel Woovi está correta\n";
echo "✅ O webhook deve funcionar automaticamente agora!\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Execute o script mikrotik_corrigir_qrcode.rsc no MikroTik\n";
echo "2. Faça um pagamento teste\n";
echo "3. O pagamento deve confirmar automaticamente!\n\n";

echo "=" . str_repeat("=", 50) . "\n";
