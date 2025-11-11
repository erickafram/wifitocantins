<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;

echo "🔍 VERIFICANDO MAC REAL NO BANCO:\n";
echo "==================================\n\n";

// Buscar usuários mais recentes
$users = User::orderBy('created_at', 'desc')->limit(5)->get();

echo "👥 ÚLTIMOS 5 USUÁRIOS:\n";
foreach ($users as $user) {
    $isReal = !preg_match('/^02:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}$/', $user->mac_address);
    $macType = $isReal ? "✅ REAL" : "❌ MOCK";
    
    echo "ID: {$user->id} | MAC: {$user->mac_address} | {$macType} | {$user->created_at}\n";
}

echo "\n💳 PAGAMENTOS RECENTES:\n";
$payments = Payment::with('user')->orderBy('created_at', 'desc')->limit(3)->get();

foreach ($payments as $payment) {
    $mac = $payment->user->mac_address ?? 'N/A';
    $isReal = !preg_match('/^02:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}$/', $mac);
    $macType = $isReal ? "✅ REAL" : "❌ MOCK";
    
    echo "Payment ID: {$payment->id} | Status: {$payment->status} | MAC: {$mac} | {$macType}\n";
}

echo "\n🎯 TESTE: Acesse o portal e faça um pagamento teste\n";
echo "📱 URL: https://www.tocantinstransportewifi.com.br/?mac=YOUR_REAL_MAC\n";
echo "🔄 Execute este script novamente para ver se o MAC foi salvo corretamente\n";
?>
