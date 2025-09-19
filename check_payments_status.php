<?php
/**
 * Verificar Status dos Pagamentos
 * Execute: php check_payments_status.php
 */

require_once 'vendor/autoload.php';

// Carregar configurações Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "💳 STATUS DOS PAGAMENTOS\n";
echo "========================\n";

// Buscar todos os pagamentos
$payments = App\Models\Payment::with('user')
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de pagamentos: " . $payments->count() . "\n\n";

// Estatísticas
$pending = $payments->where('status', 'pending')->count();
$completed = $payments->where('status', 'completed')->count();
$failed = $payments->where('status', 'failed')->count();
$cancelled = $payments->where('status', 'cancelled')->count();

echo "📊 ESTATÍSTICAS:\n";
echo "Pending: {$pending}\n";
echo "Completed: {$completed}\n";
echo "Failed: {$failed}\n";
echo "Cancelled: {$cancelled}\n\n";

// Listar pagamentos detalhadamente
echo "📋 DETALHES DOS PAGAMENTOS:\n";
echo str_repeat("=", 80) . "\n";

foreach ($payments as $payment) {
    echo "ID: {$payment->id}\n";
    echo "Status: " . strtoupper($payment->status) . "\n";
    echo "Valor: R$ " . number_format($payment->amount, 2, ',', '.') . "\n";
    echo "User ID: {$payment->user_id}\n";
    echo "MAC Address: " . ($payment->user->mac_address ?? 'N/A') . "\n";
    echo "Transaction ID: {$payment->transaction_id}\n";
    echo "PIX Location: " . ($payment->pix_location ?? 'N/A') . "\n";
    echo "Gateway Payment ID: " . ($payment->gateway_payment_id ?? 'N/A') . "\n";
    echo "Criado em: {$payment->created_at}\n";
    echo "Pago em: " . ($payment->paid_at ?? 'N/A') . "\n";
    
    // Verificar sessões
    $sessions = App\Models\Session::where('payment_id', $payment->id)->get();
    echo "Sessões: " . $sessions->count() . "\n";
    
    if ($sessions->count() > 0) {
        foreach ($sessions as $session) {
            echo "  - Sessão ID: {$session->id} | Status: {$session->session_status}\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n";
}

// Verificar usuários conectados
echo "\n👥 USUÁRIOS CONECTADOS:\n";
$connectedUsers = App\Models\User::where('status', 'connected')->get();
echo "Total: " . $connectedUsers->count() . "\n";

foreach ($connectedUsers as $user) {
    echo "User ID: {$user->id} | MAC: {$user->mac_address} | Expira: " . ($user->expires_at ?? 'N/A') . "\n";
}

echo "\n========================\n";
echo "Verificação concluída!\n"; 