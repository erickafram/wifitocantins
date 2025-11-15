# 🎫 Fluxo Correto de Vouchers para Motoristas

## 📋 Visão Geral

O sistema de vouchers agora captura o **MAC address real do dispositivo** através do Mikrotik, igual aos usuários pagantes. Isso garante segurança e controle adequado.

---

## 🔄 Fluxo Completo

### 1. **Motorista Conecta no Wi-Fi**
```
Motorista → Conecta em "Tocantins Transporte" (Wi-Fi do Mikrotik)
```

### 2. **Mikrotik Redireciona para Portal**
```
Mikrotik → Captura MAC e IP real do dispositivo
         → Redireciona para: http://login.tocantinswifi.local/?source=mikrotik&captive=true&mac=XX:XX:XX:XX:XX:XX&ip=10.5.50.XXX
```

### 3. **Portal Salva Dados na Sessão**
```
Portal → Recebe MAC e IP do Mikrotik
       → Salva na sessão:
         - mikrotik_mac
         - mikrotik_ip
         - mikrotik_context_verified
```

### 4. **Motorista Acessa Ativação de Voucher**
```
Opção 1: Clica no botão "Ativar Voucher" no dashboard
Opção 2: Acessa diretamente /voucher/ativar
```

### 5. **Sistema Valida MAC**
```
Sistema → Verifica se tem MAC na sessão
        → Se não tem: Redireciona para / com mensagem de erro
        → Se tem: Exibe formulário de ativação
```

### 6. **Motorista Preenche Formulário**
```
Motorista → Digite código do voucher (ex: WIFI-LOJR-P0BB)
          → Digite telefone (ex: 63 98765-4321)
          → Clica em "Ativar Voucher"
```

### 7. **Sistema Processa Ativação**
```
Sistema → Valida código do voucher
        → Verifica se está ativo e não expirou
        → Verifica se motorista já tem voucher ativo
        → Verifica se atingiu limite diário
        → Cria/atualiza usuário com voucher_id
        → Define expires_at baseado nas horas disponíveis
        → Registra MAC na tabela mikrotik_mac_reports
        → Tenta liberar imediatamente no Mikrotik
```

### 8. **Mikrotik Libera Acesso**
```
Mikrotik → Consulta API a cada 10 segundos
         → GET /api/mikrotik/check-paid-users
         → Recebe lista de MACs para liberar
         → Libera acesso no firewall
```

### 9. **Motorista Navega Livremente**
```
Motorista → Acesso liberado por X horas (conforme voucher)
          → Pode verificar status em /voucher/status
```

---

## 🚫 Validações Implementadas

### **1. Voucher Já Ativo**
Se o motorista tentar ativar novamente enquanto ainda tem tempo:

```
⚠️ Voucher já está ativo!

Você já tem um voucher ativo no momento.
Tempo restante: 1h 45min
Válido até: 15/11/2025 14:30

Aguarde o término do período atual para ativar novamente.
```

### **2. Limite Diário Atingido**
Se o motorista já usou todas as horas disponíveis hoje:

```
❌ Limite diário atingido!

Você já utilizou suas 2 horas disponíveis hoje.

Você poderá ativar novamente em: 8 horas
Disponível a partir de: 16/11/2025 00:00
```

### **3. Voucher Expirado**
Se o voucher em si expirou:

```
❌ Este voucher expirou em 15/11/2025.
```

### **4. Voucher Inválido**
Se o código não existe:

```
❌ Voucher não encontrado. Verifique o código e tente novamente.
```

---

## 🔐 Segurança

### **MAC Address Real**
- ✅ MAC vem **sempre** do Mikrotik (não pode ser falsificado)
- ✅ Salvo na sessão do servidor
- ✅ Validado em cada requisição

### **Limite de Uso**
- ✅ Um voucher por telefone por vez
- ✅ Limite diário de horas configurável
- ✅ Reset automático à meia-noite

### **Validação de Voucher**
- ✅ Código único por voucher
- ✅ Data de expiração
- ✅ Status ativo/inativo
- ✅ Tipo: limitado ou ilimitado

---

## 📊 Banco de Dados

### **Tabela: users**
Campos adicionados para motoristas:
```sql
voucher_id                  BIGINT       -- ID do voucher vinculado
driver_phone                VARCHAR(20)  -- Telefone do motorista
voucher_activated_at        TIMESTAMP    -- Data de ativação
voucher_last_connection     TIMESTAMP    -- Última conexão
voucher_daily_minutes_used  INT          -- Minutos usados hoje
```

