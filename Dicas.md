# 1. Entrar no diretório do projeto
git add .
git commit -m "Implementação de questionários dinâmicos e override de competência"
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
chown -R tocantinstransportewifi:tocantinstransportewifi /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br
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


----------------------------------
# 🎛️ PAINEL DE ADMINISTRAÇÃO REMOTA DO MIKROTIK
----------------------------------

## Acesso ao Painel
URL: https://tocantinstransportewifi.com.br/admin/mikrotik/remote
Requer: Login como administrador (role = 'admin')

## Funcionalidades
- ✅ Visualizar status do Mikrotik em tempo real
- ✅ Liberar MAC address remotamente
- ✅ Bloquear MAC address remotamente
- ✅ Forçar sincronização
- ✅ Ver logs de comandos executados

## Como Funciona
1. Admin cria comando no painel web → Salvo no banco de dados
2. Mikrotik consulta API a cada 15 segundos → Busca comandos pendentes
3. Mikrotik executa comandos → Reporta resultado para API
4. Painel atualiza status em tempo real

## Documentação Completa
Ver: docs/MIKROTIK-REMOTE-ADMIN.md

## Endpoints da API
- GET /api/mikrotik/get-commands?token=mikrotik-sync-2024
- POST /api/mikrotik/command-result

## Atualizar Script do Mikrotik
O script de sincronização que já roda a cada 15 segundos precisa ser atualizado
para buscar e executar comandos do painel remoto.

Ver código completo em: docs/MIKROTIK-REMOTE-ADMIN.md

## Troubleshooting

### Verificar comandos pendentes no banco
```bash
php artisan tinker
\App\Models\MikrotikCommand::pending()->get();
```

### Criar comando manualmente para teste
```bash
php artisan tinker
\App\Models\MikrotikCommand::create([
    'command_type' => 'liberate',
    'mac_address' => 'AA:BB:CC:DD:EE:FF',
    'status' => 'pending'
]);
```

### Ver logs do Laravel
```bash
tail -f storage/logs/laravel.log | grep "🎛️"
```

### Testar endpoint manualmente
```bash
curl "https://tocantinstransportewifi.com.br/api/mikrotik/get-commands?token=mikrotik-sync-2024"
```
