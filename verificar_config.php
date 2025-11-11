<?php
/**
 * Verificar configurações atuais
 * Execute: php verificar_config.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificando Configurações do Sistema\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Verificar .env
echo "1️⃣ Arquivo .env:\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Buscar WIFI_DEFAULT_PRICE
    if (preg_match('/WIFI_DEFAULT_PRICE=(.+)/', $envContent, $matches)) {
        $priceEnv = trim($matches[1]);
        echo "   WIFI_DEFAULT_PRICE = $priceEnv\n";
        
        if ($priceEnv == '1.00' || $priceEnv == '1') {
            echo "   ✅ Valor correto no .env\n";
        } else {
            echo "   ❌ Valor incorreto! Deveria ser 1.00\n";
        }
    } else {
        echo "   ⚠️ WIFI_DEFAULT_PRICE não encontrado no .env\n";
    }
    
    // Buscar PIX_ENVIRONMENT
    if (preg_match('/PIX_ENVIRONMENT=(.+)/', $envContent, $matches)) {
        $pixEnv = trim($matches[1]);
        echo "   PIX_ENVIRONMENT = $pixEnv\n";
        
        if ($pixEnv == 'production') {
            echo "   ✅ Ambiente de produção\n";
        } else {
            echo "   ⚠️ Ainda em sandbox\n";
        }
    }
} else {
    echo "   ❌ Arquivo .env não encontrado\n";
}

echo "\n";

// 2. Verificar config carregada
echo "2️⃣ Configuração Carregada (config/wifi.php):\n";
$defaultPrice = config('wifi.pricing.default_price');
echo "   config('wifi.pricing.default_price') = $defaultPrice\n";

if ($defaultPrice >= 1.00) {
    echo "   ✅ Configuração correta\n";
} else {
    echo "   ❌ Configuração incorreta! Execute: php artisan config:clear\n";
}

echo "\n";

// 3. Verificar ambiente PIX
echo "3️⃣ Ambiente PIX:\n";
$pixEnv = config('wifi.payment_gateways.pix.environment');
echo "   config('wifi.payment_gateways.pix.environment') = $pixEnv\n";

if ($pixEnv === 'production') {
    echo "   ✅ Produção\n";
} else {
    echo "   ⚠️ Sandbox\n";
}

echo "\n";

// 4. Verificar token PagBank
echo "4️⃣ Token PagBank:\n";
$token = config('wifi.payment_gateways.pix.pagbank_token');
if ($token) {
    echo "   Token: " . substr($token, 0, 20) . "..." . substr($token, -20) . "\n";
    echo "   Tamanho: " . strlen($token) . " caracteres\n";
    
    if (strlen($token) == 100) {
        echo "   ✅ Token configurado\n";
    } else {
        echo "   ⚠️ Token pode estar incorreto\n";
    }
} else {
    echo "   ❌ Token não configurado\n";
}

echo "\n";

// 5. Verificar pagamentos existentes
echo "5️⃣ Últimos Pagamentos no Banco:\n";
try {
    $payments = \App\Models\Payment::orderBy('created_at', 'desc')
        ->take(5)
        ->get(['id', 'amount', 'status', 'created_at']);
    
    if ($payments->count() > 0) {
        foreach ($payments as $payment) {
            $status = $payment->status;
            $emoji = $status === 'paid' ? '✅' : ($status === 'pending' ? '⏳' : '❌');
            echo "   $emoji ID: {$payment->id} | R$ {$payment->amount} | {$status} | {$payment->created_at}\n";
        }
        
        $oldPayments = $payments->where('amount', '<', 1.00)->count();
        if ($oldPayments > 0) {
            echo "\n   ⚠️ Existem $oldPayments pagamentos com valor < R$ 1,00\n";
            echo "   Esses pagamentos antigos podem causar erro ao regenerar\n";
        }
    } else {
        echo "   Nenhum pagamento encontrado\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erro ao consultar pagamentos: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "\n📋 RESUMO:\n";
echo "   1. Verifique se WIFI_DEFAULT_PRICE=1.00 no .env\n";
echo "   2. Execute: php artisan config:clear\n";
echo "   3. Execute: php artisan cache:clear\n";
echo "   4. Teste criando um NOVO pagamento (não regenerar antigo)\n";
echo "\n" . str_repeat("=", 70) . "\n";
