<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use App\Models\Payment;

echo "🔧 CORREÇÃO MAC ADDRESS - KAUANY\n";
echo "=================================\n";

try {
    // Buscar pagamento da Kauany pelo transaction_id
    $transactionId = 'TXN_1758499536_B319D28A';
    $payment = Payment::where('transaction_id', $transactionId)
        ->where('status', 'completed')
        ->first();
    
    if (!$payment) {
        echo "❌ Pagamento não encontrado: $transactionId\n";
        exit(1);
    }
    
    echo "✅ Pagamento encontrado:\n";
    echo "   ID: {$payment->id}\n";
    echo "   User ID: {$payment->user_id}\n";
    echo "   Valor: R$ {$payment->amount}\n";
    echo "   Status: {$payment->status}\n";
    echo "   Pago em: {$payment->paid_at}\n\n";
    
    // Buscar usuário
    $user = User::find($payment->user_id);
    
    if (!$user) {
        echo "❌ Usuário não encontrado: {$payment->user_id}\n";
        exit(1);
    }
    
    echo "👤 USUÁRIO ATUAL:\n";
    echo "   ID: {$user->id}\n";
    echo "   MAC atual: {$user->mac_address}\n";
    echo "   IP atual: {$user->ip_address}\n";
    echo "   Status: {$user->status}\n";
    echo "   Expira em: {$user->expires_at}\n\n";
    
    // Atualizar com MAC real
    $realMac = 'e4:84:d3:f4:7f:eb';
    $realIp = '10.10.10.100';
    
    echo "🔄 ATUALIZANDO DADOS:\n";
    echo "   MAC antigo: {$user->mac_address}\n";
    echo "   MAC novo: $realMac\n";
    echo "   IP antigo: {$user->ip_address}\n";
    echo "   IP novo: $realIp\n\n";
    
    $user->update([
        'mac_address' => $realMac,
        'ip_address' => $realIp,
        'status' => 'connected'
    ]);
    
    echo "✅ USUÁRIO ATUALIZADO COM SUCESSO!\n\n";
    
    // Verificar resultado
    $user->refresh();
    echo "📋 DADOS FINAIS:\n";
    echo "   MAC: {$user->mac_address}\n";
    echo "   IP: {$user->ip_address}\n";
    echo "   Status: {$user->status}\n";
    echo "   Expira em: {$user->expires_at}\n";
    
    if ($user->expires_at > now()) {
        echo "✅ Usuário deve ter acesso até: " . $user->expires_at->format('d/m/Y H:i:s') . "\n";
    } else {
        echo "⚠️ Usuário já expirou!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ CORREÇÃO CONCLUÍDA!\n";
