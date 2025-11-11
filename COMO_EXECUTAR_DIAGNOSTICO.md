# 🔬 COMO EXECUTAR O DIAGNÓSTICO DETALHADO

Existem **2 formas** de executar o diagnóstico ultra-detalhado que vai mostrar **EXATAMENTE** onde está o erro:

---

## 📋 **OPÇÃO 1: Diagnóstico via SHELL** (Mais completo)

**Copie e cole no servidor:**

```bash
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# Dar permissão de execução
chmod +x diagnostico_detalhado_erro.sh

# Executar
./diagnostico_detalhado_erro.sh
```

---

## 📋 **OPÇÃO 2: Diagnóstico via ARTISAN** (Interface mais limpa)

**Copie e cole no servidor:**

```bash
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# Executar comando Artisan
php artisan santander:diagnostico
```

---

## 📊 **O QUE CADA DIAGNÓSTICO MOSTRA:**

Ambos os diagnósticos vão mostrar **EXATAMENTE**:

### ✅ **PARTE 1: Análise do Certificado**
- Caminho do certificado
- Informações (emissor, validade)
- Quantidade de chaves privadas
- Quantidade de certificados

### ✅ **PARTE 2: Autenticação OAuth**
- URL completa da requisição
- Todos os headers enviados
- Body da requisição
- HTTP Status da resposta
- Se o token foi obtido com sucesso

### ✅ **PARTE 3: Análise do Token JWT**
- **HEADER decodificado** (algoritmo, tipo)
- **PAYLOAD decodificado** (issuer, audience, scopes, etc.)
- **Campos críticos:**
  - `iss` (Issuer) - Quem emitiu o token
  - `aud` (Audience) - Para quem o token é válido
  - `alg` (Algorithm) - Algoritmo de assinatura
  - `scope` - Escopos/permissões do token ⚠️
  - `clientId` - ID do cliente
  - `exp` - Quando o token expira

### ✅ **PARTE 4: Requisição à API PIX**
- Endpoint completo (`/api/v1/cob/{txid}`)
- Todos os headers enviados
- Payload JSON enviado
- **RESPOSTA COMPLETA:**
  - HTTP Status
  - Todos os headers da resposta
  - Body da resposta (formatado)

### ✅ **PARTE 5: Análise do Erro**
- Tipo do erro (errorcode)
- Mensagem completa do erro
- Categorização do erro

### ✅ **PARTE 6: Diagnóstico do Problema**

Se o erro for **AlgorithmMismatch**, mostra as **3 causas possíveis** ordenadas por probabilidade:

**1️⃣ API PIX NÃO HABILITADA na aplicação** ⭐ **MAIS PROVÁVEL**
- Credenciais funcionam para OAuth ✅
- Mas aplicação não tem permissão para API PIX ❌
- **Solução:** Habilitar API PIX no Portal do Desenvolvedor

**2️⃣ FALTA ASSINATURA JWS** ⭐ **PROVÁVEL**
- API PIX pode requerer JWS (JSON Web Signature)
- **Solução:** Confirmar com Santander

**3️⃣ ESCOPO VAZIO no token** ⭐ **CONSEQUÊNCIA DO PROBLEMA 1**
- Mostra o scope atual
- Mostra o scope esperado
- **Solução:** Habilitar API PIX

### ✅ **PARTE 7: Próximos Passos**
- Link para o Portal do Desenvolvedor
- Caminho exato no portal
- Perguntas para fazer ao Santander
- Documentos de apoio

---

## 🎯 **RECOMENDAÇÃO:**

**Use a OPÇÃO 2 (Artisan)** primeiro, pois tem uma interface mais limpa e organizada.

Se precisar de ainda mais detalhes técnicos (verbose do curl), use a OPÇÃO 1 (Shell).

---

## 📸 **O QUE FAZER COM O RESULTADO:**

1. ✅ **Copie TODO o output** do diagnóstico
2. ✅ **Procure pela seção "PARTE 6"** - ela vai dizer a causa mais provável
3. ✅ **Se for "API PIX NÃO HABILITADA":**
   - Acesse o Portal do Desenvolvedor Santander
   - Vá em "Minhas Aplicações" > "STARLINK QR CODE"
   - Verifique se a API PIX está na lista de "APIs Associadas"
   - Tire um print
4. ✅ **Envie o output completo + o print** ao suporte Santander

---

## 🚨 **IMPORTANTE:**

O diagnóstico vai **decodificar o token JWT** e mostrar **EXATAMENTE** qual é o problema:

- Se o `scope` estiver **vazio** → API PIX não habilitada
- Se o `aud` for "Santander Open API" → Credenciais genéricas
- Se o erro for "AlgorithmMismatch" → Falta permissão ou JWS

---

## 📞 **APÓS O DIAGNÓSTICO:**

Com o resultado em mãos, você terá **PROVA TÉCNICA** do problema para mostrar ao Santander:

- ✅ Certificado: OK
- ✅ OAuth: OK
- ✅ Token obtido: OK
- ❌ **Token não autorizado para API PIX**

Isso vai **acelerar MUITO** o suporte deles! 🚀 