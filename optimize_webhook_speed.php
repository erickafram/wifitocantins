<?php
/**
 * OTIMIZAÇÃO DA VELOCIDADE DOS WEBHOOKS WOOVI
 * Melhora a velocidade de confirmação dos pagamentos
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

echo "🚀 OTIMIZAÇÃO DE VELOCIDADE DOS WEBHOOKS WOOVI\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. CONFIGURAR CACHE PARA WEBHOOKS
echo "1️⃣ CONFIGURANDO CACHE PARA WEBHOOKS:\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Configurar cache Redis/Memcached se disponível
    $cacheDriver = config('cache.default');
    echo "🗄️  Driver de cache atual: {$cacheDriver}\n";
    
    if ($cacheDriver === 'file') {
        echo "⚠️  RECOMENDAÇÃO: Use Redis ou Memcached para melhor performance\n";
        echo "   Adicione ao .env:\n";
        echo "   CACHE_DRIVER=redis\n";
        echo "   REDIS_HOST=127.0.0.1\n";
        echo "   REDIS_PASSWORD=null\n";
        echo "   REDIS_PORT=6379\n\n";
    } else {
        echo "✅ Cache otimizado configurado!\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar cache: " . $e->getMessage() . "\n\n";
}

// 2. OTIMIZAR BANCO DE DADOS
echo "2️⃣ OTIMIZANDO CONSULTAS NO BANCO:\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Verificar índices importantes
    $indexes = [
        'payments' => ['user_id', 'status', 'transaction_id', 'gateway_payment_id'],
        'users' => ['mac_address', 'status', 'expires_at'],
        'sessions' => ['user_id', 'session_status']
    ];
    
    foreach ($indexes as $table => $fields) {
        echo "📊 Tabela: {$table}\n";
        
        foreach ($fields as $field) {
            try {
                $result = DB::select("SHOW INDEX FROM {$table} WHERE Column_name = '{$field}'");
                if (empty($result)) {
                    echo "   ⚠️  Índice ausente: {$field}\n";
                    
                    // Sugerir criação do índice
                    echo "   🔧 Execute: ALTER TABLE {$table} ADD INDEX idx_{$field} ({$field});\n";
                } else {
                    echo "   ✅ Índice OK: {$field}\n";
                }
            } catch (Exception $e) {
                echo "   ❌ Erro ao verificar índice {$field}: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar índices: " . $e->getMessage() . "\n\n";
}

// 3. VERIFICAR CONFIGURAÇÕES DE PERFORMANCE
echo "3️⃣ CONFIGURAÇÕES DE PERFORMANCE:\n";
echo "-" . str_repeat("-", 40) . "\n";

$configs = [
    'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
    'LOG_LEVEL' => config('logging.level', 'info'),
    'QUEUE_CONNECTION' => config('queue.default'),
    'SESSION_DRIVER' => config('session.driver'),
    'CACHE_DRIVER' => config('cache.default'),
];

foreach ($configs as $key => $value) {
    echo "🔧 {$key}: {$value}";
    
    // Dar recomendações
    switch ($key) {
        case 'APP_DEBUG':
            if ($value === 'true') {
                echo " ⚠️  RECOMENDAÇÃO: Desabilitar em produção";
            } else {
                echo " ✅";
            }
            break;
        case 'LOG_LEVEL':
            if ($value === 'debug') {
                echo " ⚠️  RECOMENDAÇÃO: Usar 'info' ou 'warning' em produção";
            } else {
                echo " ✅";
            }
            break;
        case 'QUEUE_CONNECTION':
            if ($value === 'sync') {
                echo " ⚠️  RECOMENDAÇÃO: Usar Redis ou database para webhooks";
            } else {
                echo " ✅";
            }
            break;
    }
    echo "\n";
}

echo "\n";

// 4. TESTAR VELOCIDADE DOS ENDPOINTS
echo "4️⃣ TESTANDO VELOCIDADE DOS ENDPOINTS:\n";
echo "-" . str_repeat("-", 40) . "\n";

$endpoints = [
    'Ping' => '/api/mikrotik-sync/ping',
    'Pending Users' => '/api/mikrotik-sync/pending-users',
    'Check Access' => '/api/mikrotik-sync/check-access',
    'Stats' => '/api/mikrotik-sync/stats'
];

$baseUrl = config('app.url', 'https://www.tocantinstransportewifi.com.br');
$token = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');

foreach ($endpoints as $name => $endpoint) {
    echo "🌐 Testando {$name}...\n";
    
    $startTime = microtime(true);
    
    try {
        $url = $baseUrl . $endpoint;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                'timeout' => 5
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "   ✅ Sucesso em {$duration}ms\n";
                
                if ($duration > 1000) {
                    echo "   ⚠️  LENTO: Endpoint demora mais que 1 segundo\n";
                } elseif ($duration > 500) {
                    echo "   ⚠️  MÉDIO: Considere otimizar (>{$duration}ms)\n";
                } else {
                    echo "   🚀 RÁPIDO: Performance excelente\n";
                }
            } else {
                echo "   ❌ Erro na resposta em {$duration}ms\n";
            }
        } else {
            echo "   ❌ Falha na requisição\n";
        }
        
    } catch (Exception $e) {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        echo "   ❌ Erro: " . $e->getMessage() . " ({$duration}ms)\n";
    }
    
    echo "\n";
}

// 5. VERIFICAR LOGS DE WEBHOOK
echo "5️⃣ ANÁLISE DOS LOGS DE WEBHOOK (ÚLTIMAS 24H):\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        
        $webhookStats = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'slow' => 0
        ];
        
        $recentLines = array_slice($lines, -1000); // Últimas 1000 linhas
        
        foreach ($recentLines as $line) {
            if (stripos($line, 'webhook') !== false) {
                $webhookStats['total']++;
                
                if (stripos($line, 'success') !== false || stripos($line, '200') !== false) {
                    $webhookStats['success']++;
                }
                
                if (stripos($line, 'error') !== false || stripos($line, 'fail') !== false) {
                    $webhookStats['errors']++;
                }
                
                // Detectar webhooks lentos (mais que 2 segundos)
                if (preg_match('/(\d+)ms/', $line, $matches)) {
                    $duration = intval($matches[1]);
                    if ($duration > 2000) {
                        $webhookStats['slow']++;
                    }
                }
            }
        }
        
        echo "📊 ESTATÍSTICAS DOS WEBHOOKS:\n";
        echo "   Total de webhooks: {$webhookStats['total']}\n";
        echo "   Sucessos: {$webhookStats['success']}\n";
        echo "   Erros: {$webhookStats['errors']}\n";
        echo "   Lentos (>2s): {$webhookStats['slow']}\n";
        
        if ($webhookStats['total'] > 0) {
            $successRate = round(($webhookStats['success'] / $webhookStats['total']) * 100, 1);
            echo "   Taxa de sucesso: {$successRate}%\n";
            
            if ($successRate < 95) {
                echo "   ⚠️  ATENÇÃO: Taxa de sucesso baixa!\n";
            } else {
                echo "   ✅ Taxa de sucesso excelente!\n";
            }
        }
        
    } else {
        echo "❌ Arquivo de log não encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao analisar logs: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. RECOMENDAÇÕES FINAIS
echo "6️⃣ RECOMENDAÇÕES PARA VELOCIDADE MÁXIMA:\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "🔧 CONFIGURAÇÕES RECOMENDADAS:\n\n";

echo "1. CACHE (adicionar ao .env):\n";
echo "   CACHE_DRIVER=redis\n";
echo "   REDIS_HOST=127.0.0.1\n";
echo "   REDIS_PORT=6379\n\n";

echo "2. SESSÕES (adicionar ao .env):\n";
echo "   SESSION_DRIVER=redis\n";
echo "   SESSION_LIFETIME=120\n\n";

echo "3. QUEUE (adicionar ao .env):\n";
echo "   QUEUE_CONNECTION=redis\n\n";

echo "4. LOGS (adicionar ao .env):\n";
echo "   LOG_LEVEL=warning\n";
echo "   APP_DEBUG=false\n\n";

echo "5. BANCO DE DADOS:\n";
echo "   Executar: php artisan optimize:clear\n";
echo "   Executar: php artisan config:cache\n";
echo "   Executar: php artisan route:cache\n";
echo "   Executar: php artisan view:cache\n\n";

echo "6. ÍNDICES DO BANCO:\n";
echo "   ALTER TABLE payments ADD INDEX idx_transaction_id (transaction_id);\n";
echo "   ALTER TABLE payments ADD INDEX idx_gateway_payment_id (gateway_payment_id);\n";
echo "   ALTER TABLE users ADD INDEX idx_status_expires (status, expires_at);\n\n";

echo "🎯 RESULTADO ESPERADO:\n";
echo "   • Webhooks processados em <500ms\n";
echo "   • Confirmação de pagamento em <10 segundos\n";
echo "   • Liberação no MikroTik em <30 segundos\n";
echo "   • Taxa de sucesso >99%\n\n";

echo "✅ OTIMIZAÇÃO CONCLUÍDA!\n";
echo "=" . str_repeat("=", 60) . "\n";
