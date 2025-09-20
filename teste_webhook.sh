#!/bin/bash
# TESTE DO WEBHOOK WOOVI

echo "🧪 Testando webhook Woovi..."

# Testar endpoint
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi \
     -H "Content-Type: application/json" \
     -H "User-Agent: Woovi-Webhook/1.0" \
     -d '{
       "event": "OPENPIX:CHARGE_COMPLETED",
       "charge": {
         "correlationID": "TEST_WEBHOOK",
         "status": "COMPLETED"
       }
     }'

echo ""
echo "✅ Teste concluído!"
