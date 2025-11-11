#!/bin/bash

# =============================================================================
# TESTE DIRETO - SANTANDER PIX CLIENT ID E CLIENT SECRET
# =============================================================================
# Este script testa diretamente se o Client ID e Client Secret são válidos
# =============================================================================

echo ""
echo "=========================================="
echo "🔐 TESTE DIRETO - AUTENTICAÇÃO SANTANDER"
echo "=========================================="
echo ""

# Configurações
CLIENT_ID="RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB"
CLIENT_SECRET="nSkWIV8TFJUGRBur"
CERT_PATH="/home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/storage/app/certificates/santander.pem"
URL="https://trust-pix.santander.com.br/oauth/token"

echo "📋 CONFIGURAÇÕES:"
echo "   Client ID: $CLIENT_ID"
echo "   Client Secret: ${CLIENT_SECRET:0:4}************"
echo "   Certificado: $CERT_PATH"
echo "   URL: $URL"
echo ""

# Verificar se o certificado existe
if [ ! -f "$CERT_PATH" ]; then
    echo "❌ ERRO: Certificado não encontrado!"
    echo "   Caminho: $CERT_PATH"
    exit 1
fi

echo "✅ Certificado encontrado"
echo ""

# Gerar Basic Auth
BASIC_AUTH=$(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)

echo "=========================================="
echo "📤 ENVIANDO REQUISIÇÃO..."
echo "=========================================="
echo ""

# Fazer a requisição com saída detalhada
RESPONSE=$(curl -s -w "\n---SEPARATOR---\n%{http_code}\n%{content_type}\n%{size_download}" \
  -X POST "$URL" \
  -H "Authorization: Basic $BASIC_AUTH" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --cert "$CERT_PATH" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "scope=cob.write cob.read pix.write pix.read webhook.read webhook.write")

# Separar a resposta
BODY=$(echo "$RESPONSE" | sed -n '1,/---SEPARATOR---/p' | sed '$d')
HTTP_CODE=$(echo "$RESPONSE" | tail -n 3 | head -n 1)
CONTENT_TYPE=$(echo "$RESPONSE" | tail -n 2 | head -n 1)
SIZE=$(echo "$RESPONSE" | tail -n 1)

echo "=========================================="
echo "📥 RESPOSTA DO SERVIDOR"
echo "=========================================="
echo ""
echo "🔢 HTTP Status Code: $HTTP_CODE"
echo "📄 Content-Type: $CONTENT_TYPE"
echo "📦 Tamanho: $SIZE bytes"
echo ""
echo "📃 BODY da Resposta:"
echo "-------------------------------------------"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo "-------------------------------------------"
echo ""

# Análise do resultado
echo "=========================================="
echo "🔍 DIAGNÓSTICO"
echo "=========================================="
echo ""

case $HTTP_CODE in
    200)
        echo "✅ SUCESSO!"
        echo "   As credenciais estão CORRETAS!"
        echo "   Token obtido com sucesso."
        ;;
    400)
        echo "❌ ERRO 400 - Requisição Inválida"
        echo ""
        echo "   🔴 PROBLEMA IDENTIFICADO:"
        echo "   ────────────────────────────────────────"
        if echo "$BODY" | grep -q "parâmetros necessários"; then
            echo "   ⚠️  'A requisição não possui os parâmetros necessários'"
            echo ""
            echo "   📌 CAUSAS POSSÍVEIS:"
            echo "   1. ❌ Client ID e Client Secret NÃO são da API PIX"
            echo "   2. ❌ Credenciais são de outro produto (API Gateway)"
            echo "   3. ❌ Falta ativar as credenciais no Portal Santander"
            echo "   4. ❌ Certificado não vinculado às credenciais"
            echo ""
            echo "   ✅ SOLUÇÃO:"
            echo "   • Entre em contato com o Santander Developer"
            echo "   • Solicite credenciais ESPECÍFICAS da API PIX"
            echo "   • Confirme que o certificado está vinculado"
        else
            echo "   • Erro não identificado automaticamente"
            echo "   • Verifique o body da resposta acima"
        fi
        ;;
    401)
        echo "❌ ERRO 401 - Não Autorizado"
        echo ""
        echo "   🔴 PROBLEMA IDENTIFICADO:"
        echo "   ────────────────────────────────────────"
        echo "   ⚠️  Client ID ou Client Secret INCORRETOS"
        echo ""
        echo "   ✅ SOLUÇÃO:"
        echo "   • Verifique se copiou as credenciais corretamente"
        echo "   • Confirme com o Santander se as credenciais estão ativas"
        ;;
    403)
        echo "❌ ERRO 403 - Proibido"
        echo ""
        echo "   🔴 PROBLEMA IDENTIFICADO:"
        echo "   ────────────────────────────────────────"
        echo "   ⚠️  Certificado NÃO vinculado OU conta NÃO ativada"
        echo ""
        echo "   ✅ SOLUÇÃO:"
        echo "   • Verifique se o certificado está vinculado no Portal"
        echo "   • Confirme se a conta PIX está ativada"
        ;;
    500)
        echo "❌ ERRO 500 - Erro Interno do Servidor"
        echo ""
        echo "   🔴 PROBLEMA IDENTIFICADO:"
        echo "   ────────────────────────────────────────"
        echo "   ⚠️  Erro no servidor do Santander"
        echo ""
        echo "   ✅ SOLUÇÃO:"
        echo "   • Tente novamente em alguns minutos"
        echo "   • Se persistir, entre em contato com o Santander"
        ;;
    *)
        echo "❌ ERRO $HTTP_CODE - Não Mapeado"
        echo ""
        echo "   • Verifique o body da resposta acima"
        echo "   • Entre em contato com o suporte do Santander"
        ;;
esac

echo ""
echo "=========================================="
echo "📊 RESUMO"
echo "=========================================="
echo ""

if [ "$HTTP_CODE" = "200" ]; then
    echo "🟢 Status: CREDENCIAIS VÁLIDAS"
    echo "🟢 Ação: Pode prosseguir com a integração"
else
    echo "🔴 Status: CREDENCIAIS INVÁLIDAS ou PROBLEMA DE CONFIGURAÇÃO"
    echo "🔴 Ação: Revisar configurações ou solicitar novas credenciais"
fi

echo ""
echo "=========================================="
echo "" 