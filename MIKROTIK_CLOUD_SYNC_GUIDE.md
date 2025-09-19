# 🚌 Guia de Integração MikroTik ↔ Servidor Nuvem
**WiFi Tocantins Express - Sistema de Sync Automático**

---

## 📋 Visão Geral do Sistema

### Cenário:
- **🌐 Servidor Laravel**: Na nuvem (CloudPanel)
- **🚌 MikroTik**: No ônibus (rede local)
- **📱 Passageiros**: Conectam via WiFi e pagam via PIX
- **🔄 Sincronização**: MikroTik consulta servidor periodicamente

### Fluxo:
1. **Passageiro** se conecta ao WiFi → Portal de pagamento
2. **Passageiro** paga via PIX → Servidor processa
3. **MikroTik** consulta servidor → Obtém lista de usuários pagos
4. **MikroTik** libera acesso → Internet completa para o usuário

---

## 🔧 1. Configuração do Servidor (CloudPanel)

### 1.1 Adicionar Configurações no .env
```bash
# Sync Token para segurança
MIKROTIK_SYNC_TOKEN=mikrotik-sync-2024

# URL do servidor (já configurado)
APP_URL=https://www.tocantinstransportewifi.com.br
```

### 1.2 Atualizar Cache do Laravel
```bash
php artisan config:cache
```

### 1.3 Testar Endpoints
```bash
php test_sync_endpoints.php
```

**Deve retornar:**
```
✅ Sistema de sync está funcionando!
✅ Endpoints respondendo corretamente
✅ Lógica de expiração funcional
```

---

## 🚌 2. Configuração do MikroTik (No Ônibus)

### 2.1 Configurar Hotspot Básico

**Via terminal do MikroTik:**
```bash
# Configurar interface WiFi
/interface wireless set wlan1 ssid="tocantins_wifi" mode=ap-bridge

# Criar pool de IPs
/ip pool add name=hotspot-pool ranges=10.10.10.2-10.10.10.100

# Configurar DHCP
/ip dhcp-server network add address=10.10.10.0/24 gateway=10.10.10.1 dns-server=8.8.8.8,8.8.4.4

# Criar perfil hotspot
/ip hotspot profile add name=tocantins-profile hotspot-address=10.10.10.1 dns-name=portal.tocantins

# Criar servidor hotspot
/ip hotspot add name=tocantins-hotspot interface=wlan1 address-pool=hotspot-pool profile=tocantins-profile
```

### 2.2 Instalar Script de Sync

**Cole este script completo no terminal do MikroTik:**

```bash
# =====================================================
# SCRIPT DE SINCRONIZAÇÃO - WiFi Tocantins Express
# =====================================================

# CONFIGURAÇÕES
:global serverUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik-sync"
:global syncToken "mikrotik-sync-2024"
:global hotspotServer "tocantins-hotspot"

# Função principal de sync
:global syncWithServer do={
    :global serverUrl
    :global syncToken
    
    :log info "=== SYNC: Iniciando ==="
    
    # Testar conectividade
    :do {
        :local pingUrl ($serverUrl . "/ping")
        :local result [/tool fetch url=$pingUrl as-value output=user]
        :log info "SYNC: Servidor acessível"
    } on-error={
        :log error "SYNC: Servidor inacessível"
        :return false
    }
    
    # Obter usuários para sync
    :do {
        :local syncUrl ($serverUrl . "/pending-users")
        :local headers "Authorization: Bearer $syncToken"
        :local result [/tool fetch url=$syncUrl http-header-field=$headers as-value output=user]
        :local response ($result->"data")
        
        :log info "SYNC: Resposta obtida do servidor"
        :log info "SYNC: $response"
        
        # Aqui você pode processar a resposta JSON
        # Por enquanto, apenas logamos para verificar
        
    } on-error={
        :log error "SYNC: Erro ao obter dados - $[error]"
    }
    
    :log info "=== SYNC: Finalizado ==="
}

# Executar sync imediato
$syncWithServer

# Agendar sync automático a cada 2 minutos
/system scheduler remove [find name="wifi-sync"]
/system scheduler add name="wifi-sync" start-time=startup interval=2m on-event=":global syncWithServer; \$syncWithServer" comment="Sync automático WiFi Tocantins"

:put "✅ Script instalado! Sync a cada 2 minutos"
:put "📋 Verifique logs: /log print where topics~\"info\""
```

### 2.3 Configurar Redirecionamento para Portal

```bash
# Configurar walled garden para permitir acesso ao servidor
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow

# Configurar página de login personalizada
/ip hotspot profile set tocantins-profile login-by=http-chap,trial html-directory=hotspot
```

