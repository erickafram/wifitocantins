# 🔐 Como Resolver Erro de JWS do Santander

## ❌ Erro Recebido
```
Algorithm in header did not match any algorithm specified in Configuration: 
policy(VJWT-Token) algorithm(RS256)
```

Este erro indica que o **Santander está exigindo JWS** (JSON Web Signature) assinado com algoritmo **RS256**.

---

## ✅ Solução Implementada

### **1. Código PHP Atualizado**

Implementamos suporte a **JWS com RS256** no arquivo:
- `app/Services/SantanderPixService.php`

O código agora:
- ✅ Gera JWS (JSON Web Signature) automaticamente
- ✅ Usa algoritmo **RS256** (RSA with SHA-256)
- ✅ Assina o payload com o certificado privado
- ✅ Adiciona header `x-jws-signature` nas requisições

### **2. Como Habilitar JWS**

Adicione no seu `.env`:

```env
# Habilitar JWS (JSON Web Signature) para Santander
SANTANDER_USE_JWS=true
```

⚠️ **Importante:** Somente habilite se o Santander confirmar que JWS é obrigatório!

---

## 📋 Configuração no Postman (Testes Manuais)

### **1. Configurar Certificado**

No Postman, vá em: **Settings** → **Certificates** → **Add Certificate**

```
Hostname: trust-pix.santander.com.br
Port: 443
CRT file: santander.pem
KEY file: santander.pem (o mesmo arquivo)
PFX file: (deixe em branco)
Passphrase: (deixe em branco, se não tiver senha)
```

### **2. Teste 1: Obter Token OAuth**

**Método:** `POST`
**URL:** `https://trust-pix.santander.com.br/auth/oauth/v2/token`

**Headers:**
```
Authorization: Basic UkE0VVAyM0w3dFFMbEFsY3NrOE85UUY5UTZPaWg2TkI6blNrV0lWOFRGSlVHUkJ1cg==
Content-Type: application/x-www-form-urlencoded
```

**Body (x-www-form-urlencoded):**
```
grant_type=client_credentials
scope=cob.write cob.read pix.write pix.read webhook.read webhook.write
```

**Como gerar o Basic Auth:**
```bash
echo -n "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB:nSkWIV8TFJUGRBur" | base64
```

**Resultado esperado:** HTTP 200 com `access_token`

---

### **3. Teste 2: Criar Cobrança PIX (SEM JWS)**

Primeiro teste SEM JWS para verificar se realmente é obrigatório.

**Método:** `PUT`
**URL:** `https://trust-pix.santander.com.br/api/v1/cob/WIFITESTE12345678901234567890`

**Headers:**
```
Authorization: Bearer <access_token_obtido_no_passo_1>
Content-Type: application/json
X-Application-Key: RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB
```

**Body (JSON):**
```json
{
  "calendario": {
    "expiracao": 900
  },
  "valor": {
    "original": "0.05"
  },
  "chave": "pix@tocantinstransportewifi.com.br",
  "solicitacaoPagador": "Teste WiFi Tocantins"
}
```

**Resultados possíveis:**
- ✅ **HTTP 200** → JWS NÃO é obrigatório, tudo funcionando!
- ❌ **HTTP 401** → JWS é obrigatório (vá para Teste 3)

---

### **4. Teste 3: Criar Cobrança PIX (COM JWS)**

Se o teste anterior falhou, você precisa adicionar o header `x-jws-signature`.

#### **Gerar JWS Manualmente (Node.js)**

Crie um arquivo `generate-jws.js`:

