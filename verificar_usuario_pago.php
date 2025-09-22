<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configuração Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Payment;
use App\Models\Session;

echo "=== VERIFICANDO USUÁRIO MAC 4a:24:2c:27:7e:86 ===\n\n";

// 1. Buscar usuário pelo MAC
$user = User::where('mac_address', '4a:24:2c:27:7e:86')->first();

if (!$user) {
    echo "❌ Usuário não encontrado pelo MAC\n";
    echo "🔍 Tentando buscar por IP...\n";
    
    $user = User::where('ip_address', '10.10.10.100')->first();
    
    if (!$user) {
        echo "❌ Usuário não encontrado por IP também\n";
        exit(1);
    }
}

echo "✅ USUÁRIO ENCONTRADO:\n";
echo "   ID: {$user->id}\n";
echo "   Nome: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   MAC: {$user->mac_address}\n";
echo "   IP: {$user->ip_address}\n";
echo "   Status: {$user->status}\n";
echo "   Conectado em: {$user->connected_at}\n";
echo "   Expira em: {$user->expires_at}\n\n";

// 2. Verificar pagamentos
echo "💰 PAGAMENTOS DO USUÁRIO:\n";
$payments = Payment::where('user_id', $user->id)->orderBy('id', 'desc')->get();

foreach ($payments as $payment) {
    echo "   Pagamento ID: {$payment->id}\n";
    echo "   Valor: R$ {$payment->amount}\n";
    echo "   Status: {$payment->status}\n";
    echo "   Tipo: {$payment->payment_type}\n";
    echo "   Transaction ID: {$payment->transaction_id}\n";
    echo "   Pago em: {$payment->paid_at}\n";
    echo "   Criado em: {$payment->created_at}\n";
    echo "   ---\n";
}

// 3. Verificar sessões
echo "\n🔗 SESSÕES DO USUÁRIO:\n";
$sessions = Session::where('user_id', $user->id)->orderBy('id', 'desc')->get();

foreach ($sessions as $session) {
    echo "   Sessão ID: {$session->id}\n";
    echo "   Payment ID: {$session->payment_id}\n";
    echo "   Status: {$session->session_status}\n";
    echo "   Iniciada em: {$session->started_at}\n";
    echo "   Finalizada em: {$session->ended_at}\n";
    echo "   ---\n";
}

// 4. Verificar se precisa liberar
$latestPayment = Payment::where('user_id', $user->id)
    ->where('status', 'completed')
    ->orderBy('id', 'desc')
    ->first();

if ($latestPayment) {
    echo "\n💳 ÚLTIMO PAGAMENTO APROVADO:\n";
    echo "   ID: {$latestPayment->id}\n";
    echo "   Valor: R$ {$latestPayment->amount}\n";
    echo "   Pago em: {$latestPayment->paid_at}\n";
    
    if ($user->status !== 'connected') {
        echo "\n⚠️ USUÁRIO PAGOU MAS NÃO ESTÁ CONECTADO!\n";
        echo "   Status atual: {$user->status}\n";
        echo "   Precisa ser liberado no MikroTik\n";
    } else {
        echo "\n✅ Usuário está com status 'connected'\n";
        
        if ($user->expires_at && $user->expires_at > now()) {
            echo "✅ Acesso ainda válido até: {$user->expires_at}\n";
        } else {
            echo "⚠️ Acesso expirado em: {$user->expires_at}\n";
        }
    }
} else {
    echo "\n❌ Nenhum pagamento aprovado encontrado\n";
}

echo "\n=== FIM VERIFICAÇÃO ===\n";
