#!/bin/bash

# Script para corrigir SANTANDER_USE_JWS duplicado no .env
# Remove a linha duplicada e mantém apenas SANTANDER_USE_JWS=true

echo "🔧 Corrigindo SANTANDER_USE_JWS duplicado no .env..."

# Backup do .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado"

# Remover TODAS as linhas SANTANDER_USE_JWS
sed -i '/^SANTANDER_USE_JWS=/d' .env
echo "✅ Linhas duplicadas removidas"

# Adicionar apenas UMA linha com valor true após SANTANDER_STATION_CODE
sed -i '/^SANTANDER_STATION_CODE=/a SANTANDER_USE_JWS=true' .env
echo "✅ SANTANDER_USE_JWS=true adicionado"

# Verificar
echo ""
echo "🔍 Verificando .env:"
grep SANTANDER_USE_JWS .env

echo ""
echo "🧹 Limpando cache..."
php artisan config:clear

echo ""
echo "✅ Correção concluída!"
echo ""
echo "🧪 Teste agora:"
echo "   php artisan santander:diagnostico"

