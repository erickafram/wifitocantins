#!/usr/bin/env php
<?php
/**
 * Diagnóstico completo da configuração PagBank
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       DIAGNÓSTICO CONFIGURAÇÃO PAGBANK                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Verificar variáveis de ambiente
echo "📋 VARIÁVEIS DE AMBIENTE (.env):\n";
echo str_repeat("-", 60) . "\n";
echo "PIX_GATEWAY: " . (env('PIX_GATEWAY') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "PIX_ENVIRONMENT: " . (env('PIX_ENVIRONMENT') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "PAGBANK_TOKEN: " . (env('PAGBANK_TOKEN') ? '✅ ' . substr(env('PAGBANK_TOKEN'), 0, 30) . '...' : '❌ NÃO ENCONTRADO') . "\n";
echo "PAGBANK_EMAIL: " . (env('PAGBANK_EMAIL') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "\n";

// 2. Verificar configuração do Laravel
echo "⚙️  CONFIGURAÇÃO LARAVEL (config):\n";
echo str_repeat("-", 60) . "\n";
echo "config('wifi.payment_gateways.pix.gateway'): " . (config('wifi.payment_gateways.pix.gateway') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "config('wifi.payment_gateways.pix.environment'): " . (config('wifi.payment_gateways.pix.environment') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "config('wifi.payment_gateways.pix.pagbank_token'): " . (config('wifi.payment_gateways.pix.pagbank_token') ? '✅ ' . substr(config('wifi.payment_gateways.pix.pagbank_token'), 0, 30) . '...' : '❌ NÃO ENCONTRADO') . "\n";
echo "config('wifi.payment_gateways.pix.pagbank_email'): " . (config('wifi.payment_gateways.pix.pagbank_email') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "\n";

// 3. Verificar configuração alternativa
echo "⚙️  CONFIGURAÇÃO ALTERNATIVA:\n";
echo str_repeat("-", 60) . "\n";
echo "config('wifi.payment.default_gateway'): " . (config('wifi.payment.default_gateway') ?: '❌ NÃO ENCONTRADO') . "\n";
echo "\n";

// 4. Verificar SystemSettings
echo "🗄️  BANCO DE DADOS (SystemSettings):\n";
echo str_repeat("-", 60) . "\n";
try {
    $pixGateway = \App\Models\SystemSetting::getValue('pix_gateway');
    echo "SystemSetting::getValue('pix_gateway'): " . ($pixGateway ?: '❌ NÃO ENCONTRADO') . "\n";
} catch (Exception $e) {
    echo "❌ Erro ao buscar SystemSettings: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Análise
echo "🔍 ANÁLISE:\n";
echo str_repeat("-", 60) . "\n";

$gateway = config('wifi.payment_gateways.pix.gateway') ?: config('wifi.payment.default_gateway');
$token = config('wifi.payment_gateways.pix.pagbank_token');
$environment = config('wifi.payment_gateways.pix.environment');

if ($gateway === 'pagbank') {
    echo "✅ Gateway configurado para: pagbank\n";
    
    if ($token) {
        echo "✅ Token PagBank encontrado\n";
        echo "✅ Ambiente: " . $environment . "\n";
        echo "\n";
        echo "🎉 CONFIGURAÇÃO CORRETA!\n";
        echo "   O sistema DEVERIA usar PagBank.\n";
    } else {
        echo "❌ Token PagBank NÃO encontrado!\n";
        echo "   Sistema vai usar fallback manual.\n";
    }
} else {
    echo "❌ Gateway configurado para: " . ($gateway ?: 'nenhum') . "\n";
    echo "   Deveria ser: pagbank\n";
    echo "\n";
    echo "💡 SOLUÇÃO:\n";
    echo "   1. Verificar se .env tem: PIX_GATEWAY=pagbank\n";
    echo "   2. Executar: php artisan config:clear\n";
    echo "   3. Executar: php artisan cache:clear\n";
}

echo "\n";

// 6. Testar instanciação do serviço
echo "🧪 TESTE DE INSTANCIAÇÃO:\n";
echo str_repeat("-", 60) . "\n";

try {
    $service = new \App\Services\PagBankPixService();
    echo "✅ PagBankPixService instanciado com sucesso\n";
    
    // Testar método de conexão
    $testResult = $service->testConnection();
    
    if ($testResult['success'] ?? false) {
        echo "✅ Teste de conexão: SUCESSO\n";
        echo "   " . ($testResult['message'] ?? '') . "\n";
    } else {
        echo "❌ Teste de conexão: FALHOU\n";
        echo "   " . ($testResult['message'] ?? 'Erro desconhecido') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao instanciar PagBankPixService:\n";
    echo "   " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "                   FIM DO DIAGNÓSTICO                      \n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

// 7. Recomendações
echo "📝 RECOMENDAÇÕES:\n";
echo "\n";

if (!$token) {
    echo "❌ Token PagBank não encontrado na configuração!\n";
    echo "\n";
    echo "SOLUÇÃO 1: Verificar .env\n";
    echo "  grep 'PAGBANK_TOKEN' .env\n";
    echo "\n";
    echo "SOLUÇÃO 2: Limpar TODOS os caches\n";
    echo "  php artisan config:clear\n";
    echo "  php artisan cache:clear\n";
    echo "  php artisan config:cache\n";
    echo "\n";
    echo "SOLUÇÃO 3: Reiniciar servidor web\n";
    echo "  systemctl restart nginx\n";
    echo "  systemctl restart php8.1-fpm\n";
    echo "\n";
} else {
    echo "✅ Configuração parece correta!\n";
    echo "\n";
    echo "Se ainda não funcionar no portal:\n";
    echo "1. Limpar cache do navegador (Ctrl+F5)\n";
    echo "2. Verificar logs: tail -f storage/logs/laravel.log\n";
    echo "3. Testar diretamente: php teste_pagbank_final.php\n";
    echo "\n";
}

