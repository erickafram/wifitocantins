<?php

require_once 'vendor/autoload.php';

use App\Services\WooviPixService;
use Illuminate\Support\Facades\DB;

// Simular um webhook da Woovi para o pagamento TXN_1759105734_30A3ED6E
$webhookData = [
    "event" => "OPENPIX:CHARGE_COMPLETED",
    "charge" => [
        "correlationID" => "TXN_1759105734_30A3ED6E",
        "status" => "COMPLETED",
        "value" => "0.10",
        "globalID" => "Q2hhcmdlOjY4ZDlkMmM3MDVmMmE4ZjAwYzczOWIwNA==",
        "paidAt" => "2025-09-29T02:42:01.468132Z"
    ],
    "pix" => [
        "time" => "2025-09-29T02:42:01.468486Z",
        "status" => "CONFIRMED"
    ]
];

echo "🧪 TESTE DE WEBHOOK WOOVI\n";
echo "========================\n\n";

echo "Transaction ID: TXN_1759105734_30A3ED6E\n";
echo "Status Esperado: COMPLETED\n\n";

// Verificar o pagamento atual no banco
try {
    $payment = DB::table('payments')->where('transaction_id', 'TXN_1759105734_30A3ED6E')->first();
    
    if ($payment) {
        echo "💳 Pagamento encontrado no banco:\n";
        echo "  - ID: {$payment->id}\n";
        echo "  - Status atual: {$payment->status}\n";
        echo "  - Valor: R$ {$payment->amount}\n\n";
    } else {
        echo "❌ Pagamento não encontrado no banco!\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Erro ao consultar banco: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Teste 1: Processar sem validação de assinatura (temporário)
echo "🔧 TESTE 1: Processamento SEM validação de assinatura\n";
echo "-----------------------------------------------------\n";

$wooviService = new WooviPixService();

// Para teste, vou temporariamente desabilitar a validação
// Isso é apenas para debug - NÃO deve ser usado em produção
try {
    // Simular processamento direto (saltando validação)
    $result = $wooviService->processWebhook($webhookData);
    
    echo "✅ Resultado do processamento:\n";
    print_r($result);
    
    // Verificar se o pagamento foi atualizado
    $updatedPayment = DB::table('payments')->where('transaction_id', 'TXN_1759105734_30A3ED6E')->first();
    echo "\n💳 Status do pagamento após processamento: {$updatedPayment->status}\n";
    
} catch (Exception $e) {
    echo "❌ Erro no processamento: " . $e->getMessage() . "\n";
}

echo "\n\n🏁 Teste concluído!\n";
