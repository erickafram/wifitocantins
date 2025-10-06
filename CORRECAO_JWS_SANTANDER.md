# 🔧 Correção: Habilitar JWS para Santander PIX

## 📋 Problema Identificado

O Santander PIX **exige** JWS (JSON Web Signature) com algoritmo RS256 em todas as requisições autenticadas. O erro que você estava recebendo:

```
Algorithm in header did not match any algorithm specified in Configuration: 
policy(VJWT-Token) algorithm(RS256)
```

Indica que o header `x-jws-signature` não estava sendo enviado ou estava com formato incorreto.

---

## ✅ Correções Implementadas

### 1. **Biblioteca firebase/php-jwt instalada**
   - Adiciona suporte completo para geração de JWS com RS256

### 2. **Melhorias no método `generateJWS()`**
   - Adiciona campos obrigatórios JWT: `iat`, `exp`, `iss`
   - Inclui headers corretos: `alg: RS256`, `typ: JWT`
   - Validação da chave privada

### 3. **Validação automática do certificado**
   - Verifica se o certificado existe
   - Detecta se contém chave privada
   - Logs informativos para debug

### 4. **JWS habilitado por padrão**
   - `config/wifi.php` agora habilita JWS automaticamente
   - Compatível com `.env` configurado como `true` ou `false`

---

## 🚀 Passos para Aplicar no Servidor

### **Passo 1: Conectar ao servidor**
```bash
ssh root@206.189.217.189
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br
```

### **Passo 2: Fazer pull das alterações**
```bash
git pull origin main
```

### **Passo 3: Instalar dependências**
```bash
composer install --no-dev --optimize-autoloader
```

### **Passo 4: Habilitar JWS (método 1 - automático)**
```bash
php artisan santander:enable-jws
```

**OU método 2 - manual:**
```bash
nano .env
# Alterar ou adicionar:
SANTANDER_USE_JWS=true
# Salvar: Ctrl+O, Enter, Ctrl+X
```

### **Passo 5: Limpar cache**
```bash
php artisan config:clear
php artisan cache:clear
```

### **Passo 6: Verificar certificado**
```bash
php artisan santander:test
```

Verifique nos logs se aparece:
- ✅ `has_private_key: true`
- ✅ `has_certificate: true`

---

## 🔍 Verificação do Certificado

Seu certificado **DEVE** conter a chave privada para o JWS funcionar. Execute:

```bash
cat storage/app/certificado/santander.pem | grep -E "BEGIN (PRIVATE|RSA PRIVATE|ENCRYPTED PRIVATE)"
```

**Resultado esperado:**
```
-----BEGIN PRIVATE KEY-----
```
ou
```
-----BEGIN RSA PRIVATE KEY-----
```

Se NÃO aparecer nada, o certificado está incompleto e você precisa:
1. Baixar novamente do portal Santander
2. Garantir que exportou com chave privada incluída
3. Converter se necessário: `openssl pkcs12 -in cert.pfx -out santander.pem -nodes`

---

## 🧪 Testar Integração

### Teste 1: Verificar configuração
```bash
php artisan tinker
```
```php
config('wifi.payment_gateways.pix.use_jws')
// Deve retornar: true
exit
```

### Teste 2: Criar cobrança PIX de teste
Acesse o portal e tente gerar um QR Code PIX.

### Teste 3: Monitorar logs
```bash
tail -f storage/logs/laravel.log | grep -E "(JWS|Santander|PIX)"
```

**Logs esperados:**
```
✅ JWS gerado com sucesso
✅ Token OAuth 2.0 obtido com sucesso
✅ Cobrança PIX criada com sucesso
```

---

## 📊 Estrutura do JWS

O JWS gerado agora inclui:

**Header:**
```json
{
  "alg": "RS256",
  "typ": "JWT"
}
```

**Payload:**
```json
{
  "iat": 1696612345,           // Timestamp de emissão
  "exp": 1696612645,           // Expira em 5 minutos
  "iss": "SEU_CLIENT_ID",      // Emissor (Client ID Santander)
  // ... + dados da requisição
}
```

**Assinatura:** RS256 com chave privada do certificado

---

## ⚠️ Troubleshooting

### Erro: "Certificado sem chave privada"
**Solução:** Baixe novamente o certificado do portal Santander incluindo a chave privada.

### Erro: "Erro ao carregar chave privada para JWS"
**Solução:** 
1. Verifique se `SANTANDER_CERTIFICATE_PASSWORD` está correto no `.env`
2. Teste manualmente:
   ```bash
   openssl pkey -in storage/app/certificado/santander.pem -text -noout
   ```

### Erro: "Algorithm in header did not match" (ainda persiste)
**Solução:**
1. Confirme que `SANTANDER_USE_JWS=true` está no `.env`
2. Limpe cache: `php artisan config:clear`
3. Verifique logs: `tail -f storage/logs/laravel.log`
4. Procure por: `✅ JWS gerado com sucesso`

### Erro 401 após habilitar JWS
**Possíveis causas:**
- Certificado expirado
- Chave privada não corresponde ao certificado público
- Client ID/Secret incorretos

---

## 📞 Suporte

Se o erro persistir após seguir todos os passos:

1. **Verifique os logs completos:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **Capture o erro exato:**
   ```bash
   php artisan santander:test 2>&1 | tee santander-debug.log
   ```

3. **Verifique as credenciais no portal Santander:**
   - Client ID está correto?
   - Client Secret está correto?
   - Certificado está válido e ativo?
   - Chave PIX está cadastrada?

---

## ✨ Resumo

### Antes:
- ❌ JWS desabilitado (`SANTANDER_USE_JWS=false`)
- ❌ Header `x-jws-signature` não enviado
- ❌ Santander rejeitava requisições com erro 401

### Depois:
- ✅ JWS habilitado por padrão
- ✅ Header `x-jws-signature` com RS256
- ✅ Validação automática do certificado
- ✅ Logs detalhados para debug
- ✅ Compatível com especificação Santander PIX

---

**Última atualização:** 06/10/2025  
**Versão:** 1.0

