#!/bin/bash

echo "=========================================="
echo "🔍 TESTE DE SCOPE SANTANDER PIX"
echo "=========================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Ler credenciais do .env do Laravel
echo -e "${BLUE}📋 Lendo credenciais do .env...${NC}"

# Carregar variáveis do .env
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | grep -v '^\s*$' | xargs)
else
    echo -e "${RED}❌ Arquivo .env não encontrado!${NC}"
    exit 1
fi

# Usar as variáveis do Laravel
CLIENT_ID="$SANTANDER_PIX_CLIENT_ID"
CLIENT_SECRET="$SANTANDER_PIX_CLIENT_SECRET"
CERT_PATH="storage/app/$SANTANDER_PIX_CERTIFICATE_PATH"
CERT_PASSWORD="$SANTANDER_PIX_CERTIFICATE_PASSWORD"
ENVIRONMENT="${SANTANDER_PIX_ENVIRONMENT:-sandbox}"

# Definir URL base conforme ambiente
if [ "$ENVIRONMENT" = "production" ]; then
    BASE_URL="https://trust-pix.santander.com.br"
else
    BASE_URL="https://trust-pix-h.santander.com.br"
fi

echo -e "${GREEN}✅ Credenciais carregadas${NC}"
echo "   Client ID: ${CLIENT_ID:0:10}...${CLIENT_ID: -4}"
echo "   Ambiente: $ENVIRONMENT"
echo "   Base URL: $BASE_URL"
echo ""

# Verificar se certificado existe
if [ ! -f "$CERT_PATH" ]; then
    echo -e "${RED}❌ Certificado não encontrado: $CERT_PATH${NC}"
    exit 1
fi

echo -e "${BLUE}🔐 Testando autenticação OAuth...${NC}"
echo ""

# Fazer requisição OAuth
BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

echo "📤 Enviando requisição para: $BASE_URL/auth/oauth/v2/token"
echo ""

# Requisição com certificado mTLS
RESPONSE=$(curl -s -w "\n%{http_code}" \
    -X POST "$BASE_URL/auth/oauth/v2/token" \
    -H "Authorization: Basic $BASIC_AUTH" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    --cert "$CERT_PATH:$CERT_PASSWORD" \
    -d "grant_type=client_credentials&scope=cob.write cob.read pix.write pix.read")

# Separar body e status code
HTTP_BODY=$(echo "$RESPONSE" | head -n -1)
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)

echo "📥 Status HTTP: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✅ Token obtido com sucesso!${NC}"
    echo ""
    
    # Extrair token
    ACCESS_TOKEN=$(echo "$HTTP_BODY" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
    
    if [ -z "$ACCESS_TOKEN" ]; then
        echo -e "${RED}❌ Token não encontrado na resposta${NC}"
        echo "Resposta completa:"
        echo "$HTTP_BODY" | jq '.' 2>/dev/null || echo "$HTTP_BODY"
        exit 1
    fi
    
    echo "🔑 Token JWT obtido (primeiros 50 chars):"
    echo "   ${ACCESS_TOKEN:0:50}..."
    echo ""
    
    # Decodificar JWT (payload é a segunda parte)
    echo -e "${BLUE}🔍 Decodificando JWT...${NC}"
    echo ""
    
    # Extrair payload (segunda parte do JWT)
    PAYLOAD=$(echo "$ACCESS_TOKEN" | cut -d'.' -f2)
    
    # Adicionar padding se necessário (JWT usa base64url sem padding)
    case $((${#PAYLOAD} % 4)) in
        2) PAYLOAD="${PAYLOAD}==" ;;
        3) PAYLOAD="${PAYLOAD}=" ;;
    esac
    
    # Decodificar base64url (substituir - por + e _ por /)
    PAYLOAD=$(echo "$PAYLOAD" | tr '_-' '/+')
    DECODED=$(echo "$PAYLOAD" | base64 -d 2>/dev/null)
    
    echo "=========================================="
    echo "📋 PAYLOAD DO TOKEN JWT:"
    echo "=========================================="
    echo "$DECODED" | jq '.' 2>/dev/null || echo "$DECODED"
    echo ""
    
    # Verificar scope
    SCOPE=$(echo "$DECODED" | jq -r '.scope' 2>/dev/null)
    
    echo "=========================================="
    echo "🎯 ANÁLISE DO SCOPE:"
    echo "=========================================="
    
    if [ "$SCOPE" = "" ] || [ "$SCOPE" = "null" ] || [ -z "$SCOPE" ]; then
        echo -e "${RED}❌ PROBLEMA CONFIRMADO: SCOPE VAZIO!${NC}"
        echo ""
        echo "   Atual: \"$SCOPE\""
        echo "   Esperado: \"cob.write cob.read pix.write pix.read\""
        echo ""
        echo -e "${YELLOW}⚠️  ESTE É O PROBLEMA!${NC}"
        echo "   O token não possui permissões para acessar a API PIX."
        echo "   A aplicação precisa ter a 'API Pix - Geração de QRCode' habilitada."
        echo ""
    else
        echo -e "${GREEN}✅ SCOPE PRESENTE!${NC}"
        echo "   Scope: $SCOPE"
        echo ""
    fi
    
    # Verificar outros campos importantes
    echo "=========================================="
    echo "📊 OUTROS CAMPOS RELEVANTES:"
    echo "=========================================="
    
    AUD=$(echo "$DECODED" | jq -r '.aud' 2>/dev/null)
    ISS=$(echo "$DECODED" | jq -r '.iss' 2>/dev/null)
    CLIENT_ID_TOKEN=$(echo "$DECODED" | jq -r '.clientId' 2>/dev/null)
    
    echo "   Audience (aud): $AUD"
    echo "   Issuer (iss): $ISS"
    echo "   Client ID: $CLIENT_ID_TOKEN"
    echo ""
    
    if [ "$AUD" = "Santander Open API" ]; then
        echo -e "${YELLOW}⚠️  Audience é genérico (não específico para PIX)${NC}"
        echo ""
    fi
    
    echo "=========================================="
    echo "📸 PRINT ESTA TELA E ENVIE AO SANTANDER"
    echo "=========================================="
    echo ""
    echo "Informações para o suporte:"
    echo "  • Aplicação: STARLINK QR CODE"
    echo "  • Client ID: $CLIENT_ID"
    echo "  • Problema: Token OAuth sem scope PIX"
    echo "  • Evidência: Campo 'scope' vazio no JWT acima"
    echo ""
    
else
    echo -e "${RED}❌ Erro ao obter token!${NC}"
    echo ""
    echo "Status HTTP: $HTTP_CODE"
    echo "Resposta:"
    echo "$HTTP_BODY" | jq '.' 2>/dev/null || echo "$HTTP_BODY"
    echo ""
fi

echo "=========================================="
echo "✅ TESTE CONCLUÍDO"
echo "=========================================="

