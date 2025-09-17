# 🏦 SANTANDER PIX - Guia de Implementação

## 📋 CHECKLIST - O que você precisa providenciar

### 🏢 **EMPRESARIAIS**
- [ ] CNPJ da empresa
- [ ] Conta PJ Santander ativa
- [ ] Usuário Master no Internet Banking PJ
- [ ] Contrato de conta corrente

### 🔒 **CERTIFICADO DIGITAL**
- [ ] Certificado A1/A3 formato .PFX
- [ ] Certificado associado ao CNPJ
- [ ] Validade mínima 12 meses
- [ ] Senha do certificado
- [ ] Backup seguro do certificado

### 🔑 **PIX**
- [ ] Chave PIX cadastrada no Internet Banking
- [ ] Tipo da chave definido (CNPJ/email/telefone/aleatória)
- [ ] Conta de recebimento configurada

---

## 🚀 PROCESSO DE CADASTRO

### **1. Portal do Desenvolvedor**
```
URL: https://developer.santander.com.br
Login: Credenciais PJ (Usuário Master)
```

**Criar Aplicação:**
- Nome: "WiFi Tocantins Express - PIX API"
- Tipo: "Sou desenvolvedor"
- Produto: "PIX - Geração de QR Code"
- Ambiente: Produção
- Certificado: Upload do .PFX
- Descrição: "Sistema de pagamento WiFi via PIX"

### **2. Credenciais Fornecidas**
Após aprovação, você receberá:
- **Client ID**: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- **Client Secret**: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
- **Workspace ID**: `xxxxxxxx`
- **URLs dos Endpoints**

### **3. Configuração no Sistema**
```env
# .env - Configurações PIX Santander
PIX_ENABLED=true
PIX_GATEWAY=santander

# Credenciais Santander API
SANTANDER_CLIENT_ID=seu_client_id_aqui
SANTANDER_CLIENT_SECRET=seu_client_secret_aqui
SANTANDER_WORKSPACE_ID=seu_workspace_id

# Certificado Digital
SANTANDER_CERTIFICATE_PATH=storage/certificates/santander.pfx
SANTANDER_CERTIFICATE_PASSWORD=senha_do_certificado

# Ambiente (sandbox para testes, production para produção)
SANTANDER_ENVIRONMENT=sandbox

# Dados PIX
PIX_KEY=sua_chave_pix@empresa.com.br
PIX_MERCHANT_NAME=WiFi Tocantins Express
PIX_MERCHANT_CITY=Palmas
```

### **4. Upload do Certificado**
```bash
# Criar pasta para certificados
mkdir -p storage/app/storage/certificates

# Upload do certificado .pfx
# Coloque o arquivo santander.pfx na pasta storage/app/storage/certificates/
```

---

## 📞 **QUEM PROCURAR NO SANTANDER**

### **1. Gerente da Conta**
- **O que pedir:** Liberação para usar APIs PIX
- **Informar:** Que você vai integrar PIX via API
- **Solicitar:** Código de Estação (se necessário)

### **2. Suporte Digital/Open Banking**
- **Telefone:** 4004-3535 (opção Open Banking)
- **O que pedir:** Suporte técnico para integração PIX
- **Ter em mãos:** CNPJ, dados da conta

### **3. Central de Relacionamento PJ**
- **Para:** Questões contratuais e liberações
- **Quando:** Se houver restrições na conta

---

## 🔧 **ENDPOINTS PRINCIPAIS**

### **Autenticação OAuth 2.0**
```
POST /auth/oauth/v2/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
&client_id={CLIENT_ID}
&client_secret={CLIENT_SECRET}
```

### **Criar Cobrança PIX**
```
POST /pix_payments/v1/qr_codes
Authorization: Bearer {ACCESS_TOKEN}
Content-Type: application/json

{
  "key": "sua_chave_pix",
  "amount": {
    "value": 499
  },
  "expiration_datetime": "2024-12-31T23:59:59Z",
  "external_id": "WIFI_TXN_123"
}
```

### **Consultar Pagamento**
```
GET /pix_payments/v1/payments/{payment_id}
Authorization: Bearer {ACCESS_TOKEN}
```

### **Webhook (Notificações)**
```
POST /api/webhook/santander
Content-Type: application/json

{
  "event_type": "payment_approved",
  "payment_id": "xxx",
  "amount": 499,
  "paid_at": "2024-01-01T10:00:00Z"
}
```

---

## ⚠️ **PONTOS DE ATENÇÃO**

### **Certificado Digital**
- ⚠️ **CRÍTICO:** Certificado deve estar associado ao mesmo CNPJ
- ⚠️ **VALIDADE:** Monitorar vencimento (renovar antes)
- ⚠️ **BACKUP:** Manter cópia segura
- ⚠️ **SENHA:** Armazenar com segurança

### **Ambiente de Testes**
- ✅ **Sandbox disponível** para testes
- ✅ **Credenciais separadas** para homologação
- ✅ **QR Codes fictícios** para validação

### **Segurança**
- 🔒 **HTTPS obrigatório** em produção
- 🔒 **Tokens temporários** (OAuth)
- 🔒 **Validação de webhook** com assinatura
- 🔒 **Logs de auditoria**

---

## 📞 **CONTATOS ÚTEIS**

### **Santander**
- **Portal:** developer.santander.com.br
- **Suporte:** 4004-3535 (Open Banking)
- **Email:** Através do portal do desenvolvedor

### **Certificado Digital**
- **Serasa:** serasa.com.br/certificado-digital
- **Certisign:** certisign.com.br
- **Valid:** valid.com.br
- **AC Santander:** santander.com.br (procurar certificado)

---

## 🕐 **PRAZO ESTIMADO**

- **Certificado Digital:** 1-2 dias úteis
- **Cadastro Portal:** 1-3 dias úteis (análise)
- **Implementação:** 3-5 dias úteis
- **Testes:** 2-3 dias úteis
- **Total:** 7-13 dias úteis

---

## ✅ **PRÓXIMOS PASSOS**

1. **Providenciar certificado digital** (se não tiver)
2. **Cadastrar chave PIX** (se não tiver)
3. **Acessar portal do desenvolvedor**
4. **Criar aplicação PIX**
5. **Aguardar aprovação**
6. **Implementar integração**
7. **Realizar testes**
8. **Colocar em produção**
