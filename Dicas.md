# 1. Entrar no diretório do projeto
git add .
git commit -m "implementar projeto"
git push -u origin main

----------------------------------
# DEPLOY COMPLETO PARA PRODUÇÃO
----------------------------------

# 1. Ir para o diretório correto
cd /home/tocantinstransportewifi/htdocs/tocantinstransportewifi.com.br

# 2. Fazer backup do .env
cp .env .env.backup

# 3. Baixar atualizações
git pull origin main --no-rebase

# 4. Restaurar .env (caso tenha sido sobrescrito)
cp .env.backup .env

# 5. Instalar/Atualizar dependências
composer install --optimize-autoloader --no-dev

# 6. Executar migrações (se houver novas)

php artisan migrate --force

# 7. Rodar seeder das configurações
php artisan db:seed --class=SystemSettingsSeeder

# 8. Limpar TODOS os caches
php artisan config:clear  
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 9. Corrigir permissões
chown -R tocantinstransportewifi:tocantinstransportewifi /home/tocantinstransportewifi/htdocs/tocantinstransportewifi.com.br
chmod -R 775 storage bootstrap/cache

# 10. Otimizar para produção (APÓS limpar cache)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Recarregar Nginx
sudo systemctl reload nginx

# 12. Testar se funcionou
curl -k https://www.tocantinstransportewifi.com.br

# 13. Verificar configurações no banco
php artisan tinker
# Digite: \App\Models\SystemSetting::all();
# Pressione Ctrl+D para sair