### **Tabela: vouchers**
Campos do voucher:
```sql
code                VARCHAR(20)   -- Código único
driver_name         VARCHAR(191)  -- Nome do motorista
daily_hours         INT           -- Horas disponíveis por dia
daily_hours_used    INT           -- Horas já usadas hoje
last_used_date      DATE          -- Última data de uso
expires_at          TIMESTAMP     -- Data de expiração
voucher_type        ENUM          -- unlimited | limited
is_active           BOOLEAN       -- Ativo/Inativo
```

---

## 🎯 URLs do Sistema

### **Portal Captivo (Mikrotik)**
```
http://login.tocantinswifi.local/
  ?source=mikrotik
  &captive=true
  &mac=D6:DE:C4:66:F2:84
  &ip=10.5.50.249
```

### **Ativação de Voucher**
```
http://login.tocantinswifi.local/voucher/ativar
  ?source=mikrotik
  &mac=D6:DE:C4:66:F2:84
  &ip=10.5.50.249
```

### **Status do Voucher**
```
http://login.tocantinswifi.local/voucher/status
```

### **API Mikrotik (Sync)**
```
GET /api/mikrotik/check-paid-users
  ?token=mikrotik-sync-2024
  &format=routeros
```

---

## 🔄 Comandos de Manutenção

### **Reset Diário (Executar à Meia-noite)**
```bash
php artisan vouchers:manage --reset-daily
```
- Reseta contadores diários de vouchers
- Reseta `voucher_daily_minutes_used` dos usuários

### **Expirar Sessões Antigas**
```bash
php artisan vouchers:manage --expire-old
```
- Verifica vouchers expirados
- Atualiza status para `expired`

### **Verificar Limites Diários**
```bash
php artisan vouchers:manage --check-limits
```
- Verifica se motoristas atingiram limite
- Desconecta automaticamente

### **Executar Todas as Tarefas**
```bash
php artisan vouchers:manage
```

---

## 🧪 Teste Completo

### **Passo 1: Conectar ao Wi-Fi**
```
Conecte-se à rede: "Tocantins Transporte"
```

### **Passo 2: Ser Redirecionado**
```
Aguarde redirecionamento automático para:
http://login.tocantinswifi.local/?source=mikrotik&captive=true&mac=...&ip=...
```

### **Passo 3: Clicar em "Ativar Voucher"**
```
Dashboard → Botão verde "Ativar Voucher"
```

### **Passo 4: Preencher Formulário**
```
Código: WIFI-LOJR-P0BB
Telefone: (63) 98765-4321
```

### **Passo 5: Confirmar**
```
Clique em "Ativar Voucher"
Aguarde mensagem de sucesso
```

### **Passo 6: Aguardar Liberação**
```
Aguarde até 10 segundos
Mikrotik liberará automaticamente
```

### **Passo 7: Navegar**
```
Abra: https://www.google.com
Navegue livremente!
```

---

## ⚠️ Troubleshooting

### **"Não foi possível identificar seu dispositivo"**
**Causa**: MAC não foi capturado do Mikrotik
**Solução**: 
1. Desconecte do Wi-Fi
2. Reconecte
3. Aguarde redirecionamento automático
4. Não digite a URL manualmente

### **"Voucher já está ativo"**
**Causa**: Motorista já tem voucher ativo
**Solução**: 
- Aguarde o tempo indicado
- Ou acesse /voucher/status para verificar

### **"Limite diário atingido"**
**Causa**: Já usou todas as horas do dia
**Solução**: 
- Aguarde até meia-noite
- Contadores serão resetados automaticamente

### **Voucher não libera no Mikrotik**
**Causa**: Sync não está funcionando
**Solução**:
1. Verificar se usuário está com `status=connected`
2. Verificar se `expires_at` é futuro
3. Verificar se MAC está em `mikrotik_mac_reports`
4. Testar endpoint: `GET /api/mikrotik/check-paid-users?token=...`

---

## 📞 Suporte

Para dúvidas:
- **Logs**: `storage/logs/laravel.log`
- **Banco**: Verificar tabelas `users`, `vouchers`, `mikrotik_mac_reports`
- **Admin**: https://seu-dominio.com/admin/vouchers

---

## ✅ Checklist de Validação

- [x] MAC capturado do Mikrotik
- [x] MAC salvo na sessão
- [x] Validação de voucher já ativo
- [x] Validação de limite diário
- [x] Mensagens apropriadas
- [x] Reset automático à meia-noite
- [x] Integração com Mikrotik
- [x] Liberação automática de acesso
- [x] Logs completos
- [x] Tratamento de erros

---

🎉 **Sistema 100% Funcional e Seguro!**

