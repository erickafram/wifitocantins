#!/bin/bash

# =============================================================================
# TESTE MANUAL - AUTENTICAÇÃO SANTANDER PIX
# =============================================================================
# Este script testa a autenticação diretamente com curl, isolando o Laravel
# =============================================================================

echo "🔐 TESTE MANUAL - AUTENTICAÇÃO SANTANDER PIX"
echo "=============================================="
echo ""

# Configurações
CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"
URL="https://trust-pix.santander.com.br/oauth/token"

# Verificar se o certificado existe
if [ ! -f "$CERT_PATH" ]; then
    echo "❌ ERRO: Certificado não encontrado em $CERT_PATH"
    exit 1
fi

echo "✅ Certificado encontrado: $CERT_PATH"
echo ""

# ==================================================
# TESTE 1: Basic Auth com scope
# ==================================================
echo "📤 TESTE 1: Basic Auth + Scope"
echo "----------------------------------------------"
echo "URL: $URL"
echo "Method: POST"
echo "Auth: Basic (client_id:client_secret)"
echo "Body: grant_type=client_credentials&scope=..."
echo ""

BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

curl -v -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "scope=cob.write cob.read pix.write pix.read webhook.read webhook.write" \
  2>&1 | tee /tmp/santander_test1.log

echo ""
echo ""

# ==================================================
# TESTE 2: Body params com scope
# ==================================================
echo "📤 TESTE 2: Body params + Scope"
echo "----------------------------------------------"
echo "URL: $URL"
echo "Method: POST"
echo "Body: grant_type + client_id + client_secret + scope"
echo ""

curl -v -X POST "$URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "client_id=$CLIENT_ID" \
  --data-urlencode "client_secret=$CLIENT_SECRET" \
  --data-urlencode "scope=cob.write cob.read pix.write pix.read webhook.read webhook.write" \
  2>&1 | tee /tmp/santander_test2.log

echo ""
echo ""

# ==================================================
# TESTE 3: Basic Auth SEM scope
# ==================================================
echo "📤 TESTE 3: Basic Auth SEM Scope"
echo "----------------------------------------------"
echo "URL: $URL"
echo "Method: POST"
echo "Auth: Basic (client_id:client_secret)"
echo "Body: grant_type=client_credentials"
echo ""

curl -v -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  2>&1 | tee /tmp/santander_test3.log

echo ""
echo ""

# ==================================================
# RESUMO
# ==================================================
echo "=============================================="
echo "✅ TESTES CONCLUÍDOS"
echo "=============================================="
echo ""
echo "📋 Logs salvos em:"
echo "  - /tmp/santander_test1.log (Basic Auth + Scope)"
echo "  - /tmp/santander_test2.log (Body params + Scope)"
echo "  - /tmp/santander_test3.log (Basic Auth SEM Scope)"
echo ""
echo "🔍 COMO INTERPRETAR OS RESULTADOS:"
echo ""
echo "✅ SUCESSO (HTTP 200):"
echo "   {\"access_token\":\"...\",\"token_type\":\"Bearer\",\"expires_in\":900}"
echo ""
echo "❌ ERRO 400 - Requisição inválida:"
echo "   Problema: Credenciais incorretas OU parâmetros errados"
echo ""
echo "❌ ERRO 401 - Não autorizado:"
echo "   Problema: Client ID ou Client Secret incorretos"
echo ""
echo "❌ ERRO 403 - Proibido:"
echo "   Problema: Certificado não vinculado OU conta não ativada"
echo ""
echo "❌ ERRO 500 - Erro do servidor:"
echo "   Problema: Erro interno do Santander (entrar em contato)"
echo "" 