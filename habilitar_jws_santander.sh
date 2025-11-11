#!/bin/bash

# Script para habilitar JWS no Santander PIX
# Autor: Sistema WiFi Tocantins
# Data: 06/10/2025

echo "🔐 Habilitando JWS para Santander PIX..."
echo ""

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se .env existe
if [ ! -f .env ]; then
    echo -e "${RED}❌ Arquivo .env não encontrado!${NC}"
    exit 1
fi

# Backup do .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✅ Backup do .env criado${NC}"

# Atualizar ou adicionar SANTANDER_USE_JWS=true
if grep -q "SANTANDER_USE_JWS=" .env; then
    # Atualizar valor existente
    sed -i 's/^SANTANDER_USE_JWS=.*/SANTANDER_USE_JWS=true/' .env
    echo -e "${GREEN}✅ SANTANDER_USE_JWS atualizado para true${NC}"
else
    # Adicionar nova linha
    echo "" >> .env
    echo "# JWS obrigatório para Santander PIX" >> .env
    echo "SANTANDER_USE_JWS=true" >> .env
    echo -e "${GREEN}✅ SANTANDER_USE_JWS adicionado ao .env${NC}"
fi

echo ""
echo "📦 Instalando dependências..."
composer install --no-dev --optimize-autoloader --quiet

echo ""
echo "🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "🔍 Verificando certificado..."
php artisan santander:test

echo ""
echo -e "${GREEN}✨ Configuração concluída!${NC}"
echo ""
echo "📋 Próximos passos:"
echo "   1. Verifique se o certificado tem chave privada (veja acima)"
echo "   2. Teste a geração de QR Code PIX no portal"
echo "   3. Monitore os logs: tail -f storage/logs/laravel.log | grep JWS"
echo ""

