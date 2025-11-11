# 🔴 DIAGNÓSTICO COMPLETO - SANTANDER PIX

**Data:** 02/10/2025  
**Status:** ⚠️ BLOQUEADO - Aguardando suporte Santander

---

## ✅ **O QUE FUNCIONA:**

| Item | Status | Detalhes |
|------|--------|----------|
| **Certificado mTLS** | ✅ **OK** | ICP-Brasil A1, chave privada presente |
| **Autenticação OAuth** | ✅ **OK** | Token JWT obtido com sucesso |
| **Endpoint OAuth** | ✅ **OK** | `/auth/oauth/v2/token` |
| **Base URL** | ✅ **OK** | `https://trust-pix.santander.com.br` |
| **Credenciais** | ✅ **OK** | Client ID e Secret válidos |

---

## ❌ **O QUE NÃO FUNCIONA:**

### **Erro Principal:**

```json
{
  "fault": {
    "faultstring": "Algorithm in header did not match any algorithm specified in Configuration: policy(VJWT-Token) algorithm(RS256)",
    "detail": {
      "errorcode": "steps.jwt.AlgorithmMismatch"
    }
  }
}
```

**HTTP Status:** `401 Unauthorized`  
**Endpoint:** `PUT /api/v1/cob/{txid}`

---

## 🔍 **ANÁLISE TÉCNICA:**

### **1. Testes Realizados:**

✅ Testamos **7 variações de endpoints**:
- `/api/v1/cob/{txid}` → **HTTP 401** (endpoint existe, mas rejeitado)
- `/api/v2/cob/{txid}` → **HTTP 500** (endpoint existe, erro interno)
- `/pix/v1/cob/{txid}` → **HTTP 404** (não existe)
- `/pix/v2/cob/{txid}` → **HTTP 404** (não existe)
- Outros → **HTTP 404**

**CONCLUSÃO:** O endpoint correto é **`/api/v1/cob/{txid}`**

---

### **2. Headers HTTP Testados:**

✅ Conforme documentação oficial (atualizada 08/09/2025):

```http
Authorization: Bearer {access_token}
Content-Type: application/json
X-Application-Key: {client_id}
```

**CONCLUSÃO:** Headers estão corretos conforme docs.

---

### **3. Análise do Token JWT:**

**Payload do Token:**
```json
{
  "sub": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
  "aud": "Santander Open API",
  "clientId": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
  "azp": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
  "iss": "Santander JWT Authority",
  "typ": "Bearer",
  "alg": "RS256",
  "scope": ""
}
```

**🚨 PROBLEMAS IDENTIFICADOS:**

1. ❌ `"aud": "Santander Open API"` (genérico, não "Santander PIX API")
2. ❌ `"scope": ""` (vazio, sem scopes PIX específicos)
3. ❌ Token aceito pelo endpoint OAuth
4. ❌ Token **rejeitado** pelo endpoint PIX

**CONCLUSÃO:** Token é válido para autenticação, mas **não autorizado** para API PIX.

---

## 💡 **HIPÓTESE PRINCIPAL:**

### **Problema: "policy(VJWT-Token)"**

O erro menciona uma **política chamada VJWT-Token** que está configurada no API Gateway (Apigee) do Santander.

**Possíveis Causas:**

### **1️⃣ Credenciais não vinculadas à API PIX** ⭐ **MAIS PROVÁVEL**

- ✅ Credenciais funcionam para OAuth
- ❌ Aplicação "STARLINK QR CODE" não tem API PIX habilitada
- ❌ Token não possui scopes PIX (`cob.write`, `cob.read`, etc.)

**AÇÃO:** Verificar no Portal do Desenvolvedor se a API PIX está **associada** à aplicação.

---

### **2️⃣ Requer JWS (JSON Web Signature)** ⭐ **PROVÁVEL**

APIs de **Open Banking** (padrão similar ao PIX) frequentemente requerem:

1. **JWT** (para autenticação) ✅ Você tem
2. **JWS** (para assinatura do payload) ❌ Você NÃO está enviando

**Como funciona JWS:**
- Assinar o **corpo da requisição** com chave privada
- Enviar assinatura no header `x-jws-signature`
- Santander valida com chave pública (do certificado)

**AÇÃO:** Consultar Santander se JWS é obrigatório.

---

### **3️⃣ Algoritmo incompatível** ⭐ **MENOS PROVÁVEL**

- Token usa **RS256** (correto)
- Erro menciona "algorithm(RS256)" (esperado)
- Pode estar esperando **PS256** (RSA-PSS)

**AÇÃO:** Perguntar ao Santander qual algoritmo é aceito.

---

## 📋 **DOCUMENTAÇÃO CONSULTADA:**

### **Portal do Desenvolvedor Santander:**
- URL: `https://developer.santander.com.br`
- Biblioteca: **"Pix - Geração de QRCode"**
- Atualização: **08/09/2025**

