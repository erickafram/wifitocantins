#!/bin/bash

echo "=========================================="
echo "🔬 DIAGNÓSTICO DETALHADO - SANTANDER PIX"
echo "=========================================="
echo ""

CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"
BASE_URL="https://trust-pix.santander.com.br"

# ============================================
# PARTE 1: VERIFICAR CERTIFICADO
# ============================================
echo "📋 PARTE 1: ANÁLISE DO CERTIFICADO"
echo "=========================================="
echo ""

echo "Caminho do certificado: $CERT_PATH"
echo ""

if [ ! -f "$CERT_PATH" ]; then
    echo "❌ ERRO: Certificado não encontrado!"
    exit 1
fi

echo "✅ Certificado encontrado"
echo ""

# Extrair informações do certificado
echo "📌 Informações do Certificado:"
openssl x509 -in "$CERT_PATH" -noout -subject -issuer -dates 2>/dev/null

echo ""
echo "📌 Verificando se tem chave privada:"
grep -c "BEGIN PRIVATE KEY\|BEGIN RSA PRIVATE KEY\|BEGIN ENCRYPTED PRIVATE KEY" "$CERT_PATH"

echo ""

# ============================================
# PARTE 2: OBTER TOKEN OAUTH
# ============================================
echo "=========================================="
echo "📋 PARTE 2: AUTENTICAÇÃO OAUTH"
echo "=========================================="
echo ""

BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

echo "📤 REQUISIÇÃO OAuth:"
echo "URL: $BASE_URL/auth/oauth/v2/token"
echo "Method: POST"
echo "Headers:"
echo "  Authorization: Basic [REDACTED]"
echo "  Content-Type: application/x-www-form-urlencoded"
echo "Body:"
echo "  client_id=$CLIENT_ID"
echo "  client_secret=[REDACTED]"
echo "  grant_type=client_credentials"
echo ""

TOKEN_RESPONSE=$(curl -s -w "\n---HEADERS---\n%{http_code}\n%{header_json}" \
  -X POST "$BASE_URL/auth/oauth/v2/token" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data "client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET&grant_type=client_credentials")

BODY=$(echo "$TOKEN_RESPONSE" | sed -n '1,/---HEADERS---/p' | sed '$d')
HTTP_CODE=$(echo "$TOKEN_RESPONSE" | tail -n 2 | head -n 1)

echo "📥 RESPOSTA OAuth:"
echo "HTTP Status: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" != "200" ]; then
    echo "❌ ERRO: Falha na autenticação OAuth!"
    echo "Resposta:"
    echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
    exit 1
fi

echo "✅ Autenticação OAuth bem-sucedida!"
echo ""

