#!/bin/bash

# Script de Atualização - Tocantins Transport WiFi
# Dominio: www.tocantinstransportewifi.com.br
# Usuário: tocantinstransportewifi

echo "🚀 Iniciando atualização do Tocantins Transport WiFi..."

# Definir variáveis
PROJECT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br"
PROJECT_USER="tocantinstransportewifi"
DOMAIN="www.tocantinstransportewifi.com.br"

# 1. Ir para o diretório correto
echo "📁 Navegando para o diretório do projeto..."
cd $PROJECT_PATH

# 2. Fazer backup do .env
echo "💾 Fazendo backup do arquivo .env..."
cp .env .env.backup

# 3. Baixar atualizações
echo "⬇️ Baixando atualizações do GitHub..."
git pull origin main

# 4. Restaurar .env
echo "🔧 Restaurando configurações do .env..."
cp .env.backup .env

# 5. Instalar dependências
echo "📦 Instalando dependências do Composer..."
composer install --optimize-autoloader --no-dev --quiet

# 6. Executar migrações
echo "🗄️ Executando migrações do banco de dados..."
php artisan migrate --force --quiet

# 7. Limpar caches
echo "🧹 Limpando caches..."
php artisan config:clear --quiet
php artisan cache:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet

# 8. Otimizar para produção
echo "⚡ Otimizando para produção..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

# 9. Corrigir permissões
echo "🔐 Corrigindo permissões..."
chown -R $PROJECT_USER:$PROJECT_USER $PROJECT_PATH
chmod -R 775 storage bootstrap/cache

# 10. Recarregar Nginx
echo "🔄 Recarregando Nginx..."
systemctl reload nginx

# 11. Testar se funcionou
echo "🧪 Testando o site..."
HTTP_CODE=$(curl -k -s -o /dev/null -w "%{http_code}" https://$DOMAIN)

if [ $HTTP_CODE -eq 200 ]; then
    echo "✅ Atualização concluída com sucesso!"
    echo "🌐 Site funcionando: https://$DOMAIN"
else
    echo "❌ Erro na atualização. Código HTTP: $HTTP_CODE"
    echo "🔍 Verifique os logs: tail -f storage/logs/laravel.log"
fi

echo "📋 Resumo da atualização:"
echo "   - Projeto: $PROJECT_PATH"
echo "   - Usuário: $PROJECT_USER" 
echo "   - Domínio: https://$DOMAIN"
echo "   - Status: HTTP $HTTP_CODE"

echo "🏁 Script finalizado!"
