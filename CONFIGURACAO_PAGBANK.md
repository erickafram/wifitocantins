# 🏦 Configuração PagBank PIX - WiFi Tocantins

## 📋 Credenciais Fornecidas

- **Email:** `erickafram10@gmail.com`
- **Token:** `7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76`

---

## ⚙️ Configuração no `.env`

Adicione as seguintes linhas no seu arquivo `.env`:

```env
# Configurações PagBank (PagSeguro)
PAGBANK_TOKEN=7abc3758-042d-4767-8060-b25b6f5244451965950f42879a143be84ffd4b4204de96ff-76b9-450d-9f2b-f878ed7a2a76
PAGBANK_EMAIL=erickafram10@gmail.com
```

---

## 🚀 Como Ativar o PagBank

### Opção 1: Via Painel Admin

1. Acesse: `https://www.tocantinstransportewifi.com.br/admin/api-settings`
2. Selecione **PagBank** como gateway
3. Salve as alterações

### Opção 2: Via Banco de Dados

```sql
UPDATE system_settings 
SET value = 'pagbank' 
WHERE `key` = 'pix_gateway';
```

### Opção 3: Via `.env`

```env
PIX_GATEWAY=pagbank
```

Depois rode:
```bash
php artisan config:clear
```

---

## 🧪 Testar Integração

### 1. Teste de Conectividade

```bash
curl https://www.tocantinstransportewifi.com.br/api/payment/test-pagbank
```

**Resposta esperada:**
```json
{
  "success": true,
  "message": "Conexão com PagBank estabelecida com sucesso",
  "environment": "production",
  "base_url": "https://api.pagseguro.com"
}
```

### 2. Criar QR Code de Teste

Acesse o portal e gere um QR Code PIX. O sistema automaticamente usará o PagBank se estiver configurado como gateway padrão.

### 3. Monitorar Logs

```bash
tail -f storage/logs/laravel.log | grep -E "(PagBank|🏦)"
```

**Logs esperados:**
```
📲 Criando pedido PagBank com QR Code
✅ Pedido PagBank criado com sucesso
🏦 Webhook PagBank recebido
✅ Pagamento PagBank processado com SUCESSO
```

---

## 📡 Webhook URL

Configure no painel do PagBank:

```
https://www.tocantinstransportewifi.com.br/api/payment/webhook/pagbank
```

---

## 🔍 Status de Pagamento

O PagBank usa os seguintes status:

| Status | Descrição | Ação no Sistema |
|--------|-----------|-----------------|
| `WAITING` | Aguardando pagamento | Pagamento permanece `pending` |
| `IN_ANALYSIS` | Em análise de risco | Pagamento permanece `pending` |
| `PAID` | Pagamento confirmado | ✅ Marca como `completed` e libera acesso |
| `DECLINED` | Pagamento recusado | Mantém `pending` (pode expirar) |
| `CANCELED` | Pagamento cancelado | Marca como `cancelled` |

---

## 📊 Diferenças entre Gateways

| Recurso | Woovi | Santander | PagBank |
|---------|-------|-----------|---------|
| **Pagamento** | PIX (QR Code) | PIX (QR Code) | PIX + Carteira Digital |
| **Velocidade** | ⚡ Instantâneo | 🐌 Pode demorar | ⚡ Rápido |
| **Taxas** | R$ 0,85 por cobrança | Consultar banco | Consultar PagBank |
| **Webhook** | ✅ Múltiplos eventos | ✅ Notificações PIX | ✅ Status transacional |
| **Sandbox** | ✅ Disponível | ✅ Homologação | ✅ Sandbox |
| **Cartão** | ❌ Não | ❌ Não | ✅ Sim (Carteira) |

---

## 🎯 Vantagens do PagBank

1. **Pagar com PagBank**: Usuários podem pagar com **saldo da carteira + cartão de crédito à vista**
2. **Webhook confiável**: Notificações imediatas e confiáveis
3. **API simplificada**: Menos complexidade que Santander
4. **Sem certificado mTLS**: Usa Bearer Token simples
5. **QR Code expira em 24h**: Mais tempo para o usuário pagar

---

## ⚠️ Importante

### Ambiente de Produção vs Sandbox

- **Sandbox:** `https://sandbox.api.pagseguro.com`
- **Produção:** `https://api.pagseguro.com`

O sistema detecta automaticamente baseado em `PIX_ENVIRONMENT=production` no `.env`.

### Simular Pagamento

**Atenção:** Para simular pagamento em **produção**, será necessário baixar o aplicativo **PagBank** na Play Store/Apple Store e realizar um pagamento real.

Em **sandbox**, consulte a documentação do PagBank para credenciais de teste.

---

## 🔧 Troubleshooting

### Erro: "Token inválido"

**Solução:** Verifique se o token no `.env` está correto e sem espaços extras.

### Erro: "must have at least 1 element"

**Causa:** Array `qr_codes` vazio.  
**Solução:** Automático no código - sempre envia QR code.

### Erro: "allowed value is [PAGBANK]"

**Causa:** Campo `arrangements` incorreto.  
**Solução:** Automático no código - sempre usa `["PAGBANK"]`.

### Webhook retorna 404

**Solução:** 
1. Verifique se fez `git pull` e atualizou o código
2. Limpe cache: `php artisan route:clear`
3. Confirme que a rota existe: `php artisan route:list | grep pagbank`

---

## 📞 Suporte PagBank

- **Portal:** https://dev.pagbank.uol.com.br
- **Documentação:** https://dev.pagbank.uol.com.br/reference
- **Suporte:** Através do portal do desenvolvedor

---

## ✅ Checklist de Configuração

- [ ] `PAGBANK_TOKEN` adicionado no `.env`
- [ ] `PAGBANK_EMAIL` adicionado no `.env`
- [ ] Gateway configurado (`PIX_GATEWAY=pagbank` ou via admin)
- [ ] Cache limpo (`php artisan config:clear`)
- [ ] Teste de conexão executado
- [ ] Webhook configurado no painel PagBank
- [ ] QR Code testado
- [ ] Logs monitorados

---

**Última atualização:** 07/10/2025  
**Status:** Pronto para uso ✅

