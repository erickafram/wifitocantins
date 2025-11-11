# ⚡ INSTRUÇÕES RÁPIDAS - Configurar Woovi + PagBank

## 🎯 O que precisa fazer no `.env`

### 1. Configurações Woovi (HMAC Secrets)

Adicione esta linha no `.env` **depois** das configurações atuais da Woovi:

```env
# Woovi - HMAC Secrets por evento
WOOVI_WEBHOOK_SECRETS=OPENPIX:CHARGE_COMPLETED=openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=,OPENPIX:CHARGE_CREATED=openpix_nbIOgCe5MwcztpDCHAvt1hZTX3lKPQxsT0xGc0P/Klg=,OPENPIX:CHARGE_EXPIRED=openpix_ipTxMSCVVveI3l2qq4sh0Ir0q+8I8IVc+uHyI6Np8Jc=,OPENPIX:TRANSACTION_RECEIVED=openpix_SBjWAX6GjPVfnWEXPgRbTDtyuFRpWsgaQ6pK7XOG3Ec=,OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER=openpix_DhB1l1upMl8z4zlU8s6nHafcytLUKRxGTddI/CgsOwI=
```

### 2. Configurações PagBank (nova)

Adicione estas linhas **depois** das configurações da Woovi:

```env
# Configurações PagBank (PagSeguro)
PAGBANK_TOKEN=7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76
PAGBANK_EMAIL=erickafram10@gmail.com
```

### 3. Escolher Gateway Padrão (opcional)

Para usar o PagBank como padrão, altere:

```env
PIX_GATEWAY=pagbank
```

Ou mantenha `woovi` e altere pelo painel admin quando quiser.

---

## 📋 Seu `.env` completo (seção PIX)

```env
PIX_ENABLED=true
PIX_GATEWAY=woovi
PIX_ENVIRONMENT=production
PIX_KEY=pix@tocantinstransportewifi.com.br
PIX_MERCHANT_NAME=TocantinsTransportWiFi
PIX_MERCHANT_CITY=Palmas
WIFI_DEFAULT_PRICE=0.05

# Credenciais Woovi
WOOVI_APP_ID=Q2xpZW50X0lkXzZlMTFjNjRmLTI1ZDgtNDUzZS1iMDc5LWJhNWIyZDIwNTc0ZTpDbGllbnRfU2VjcmV0X0hyMHZZV3NKOE8wRjJicVhqYkFuMHB6alh3c0JVVUlnT1NVQ01ZWW05Qnc9
WOOVI_APP_SECRET=Q2xpZW50X0lkXzZlMTFjNjRmLTI1ZDgtNDUzZS1iMDc5LWJhNWIyZDIwNTc0ZTpDbGllbnRfU2VjcmV0X1ZJSVczUTdrUTJZQ2I4SFZLRkExMUs4SUhYYmozVHg3dlNYRlBYVXZLNkE9
WOOVI_WEBHOOK_SECRET=openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=
WOOVI_WEBHOOK_SECRETS=OPENPIX:CHARGE_COMPLETED=openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=,OPENPIX:CHARGE_CREATED=openpix_nbIOgCe5MwcztpDCHAvt1hZTX3lKPQxsT0xGc0P/Klg=,OPENPIX:CHARGE_EXPIRED=openpix_ipTxMSCVVveI3l2qq4sh0Ir0q+8I8IVc+uHyI6Np8Jc=,OPENPIX:TRANSACTION_RECEIVED=openpix_SBjWAX6GjPVfnWEXPgRbTDtyuFRpWsgaQ6pK7XOG3Ec=,OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER=openpix_DhB1l1upMl8z4zlU8s6nHafcytLUKRxGTddI/CgsOwI=

# Configurações PagBank (PagSeguro)
PAGBANK_TOKEN=7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76
PAGBANK_EMAIL=erickafram10@gmail.com

# Credenciais Santander PIX
SANTANDER_CLIENT_ID=RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB
SANTANDER_CLIENT_SECRET=nSkWIV8TFJUGRBur
SANTANDER_WORKSPACE_ID=
SANTANDER_CERTIFICATE_PATH=certificado/santander.pem
SANTANDER_CERTIFICATE_PASSWORD=
SANTANDER_STATION_CODE=
SANTANDER_USE_JWS=true
```

---

## 🚀 Aplicar no Servidor

### No terminal LOCAL (Windows):

```bash
git add .
git commit -m "feat: adicionar integração PagBank PIX e corrigir webhooks Woovi"
git push origin main
```

### No servidor SSH:

```bash
ssh root@206.189.217.189
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# Atualizar código
git pull origin main

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Editar .env
nano .env
# Cole as linhas:
# WOOVI_WEBHOOK_SECRETS=OPENPIX:CHARGE_COMPLETED=openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=,OPENPIX:CHARGE_CREATED=openpix_nbIOgCe5MwcztpDCHAvt1hZTX3lKPQxsT0xGc0P/Klg=,OPENPIX:CHARGE_EXPIRED=openpix_ipTxMSCVVveI3l2qq4sh0Ir0q+8I8IVc+uHyI6Np8Jc=,OPENPIX:TRANSACTION_RECEIVED=openpix_SBjWAX6GjPVfnWEXPgRbTDtyuFRpWsgaQ6pK7XOG3Ec=,OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER=openpix_DhB1l1upMl8z4zlU8s6nHafcytLUKRxGTddI/CgsOwI=
#
# PAGBANK_TOKEN=7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76
# PAGBANK_EMAIL=erickafram10@gmail.com
# Salvar: Ctrl+O, Enter, Ctrl+X

# Limpar cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Testar PagBank
curl https://www.tocantinstransportewifi.com.br/api/payment/test-pagbank
```

---

## 🔔 Configurar Webhook no PagBank

1. Acesse: https://pagseguro.uol.com.br (ou portal desenvolvedor)
2. Vá em **Webhooks** ou **Notificações**
3. Adicione a URL:
   ```
   https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank
   ```
4. Selecione eventos: **Pedido Pago**, **Pedido Cancelado**, etc.

---

## ✅ Resultado Esperado

### Woovi (corrigido):
- ✅ Webhooks `created` retornam 200 (não mais 404)
- ✅ Webhooks `expired` retornam 200
- ✅ Apenas `CHARGE_COMPLETED` marca como pago
- ✅ Validação de assinatura HMAC funcionando

### PagBank (novo):
- ✅ QR Code gerado com sucesso
- ✅ Pagamento via app PagBank (saldo + cartão)
- ✅ Webhook recebe status `PAID` e libera usuário
- ✅ Status intermediários (`IN_ANALYSIS`) não liberam acesso

---

## 📊 Monitoramento

### Ver logs em tempo real:

```bash
# Todos os gateways
tail -f storage/logs/laravel.log | grep -E "(Woovi|PagBank|Santander|PIX)"

# Apenas Woovi
tail -f storage/logs/laravel.log | grep "Woovi"

# Apenas PagBank
tail -f storage/logs/laravel.log | grep "PagBank"
```

---

## 🎉 Resumo

| Gateway | Status | URL Webhook |
|---------|--------|-------------|
| **Woovi** | ✅ Corrigido | `/api/payment/webhook/woovi/*` (5 endpoints) |
| **PagBank** | ✅ Novo | `/api/payment/webhook/pagbank` |
| **Santander** | ⏳ Aguardando suporte | `/api/payment/webhook/santander` |

---

**Última atualização:** 07/10/2025  
**Status:** Pronto para deploy ✅