### **Informações Confirmadas:**

✅ Base URL Produção: `trust-pix.santander.com.br`  
✅ Base URL Sandbox: `trust-sandbox.api.santander.com.br`  
✅ Base URL OAuth Produção: `trust-open.api.santander.com.br`  
✅ Endpoint OAuth: `/auth/oauth/v2/token`  
✅ Endpoint PIX: `/api/v1/cob/{txid}` (confirmado por teste)  
✅ Headers obrigatórios: `Authorization`, `Content-Type`, `X-Application-Key`  
✅ Método: `PUT` para criar cobrança

---

## 🎯 **PRÓXIMOS PASSOS:**

### **1. VERIFICAR NO PORTAL (URGENTE):**

Acesse: `https://developer.santander.com.br`

1. Faça login
2. Vá em **"Minhas Aplicações"**
3. Selecione **"STARLINK QR CODE"**
4. Clique na aba **"APIs Associadas"**
5. **Verifique:** A **"API Pix - Geração de QRCode"** está **HABILITADA**?

**📸 Tire um print e envie ao suporte Santander**

---

### **2. ENTRAR EM CONTATO COM SANTANDER:**

**ASSUNTO:** API PIX - Erro "AlgorithmMismatch" na policy VJWT-Token

**PERGUNTAS CRÍTICAS:**

#### **A) Sobre Habilitação da API:**
> A aplicação "STARLINK QR CODE" (Client ID: `RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB`) tem a **API PIX habilitada**?

#### **B) Sobre JWS:**
> A API PIX requer **JWS (JSON Web Signature)** além do token JWT OAuth?  
> Se sim, qual header usar? (`x-jws-signature`?)

#### **C) Sobre Algoritmo:**
> Qual algoritmo de assinatura a política VJWT-Token aceita?
> - RS256 (RSA with SHA-256)?
> - PS256 (RSA-PSS with SHA-256)?

#### **D) Sobre Documentação:**
> Onde encontrar a documentação **COMPLETA** da API PIX com:
> - Requisitos de autenticação detalhados
> - Exemplos de código PHP funcionais
> - Postman Collection atualizada

#### **E) Sobre Ambiente:**
> Existe ambiente de **HOMOLOGAÇÃO/SANDBOX** disponível?

---

### **3. TESTES ADICIONAIS (se Santander confirmar JWS):**

Se o Santander confirmar que JWS é necessário, você precisará:

1. Implementar geração de JWS em PHP
2. Assinar o payload da requisição
3. Adicionar header `x-jws-signature`

**Posso ajudar com isso se necessário.**

---

## 📊 **RESUMO EXECUTIVO:**

| Item | Status |
|------|--------|
| **Integração OAuth** | ✅ Concluída |
| **Certificado mTLS** | ✅ Configurado |
| **Endpoint correto** | ✅ Identificado (`/api/v1/cob/{txid}`) |
| **Headers corretos** | ✅ Implementados |
| **API PIX funcionando** | ❌ **BLOQUEADO** |

---

## 🚨 **BLOQUEIO ATUAL:**

**MOTIVO:** Credenciais OAuth funcionam, mas **não estão autorizadas** para a API PIX.

**EVIDÊNCIA:** 
- ✅ Token obtido com sucesso
- ❌ Token rejeitado pela API PIX com erro "AlgorithmMismatch"
- ⚠️ Scope vazio no token (`"scope": ""`)

**PRÓXIMA AÇÃO NECESSÁRIA:**  
✅ Verificar habilitação da API PIX na aplicação  
✅ Entrar em contato com suporte Santander

---

## 📞 **INFORMAÇÕES PARA O SUPORTE:**

**Dados da Integração:**
- **Aplicação:** STARLINK QR CODE
- **Client ID:** RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB
- **Base URL:** https://trust-pix.santander.com.br
- **Endpoint:** PUT /api/v1/cob/{txid}
- **Erro:** HTTP 401 - "Algorithm in header did not match... policy(VJWT-Token) algorithm(RS256)"

**Certificado:**
- ✅ ICP-Brasil A1
- ✅ mTLS funcionando
- ✅ Chave privada presente
- ✅ Validado com sucesso

**Token JWT:**
- ✅ Obtido com sucesso
- ✅ Algoritmo: RS256
- ❌ Scope vazio
- ❌ Audience: "Santander Open API" (genérico)

---

## 📎 **ARQUIVOS DE EVIDÊNCIA:**

1. ✅ `analisar_token_santander.sh` - Análise do JWT
2. ✅ `testar_todos_endpoints_pix.sh` - Teste de endpoints
3. ✅ `PERGUNTAS_CRITICAS_SANTANDER.md` - Perguntas para suporte
4. ✅ Este documento - Diagnóstico completo

---

**Status:** ⏳ **Aguardando ação do Santander** 