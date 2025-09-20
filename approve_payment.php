<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== APROVAÇÃO MANUAL DE PAGAMENTO ===\n";

// Payment ID que está pendente (baseado nos logs do usuário)
$paymentId = 37; // ID do pagamento pendente

echo "🔍 Verificando pagamento ID: {$paymentId}\n\n";

try {
    // Buscar pagamento
    $payment = \App\Models\Payment::find($paymentId);
    
    if (!$payment) {
        echo "❌ Pagamento ID {$paymentId} não encontrado!\n";
        exit;
    }
    
    echo "📋 DADOS DO PAGAMENTO:\n";
    echo "   ID: {$payment->id}\n";
    echo "   Usuário ID: {$payment->user_id}\n";
    echo "   Valor: R$ " . number_format($payment->amount, 2) . "\n";
    echo "   Status: {$payment->status}\n";
    echo "   Tipo: {$payment->payment_type}\n";
    echo "   Transaction ID: {$payment->transaction_id}\n";
    echo "   Gateway Payment ID: " . ($payment->gateway_payment_id ?: 'N/A') . "\n";
    echo "   Criado em: {$payment->created_at}\n";
    
    // Buscar usuário
    $user = $payment->user;
    if ($user) {
        echo "\n📱 DADOS DO USUÁRIO:\n";
        echo "   ID: {$user->id}\n";
        echo "   MAC: {$user->mac_address}\n";
        echo "   IP: {$user->ip_address}\n";
        echo "   Status: {$user->status}\n";
        echo "   Conectado em: " . ($user->connected_at ?: 'Nunca') . "\n";
        echo "   Expira em: " . ($user->expires_at ?: 'Não definido') . "\n";
    }
    
    if ($payment->status === 'completed') {
        echo "\n✅ Pagamento já está aprovado!\n";
        exit;
    }
    
    echo "\n🔄 Aprovando pagamento automaticamente...\n";
    
    // Usar transação para garantir consistência
    \Illuminate\Support\Facades\DB::beginTransaction();
    
    // 1. Atualizar status do pagamento
    $payment->update([
        'status' => 'completed',
        'paid_at' => now()
    ]);
    
    echo "   ✅ Pagamento marcado como pago\n";
    
    // 2. Criar sessão ativa
    $session = \App\Models\Session::create([
        'user_id' => $payment->user_id,
        'payment_id' => $payment->id,
        'started_at' => now(),
        'session_status' => 'active'
    ]);
    
    echo "   ✅ Sessão criada: ID {$session->id}\n";
    
    // 3. Atualizar status do usuário
    $sessionDurationHours = config('wifi.pricing.session_duration_hours', 24);
    $user->update([
        'status' => 'connected',
        'connected_at' => now(),
        'expires_at' => now()->addHours($sessionDurationHours)
    ]);
    
    echo "   ✅ Usuário marcado como conectado\n";
    echo "   ⏰ Expira em: {$user->expires_at} ({$sessionDurationHours}h de duração)\n";
    
    \Illuminate\Support\Facades\DB::commit();
    
    echo "\n🎉 PAGAMENTO APROVADO COM SUCESSO!\n";
    echo "\n📊 RESUMO:\n";
    echo "   💳 Pagamento: APROVADO\n";
    echo "   👤 Usuário: CONECTADO\n";
    echo "   📱 MAC: {$user->mac_address}\n";
    echo "   ⏰ Válido até: {$user->expires_at}\n";
    echo "   🔄 Sync MikroTik: Automático a cada 2 minutos\n";
    
    echo "\n🚀 PRÓXIMOS PASSOS:\n";
    echo "1. O usuário já pode navegar normalmente\n";
    echo "2. O MikroTik vai liberar o acesso no próximo sync\n";
    echo "3. Verifique os logs do sync no MikroTik\n";
    
} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::rollback();
    echo "❌ Erro ao aprovar pagamento: {$e->getMessage()}\n";
    echo "📜 Trace: {$e->getTraceAsString()}\n";
}

echo "\n=== FIM ===\n";
