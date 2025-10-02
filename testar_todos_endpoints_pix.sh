#!/bin/bash

echo "=========================================="
echo "🔬 TESTE DE TODOS OS ENDPOINTS PIX"
echo "=========================================="
echo ""

CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"
BASE_URL="https://trust-pix.santander.com.br"

# 1. Obter token
echo "📌 Obtendo token de acesso..."
BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

TOKEN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/oauth/v2/token" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data "client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET&grant_type=client_credentials")

TOKEN=$(echo "$TOKEN_RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin)['access_token'])" 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "❌ Não foi possível obter o token!"
    exit 1
fi

echo "✅ Token obtido!"
echo ""

# TXId de teste
TXID="TESTE$(date +%s)WIFI$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 15 | head -n 1)"

PAYLOAD='{
  "calendario": {"expiracao": 900},
  "valor": {"original": "0.01"},
  "chave": "pix@tocantinstransportewifi.com.br",
  "solicitacaoPagador": "Teste"
}'

# Lista de endpoints para testar
ENDPOINTS=(
    "/api/v1/cob/$TXID"
    "/api/v2/cob/$TXID"
    "/pix/v1/cob/$TXID"
    "/pix/v2/cob/$TXID"
    "/v1/cob/$TXID"
    "/v2/cob/$TXID"
    "/cob/$TXID"
)

echo "=========================================="
echo "🧪 TESTANDO ENDPOINTS"
echo "=========================================="
echo ""

for ENDPOINT in "${ENDPOINTS[@]}"; do
    echo "📤 Testando: $ENDPOINT"
    
    RESPONSE=$(curl -s -w "\n---HTTP---\n%{http_code}" \
        -X PUT "$BASE_URL$ENDPOINT" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        --cert "$CERT_PATH" \
        --data "$PAYLOAD")
    
    HTTP=$(echo "$RESPONSE" | tail -n 1)
    BODY=$(echo "$RESPONSE" | sed -n '1,/---HTTP---/p' | sed '$d')
    
    echo "   HTTP: $HTTP"
    
    if [ "$HTTP" = "200" ] || [ "$HTTP" = "201" ]; then
        echo "   ✅ SUCESSO!"
        echo ""
        echo "=========================================="
        echo "🎉 ENDPOINT CORRETO ENCONTRADO!"
        echo "=========================================="
        echo ""
        echo "Endpoint: $ENDPOINT"
        echo "URL completa: $BASE_URL$ENDPOINT"
        echo ""
        echo "Resposta:"
        echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
        echo ""
        exit 0
    elif [ "$HTTP" = "401" ]; then
        ERROR=$(echo "$BODY" | python3 -c "import sys, json; d=json.load(sys.stdin); print(d.get('fault',{}).get('faultstring',''))" 2>/dev/null)
        echo "   ❌ 401: $ERROR"
    elif [ "$HTTP" = "404" ]; then
        echo "   ❌ 404: Endpoint não existe"
    elif [ "$HTTP" = "400" ]; then
        ERROR=$(echo "$BODY" | python3 -c "import sys, json; d=json.load(sys.stdin); print(d.get('detail','') or d.get('message',''))" 2>/dev/null)
        echo "   ⚠️  400: $ERROR"
    else
        echo "   ❓ HTTP $HTTP"
    fi
    
    echo ""
done

echo "=========================================="
echo "📊 RESULTADO FINAL"
echo "=========================================="
echo ""
echo "❌ NENHUM ENDPOINT FUNCIONOU"
echo ""
echo "Todos os endpoints testados retornaram erro."
echo ""
echo "💡 PRÓXIMAS AÇÕES:"
echo ""
echo "1. Verifique no Portal do Desenvolvedor Santander:"
echo "   - Vá em 'STARLINK QR CODE' > APIs Associadas"
echo "   - Confirme se a API PIX está HABILITADA"
echo ""
echo "2. Entre em contato com o Santander e pergunte:"
echo "   - Qual é o endpoint CORRETO para criar cobrança PIX?"
echo "   - A aplicação 'STARLINK QR CODE' tem acesso à API PIX?"
echo "   - Existe documentação técnica com exemplos reais?"
echo ""
echo "3. Possível causa:"
echo "   - As credenciais podem ser do 'Santander Open API' genérico"
echo "   - Não estão associadas especificamente à API PIX"
echo "" 