<?php
/**
 * 🔥 SCRIPT PARA CORRIGIR PAGAMENTO DA KAUANY
 * Transfere pagamento do usuário duplicado para a Kauany correta
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🔥 INICIANDO CORREÇÃO DO PAGAMENTO DA KAUANY...\n\n";

try {
    DB::beginTransaction();

    // 1. BUSCAR USUÁRIOS
    $kauany = User::find(71); // Kauany Neres (correto)
    $usuarioDuplicado = User::find(72); // Usuário NULL (duplicado)

    if (!$kauany) {
        throw new Exception("❌ Usuário Kauany (ID 71) não encontrado!");
    }

    if (!$usuarioDuplicado) {
        throw new Exception("❌ Usuário duplicado (ID 72) não encontrado!");
    }

    echo "👤 USUÁRIOS ENCONTRADOS:\n";
    echo "   - Kauany (ID 71): {$kauany->name} - {$kauany->email}\n";
    echo "   - Duplicado (ID 72): MAC {$usuarioDuplicado->mac_address}\n\n";

    // 2. BUSCAR PAGAMENTO
    $pagamento = Payment::where('user_id', 72)->where('status', 'completed')->first();
    
    if (!$pagamento) {
        throw new Exception("❌ Pagamento do usuário duplicado não encontrado!");
    }

    echo "💳 PAGAMENTO ENCONTRADO:\n";
    echo "   - ID: {$pagamento->id}\n";
    echo "   - Valor: R$ {$pagamento->amount}\n";
    echo "   - Status: {$pagamento->status}\n";
    echo "   - Transaction ID: {$pagamento->transaction_id}\n\n";

    // 3. TRANSFERIR MAC PARA KAUANY
    echo "🔄 TRANSFERINDO MAC PARA KAUANY...\n";
    $macAddress = $usuarioDuplicado->mac_address;
    
    $kauany->update([
        'mac_address' => $macAddress,
        'ip_address' => $usuarioDuplicado->ip_address,
        'status' => 'connected',
        'connected_at' => now(),
        'expires_at' => now()->addHours(24)
    ]);
    
    echo "✅ MAC {$macAddress} transferido para Kauany\n";

    // 4. TRANSFERIR PAGAMENTO
    echo "🔄 TRANSFERINDO PAGAMENTO...\n";
    $pagamento->update(['user_id' => 71]);
    echo "✅ Pagamento transferido para Kauany (ID 71)\n";

    // 5. TRANSFERIR SESSÃO
    $sessao = Session::where('user_id', 72)->first();
    if ($sessao) {
        echo "🔄 TRANSFERINDO SESSÃO...\n";
        $sessao->update(['user_id' => 71]);
        echo "✅ Sessão transferida para Kauany\n";
    }

    // 6. DELETAR USUÁRIO DUPLICADO
    echo "🗑️ REMOVENDO USUÁRIO DUPLICADO...\n";
    $usuarioDuplicado->delete();
    echo "✅ Usuário duplicado (ID 72) removido\n";

    DB::commit();

    echo "\n🎉 CORREÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "💡 Agora a Kauany deve ter acesso total à internet!\n";
    echo "📱 MAC da Kauany: {$macAddress}\n";
    echo "📧 Email: {$kauany->email}\n";
    echo "⏰ Acesso válido até: " . $kauany->expires_at->format('d/m/Y H:i') . "\n\n";

    // 7. VERIFICAR STATUS FINAL
    echo "📊 STATUS FINAL:\n";
    $kauanyAtualizada = User::find(71);
    echo "   - Status: {$kauanyAtualizada->status}\n";
    echo "   - MAC: {$kauanyAtualizada->mac_address}\n";
    echo "   - IP: {$kauanyAtualizada->ip_address}\n";
    echo "   - Expira em: " . $kauanyAtualizada->expires_at->format('d/m/Y H:i:s') . "\n";

} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
