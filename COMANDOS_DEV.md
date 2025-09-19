# 🚀 COMANDOS PARA DESENVOLVIMENTO LOCAL

## ⚡ Iniciar Projeto (Comando Rápido)
```powershell
# Navegar para o diretório
cd C:\wamp64\www\wifitocantins

# Definir alias para PHP (a cada nova sessão)
Set-Alias -Name php -Value "C:\wamp64\bin\php\php8.3.14\php.exe"

# Iniciar servidor Laravel (usar porta alternativa se 8000 estiver ocupada)
php artisan serve --port=8001

# Em outro terminal, iniciar Vite (se necessário)
npm run dev
```

## 🛠️ Comandos Artisan Úteis
```powershell
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Executar migrações
php artisan migrate

# Executar seeders
php artisan db:seed

# Criar nova migração
php artisan make:migration nome_da_migration

# Criar controller
php artisan make:controller NomeController

# Listar rotas
php artisan route:list
```

## 📊 Comandos de Banco de Dados
```powershell
# Reset completo do banco
php artisan migrate:fresh --seed

# Status das migrações
php artisan migrate:status

# Rollback última migração
php artisan migrate:rollback
```

## 🎨 Comandos Frontend
```powershell
# Instalar dependências
npm install

# Desenvolvimento (watch mode)
npm run dev

# Build para produção
npm run build
```

## 🔍 Debug e Logs
```powershell
# Ver logs em tempo real
php artisan pail

# Limpar logs
Remove-Item storage/logs/laravel.log
```

## 🌐 URLs Importantes
- **Aplicação:** http://localhost:8001
- **Portal WiFi:** http://localhost:8001/ (ou http://localhost:8001/portal)
- **Admin:** http://localhost:8001/admin (requer login)

## ⚙️ Configurações Importantes

### Banco de Dados
- **Host:** 127.0.0.1:3306
- **Database:** wifitocantins
- **User:** root
- **Password:** (vazio)

### PIX (Desenvolvimento)
- **Status:** Desabilitado (PIX_ENABLED=false)
- **Gateway:** Woovi (sandbox)
- **Para habilitar:** Altere PIX_ENABLED=true no .env 