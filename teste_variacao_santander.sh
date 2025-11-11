#!/bin/bash

# =============================================================================
# TESTE DE VARIAÇÕES - OAUTH SANTANDER PIX
# =============================================================================
# Testa diferentes combinações de parâmetros para descobrir o correto
# =============================================================================

CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"
URL="https://trust-pix.santander.com.br/oauth/token"

echo "🔬 TESTE DE VARIAÇÕES - OAUTH SANTANDER PIX"
echo "=============================================="
echo ""

# ==================================================
# TESTE 1: Basic Auth + grant_type (mínimo)
# ==================================================
echo "📤 TESTE 1: Basic Auth + grant_type (MÍNIMO)"
echo "----------------------------------------------"

BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

curl -s -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 2: Body params (sem Basic Auth)
# ==================================================
echo "📤 TESTE 2: Body params SEM Basic Auth"
echo "----------------------------------------------"

curl -s -X POST "$URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "client_id=$CLIENT_ID" \
  --data-urlencode "client_secret=$CLIENT_SECRET" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 3: Basic Auth + scope específico PIX
# ==================================================
echo "📤 TESTE 3: Basic Auth + scope PIX específico"
echo "----------------------------------------------"

curl -s -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "scope=pix" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 4: Basic Auth + scope completo
# ==================================================
echo "📤 TESTE 4: Basic Auth + scope completo"
echo "----------------------------------------------"

curl -s -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "scope=cob.write cob.read pix.write pix.read" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 5: Endpoint alternativo /auth/oauth/v2/token
# ==================================================
echo "📤 TESTE 5: Endpoint alternativo /auth/oauth/v2/token"
echo "----------------------------------------------"

ALT_URL="https://trust-pix.santander.com.br/auth/oauth/v2/token"

curl -s -X POST "$ALT_URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 6: Endpoint alternativo /api/oauth/token
# ==================================================
echo "📤 TESTE 6: Endpoint alternativo /api/oauth/token"
echo "----------------------------------------------"

ALT_URL2="https://trust-pix.santander.com.br/api/oauth/token"

curl -s -X POST "$ALT_URL2" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 7: Com header x-application-key
# ==================================================
echo "📤 TESTE 7: Basic Auth + header x-application-key"
echo "----------------------------------------------"

curl -s -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "x-application-key: $CLIENT_ID" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# TESTE 8: JSON body em vez de form-urlencoded
# ==================================================
echo "📤 TESTE 8: JSON body"
echo "----------------------------------------------"

curl -s -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/json" \
  --cert "$CERT_PATH" \
  -d '{"grant_type":"client_credentials"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""

# ==================================================
# RESUMO
# ==================================================
echo "=============================================="
echo "✅ TESTES CONCLUÍDOS"
echo "=============================================="
echo ""
echo "📋 Se algum teste retornou HTTP 200, esse é o formato correto!"
echo ""
echo "🔍 Procure por:"
echo "  - HTTP Status: 200 (SUCESSO)"
echo "  - \"access_token\": \"...\" (TOKEN OBTIDO)"
echo "" 