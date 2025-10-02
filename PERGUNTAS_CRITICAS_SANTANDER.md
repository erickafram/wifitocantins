# 🔴 PERGUNTAS CRÍTICAS PARA O SANTANDER PIX

## ⚠️ SITUAÇÃO ATUAL

- ✅ **Autenticação OAuth funciona** (endpoint `/auth/oauth/v2/token`)
- ✅ **Token JWT obtido com sucesso**
- ❌ **Requisições à API PIX falham** com erro:
  ```
  "Algorithm in header did not match any algorithm specified in Configuration: policy(VJWT-Token) algorithm(RS256)"
  ```

## 📋 PERGUNTAS PARA O SUPORTE SANTANDER

### 1️⃣ SOBRE A POLÍTICA VJWT-Token

**PERGUNTA:**
> A API PIX do Santander requer **JWS (JSON Web Signature)** além do token JWT de autenticação?

**CONTEXTO:**
- Atualmente estamos usando apenas o token JWT no header `Authorization: Bearer {token}`
- O erro menciona uma "policy(VJWT-Token)" que não está documentada publicamente

---

### 2️⃣ SOBRE O ALGORITMO DE ASSINATURA

**PERGUNTA:**
> Qual algoritmo de assinatura a API PIX espera?
> - RS256 (RSA com SHA-256)?
> - PS256 (RSA-PSS com SHA-256)?
> - Outro?

**CONTEXTO:**
- O token OAuth está usando RS256
- O erro sugere que pode haver incompatibilidade de algoritmo

---

### 3️⃣ SOBRE HEADERS OBRIGATÓRIOS

**PERGUNTA:**
> Além do `Authorization: Bearer {token}`, quais headers HTTP são obrigatórios para requisições à API PIX?

**ESTAMOS USANDO:**
```
Authorization: Bearer {token}
Content-Type: application/json
X-Application-Key: {client_id}
client_id: {client_id}
```

**PERGUNTA:** Falta algum header?

---

### 4️⃣ SOBRE CREDENCIAIS

**PERGUNTA:**
> As credenciais `Client ID` e `Client Secret` fornecidas são **ESPECÍFICAS** da API PIX ou são do **Portal do Desenvolvedor genérico**?

**NOSSO CLIENT_ID:** `RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB`

**CONTEXTO:**
- O token obtido tem `aud: "Santander Open API"` (genérico)
- O token tem `scope: ""` (vazio, sem scopes PIX)
- Isso sugere que as credenciais podem ser de um produto diferente

---

### 5️⃣ SOBRE DOCUMENTAÇÃO

**PERGUNTA:**
> Onde podemos encontrar a documentação **COMPLETA** da API PIX do Santander, incluindo:
> 1. Requisitos de autenticação (OAuth + JWS?)
> 2. Exemplos de código (PHP, Node, Python, Java)?
> 3. Postman Collection atualizada?
> 4. Explicação sobre a política VJWT-Token?

---

### 6️⃣ SOBRE O ENDPOINT DE COBRANÇA

**PERGUNTA:**
> O endpoint `/api/v1/cob/{txid}` (PUT) está correto para criação de cobranças PIX dinâmicas?

**PAYLOAD QUE ESTAMOS USANDO:**
```json
{
  "calendario": {"expiracao": 900},
  "valor": {"original": "0.01"},
  "chave": "pix@example.com",
  "solicitacaoPagador": "Teste"
}
```

---

### 7️⃣ SOBRE O AMBIENTE

**PERGUNTA:**
> Estamos usando o ambiente de **PRODUÇÃO**:
> - Base URL: `https://trust-pix.santander.com.br`
> 
> Existe um ambiente de **HOMOLOGAÇÃO/SANDBOX** disponível?

---

## 🎯 RESUMO DO QUE PRECISAMOS

1. **Confirmação**: As credenciais atuais são da API PIX (não de outro produto)
2. **Algoritmo**: Qual algoritmo de assinatura usar (RS256, PS256, etc.)
3. **JWS**: Se é necessário assinar o payload das requisições (além do OAuth)
4. **Headers**: Lista completa de headers obrigatórios
5. **Documentação**: Link para docs técnicos completos da API PIX
6. **Exemplo**: Código funcional de exemplo (preferencialmente PHP)

---

## 📞 INFORMAÇÕES DE CONTATO

**Dados da Integração:**
- Client ID: `RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB`
- Base URL: `https://trust-pix.santander.com.br`
- Certificado: ICP-Brasil A1 (válido, mTLS funcionando)

---

## ⏰ URGÊNCIA

🔴 **ALTA PRIORIDADE**

Este bloqueio está impedindo a entrada em produção do sistema de pagamentos PIX.

---

## 📎 ANEXAR AO CHAMADO

1. **Token JWT decodificado** (payload do `analisar_token_santander.sh`)
2. **Logs de erro** da aplicação
3. **Resultado** do script `analisar_requisicao_pix.sh` 