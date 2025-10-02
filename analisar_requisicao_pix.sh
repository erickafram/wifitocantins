#!/bin/bash

echo "=========================================="
echo "🔐 ANÁLISE DA REQUISIÇÃO PIX SANTANDER"
echo "=========================================="
echo ""

CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"

# 1. Obter token
echo "📌 Passo 1: Obtendo token de acesso..."
BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

TOKEN_RESPONSE=$(curl -s -X POST "https://trust-pix.santander.com.br/auth/oauth/v2/token" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data "client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET&grant_type=client_credentials")

TOKEN=$(echo "$TOKEN_RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin)['access_token'])" 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "❌ Não foi possível obter o token!"
    exit 1
fi

echo "✅ Token obtido com sucesso!"
echo ""

# 2. Testar requisição PIX COM headers adicionais
echo "=========================================="
echo "📤 TESTE 1: Requisição COM headers extras"
echo "=========================================="

TXID="TESTE$(date +%s)WIFI$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 15 | head -n 1)"

PAYLOAD='{
  "calendario": {"expiracao": 900},
  "valor": {"original": "0.01"},
  "chave": "pix@tocantinstransportewifi.com.br",
  "solicitacaoPagador": "Teste de integracao"
}'

echo "TXId: $TXID"
echo ""

RESPONSE1=$(curl -s -w "\n---HTTP_CODE---\n%{http_code}" \
  -X PUT "https://trust-pix.santander.com.br/api/v1/cob/$TXID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Application-Key: $CLIENT_ID" \
  -H "client_id: $CLIENT_ID" \
  --cert "$CERT_PATH" \
  --data "$PAYLOAD")

BODY1=$(echo "$RESPONSE1" | sed -n '1,/---HTTP_CODE---/p' | sed '$d')
HTTP1=$(echo "$RESPONSE1" | tail -n 1)

echo "HTTP Status: $HTTP1"
echo "Resposta:"
echo "$BODY1" | python3 -m json.tool 2>/dev/null || echo "$BODY1"
echo ""

# 3. Testar APENAS com Authorization header
echo "=========================================="
echo "📤 TESTE 2: Requisição SEM headers extras"
echo "=========================================="

TXID2="TESTE$(date +%s)WIFI$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 15 | head -n 1)"

RESPONSE2=$(curl -s -w "\n---HTTP_CODE---\n%{http_code}" \
  -X PUT "https://trust-pix.santander.com.br/api/v1/cob/$TXID2" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  --cert "$CERT_PATH" \
  --data "$PAYLOAD")

BODY2=$(echo "$RESPONSE2" | sed -n '1,/---HTTP_CODE---/p' | sed '$d')
HTTP2=$(echo "$RESPONSE2" | tail -n 1)

echo "TXId: $TXID2"
echo ""
echo "HTTP Status: $HTTP2"
echo "Resposta:"
echo "$BODY2" | python3 -m json.tool 2>/dev/null || echo "$BODY2"
echo ""

echo "=========================================="
echo "📊 RESUMO DOS TESTES"
echo "=========================================="
echo "Teste 1 (COM X-Application-Key): HTTP $HTTP1"
echo "Teste 2 (SEM X-Application-Key): HTTP $HTTP2"
echo ""

if [ "$HTTP1" = "201" ] || [ "$HTTP1" = "200" ]; then
    echo "✅ SUCESSO com headers adicionais!"
elif [ "$HTTP2" = "201" ] || [ "$HTTP2" = "200" ]; then
    echo "✅ SUCESSO sem headers adicionais!"
else
    echo "❌ AMBOS FALHARAM"
    echo ""
    echo "💡 CONCLUSÃO:"
    echo "O problema NÃO está nos headers HTTP."
    echo "O Santander pode estar exigindo JWS (assinatura do payload)."
    echo ""
    echo "📖 PRÓXIMO PASSO:"
    echo "Entre em contato com o Santander e pergunte:"
    echo "1. A API PIX requer JWS (JSON Web Signature)?"
    echo "2. Qual é o formato correto da assinatura?"
    echo "3. Existe documentação específica para a API PIX?"
fi 