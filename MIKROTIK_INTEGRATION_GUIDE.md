# 🚀 Guia Completo de Integração MikroTik + Sistema de Pagamento

Este guia explica como configurar completamente a integração entre o MikroTik e o sistema de pagamento WiFi Tocantins Express.

## 📋 Pré-requisitos

- ✅ MikroTik RouterOS (versão 6.40+)
- ✅ Sistema Laravel funcionando na nuvem
- ✅ Pagamentos PIX já configurados (Woovi/Santander)
- ✅ Acesso administrativo ao MikroTik (Winbox/SSH/Web)

## 🔧 Passo 1: Configuração do Ambiente Laravel

### 1.1 Variáveis de Ambiente (.env)

Adicione/configure estas variáveis no seu arquivo `.env`:

```bash
# Configuração MikroTik
MIKROTIK_HOST=10.10.10.1
MIKROTIK_USERNAME=api-tocantins
MIKROTIK_PASSWORD=TocantinsWiFi2024!
MIKROTIK_PORT=8728
MIKROTIK_API_ENABLED=true
MIKROTIK_HOTSPOT_SERVER=tocantins-hotspot
```

### 1.2 Verificar Configurações

Execute o comando para verificar se as configurações estão corretas:

```bash
php artisan config:cache
php artisan config:clear
```

## 🏗️ Passo 2: Configuração do MikroTik

### 2.1 Aplicar Script de Configuração

1. **Conecte-se ao MikroTik** via Winbox, SSH ou Terminal Web
2. **Copie todo o conteúdo** do arquivo `mikrotik-hotspot-integrado.rsc`
3. **Cole no Terminal** do MikroTik
4. **Execute** o script completo

⚠️ **ATENÇÃO**: O script vai reconfigurar o MikroTik. Faça backup antes!

### 2.2 Verificação da Configuração

Após aplicar o script, verifique se tudo foi criado corretamente:

```bash
# No terminal do MikroTik
/ip hotspot print
/ip hotspot user print
/user print
/ip service print
```

Você deve ver:
- ✅ Hotspot `tocantins-hotspot` criado
- ✅ Usuário `api-tocantins` criado
- ✅ API habilitada na porta 8728

## 🔗 Passo 3: Teste da Integração

### 3.1 Executar Teste Automatizado

No servidor Laravel, execute:

```bash
php test_mikrotik_integration.php
```

### 3.2 Teste Manual

1. **Conecte um dispositivo** à rede WiFi `Tocantins_WiFi`
2. **Acesse qualquer site** - deve redirecionar para o portal
3. **Faça um pagamento** via PIX
4. **Após aprovação**, o dispositivo deve ter acesso completo à internet

## 📱 Passo 4: Fluxo de Funcionamento

### 4.1 Como Funciona

1. **Usuário conecta** ao WiFi → MikroTik detecta MAC address
2. **Acessa internet** → Redirecionado para portal de pagamento
3. **Faz pagamento PIX** → Sistema processa via Woovi/Santander
4. **Webhook confirma** pagamento → Laravel atualiza banco de dados
5. **Sistema libera** usuário → Chama API MikroTik para criar/ativar usuário hotspot
6. **Usuário tem acesso** completo à internet por 24h

### 4.2 Estrutura Técnica

```
Dispositivo → MikroTik Hotspot → Portal Laravel → Gateway PIX
     ↑                                                    ↓
     ←←←←←← API RouterOS ←←←←← Webhook ←←←←← Confirmação
```

## 🛠️ Passo 5: Resolução de Problemas

### 5.1 Problemas Comuns

**❌ Erro: "Erro ao conectar ao MikroTik"**
- Verifique se o IP `10.10.10.1` está correto
- Confirme se a API está habilitada: `/ip service print`
- Teste conectividade: `ping 10.10.10.1`

**❌ Erro: "Falha na autenticação"**
- Verifique usuário/senha no MikroTik: `/user print`
- Confirme as credenciais no `.env`

**❌ Pagamento aprovado mas usuário não liberado**
- Verifique logs: `tail -f storage/logs/laravel.log`
- Teste API manualmente: `/ip hotspot user print`

### 5.2 Comandos de Diagnóstico

**No MikroTik:**
```bash
# Ver usuários do hotspot
/ip hotspot user print

# Ver usuários ativos
/ip hotspot active print

# Ver logs
/log print where topics~"hotspot"

# Testar API
/ip hotspot user add name=teste-manual mac-address=00:11:22:33:44:55
```

**No Laravel:**
```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Testar conexão
php test_mikrotik_integration.php

# Ver pagamentos
php artisan tinker
>>> App\Models\Payment::latest()->take(5)->get()
```

## 🔒 Passo 6: Segurança

### 6.1 Configurações de Segurança

- ✅ API MikroTik limitada à rede local
- ✅ Usuário API com permissões específicas
- ✅ Timeout de sessão configurado (24h)
- ✅ Limpeza automática de usuários expirados

### 6.2 Monitoramento

O sistema inclui:
- 📊 Logs detalhados de conexões
- 🧹 Limpeza automática diária (3h da manhã)
- 💾 Backup automático diário (2h da manhã)
- 📈 Métricas de uso por dispositivo

## 📊 Passo 7: Monitoramento e Manutenção

### 7.1 Verificações Diárias

```bash
# Verificar usuários ativos
/ip hotspot active print

# Ver estatísticas
/ip hotspot print stats

# Verificar logs de erro
/log print where topics~"error"
```

### 7.2 Manutenção Mensal

- 🔄 Atualizar RouterOS se necessário
- 🧹 Revisar logs e limpar antigos
- 📊 Analisar estatísticas de uso
- 🔐 Verificar segurança e backups

## 🎯 Passo 8: Otimizações Avançadas

### 8.1 Configurações de Performance

```bash
# Ajustar limites de banda por usuário
/ip hotspot user profile set default rate-limit=5M/1M

# Configurar QoS avançado
/queue tree add name=hotspot-users parent=bridge-hotspot
```

### 8.2 Funcionalidades Extras

- 📱 Portal customizado com logo da empresa
- 💳 Múltiplos gateways de pagamento
- 🎫 Sistema de vouchers
- 👥 Diferentes perfis de usuário
- 📊 Dashboard administrativo

## 📞 Suporte

### 8.1 Logs Importantes

**Laravel:** `storage/logs/laravel.log`
**MikroTik:** `/log print`
**Sistema:** `/system logging print`

### 8.2 Contatos

- 📧 Suporte técnico: suporte@wifitocantins.com.br
- 📱 WhatsApp: (63) 99999-9999
- 🌐 Portal: https://tocantinstransportewifi.com.br

---

## ✅ Checklist Final

- [ ] MikroTik configurado com script
- [ ] Laravel com variáveis corretas
- [ ] Teste de integração executado com sucesso
- [ ] Teste real com dispositivo móvel
- [ ] Pagamento PIX funcionando
- [ ] Liberação automática após pagamento
- [ ] Logs sendo gerados corretamente
- [ ] Backup e limpeza automática configurados

**🎉 Parabéns! Sua integração MikroTik + Sistema de Pagamento está completa e funcionando!**

---

*Última atualização: Setembro 2025*
*Versão do sistema: WiFi Tocantins Express v1.0* 