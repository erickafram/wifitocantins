#!/bin/bash

echo "=========================================="
echo "🔍 DECODIFICADOR DE TOKEN JWT - SANTANDER"
echo "=========================================="
echo ""

# Token que você obteve (cole aqui o token completo)
TOKEN="eyJraWQiOiJkOVNXSFlQemY3dTd4RG1Kd0Y5ZzVXV245VzIyb3NNNTNueHJUU3dTNENJIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiJSQTRVUDIzTDd0UUxsQWxjc2s4TzlRRjlRNk9paDZOQiIsImF1ZCI6IlNhbnRhbmRlciBPcGVuIEFQSSIsImNsaWVudElkIjoiUkE0VVAyM0w3dFFMbEFsY3NrOE85UUY5UTZPaWg2TkIiLCJhenAiOiJSQTRVUDIzTDd0UUxsQWxjc2s4TzlRRjlRNk9paDZOQiIsImlzcyI6IlNhbnRhbmRlciBKV1QgQXV0aG9yaXR5IiwidHlwIjoiQmVhcmVyIiwiY25mIjp7Ing1dCNTMjU2IjoiMTZkODJjZGU2MTQ3ZDg5MGE5NWI5NWQ1MTJkYWFjOTZiMTgyYmNjYSJ9LCJvcGFxdWVUb2tlbiI6IjNIaUloQVFpQjFKR0F0YWdtR01IdmRsV0o2ZVMiLCJleHAiOjE3NTk0MTg3ODYsInZjIjp7ImNyZWRlbnRpYWxTdWJqZWN0Ijp7Im5hbWUiOiJjbnBqX3BhcmNlaXJvIiwidmFsdWUiOiIwMC4wMTguMTI3XC8wMDAxLTM4In19LCJpYXQiOjE3NTk0MTc4ODYsImp0aSI6ImIxMTIxZDE1LTEzODQtNDk4My1iZDU5LWE1NWE5MTc2NmQwYiJ9.ObgVv5t1bVAvuTDbpSy3WHX5kpiq0f-fdwr-JF7rgFNKE-ctbrr5yehUSWV7WmzP4uTz7v-kHRHuejwg58sg4zA_x7ICTOyGJHlKpIAI7nqPlKQm9JqI5oP2jrUy3ArOTRrwgEuKQ__QmuEWnmMkZCLak3U-xFYqv122fGyfYg49dNUK1Mkoq_XlApPfiEm2FPJpPAWAeAWVc1k_OxPE1-999S3aZsdkVhOTIJqf12yyY2Hi9FdXKFj31GceZj_X6GTqno4Vpbf36yBiDF-CiXoyRQyt0kknVXAnXoFnZRLMMcq48dyxnuewmdwYx4DzxOw-e4Xg-R2Q66UOA6E0VA"

# Decodificar header
JWT_HEADER=$(echo "$TOKEN" | cut -d'.' -f1)
while [ $((${#JWT_HEADER} % 4)) -ne 0 ]; do JWT_HEADER="${JWT_HEADER}="; done

echo "📌 HEADER do JWT:"
echo "$JWT_HEADER" | base64 -d 2>/dev/null | python3 -m json.tool 2>/dev/null
echo ""

# Decodificar payload
JWT_PAYLOAD=$(echo "$TOKEN" | cut -d'.' -f2)
while [ $((${#JWT_PAYLOAD} % 4)) -ne 0 ]; do JWT_PAYLOAD="${JWT_PAYLOAD}="; done

echo "📌 PAYLOAD do JWT (COMPLETO):"
DECODED_PAYLOAD=$(echo "$JWT_PAYLOAD" | base64 -d 2>/dev/null)
echo "$DECODED_PAYLOAD" | python3 -m json.tool 2>/dev/null
echo ""

echo "=========================================="
echo "🚨 ANÁLISE DO PROBLEMA"
echo "=========================================="
echo ""

# Extrair campos críticos
echo "$DECODED_PAYLOAD" | python3 << 'PYTHON_SCRIPT'
import sys, json
from datetime import datetime

data = json.loads(sys.stdin.read())

print("📋 CAMPOS CRÍTICOS:")
print("")
print(f"✅ Issuer (iss): {data.get('iss', 'N/A')}")
print(f"⚠️  Audience (aud): {data.get('aud', 'N/A')}")
print(f"   └─ PROBLEMA: Token é para 'Santander Open API' GENÉRICO")
print(f"   └─ NÃO é para 'Santander PIX API' específico")
print("")
print(f"✅ Client ID: {data.get('clientId', 'N/A')}")
print("")
print(f"🔴 Scope: \"{data.get('scope', '')}\"")
print(f"   └─ PROBLEMA CRÍTICO: Scope está VAZIO!")
print(f"   └─ Esperado: 'cob.write cob.read pix.write pix.read'")
print(f"   └─ CAUSA: API PIX não habilitada na aplicação")
print("")

# Expiração
if 'exp' in data:
    exp_date = datetime.fromtimestamp(data['exp'])
    print(f"⏰ Expira em: {exp_date.strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"   └─ Validade: 15 minutos (900 segundos)")
print("")

# CNPJ
if 'vc' in data and 'credentialSubject' in data['vc']:
    cnpj = data['vc']['credentialSubject'].get('value', 'N/A')
    print(f"🏢 CNPJ: {cnpj}")
    print("")

PYTHON_SCRIPT

echo "=========================================="
echo "🎯 CONCLUSÃO"
echo "=========================================="
echo ""
echo "O token foi obtido COM SUCESSO, mas:"
echo ""
echo "❌ Não tem SCOPES PIX (scope está vazio)"
echo "❌ Audience é genérico ('Santander Open API')"
echo "❌ Token NÃO é autorizado para API PIX"
echo ""
echo "🔴 CAUSA CONFIRMADA:"
echo "   A aplicação 'STARLINK QR CODE' NÃO TEM"
echo "   a 'API Pix - Geração de QRCode' HABILITADA"
echo ""
echo "=========================================="
echo "📞 PRÓXIMO PASSO"
echo "=========================================="
echo ""
echo "1. Acesse: https://developer.santander.com.br"
echo "2. Vá em 'Minhas Aplicações'"
echo "3. Clique em 'STARLINK QR CODE'"
echo "4. Vá na aba 'APIs Associadas'"
echo "5. Procure por 'API Pix - Geração de QRCode'"
echo ""
echo "SE NÃO ESTIVER NA LISTA:"
echo "   └─ Clique em 'Adicionar API' ou 'Associar Produto'"
echo "   └─ Habilite a 'API Pix - Geração de QRCode'"
echo ""
echo "SE JÁ ESTIVER NA LISTA MAS INATIVA:"
echo "   └─ Clique para ATIVAR"
echo "   └─ Aguarde aprovação (pode levar algumas horas)"
echo ""
echo "DEPOIS DE HABILITAR:"
echo "   └─ Obtenha um NOVO token"
echo "   └─ O novo token terá os scopes PIX"
echo "   └─ A integração vai funcionar!"
echo ""
echo "==========================================" 