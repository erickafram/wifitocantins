<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🔄 Processando pagamento pendente...\n\n";

try {
    DB::beginTransaction();

    $transactionId = 'TXN_1759172653_7EAED671';
    
    // Buscar pagamento
    $payment = Payment::where('transaction_id', $transactionId)->first();
    
    if (!$payment) {
        echo "❌ Pagamento não encontrado com transaction_id: {$transactionId}\n";
        exit(1);
    }
    
    echo "📋 Pagamento encontrado:\n";
    echo "   ID: {$payment->id}\n";
    echo "   User ID: {$payment->user_id}\n";
    echo "   Status Atual: {$payment->status}\n";
    echo "   Valor: R$ {$payment->amount}\n\n";
    
    if ($payment->status === 'completed') {
        echo "✅ Pagamento já está marcado como concluído!\n";
        exit(0);
    }
    
    // Atualizar pagamento
    $payment->update([
        'status' => 'completed',
        'paid_at' => now(),
        'payment_data' => json_encode([
            'processed_manually' => true,
            'processed_at' => now()->toISOString(),
            'reason' => 'Webhook validation failed, processed manually',
        ]),
    ]);
    
    echo "✅ Pagamento atualizado para 'completed'\n\n";
    
    // Buscar usuário
    $user = User::find($payment->user_id);
    
    if ($user) {
        echo "👤 Usuário encontrado:\n";
        echo "   Nome: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   MAC: {$user->mac_address}\n\n";
        
        // Atualizar status do usuário
        $user->update([
            'status' => 'connected',
            'connected_at' => now(),
            'expires_at' => now()->addHours(24), // 24 horas de acesso
        ]);
        
        echo "✅ Status do usuário atualizado para 'connected'\n";
        echo "✅ Acesso válido até: " . now()->addHours(24)->format('d/m/Y H:i:s') . "\n\n";
        
        // Criar sessão WiFi
        DB::table('wifi_sessions')->insert([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'started_at' => now(),
            'session_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Sessão WiFi criada\n\n";
    } else {
        echo "⚠️ Usuário não encontrado!\n\n";
    }
    
    DB::commit();
    
    echo "🎉 PROCESSAMENTO CONCLUÍDO COM SUCESSO!\n";
    echo "\n";
    echo "📝 Resumo:\n";
    echo "   - Pagamento marcado como 'completed'\n";
    echo "   - Usuário marcado como 'connected'\n";
    echo "   - Sessão WiFi criada\n";
    echo "   - Acesso liberado por 24 horas\n";
    echo "\n";
    echo "⚠️ IMPORTANTE: Libere o MAC address no MikroTik manualmente:\n";
    echo "   MAC: {$user->mac_address}\n";
    echo "   IP: {$user->ip_address}\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
} 