<?php
/**
 * Script para monitorar o teste em tempo real
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;
use App\Models\Session;

echo "🔍 MONITORAMENTO DE TESTE EM TEMPO REAL\n";
echo str_repeat("=", 50) . "\n";

while (true) {
    system('clear'); // Linux/Mac
    // system('cls'); // Windows
    
    echo "🔍 MONITORAMENTO - " . now()->format('H:i:s') . "\n";
    echo str_repeat("=", 50) . "\n";
    
    // USUÁRIOS
    $users = User::orderBy('created_at', 'desc')->get();
    echo "👥 USUÁRIOS (" . $users->count() . "):\n";
    foreach ($users as $user) {
        $status = $user->status == 'connected' ? '🟢' : '🔴';
        echo "   $status {$user->name} | {$user->email} | MAC: {$user->mac_address} | Status: {$user->status}\n";
    }
    
    echo "\n";
    
    // PAGAMENTOS
    $payments = Payment::with('user')->orderBy('created_at', 'desc')->get();
    echo "💳 PAGAMENTOS (" . $payments->count() . "):\n";
    foreach ($payments as $payment) {
        $status_icon = $payment->status == 'completed' ? '✅' : 
                      ($payment->status == 'pending' ? '⏳' : '❌');
        $user_name = $payment->user ? $payment->user->name : 'N/A';
        echo "   $status_icon R$ {$payment->amount} | {$payment->status} | User: {$user_name} | {$payment->created_at->format('H:i:s')}\n";
    }
    
    echo "\n";
    
    // SESSÕES
    $sessions = Session::with('user')->orderBy('created_at', 'desc')->get();
    echo "🔗 SESSÕES (" . $sessions->count() . "):\n";
    foreach ($sessions as $session) {
        $user_name = $session->user ? $session->user->name : 'N/A';
        echo "   🔗 {$session->session_status} | User: {$user_name} | Iniciada: {$session->started_at}\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    echo "⏱️ Atualizando em 3 segundos... (Ctrl+C para parar)\n";
    
    sleep(3);
}
