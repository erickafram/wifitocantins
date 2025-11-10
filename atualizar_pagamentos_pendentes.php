<?php
/**
 * Atualizar valor dos pagamentos pendentes para R$ 1,00
 * Execute: php atualizar_pagamentos_pendentes.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;

echo "🔧 Atualizando Pagamentos Pendentes\n";
echo str_repeat("=", 70) . "\n\n";

// Buscar pagamentos pendentes com valor < 1.00
$payments = Payment::where('status', 'pending')
    ->where('amount', '<', 1.00)
    ->get();

if ($payments->isEmpty()) {
    echo "✅ Nenhum pagamento pendente com valor < R$ 1,00\n";
    exit(0);
}

echo "📋 Encontrados {$payments->count()} pagamentos pendentes:\n\n";

foreach ($payments as $payment) {
    echo "   ID: {$payment->id} | R$ {$payment->amount} | {$payment->created_at}\n";
}

echo "\n⚠️ Deseja atualizar todos para R$ 1,00? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirm = trim(strtolower($line));
fclose($handle);

if ($confirm !== 's' && $confirm !== 'sim') {
    echo "\n❌ Operação cancelada.\n";
    exit(0);
}

echo "\n🔄 Atualizando pagamentos...\n\n";

$updated = 0;
foreach ($payments as $payment) {
    $oldAmount = $payment->amount;
    $payment->amount = 1.00;
    $payment->save();
    
    echo "   ✅ ID {$payment->id}: R$ {$oldAmount} → R$ 1.00\n";
    $updated++;
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ {$updated} pagamentos atualizados com sucesso!\n";
echo "\nAgora você pode regenerar os QR Codes sem erro.\n";
echo str_repeat("=", 70) . "\n";