TOKEN=$(echo "$BODY" | python3 -c "import sys, json; print(json.load(sys.stdin)['access_token'])" 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "❌ ERRO: Não foi possível extrair o token!"
    exit 1
fi

echo "Token obtido (primeiros 50 caracteres): ${TOKEN:0:50}..."
echo ""

# ============================================
# PARTE 3: DECODIFICAR E ANALISAR TOKEN JWT
# ============================================
echo "=========================================="
echo "📋 PARTE 3: ANÁLISE DO TOKEN JWT"
echo "=========================================="
echo ""

# Decodificar header do JWT
JWT_HEADER=$(echo "$TOKEN" | cut -d'.' -f1)
# Adicionar padding se necessário
while [ $((${#JWT_HEADER} % 4)) -ne 0 ]; do JWT_HEADER="${JWT_HEADER}="; done

echo "📌 HEADER do JWT (decodificado):"
echo "$JWT_HEADER" | base64 -d 2>/dev/null | python3 -m json.tool 2>/dev/null
echo ""

# Decodificar payload do JWT
JWT_PAYLOAD=$(echo "$TOKEN" | cut -d'.' -f2)
# Adicionar padding se necessário
while [ $((${#JWT_PAYLOAD} % 4)) -ne 0 ]; do JWT_PAYLOAD="${JWT_PAYLOAD}="; done

echo "📌 PAYLOAD do JWT (decodificado):"
DECODED_PAYLOAD=$(echo "$JWT_PAYLOAD" | base64 -d 2>/dev/null)
echo "$DECODED_PAYLOAD" | python3 -m json.tool 2>/dev/null
echo ""

# Extrair campos importantes
echo "📌 CAMPOS CRÍTICOS DO TOKEN:"
echo "$DECODED_PAYLOAD" | python3 -c "
import sys, json
data = json.load(sys.stdin)
print(f'  Issuer (iss): {data.get(\"iss\", \"N/A\")}')
print(f'  Audience (aud): {data.get(\"aud\", \"N/A\")}')
print(f'  Algorithm (alg): VERIFICAR NO HEADER')
print(f'  Scope: \"{data.get(\"scope\", \"\")}\"')
print(f'  Client ID: {data.get(\"clientId\", \"N/A\")}')
" 2>/dev/null

echo ""

# ============================================
# PARTE 4: TESTAR REQUISIÇÃO À API PIX
# ============================================
echo "=========================================="
echo "📋 PARTE 4: REQUISIÇÃO À API PIX"
echo "=========================================="
echo ""

TXID="TESTE$(date +%s)WIFI$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 15 | head -n 1)"
ENDPOINT="/api/v1/cob/$TXID"

PAYLOAD='{
  "calendario": {"expiracao": 900},
  "valor": {"original": "0.01"},
  "chave": "pix@tocantinstransportewifi.com.br",
  "solicitacaoPagador": "Teste diagnostico"
}'

echo "📤 REQUISIÇÃO PIX:"
echo "URL: $BASE_URL$ENDPOINT"
echo "Method: PUT"
echo "Headers:"
echo "  Authorization: Bearer [PRIMEIROS 30 CHARS: ${TOKEN:0:30}...]"
echo "  Content-Type: application/json"
echo "  X-Application-Key: $CLIENT_ID"
echo "Body:"
echo "$PAYLOAD" | python3 -m json.tool 2>/dev/null || echo "$PAYLOAD"
echo ""

# Fazer requisição e capturar TUDO
PIX_RESPONSE=$(curl -v -s -w "\n---HTTP_CODE---\n%{http_code}" \
  -X PUT "$BASE_URL$ENDPOINT" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Application-Key: $CLIENT_ID" \
  --cert "$CERT_PATH" \
  --data "$PAYLOAD" 2>&1)

# Separar verbose output, body e HTTP code
VERBOSE=$(echo "$PIX_RESPONSE" | grep -E "^[<>*]" || true)
PIX_BODY=$(echo "$PIX_RESPONSE" | sed -n '/^{/,/^}/p' | head -1)
PIX_HTTP=$(echo "$PIX_RESPONSE" | grep -A1 "---HTTP_CODE---" | tail -1)

echo "📥 RESPOSTA PIX:"
echo "HTTP Status: $PIX_HTTP"
echo ""

# Mostrar headers da resposta (do verbose output)
echo "📌 Headers da Resposta (extraídos do verbose):"
echo "$VERBOSE" | grep "^<" | grep -i "HTTP\|Content-Type\|WWW-Authenticate\|X-" || echo "  (Nenhum header adicional capturado)"
echo ""

echo "📌 Body da Resposta:"
echo "$PIX_BODY" | python3 -m json.tool 2>/dev/null || echo "$PIX_BODY"
echo ""

# ============================================
# PARTE 5: ANÁLISE DO ERRO
# ============================================
echo "=========================================="
echo "📋 PARTE 5: ANÁLISE DO ERRO"
echo "=========================================="
echo ""

if [ "$PIX_HTTP" = "200" ] || [ "$PIX_HTTP" = "201" ]; then
    echo "✅ SUCESSO! A cobrança PIX foi criada!"
    echo ""
    echo "🎉 Integração funcionando corretamente!"
    exit 0
fi

# Extrair mensagem de erro
ERROR_MSG=$(echo "$PIX_BODY" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    if 'fault' in data:
        print(f\"Tipo: {data['fault'].get('detail', {}).get('errorcode', 'N/A')}\")
        print(f\"Mensagem: {data['fault'].get('faultstring', 'N/A')}\")
    elif 'detail' in data:
        print(f\"Detalhe: {data.get('detail', 'N/A')}\")
    else:
        print('Formato de erro desconhecido')
except:
    print('Não foi possível parsear o erro')
" 2>/dev/null)

echo "📌 ERRO IDENTIFICADO:"
echo "$ERROR_MSG"
echo ""

# ============================================
# PARTE 6: DIAGNÓSTICO ESPECÍFICO
# ============================================
echo "=========================================="
echo "📋 PARTE 6: DIAGNÓSTICO DO PROBLEMA"
echo "=========================================="
echo ""

if echo "$PIX_BODY" | grep -q "AlgorithmMismatch"; then
    echo "🔴 ERRO: AlgorithmMismatch na policy VJWT-Token"
    echo ""
    echo "📌 CAUSAS POSSÍVEIS:"
    echo ""
    echo "1️⃣ API PIX NÃO HABILITADA na aplicação (MAIS PROVÁVEL)"
    echo "   ├─ As credenciais funcionam para OAuth"
    echo "   ├─ Mas a aplicação 'STARLINK QR CODE' não tem permissão para API PIX"
    echo "   └─ Solução: Habilitar API PIX no Portal do Desenvolvedor"
    echo ""
    echo "2️⃣ FALTA ASSINATURA JWS (JSON Web Signature)"
    echo "   ├─ API PIX pode requerer assinatura do payload"
    echo "   ├─ Header necessário: x-jws-signature"
    echo "   └─ Solução: Confirmar com Santander se JWS é obrigatório"
    echo ""
    echo "3️⃣ ESCOPO VAZIO no token JWT"
    SCOPE=$(echo "$DECODED_PAYLOAD" | python3 -c "import sys, json; print(json.load(sys.stdin).get('scope', ''))" 2>/dev/null)
    echo "   ├─ Scope atual: \"$SCOPE\""
    echo "   ├─ Esperado: scopes PIX (cob.write, cob.read, etc.)"
    echo "   └─ Solução: Habilitar API PIX para obter scopes corretos"
    echo ""
    
elif echo "$PIX_BODY" | grep -q "Unauthorized\|401"; then
    echo "🔴 ERRO: 401 Unauthorized"
    echo ""
    echo "Token não autorizado para acessar este endpoint."
    echo ""
    
elif echo "$PIX_BODY" | grep -q "Not Found\|404"; then
    echo "🔴 ERRO: 404 Not Found"
    echo ""
    echo "Endpoint não existe ou URL incorreta."
    echo "Endpoint testado: $ENDPOINT"
    echo ""
    
else
    echo "🔴 ERRO: HTTP $PIX_HTTP"
    echo ""
    echo "Erro não categorizado. Veja a resposta acima para detalhes."
    echo ""
fi

# ============================================
# PARTE 7: RECOMENDAÇÕES
# ============================================
echo "=========================================="
echo "📋 PARTE 7: PRÓXIMOS PASSOS"
echo "=========================================="
echo ""

echo "✅ AÇÕES RECOMENDADAS:"
echo ""
echo "1. Acessar: https://developer.santander.com.br"
echo "   └─ Ir em 'Minhas Aplicações' > 'STARLINK QR CODE' > 'APIs Associadas'"
echo "   └─ Verificar se 'API Pix - Geração de QRCode' está HABILITADA"
echo ""
echo "2. Se NÃO estiver habilitada:"
echo "   └─ Clicar em 'Adicionar API' ou 'Associar Produto'"
echo "   └─ Habilitar a 'API Pix - Geração de QRCode'"
echo ""
echo "3. Entrar em contato com suporte Santander:"
echo "   └─ Assunto: API PIX - Erro AlgorithmMismatch na policy VJWT-Token"
echo "   └─ Perguntar: A aplicação tem a API PIX habilitada?"
echo "   └─ Perguntar: A API PIX requer JWS (assinatura do payload)?"
echo ""

echo "=========================================="
echo "📄 DOCUMENTOS DE APOIO"
echo "=========================================="
echo ""
echo "Use estes documentos ao contatar o Santander:"
echo "  ✅ DIAGNOSTICO_FINAL_SANTANDER_PIX.md"
echo "  ✅ PERGUNTAS_CRITICAS_SANTANDER.md"
echo ""
echo "=========================================="
echo "FIM DO DIAGNÓSTICO"
echo "==========================================" 