# CONFIGURAÇÃO COMPLETA DE WEBHOOKS WOOVI/OPENPIX

## 📋 WEBHOOKS RECOMENDADOS PARA CONFIGURAR (EVENTOS CORRETOS)

### 1. **WEBHOOK PRINCIPAL - COBRANÇA PAGA** ✅ (Já configurado)
```
Nome: webhook-charge-completed
Evento: OPENPIX:CHARGE_COMPLETED
URL: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi
Status: ✅ ATIVO (funcionando - status 200)
```

### 2. **WEBHOOK COBRANÇA CRIADA** (RECOMENDADO)
```
Nome: webhook-charge-created
Evento: OPENPIX:CHARGE_CREATED
URL: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi/created
Propósito: Confirmar criação do PIX
```

### 3. **WEBHOOK COBRANÇA EXPIRADA** (RECOMENDADO)
```
Nome: webhook-charge-expired
Evento: OPENPIX:CHARGE_EXPIRED
URL: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi/expired
Propósito: Limpar pagamentos expirados
```

### 4. **WEBHOOK TRANSAÇÃO RECEBIDA** (ADICIONAL - IMPORTANTE)
```
Nome: webhook-transaction-received
Evento: OPENPIX:TRANSACTION_RECEIVED
URL: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi/transaction
Propósito: Capturar qualquer transação PIX recebida
```

### 5. **WEBHOOK PAGAMENTO DIFERENTE** (OPCIONAL)
```
Nome: webhook-charge-different-payer
Evento: OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER
URL: https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi/different-payer
Propósito: Pagamento feito por pessoa diferente do solicitante
```

---

## 🔧 CONFIGURAÇÃO NO PAINEL WOOVI

Para cada webhook adicional, configure:

### **Cabeçalhos HTTP:**
```
Accept: application/json
Content-Type: application/json
X-OpenPix-Signature: Gerado por requisição
```

### **HMAC Secret Key:**
```
Use a mesma chave: openpix_oMMGVQZKHCqh6yK/4nf7+jYb6lQwJ/9nuzVdnAmJah0=
```

---

## 📊 ANÁLISE DOS LOGS ATUAIS

Baseado nos logs mostrados:

✅ **STATUS 200** - Webhooks sendo recebidos com sucesso
✅ **URLs funcionando** - Servidor respondendo corretamente
⚠️ **Parâmetro authorization** - Sendo enviado na URL

### **IDs dos Pagamentos Processados:**
- `f545ffa0adfd475d9245fd61ccb6b310` (22:22 e 22:21 - duplicado?)
- `cbbf8df5a339417286febd062ee83de3` (17:26)
- `715a05a2fb264e5bbe5d281665682349` (15:43)
- `b1b9c972eed74a369bee98db3b394cd5` (15:34)
- `5112b82239d349ae8d930c55f171d660` (15:24)
- `3b110b0ccc0a4376b221a7b880f8df28` (15:17)
- `4b2183f7f2c845f3b2192b1f1e6264f6` (15:06)

---

## 🚨 PROBLEMAS IDENTIFICADOS

1. **Webhooks duplicados** (22:22 e 22:21 com mesmo ID)
2. **Parâmetro authorization na URL** pode estar causando confusão
3. **Apenas 1 tipo de webhook** configurado (CHARGE_COMPLETED)

---

## 💡 RECOMENDAÇÕES

### **IMEDIATAS:**
1. ✅ Manter webhook atual (está funcionando)
2. ➕ Adicionar webhook para CHARGE_CREATED
3. 🔧 Melhorar tratamento de duplicatas

### **OPCIONAIS:**
4. ➕ Adicionar webhook para CHARGE_EXPIRED
5. ➕ Adicionar webhook para CHARGE_CANCELLED
