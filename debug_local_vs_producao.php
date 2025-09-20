<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 === DEBUG: LOCAL vs PRODUÇÃO ===\n\n";

echo "1️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
echo "   APP_ENV: " . env('APP_ENV', 'local') . "\n";
echo "   APP_URL: " . env('APP_URL', 'http://localhost') . "\n";
echo "   MIKROTIK_HOST: " . env('MIKROTIK_HOST', 'não configurado') . "\n";
echo "   MIKROTIK_ENABLED: " . (config('wifi.mikrotik.enabled') ? 'true' : 'false') . "\n\n";

echo "2️⃣ SIMULANDO REQUEST LOCAL (como você está fazendo):\n";

// Simular request local (sem MikroTik)
$request = new \Illuminate\Http\Request();
$request->server->set('REMOTE_ADDR', '192.168.0.14'); // Seu IP local
$request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

// Simular o PortalController
$controller = new \App\Http\Controllers\PortalController();

// Usar reflection para acessar método privado
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getClientInfo');
$method->setAccessible(true);

echo "   Executando getClientInfo localmente...\n";
$clientInfo = $method->invoke($controller, $request);

echo "   ✅ RESULTADO LOCAL:\n";
echo "     MAC detectado: " . $clientInfo['mac_address'] . "\n";
echo "     IP detectado: " . $clientInfo['ip_address'] . "\n";
echo "     Device type: " . $clientInfo['device_type'] . "\n\n";

echo "3️⃣ PROBLEMA IDENTIFICADO:\n";
if (strpos($clientInfo['mac_address'], '02:') === 0) {
    echo "   ❌ MAC MOCK sendo gerado (começa com 02:)\n";
    echo "   ❌ Sistema não capturou MAC real: 5C:CD:5B:2F:B9:3F\n\n";
    
    echo "4️⃣ RAZÕES POSSÍVEIS:\n";
    echo "   🔸 Você está testando LOCAL (não no MikroTik hotspot)\n";
    echo "   🔸 Request não tem parâmetros ?mac= na URL\n";
    echo "   🔸 Request não tem headers do MikroTik\n";
    echo "   🔸 Consulta ARP do MikroTik falhou/não executou\n\n";
    
    echo "5️⃣ SOLUÇÕES:\n";
    echo "   📱 TESTE REAL: Conecte no WiFi do ônibus\n";
    echo "   🔗 TESTE MANUAL: Acesse https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F\n";
    echo "   🚀 APLICAR NO SERVIDOR: Upload das correções para produção\n\n";
} else {
    echo "   ✅ MAC real capturado!\n\n";
}

echo "6️⃣ TESTE MANUAL COM MAC REAL:\n";
$macReal = '5C:CD:5B:2F:B9:3F';
echo "   Testando URL: /?mac={$macReal}\n";

// Simular request com MAC na URL
$requestComMac = new \Illuminate\Http\Request();
$requestComMac->query->set('mac', $macReal);
$requestComMac->server->set('REMOTE_ADDR', '192.168.0.14');

$clientInfoComMac = $method->invoke($controller, $requestComMac);
echo "   Resultado: " . $clientInfoComMac['mac_address'] . "\n";

if ($clientInfoComMac['mac_address'] === strtoupper($macReal)) {
    echo "   ✅ CORREÇÃO FUNCIONANDO! MAC real capturado via URL\n\n";
} else {
    echo "   ❌ Algo ainda está errado na correção\n\n";
}

echo "7️⃣ PRÓXIMOS PASSOS:\n";
echo "   1. ⬆️  Upload para produção (DigitalOcean)\n";
echo "   2. 📱 Conectar no WiFi do ônibus\n";
echo "   3. 🧪 Testar no ambiente real (MikroTik hotspot)\n";
echo "   4. 📊 Verificar logs em produção\n\n";

echo "🏁 === DEBUG CONCLUÍDO ===\n";