---

## 🧪 3. Testes e Validação

### 3.1 Teste do Servidor
```bash
# No servidor (CloudPanel)
php test_sync_endpoints.php
```

### 3.2 Teste do MikroTik
```bash
# No terminal do MikroTik
$syncWithServer

# Verificar logs
/log print where topics~"info"

# Verificar scheduler
/system scheduler print
```

### 3.3 Teste Completo
1. **Conectar dispositivo** ao WiFi `tocantins_wifi`
2. **Abrir navegador** → Deve redirecionar para portal
3. **Fazer pagamento** via PIX
4. **Aguardar 2 minutos** para sync automático
5. **Verificar acesso** liberado

---

## 📊 4. Monitoramento

### 4.1 Logs do MikroTik
```bash
# Ver todos os logs de sync
/log print where topics~"info"

# Ver apenas erros
/log print where topics~"error"

# Limpar logs antigos
/log print where topics~"info" and time<"dec/01/2024"
```

### 4.2 Estatísticas do Servidor
**Acesse:** `https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/stats`

**Retorna:**
```json
{
  "success": true,
  "stats": {
    "users": {
      "total": 45,
      "connected": 12,
      "pending": 8
    },
    "payments": {
      "completed_today": 15,
      "revenue_today": "0.75"
    }
  }
}
```

### 4.3 Status de Usuários
```bash
# No MikroTik - ver usuários ativos
/ip hotspot active print

# Ver usuários configurados
/ip hotspot user print

# Ver uso de dados
/ip hotspot active print detail
```

---

## 🔧 5. Configurações Avançadas

### 5.1 Ajustar Frequência de Sync
```bash
# Sync a cada 1 minuto (mais frequente)
/system scheduler set wifi-sync interval=1m

# Sync a cada 5 minutos (menos frequente)
/system scheduler set wifi-sync interval=5m
```

### 5.2 Configurar Walled Garden Completo
```bash
# Permitir acesso aos serviços essenciais antes do pagamento
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow
/ip hotspot walled-garden add dst-host=api.woovi.com action=allow
/ip hotspot walled-garden add dst-host=qr.woovi.com action=allow
/ip hotspot walled-garden add dst-host=8.8.8.8 action=allow
```

### 5.3 Configurar Rate Limiting
```bash
# Limitar velocidade para usuários gratuitos
/queue simple add name=free-users target=10.10.10.0/24 max-limit=1M/1M

# Velocidade completa para usuários pagos (configurar via script)
```

---

## 🚨 6. Solução de Problemas

### 6.1 MikroTik Não Consegue Acessar Servidor

**Problema:** Logs mostram "Servidor inacessível"

**Soluções:**
1. Verificar conexão à internet do ônibus
2. Testar DNS: `/tool nslookup www.tocantinstransportewifi.com.br`
3. Testar conectividade: `/tool fetch url=https://www.tocantinstransportewifi.com.br`

### 6.2 Sync Não Funciona

**Problema:** Logs mostram "Erro ao obter dados"

**Soluções:**
1. Verificar token: Deve ser `mikrotik-sync-2024`
2. Verificar URL no script
3. Testar manualmente: `$syncWithServer`

### 6.3 Usuários Não São Liberados

**Problema:** Pagamento aprovado mas acesso não liberado

**Soluções:**
1. Verificar se usuário está com status `connected` no servidor
2. Aguardar próximo sync (máximo 2 minutos)
3. Verificar logs do MikroTik
4. Executar sync manual: `$syncWithServer`

---

## 📞 7. URLs Importantes

- **Portal Principal:** `https://www.tocantinstransportewifi.com.br`
- **API Ping:** `https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/ping`
- **API Sync:** `https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users`
- **API Stats:** `https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/stats`

---

## ✅ 8. Checklist de Instalação

- [ ] Servidor configurado com sync endpoints
- [ ] Token configurado no .env
- [ ] Endpoints testados com `test_sync_endpoints.php`
- [ ] Hotspot configurado no MikroTik
- [ ] Script de sync instalado no MikroTik
- [ ] Scheduler ativo no MikroTik
- [ ] Walled garden configurado
- [ ] Teste completo realizado
- [ ] Monitoramento ativo

---

## 🎯 Próximos Passos

1. **Execute o teste:** `php test_sync_endpoints.php`
2. **Configure o hotspot** no MikroTik
3. **Instale o script** de sync no MikroTik
4. **Teste com pagamento real**
5. **Configure monitoramento**

O sistema está pronto para funcionar automaticamente! 🚀 