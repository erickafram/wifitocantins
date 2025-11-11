#!/bin/bash
# Script para aplicar configuração PagBank automaticamente

echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║   APLICANDO CONFIGURAÇÃO PAGBANK NO SERVIDOR        ║"
echo "╚══════════════════════════════════════════════════════╝"
echo ""

# Fazer backup do .env
echo "📦 Criando backup do .env..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado com sucesso!"
echo ""

# Atualizar PIX_GATEWAY
echo "🔧 Configurando PIX_GATEWAY=pagbank..."
if grep -q "^PIX_GATEWAY=" .env; then
    sed -i 's/^PIX_GATEWAY=.*/PIX_GATEWAY=pagbank/' .env
    echo "✅ PIX_GATEWAY atualizado para: pagbank"
else
    echo "PIX_GATEWAY=pagbank" >> .env
    echo "✅ PIX_GATEWAY adicionado: pagbank"
fi
echo ""

# Atualizar PIX_ENVIRONMENT
echo "🔧 Configurando PIX_ENVIRONMENT=sandbox..."
if grep -q "^PIX_ENVIRONMENT=" .env; then
    sed -i 's/^PIX_ENVIRONMENT=.*/PIX_ENVIRONMENT=sandbox/' .env
    echo "✅ PIX_ENVIRONMENT atualizado para: sandbox"
else
    echo "PIX_ENVIRONMENT=sandbox" >> .env
    echo "✅ PIX_ENVIRONMENT adicionado: sandbox"
fi
echo ""

# Limpar cache do Laravel
echo "🧹 Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
echo "✅ Cache limpo com sucesso!"
echo ""

echo "═══════════════════════════════════════════════════════"
echo "              ✅ CONFIGURAÇÃO CONCLUÍDA!               "
echo "═══════════════════════════════════════════════════════"
echo ""
echo "📋 Configuração aplicada:"
echo "   PIX_GATEWAY=pagbank"
echo "   PIX_ENVIRONMENT=sandbox"
echo ""
echo "🎯 Próximo passo:"
echo "   Teste no portal WiFi gerando um PIX!"
echo ""
echo "📝 Verificar configuração atual:"
echo "   grep 'PIX_GATEWAY\|PIX_ENVIRONMENT' .env"
echo ""

