<?php
/**
 * ROTAS LIMPAS - routes/api.php
 * Adicionar essas rotas ao arquivo routes/api.php
 */

use App\Http\Controllers\PagamentoLimpoController;
use App\Http\Controllers\PortalLimpoController;

// ============================================
// ROTAS DO SISTEMA LIMPO
// ============================================

Route::prefix('v2')->group(function () {
    
    // Portal - Detecção de MAC
    Route::post('/portal/detectar-mac', [PortalLimpoController::class, 'detectarMAC']);
    
    // Pagamento - Sistema Woovi
    Route::prefix('pagamento')->group(function () {
        Route::post('/gerar-qr', [PagamentoLimpoController::class, 'gerarQRCode']);
        Route::post('/webhook', [PagamentoLimpoController::class, 'webhook']);
        Route::get('/status', [PagamentoLimpoController::class, 'verificarStatus']);
    });
    
});

// ============================================
// ROTAS ESPECÍFICAS PARA WOOVI WEBHOOK
// ============================================

// Webhook direto (sem prefixo v2 para compatibilidade)
Route::post('/webhook/woovi', [PagamentoLimpoController::class, 'webhook']);

echo "✅ Rotas Limpas definidas!\n";
echo "📋 Endpoints disponíveis:\n";
echo "\n🔗 PORTAL:\n";
echo "   POST /api/v2/portal/detectar-mac\n";
echo "\n💳 PAGAMENTO:\n";
echo "   POST /api/v2/pagamento/gerar-qr\n";
echo "   GET  /api/v2/pagamento/status?payment_id=X\n";
echo "\n🔔 WEBHOOK:\n";
echo "   POST /api/v2/pagamento/webhook\n";
echo "   POST /api/webhook/woovi (compatibilidade)\n";
echo "\n🚀 Próximo: Frontend simplificado!\n";
