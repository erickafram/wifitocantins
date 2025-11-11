<?php
/**
 * Script para limpar dados de teste antes do teste completo
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use Illuminate\Support\Facades\Log;

try {
    echo "🧹 LIMPANDO DADOS DE TESTE...\n";
    
    // 1. LIMPAR USUÁRIOS DE TESTE
    $testUsers = User::where('email', 'like', '%test%')
                    ->orWhere('name', 'like', '%test%')
                    ->orWhere('status', 'connected')
                    ->get();
    
    foreach ($testUsers as $user) {
        echo "🗑️ Removendo usuário: {$user->name} ({$user->email})\n";
        
        // Remover pagamentos relacionados
        Payment::where('user_id', $user->id)->delete();
        
        // Remover sessões relacionadas
        Session::where('user_id', $user->id)->delete();
        
        // Remover usuário
        $user->delete();
    }
    
    // 2. LIMPAR PAGAMENTOS ÓRFÃOS
    $orphanPayments = Payment::whereDoesntHave('user')->get();
    foreach ($orphanPayments as $payment) {
        echo "🗑️ Removendo pagamento órfão: {$payment->id}\n";
        $payment->delete();
    }
    
    // 3. LIMPAR SESSÕES ÓRFÃS
    $orphanSessions = Session::whereDoesntHave('user')->get();
    foreach ($orphanSessions as $session) {
        echo "🗑️ Removendo sessão órfã: {$session->id}\n";
        $session->delete();
    }
    
    echo "\n✅ LIMPEZA CONCLUÍDA!\n";
    echo "📊 ESTADO ATUAL:\n";
    echo "👥 Usuários restantes: " . User::count() . "\n";
    echo "💳 Pagamentos restantes: " . Payment::count() . "\n";
    echo "🔗 Sessões restantes: " . Session::count() . "\n";
    
    Log::info('🧹 Limpeza de teste realizada', [
        'users_remaining' => User::count(),
        'payments_remaining' => Payment::count(),
        'sessions_remaining' => Session::count()
    ]);
    
} catch (Exception $e) {
    echo "❌ ERRO na limpeza: " . $e->getMessage() . "\n";
    Log::error('Erro na limpeza de teste', ['error' => $e->getMessage()]);
}
