# 🚀 Deploy para Produção

## Problema Atual
O servidor de produção ainda tem o código antigo que usa R$ 0,10.
As alterações que fizemos estão apenas no seu ambiente local.

## ✅ Solução: Fazer Deploy

### No seu computador local:

```bash
cd c:\wamp64\www\wifitocantins

# 1. Verificar se está tudo commitado
git status

# 2. Se houver alterações, commitar
git add .
git commit -m "Fix: Atualizar valor mínimo PIX para R$ 1,00 e remover arrangements"

# 3. Enviar para o GitHub
git push origin main
```

### No servidor de produção (SSH):

```bash
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# 1. Puxar as alterações
git pull origin main

# 2. Limpar cache do Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Atualizar pagamentos pendentes
php atualizar_pagamentos_pendentes.php

# 4. Verificar configuração
php verificar_config.php
```

## 📋 Arquivos que precisam estar atualizados no servidor:

✅ `app/Services/PagBankPixService.php` - SEM arrangements
✅ `config/wifi.php` - default_price = 1.00
✅ `app/Services/PixPaymentManager.php` - min = 1.00
✅ `app/Http/Controllers/PortalDashboardController.php` - default = 1.00
✅ `app/Http/Controllers/PortalController.php` - default = 1.00
✅ `controllers_limpos.php` - min = 1.00, default = 1.00
✅ `sistema_pagamento_limpo.php` - default = 1.00

## ⚠️ Importante

Depois do deploy, você DEVE:
1. Limpar o cache no servidor
2. Criar um NOVO pagamento (não regenerar antigo)
3. Testar o QR Code em qualquer banco

## 🔍 Como Verificar se Funcionou

Nos logs de produção, você deve ver:
```json
{
  "unit_amount": 100,  // ← R$ 1,00 em centavos
  "qr_codes": [{"amount": {"value": 100}}]  // ← SEM arrangements
}
```

Se ainda aparecer `"unit_amount": 10`, o código não foi atualizado no servidor.
