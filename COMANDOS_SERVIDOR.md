# 🚀 Comandos para Executar no Servidor de Produção

## ✅ PASSO 1: Fazer Pull das Alterações

Execute no servidor (como root ou com sudo):

```bash
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# Fazer backup antes (segurança)
git stash

# Puxar as alterações
git pull origin main

# Limpar cache do Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Recarregar PHP-FPM (CloudPanel)
systemctl reload php8.4-fpm
```

---

## ✅ PASSO 2: Adicionar Variável no .env

Adicione esta linha no arquivo `.env`:

```bash
nano .env
```

**Adicione no final da seção Santander:**

```env
# Habilitar JWS (JSON Web Signature) - Comece com false
SANTANDER_USE_JWS=false
```

**Salve e saia:** `Ctrl+X`, depois `Y`, depois `Enter`

---

## ✅ PASSO 3: Limpar Cache Novamente

```bash
php artisan config:clear
php artisan cache:clear
```

---

## ✅ PASSO 4: Testar a API

```bash
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/generate-pix \
  -H "Content-Type: application/json" \
  -d '{"amount": 0.05, "mac_address": "AA:BB:CC:DD:EE:FF"}'
```

**Resultado esperado:**
- ✅ JSON com sucesso e QR Code gerado
- ❌ Se der erro 401 "Algorithm in header did not match", significa que JWS é obrigatório

---

## ✅ PASSO 5: Monitorar Logs

```bash
tail -f storage/logs/laravel.log | grep -E "(JWS|Santander|PIX)"
```

**Logs esperados:**
```
✅ QR Code Santander gerado
📲 Criando cobrança PIX Santander
```

---

## ⚠️ SE JWS FOR OBRIGATÓRIO:

### Opção 1: Habilitar JWS Automaticamente

```bash
# Editar .env
nano .env

# Alterar para:
SANTANDER_USE_JWS=true

# Salvar e limpar cache
php artisan config:clear

# Testar novamente
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/generate-pix \
  -H "Content-Type: application/json" \
  -d '{"amount": 0.05, "mac_address": "AA:BB:CC:DD:EE:FF"}'
```

**Logs esperados com JWS:**
```
✅ JWS gerado e adicionado ao header
   jws_length: 500+
   algorithm: RS256
```

---

## 🔍 Verificar Certificado

Confirme que o certificado está no caminho correto:

```bash
ls -lh /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificado/santander.pem
```

**Saída esperada:**
```
-rw-r--r-- 1 clp clp 3.5K Oct  6 12:00 santander.pem
```

Se o arquivo não existir:

```bash
# Criar diretório
mkdir -p storage/app/certificado

# Mover certificado (ajuste o caminho de origem)
mv /caminho/atual/santander.pem storage/app/certificado/

# Ajustar permissões
chmod 644 storage/app/certificado/santander.pem
chown clp:clp storage/app/certificado/santander.pem
```

---

## 📊 Diagnóstico Completo

Se continuar com problemas, execute:

```bash
# Ver últimos erros
tail -100 storage/logs/laravel.log

# Testar sintaxe PHP
php -l app/Services/SantanderPixService.php
php -l app/Http/Controllers/PaymentController.php

# Ver configurações carregadas
php artisan tinker
>>> config('wifi.payment_gateways.pix.use_jws')
>>> config('wifi.payment_gateways.pix.client_id')
>>> config('wifi.payment_gateways.pix.certificate_path')
>>> exit
```

---

## ✅ Checklist de Verificação

- [ ] ✅ Git pull executado com sucesso
- [ ] ✅ `SANTANDER_USE_JWS=false` adicionado ao .env
- [ ] ✅ Cache do Laravel limpo
- [ ] ✅ PHP-FPM recarregado
- [ ] ✅ Certificado no caminho correto: `storage/app/certificado/santander.pem`
- [ ] ✅ Teste de API retornou sucesso ou erro específico
- [ ] ✅ Logs monitorados e analisados

---

## 🆘 Se Persistir o Erro

**Copie e cole estas informações:**

```bash
# Versão PHP
php -v

# Configurações Santander
php artisan tinker << 'EOF'
echo "Client ID: " . config('wifi.payment_gateways.pix.client_id') . "\n";
echo "Use JWS: " . (config('wifi.payment_gateways.pix.use_jws') ? 'true' : 'false') . "\n";
echo "Cert Path: " . storage_path('app/' . config('wifi.payment_gateways.pix.certificate_path')) . "\n";
echo "Cert Exists: " . (file_exists(storage_path('app/' . config('wifi.payment_gateways.pix.certificate_path'))) ? 'YES' : 'NO') . "\n";
exit
EOF

# Último erro
tail -20 storage/logs/laravel.log
```

---

**Última atualização:** 2025-10-06

