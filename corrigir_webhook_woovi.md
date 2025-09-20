# 🚨 CORREÇÃO URGENTE - WEBHOOK WOOVI

## PROBLEMA
O webhook está configurado com URL **INCORRETA** no painel Woovi:
- ❌ **URL atual**: `https://www.tocantinstransportewifi.com.br/webhook/woovi`
- ✅ **URL correta**: `https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi`

## SOLUÇÃO IMEDIATA

### 1. Acessar Painel Woovi
1. Entre em: https://app.woovi.com/
2. Vá em **Configurações** → **Webhooks**
3. Encontre o webhook atual

### 2. Corrigir URL
**Altere de:**
```
https://www.tocantinstransportewifi.com.br/webhook/woovi
```

**Para:**
```
https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi
```

### 3. Testar Webhook
Após alterar, faça um pagamento teste para verificar se funciona.

## ROTAS DISPONÍVEIS
Segundo `routes/api.php`, as rotas corretas são:
- `/api/payment/webhook/woovi`
- `/api/payment/webhook/woovi/created`
- `/api/payment/webhook/woovi/expired`
- `/api/payment/webhook/woovi/transaction`
- `/api/payment/webhook/woovi/different-payer`
- `/api/payment/webhook/woovi/unified`

## VERIFICAÇÃO
Execute este comando para testar:
```bash
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

Deve retornar **200 OK** em vez de **404 Not Found**.