```javascript
const fs = require('fs');
const crypto = require('crypto');

// Payload da requisição
const payload = {
  calendario: { expiracao: 900 },
  valor: { original: "0.05" },
  chave: "pix@tocantinstransportewifi.com.br",
  solicitacaoPagador: "Teste WiFi Tocantins"
};

// Header JWS - ⚠️ DEVE SER RS256!
const header = {
  alg: "RS256",
  typ: "JWT"
};

// Ler chave privada do certificado
const privateKeyPem = fs.readFileSync('santander.pem', 'utf8');
const privateKey = crypto.createPrivateKey({
  key: privateKeyPem,
  format: 'pem',
  // passphrase: 'SUA_SENHA_SE_HOUVER'
});

// Codificar em Base64 URL-safe
function base64UrlEncode(data) {
  return Buffer.from(data)
    .toString('base64')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=/g, '');
}

// Gerar JWS
const headerEncoded = base64UrlEncode(JSON.stringify(header));
const payloadEncoded = base64UrlEncode(JSON.stringify(payload));
const message = `${headerEncoded}.${payloadEncoded}`;

// Assinar com RS256 (SHA256)
const signature = crypto.sign('sha256', Buffer.from(message), {
  key: privateKey,
  padding: crypto.constants.RSA_PKCS1_PADDING
});

const signatureEncoded = base64UrlEncode(signature);
const jws = `${message}.${signatureEncoded}`;

console.log('JWS gerado:');
console.log(jws);
```

**Execute:**
```bash
node generate-jws.js
```

**Copie o JWS gerado e adicione no Postman:**

**Headers:**
```
Authorization: Bearer <access_token>
Content-Type: application/json
X-Application-Key: RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB
x-jws-signature: <JWS_GERADO_PELO_SCRIPT>
```

---

## 🧪 Teste Automático via PHP (Recomendado)

Se habilitou `SANTANDER_USE_JWS=true` no `.env`, o código PHP fará tudo automaticamente.

**Teste via API:**
```bash
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/generate-pix \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 0.05,
    "mac_address": "AA:BB:CC:DD:EE:FF"
  }'
```

**Verifique os logs:**
```bash
tail -f storage/logs/laravel.log | grep JWS
```

**Logs esperados:**
```
✅ JWS gerado e adicionado ao header
```

---

## 🔍 Diagnóstico

### **Verificar se JWS está sendo usado:**
```bash
php artisan tinker
```

```php
$service = new \App\Services\SantanderPixService();
$result = $service->createPixPayment(0.05, 'Teste JWS');
```

### **Logs importantes:**
```bash
tail -f storage/logs/laravel.log | grep -E "(JWS|Algorithm|RS256|PS256)"
```

---

## ⚠️ Erros Comuns

### **1. "Algorithm in header did not match"**
✅ **Solução:** Certificar que está usando `RS256` (não `PS256`)
- Verifique no código PHP: `'alg' => 'RS256'`
- Verifique no script Node.js: `alg: "RS256"`

### **2. "Invalid signature"**
✅ **Solução:** Verifique se a chave privada está correta
- Confirme que o certificado `santander.pem` contém a chave privada
- Tente extrair a chave privada: `openssl pkey -in santander.pem -text`

### **3. "Certificate not found"**
✅ **Solução:** Verifique o caminho do certificado
- Confirme: `storage/app/certificado/santander.pem`
- Permissões: `chmod 644 storage/app/certificado/santander.pem`

---

## 📞 Contato com Santander

Se o erro persistir, entre em contato com o suporte Santander:

**Assunto:** API PIX - Erro AlgorithmMismatch na policy VJWT-Token

**Perguntas:**
1. ✅ A API PIX requer JWS (JSON Web Signature)?
2. ✅ Qual algoritmo devo usar? RS256 ou PS256?
3. ✅ O header `x-jws-signature` é obrigatório?
4. ✅ A aplicação "STARLINK QR CODE" tem a API PIX habilitada?

**Anexos:**
- `DIAGNOSTICO_FINAL_SANTANDER_PIX.md`
- `PERGUNTAS_CRITICAS_SANTANDER.md`

---

## ✅ Checklist Final

- [ ] Certificado configurado no Postman (`trust-pix.santander.com.br:443`)
- [ ] Token OAuth obtido com sucesso (HTTP 200)
- [ ] Testado criar cobrança SEM JWS (verificar se é obrigatório)
- [ ] Se necessário, habilitado `SANTANDER_USE_JWS=true` no `.env`
- [ ] Logs confirmando JWS sendo gerado com RS256
- [ ] Cobrança PIX criada com sucesso (HTTP 200)

---

**Última atualização:** 2025-10-06
**Versão:** 1.0

