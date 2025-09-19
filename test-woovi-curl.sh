#!/bin/bash

# 🧪 SCRIPT DE TESTE - WOOVI API (CURL)
# Tocantins Transport WiFi

echo "🚀 TESTANDO INTEGRAÇÃO WOOVI PIX"
echo "===================================="

BASE_URL="https://www.tocantinstransportewifi.com.br"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Teste 1: Verificar se o site está online
echo -e "\n1️⃣  ${CYAN}TESTANDO CONECTIVIDADE DO SITE...${NC}"
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL" --max-time 10)
if [ "$response" -eq 200 ]; then
    echo -e "✅ ${GREEN}Site online - Status: $response${NC}"
else
    echo -e "❌ ${RED}Site offline ou inacessível - Status: $response${NC}"
fi

# Teste 2: Testar conexão com Woovi
echo -e "\n2️⃣  ${CYAN}TESTANDO CONEXÃO WOOVI API...${NC}"
response=$(curl -s "$BASE_URL/api/payment/test-woovi" \
    -H "Accept: application/json" \
    --max-time 15)

if echo "$response" | grep -q '"success":true'; then
    echo -e "✅ ${GREEN}Conexão Woovi OK!${NC}"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
else
    echo -e "❌ ${RED}Erro na conexão Woovi${NC}"
    echo "$response"
fi

# Teste 3: Gerar QR Code PIX
echo -e "\n3️⃣  ${CYAN}TESTANDO GERAÇÃO DE QR CODE PIX...${NC}"
response=$(curl -s "$BASE_URL/api/payment/pix" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"amount": 5.99, "mac_address": "02:11:22:33:44:55"}' \
    --max-time 20)

if echo "$response" | grep -q '"success":true'; then
    echo -e "✅ ${GREEN}QR Code gerado com sucesso!${NC}"
    echo "$response" | jq '.gateway, .payment_id, .qr_code.amount' 2>/dev/null || echo "$response"
else
    echo -e "❌ ${RED}Erro ao gerar QR Code${NC}"
    echo "$response"
fi

# Teste 4: Verificar rotas
echo -e "\n4️⃣  ${CYAN}VERIFICANDO ROTAS DISPONÍVEIS...${NC}"
routes=(
    "/api/payment/pix"
    "/api/payment/test-woovi"
    "/api/payment/webhook/woovi"
    "/api/payment/pix/status?payment_id=1"
)

for route in "${routes[@]}"; do
    status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$route" --max-time 5)
    if [ "$status" -eq 200 ]; then
        echo -e "✅ ${GREEN}$route - Status: $status${NC}"
    elif [ "$status" -eq 405 ] || [ "$status" -eq 422 ]; then
        echo -e "⚠️  ${YELLOW}$route - Status: $status (normal)${NC}"
    else
        echo -e "❌ ${RED}$route - Status: $status${NC}"
    fi
done

echo -e "\n🎉 ${GREEN}TESTE CONCLUÍDO!${NC}"
echo "===================================="
