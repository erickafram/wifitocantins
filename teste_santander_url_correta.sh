#!/bin/bash

echo "=========================================="
echo "🎯 TESTE COM URL CORRETA DO SANTANDER"
echo "=========================================="
echo ""

CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"

# NOVA URL CORRETA (da documentação oficial)
BASE_URL="https://api.santander.com.br"

echo "📋 Configurações:"
echo "   Base URL: $BASE_URL"
echo "   Client ID: $CLIENT_ID"
echo ""

# 1. Obter token OAuth
echo "=========================================="
echo "📌 PASSO 1: Obtendo token de acesso"
echo "=========================================="

BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

# Testar endpoint OAuth na nova URL
echo "Testando: $BASE_URL/auth/oauth/v2/token"

TOKEN_RESPONSE=$(curl -s -w "\n---HTTP_CODE---\n%{http_code}" \
  -X POST "$BASE_URL/auth/oauth/v2/token" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data "client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET&grant_type=client_credentials")

BODY=$(echo "$TOKEN_RESPONSE" | sed -n '1,/---HTTP_CODE---/p' | sed '$d')
HTTP_CODE=$(echo "$TOKEN_RESPONSE" | tail -n 1)

echo "HTTP Status: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Token obtido com sucesso!"
    TOKEN=$(echo "$BODY" | python3 -c "import sys, json; print(json.load(sys.stdin)['access_token'])" 2>/dev/null)
    echo "Token (primeiros 50 chars): ${TOKEN:0:50}..."
    echo ""
    
    # 2. Testar criação de cobrança PIX
    echo "=========================================="
    echo "📌 PASSO 2: Criando cobrança PIX"
    echo "=========================================="
    
    TXID="TESTE$(date +%s)WIFI$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 15 | head -n 1)"
    
    # Endpoint CORRETO da documentação
    ENDPOINT="/pix/v2/cob/$TXID"
    
    echo "TXId: $TXID"
    echo "Endpoint: $BASE_URL$ENDPOINT"
    echo ""
    
    PAYLOAD='{
      "calendario": {"expiracao": 900},
      "valor": {"original": "0.01"},
      "chave": "pix@tocantinstransportewifi.com.br",
      "solicitacaoPagador": "Teste integracao"
    }'
    
    PIX_RESPONSE=$(curl -s -w "\n---HTTP_CODE---\n%{http_code}" \
      -X PUT "$BASE_URL$ENDPOINT" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json" \
      --cert "$CERT_PATH" \
      --data "$PAYLOAD")
    
    PIX_BODY=$(echo "$PIX_RESPONSE" | sed -n '1,/---HTTP_CODE---/p' | sed '$d')
    PIX_HTTP=$(echo "$PIX_RESPONSE" | tail -n 1)
    
    echo "HTTP Status: $PIX_HTTP"
    echo "Resposta:"
    echo "$PIX_BODY" | python3 -m json.tool 2>/dev/null || echo "$PIX_BODY"
    echo ""
    
    # 3. Análise do resultado
    echo "=========================================="
    echo "📊 RESULTADO FINAL"
    echo "=========================================="
    
    if [ "$PIX_HTTP" = "201" ] || [ "$PIX_HTTP" = "200" ]; then
        echo "✅ SUCESSO TOTAL!"
        echo ""
        echo "A URL correta é: $BASE_URL"
        echo "Atualize o arquivo .env:"
        echo "SANTANDER_BASE_URL=$BASE_URL"
    elif [ "$PIX_HTTP" = "401" ]; then
        echo "⚠️  AINDA DÁ ERRO 401"
        echo ""
        echo "Erro: $PIX_BODY"
        echo ""
        echo "💡 PRÓXIMAS AÇÕES:"
        echo "1. Verifique se a aplicação 'STARLINK QR CODE' tem a API PIX habilitada"
        echo "2. Entre em contato com o Santander"
        echo "3. Pergunte sobre requisitos adicionais (JWS?)"
    elif [ "$PIX_HTTP" = "404" ]; then
        echo "❌ ENDPOINT NÃO ENCONTRADO"
        echo ""
        echo "O endpoint $ENDPOINT não existe nesta URL."
        echo "Consulte a documentação para o endpoint correto."
    else
        echo "❌ ERRO HTTP $PIX_HTTP"
        echo ""
        echo "Resposta: $PIX_BODY"
    fi
    
else
    echo "❌ Falha ao obter token!"
    echo ""
    echo "Resposta: $BODY"
    echo ""
    echo "💡 Possíveis causas:"
    echo "1. Endpoint OAuth pode estar em outra URL"
    echo "2. Credenciais incorretas para esta URL"
    echo "3. URL de produção diferente"
fi

echo ""
echo "=========================================="
echo "📖 DOCUMENTAÇÃO"
echo "=========================================="
echo "Portal: https://developer.santander.com.br"
echo "Guias: https://developer.santander.com.br/guias/api-pix"
echo "" 