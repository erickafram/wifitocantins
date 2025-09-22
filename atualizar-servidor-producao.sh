#!/bin/bash

# 🔥 SCRIPT PARA ATUALIZAR SERVIDOR DE PRODUÇÃO
# Execute este comando no seu computador local

echo "🚀 ATUALIZANDO SERVIDOR DE PRODUÇÃO..."

# 1. Enviar arquivo atualizado para o servidor
echo "📤 Enviando MikrotikSyncController.php atualizado..."
scp app/Http/Controllers/MikrotikSyncController.php root@159.89.175.123:/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/app/Http/Controllers/

# 2. Fazer backup e aplicar no servidor
ssh root@159.89.175.123 << 'EOF'
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

echo "📁 Fazendo backup..."
cp app/Http/Controllers/MikrotikSyncController.php app/Http/Controllers/MikrotikSyncController.backup.$(date +%Y%m%d_%H%M%S).php

echo "🔄 Aplicando atualizações..."
chmod 644 app/Http/Controllers/MikrotikSyncController.php
chown www-data:www-data app/Http/Controllers/MikrotikSyncController.php

echo "🧹 Limpando cache Laravel..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "🔧 Recarregando PHP-FPM..."
systemctl reload php8.1-fpm

echo "✅ SERVIDOR ATUALIZADO COM SUCESSO!"
echo "🔗 Testando API..."
curl -s "https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users?token=mikrotik-sync-2024" | head -n 5

EOF

echo "🎉 ATUALIZAÇÃO CONCLUÍDA!"
echo "👉 Agora execute os comandos do MikroTik para resolver o problema da Kauany"
