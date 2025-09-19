<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO DE CONEXÃO MIKROTIK ===\n";
echo "Testando conectividade específica...\n\n";

// Configurações
$mikrotikHost = config('wifi.mikrotik.host', '192.168.10.1');
$mikrotikUser = config('wifi.mikrotik.username', 'api-tocantins');
$mikrotikPass = config('wifi.mikrotik.password', 'TocantinsWiFi2024!');
$mikrotikPort = config('wifi.mikrotik.port', 8728);

echo "📋 CONFIGURAÇÕES ATUAIS:\n";
echo "   Host: {$mikrotikHost}\n";
echo "   Usuário: {$mikrotikUser}\n";
echo "   Senha: " . str_repeat('*', strlen($mikrotikPass)) . "\n";
echo "   Porta: {$mikrotikPort}\n\n";

// 1. Teste de conectividade básica (ping)
echo "1. 🌐 Testando conectividade de rede...\n";

$pingCommand = "ping -c 3 {$mikrotikHost}";
$pingOutput = [];
$pingReturn = 0;

exec($pingCommand, $pingOutput, $pingReturn);

if ($pingReturn === 0) {
    echo "   ✅ MikroTik responde ao ping\n";
    foreach ($pingOutput as $line) {
        if (strpos($line, 'time=') !== false) {
            echo "   📶 {$line}\n";
            break;
        }
    }
} else {
    echo "   ❌ MikroTik NÃO responde ao ping\n";
    echo "   🔍 Verifique:\n";
    echo "      - IP correto no .env\n";
    echo "      - MikroTik ligado e conectado\n";
    echo "      - Rede acessível\n";
}

echo "\n";

// 2. Teste de porta da API
echo "2. 🔌 Testando porta da API ({$mikrotikPort})...\n";

$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket) {
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);
    
    $result = @socket_connect($socket, $mikrotikHost, $mikrotikPort);
    
    if ($result) {
        echo "   ✅ Porta {$mikrotikPort} está aberta\n";
        socket_close($socket);
    } else {
        echo "   ❌ Porta {$mikrotikPort} não está acessível\n";
        echo "   🔍 Possíveis causas:\n";
        echo "      - API não habilitada no MikroTik\n";
        echo "      - Firewall bloqueando\n";
        echo "      - Porta diferente\n";
        socket_close($socket);
    }
} else {
    echo "   ❌ Erro ao criar socket\n";
}

echo "\n";

// 3. Teste de conexão RouterOS API
echo "3. 🔐 Testando autenticação RouterOS API...\n";

try {
    $mikrotikController = new \App\Http\Controllers\MikrotikController();
    
    // Usar reflection para testar método privado de conexão
    $reflection = new ReflectionClass($mikrotikController);
    $connectMethod = $reflection->getMethod('connectToMikroTik');
    $connectMethod->setAccessible(true);
    
    $apiSocket = $connectMethod->invoke($mikrotikController);
    
    if ($apiSocket) {
        echo "   ✅ Conexão RouterOS API bem-sucedida!\n";
        echo "   ✅ Autenticação funcionando\n";
        socket_close($apiSocket);
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro na API RouterOS: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'conectar') !== false) {
        echo "   🔍 Problema de conectividade de rede\n";
    } elseif (strpos($e->getMessage(), 'autenticação') !== false || strpos($e->getMessage(), 'login') !== false) {
        echo "   🔍 Problema de autenticação\n";
        echo "      - Usuário '{$mikrotikUser}' existe no MikroTik?\n";
        echo "      - Senha está correta?\n";
        echo "      - Usuário tem permissões 'full'?\n";
    } elseif (strpos($e->getMessage(), 'desabilitada') !== false) {
        echo "   🔍 API está desabilitada no .env\n";
    }
}

echo "\n";

// 4. Comandos para verificar no MikroTik
echo "4. 🛠️ COMANDOS PARA VERIFICAR NO MIKROTIK:\n";
echo "\n";
echo "   No terminal do MikroTik, execute:\n";
echo "\n";
echo "   # Verificar se API está habilitada:\n";
echo "   /ip service print\n";
echo "\n";
echo "   # Verificar usuários:\n";
echo "   /user print\n";
echo "\n";
echo "   # Criar usuário se não existir:\n";
echo "   /user add name=api-tocantins password=TocantinsWiFi2024! group=full\n";
echo "\n";
echo "   # Habilitar API se necessário:\n";
echo "   /ip service set api disabled=no port=8728\n";
echo "\n";
echo "   # Verificar hotspot (se já configurado):\n";
echo "   /ip hotspot print\n";
echo "   /ip hotspot user print\n";

echo "\n";

// 5. Soluções baseadas no diagnóstico
echo "5. 🎯 PRÓXIMOS PASSOS:\n";
echo "\n";

if ($pingReturn !== 0) {
    echo "   ❌ SEM CONECTIVIDADE DE REDE:\n";
    echo "      1. Verifique o IP do MikroTik\n";
    echo "      2. Confirme que está na mesma rede\n";
    echo "      3. Teste outros IPs (10.10.10.1, 192.168.88.1)\n";
    echo "\n";
} else {
    echo "   ✅ REDE OK - Problema na configuração da API:\n";
    echo "      1. Conecte no MikroTik via Winbox\n";
    echo "      2. Execute os comandos acima\n";
    echo "      3. Teste novamente\n";
    echo "\n";
}

echo "6. 🔄 TESTE RÁPIDO:\n";
echo "\n";
echo "   Execute estes comandos no MikroTik:\n";
echo "\n";
echo "   /user add name=api-tocantins password=TocantinsWiFi2024! group=full\n";
echo "   /ip service set api disabled=no port=8728\n";
echo "\n";
echo "   Depois execute novamente:\n";
echo "   php test_mikrotik_integration.php\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n"; 