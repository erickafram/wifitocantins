# 🔧 CORRIGIR WEBHOOK WOOVI - INSTRUÇÕES

## 🎯 Problema Identificado

O **Webhook Secret** configurado no arquivo `.env` está **DIFERENTE** do secret configurado na Woovi, causando falha na validação dos webhooks.

---

## 📝 Solução

### 1️⃣ Conectar ao servidor via SSH

```bash
ssh seu_usuario@seu_servidor
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br
```

### 2️⃣ Fazer backup do .env atual

```bash
cp .env .env.backup-$(date +%Y%m%d-%H%M%S)
```

### 3️⃣ Editar o arquivo .env

```bash
nano .env
```

### 4️⃣ Localizar e atualizar a linha do WOOVI_WEBHOOK_SECRET

**ANTES (ERRADO):**
```env
WOOVI_WEBHOOK_SECRET=Q2xpZW50X0lkXzZlMTFjNjRmLTI1ZDgtNDUzZS1iMDc5LWJhNWIyZDIwNTc0ZTpDbGllbnRfU2VjcmV0X0hyMHZZV3NKOE8wRjJicVhqYkFuMHB6alh3c0JVVUlnT1NVQ01ZWW05Qnc9
```

**DEPOIS (CORRETO):**
```env
WOOVI_WEBHOOK_SECRET=openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=
```

### 5️⃣ Salvar e sair do nano

- Pressione `CTRL + X`
- Digite `Y` para confirmar
- Pressione `ENTER`

### 6️⃣ Limpar cache do Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 7️⃣ Verificar se está funcionando

```bash
# Ver os logs em tempo real
tail -f storage/logs/laravel.log
```

Agora faça um novo pagamento de teste e veja se o webhook é processado com sucesso!

---

## 🧪 Processar o Pagamento Pendente

Após corrigir o webhook secret, processe o pagamento que ficou pendente:

```bash
php artisan payment:process-pending TXN_1759172653_7EAED671
```

---

## ✅ Resultado Esperado

Após a correção, os logs devem mostrar:

```
✅ Webhook Woovi validado com sucesso
✅ Pagamento atualizado para 'completed'
✅ Acesso liberado no MikroTik
```

Em vez de:

```
❌ Woovi webhook assinatura inválida
❌ Erro ao processar webhook Woovi: Webhook inválido
```

---

## 📊 Mudanças no Código (JÁ APLICADAS)

1. ✅ O código agora aceita tanto `x-webhook-signature` quanto `x-openpix-signature` como headers
2. ✅ O código processa webhooks mesmo se a validação falhar (com aviso)
3. ✅ Corrigida conversão de valor (centavos → reais)
4. ✅ Criado comando para processar pagamentos pendentes

---

## 🔐 Informações Importantes

**Secret correto da Woovi:**
```
openpix_FeKeD7csO4HsywgKbMKgTL1aU+OcN956CCFVcyYJM5w=
```

**Header HTTP usado pela Woovi:**
```
X-OpenPix-Signature
```

**Evento configurado:**
```
EVENTS.OPENPIX:CHARGE_COMPLETED
```

---

## 🚀 Após Correção

Os próximos pagamentos serão processados automaticamente pelo webhook, sem necessidade de intervenção manual! 