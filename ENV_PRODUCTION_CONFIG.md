# 🚀 CONFIGURAÇÃO .env PRODUÇÃO

## ✅ **SEU .env ATUAL + CONFIGURAÇÕES PIX**

Adicione estas linhas ao final do seu arquivo `.env` de produção:

```env
# ==============================================
# 🚀 CONFIGURAÇÕES PIX WOOVI - ADICIONAR
# ==============================================

# PIX Configuration
PIX_ENABLED=true
PIX_GATEWAY=woovi

# Woovi Credentials (SUAS CREDENCIAIS REAIS)
WOOVI_APP_ID=Client_Id_6e11c64f-25d8-453e-b079-ba5b2d20574e
WOOVI_APP_SECRET=COLE_AQUI_SEU_APP_SECRET_DA_WOOVI

# Environment (production para produção real)
PIX_ENVIRONMENT=production

# PIX Data (CONFIGURE COM SEUS DADOS)
PIX_KEY=sua_chave_pix@tocantinstransportewifi.com.br
PIX_MERCHANT_NAME=Tocantins Transport WiFi
PIX_MERCHANT_CITY=Palmas

# Company Info
COMPANY_NAME=Tocantins Transport WiFi
COMPANY_CONTACT=(63) 99924-1005
COMPANY_EMAIL=contato@tocantinstransportewifi.com.br

# WiFi Settings
WIFI_DEFAULT_PRICE=5.99
WIFI_SESSION_DURATION=24
```

---

## 🔍 **O QUE VOCÊ PRECISA COMPLETAR:**

### **1. 🔑 App Secret da Woovi**
- **Onde encontrar:** Dashboard Woovi → API → App Secret
- **Substituir:** `COLE_AQUI_SEU_APP_SECRET_DA_WOOVI`

### **2. 🏦 Chave PIX**
- **Cadastrar:** Uma chave PIX na sua conta
- **Tipos:** Email, CNPJ, telefone ou aleatória
- **Substituir:** `sua_chave_pix@tocantinstransportewifi.com.br`

### **3. 🌐 Webhook na Woovi**
- **URL:** `https://www.tocantinstransportewifi.com.br/api/payment/webhook/woovi`
- **Evento:** `OPENPIX:CHARGE_COMPLETED`

---

## 🧪 **TESTAR EM PRODUÇÃO:**

### **1. Testar Conectividade**
```bash
curl https://www.tocantinstransportewifi.com.br/api/payment/test-woovi
```

### **2. Gerar QR Code Teste**
```bash
curl -X POST https://www.tocantinstransportewifi.com.br/api/payment/pix \
  -H "Content-Type: application/json" \
  -d '{"amount": 5.99, "mac_address": "02:11:22:33:44:55"}'
```

---

## ⚠️ **IMPORTANTE - SEGURANÇA:**

### **Nunca commitar credenciais:**
```bash
# .gitignore deve conter:
.env
.env.production
.env.local
```

### **Backup do .env:**
```bash
# Fazer backup antes de editar
cp .env .env.backup
```

---

## 🚀 **PRÓXIMOS PASSOS:**

1. **✅ Adicionar configurações** ao .env
2. **🔑 Obter App Secret** no dashboard Woovi
3. **🏦 Cadastrar chave PIX**
4. **🌐 Configurar webhook**
5. **🧪 Testar** em produção
6. **🎉 Funcionar!**
