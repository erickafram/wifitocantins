# 🧪 Como Testar e Mostrar que o SCOPE está Vazio

## 🎯 Objetivo

Executar um teste simples que mostre para o pessoal do Santander que o token OAuth está sendo gerado **SEM SCOPE** (sem permissões PIX).

---

## 📋 Opção 1: Script PHP (RECOMENDADO - Mais Simples)

### Execute:

```bash
php testar_scope_santander_simples.php
```

### O que você verá:

```
==========================================
🔍 TESTE DE SCOPE SANTANDER PIX
==========================================

📋 Configurações:
   Client ID: RA4UP23L7t...h6NB
   Ambiente: production
   Base URL: https://trust-pix.santander.com.br

🔐 Fazendo requisição OAuth...

📥 Status HTTP: 200

✅ Token obtido com sucesso!

🔑 Token JWT (primeiros 80 caracteres):
   eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...

🔍 Decodificando JWT...

==========================================
📋 PAYLOAD DO TOKEN JWT:
==========================================
{
    "sub": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
    "aud": "Santander Open API",
    "clientId": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
    "azp": "RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB",
    "iss": "Santander JWT Authority",
    "typ": "Bearer",
    "alg": "RS256",
    "scope": "",    ⬅️⬅️⬅️ VAZIO!
    "exp": 1234567890
}

==========================================
🎯 ANÁLISE DO SCOPE:
==========================================
❌ PROBLEMA CONFIRMADO: SCOPE VAZIO!

   Valor atual: ""
   Valor esperado: "cob.write cob.read pix.write pix.read"

⚠️  ESTE É O PROBLEMA!
   O token não possui permissões para acessar a API PIX.
   A aplicação 'STARLINK QR CODE' precisa ter a
   'API Pix - Geração de QRCode' habilitada no portal.

==========================================
📸 COPIE ESTA SAÍDA E ENVIE AO SANTANDER
==========================================
```

---

## 📋 Opção 2: Script Bash (Alternativo)

### Execute:

```bash
chmod +x testar_scope_santander.sh
./testar_scope_santander.sh
```

**Nota:** Requer `jq` instalado. Se não tiver:
```bash
# Ubuntu/Debian
sudo apt-get install jq

# CentOS/RHEL
sudo yum install jq

# macOS
brew install jq
```

---

## 📸 Como Usar com o Santander

### 1. Execute o teste

```bash
php testar_scope_santander_simples.php
```

### 2. Tire um print/screenshot da tela

O output mostrará claramente:
- ✅ Token foi obtido com sucesso (OAuth funciona)
- ❌ Campo `"scope"` está vazio (API PIX não habilitada)
- ⚠️ Diagnóstico do problema

### 3. Envie ao Santander

Anexe o print ao chamado com a mensagem:

```
Assunto: API PIX - Token OAuth sem permissões (scope vazio)

Prezados,

Conforme evidência anexa, conseguimos obter o token OAuth com sucesso,
porém o campo "scope" está vazio.

Aplicação: STARLINK QR CODE
Client ID: RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB

Problema:
- Token OAuth: ✅ Obtido com sucesso
- Campo "scope": ❌ Vazio (deveria conter: cob.write cob.read pix.write pix.read)
- Resultado: API PIX rejeita o token (HTTP 401 - AlgorithmMismatch)

Pergunta:
A "API Pix - Geração de QRCode" está habilitada para a aplicação
"STARLINK QR CODE"?

Aguardo retorno.
```

---

## 🔍 O que o Teste Faz

1. ✅ Carrega as credenciais do Laravel (.env)
2. ✅ Faz requisição OAuth para o Santander
3. ✅ Obtém o token JWT
4. ✅ Decodifica o token (mostra o payload completo)
5. ✅ Analisa o campo "scope"
6. ✅ Mostra claramente se está vazio ou presente

---

## ⚡ Teste Rápido (1 linha)

Se quiser apenas ver o scope:

```bash
php testar_scope_santander_simples.php | grep -A 5 "ANÁLISE DO SCOPE"
```

---

## 📞 Após Habilitar a API PIX

Quando o Santander habilitar a API PIX na sua aplicação, execute o teste novamente:

```bash
php testar_scope_santander_simples.php
```

Você deverá ver:

```
==========================================
🎯 ANÁLISE DO SCOPE:
==========================================
✅ SCOPE PRESENTE!
   Scope: cob.write cob.read pix.write pix.read
```

Aí sim a integração funcionará! 🚀

---

## 🆘 Problemas?

### Erro: "Certificado não encontrado"

Verifique o caminho do certificado no `.env`:
```env
SANTANDER_PIX_CERTIFICATE_PATH=certificates/santander.pem
```

### Erro: "cURL error"

Verifique se o certificado tem chave privada e está no formato correto (PEM).

### Erro: HTTP 401 no OAuth

Verifique `Client ID` e `Client Secret` no `.env`.

---

**Criado para demonstrar claramente o problema do scope vazio ao Santander** 🎯

