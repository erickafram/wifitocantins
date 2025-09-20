# 🚀 APLICAR CORREÇÕES EM PRODUÇÃO

## 📋 ARQUIVOS ALTERADOS PARA UPLOAD:

### 1. **app/Http/Controllers/PortalController.php**
- ✅ Melhorou detecção de MAC via URL (?mac=)
- ✅ Adicionou logs detalhados
- ✅ Prioridade: URL → Headers → ARP → Mock

### 2. **mikrotik-mac-real-definitivo.rsc** (NOVO)
- ✅ Script para enviar MAC real via URL
- ✅ Redirecionamento automático com parâmetros

## 🔧 COMANDOS PARA PRODUÇÃO:

### NO SERVIDOR (DigitalOcean):
```bash
# 1. Fazer backup atual
cp app/Http/Controllers/PortalController.php app/Http/Controllers/PortalController.php.backup

# 2. Upload do arquivo corrigido
# (fazer upload manual via FTP/SFTP)

# 3. Limpar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Testar endpoint
curl "https://www.tocantinstransportewifi.com.br/api/detect-device?mac=5C:CD:5B:2F:B9:3F"

# 5. Verificar logs
tail -f storage/logs/laravel.log | grep -i "MAC\|DETECÇÃO"
```

### NO MIKROTIK:
```bash
# 1. Fazer backup da configuração atual
/export file=backup-antes-mac-real

# 2. Aplicar script de MAC real
/import mikrotik-mac-real-definitivo.rsc

# 3. Verificar execução
/log print where topics~"info"

# 4. Testar redirecionamento
# (conectar dispositivo e verificar URL gerada)
```

## 🧪 TESTES APÓS APLICAÇÃO:

### 1. **Teste Manual Direto:**
```
https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F
```
**Resultado esperado:** Sistema deve reconhecer MAC real e não gerar mock

### 2. **Teste Real no Ônibus:**
- Conectar no WiFi do ônibus
- Verificar se URL contém `?mac=5C:CD:5B:2F:B9:3F`
- Fazer pagamento PIX
- Verificar acesso total liberado

### 3. **Verificar Logs:**
```bash
tail -f storage/logs/laravel.log | grep "🔍\|✅\|⚠️"
```

## 📊 RESULTADOS ESPERADOS:

✅ **MAC Real Capturado:** `5C:CD:5B:2F:B9:3F`  
✅ **Sem MACs Mock:** Não mais `02:XX:XX:XX:XX:XX`  
✅ **Pagamentos Rápidos:** Webhooks funcionando  
✅ **Acesso Total:** Após pagamento confirmado  

## 🚨 ROLLBACK (se necessário):
```bash
# Restaurar backup
cp app/Http/Controllers/PortalController.php.backup app/Http/Controllers/PortalController.php
php artisan config:cache
```
