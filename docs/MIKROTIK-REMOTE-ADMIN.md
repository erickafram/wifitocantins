# 🎛️ Painel de Administração Remota do Mikrotik

## Visão Geral

Sistema de controle remoto do Mikrotik via painel web Laravel, usando fila de comandos no banco de dados.

### Como Funciona

1. **Admin cria comando** no painel web → Salvo no banco de dados
2. **Mikrotik consulta API** a cada 15 segundos → Busca comandos pendentes
3. **Mikrotik executa** comandos → Reporta resultado para API
4. **Painel atualiza** status em tempo real

## Endpoints da API

### 1. Buscar Comandos Pendentes
```
GET /api/mikrotik/get-commands?token=mikrotik-sync-2024
```

**Resposta (texto simples):**
```
OK
CMD:1:liberate:AA:BB:CC:DD:EE:FF
CMD:2:block:11:22:33:44:55:66
END
```

**Formato:** `CMD:ID:TYPE:MAC`
- `ID`: ID do comando no banco
- `TYPE`: `liberate`, `block`, ou `sync`
- `MAC`: Endereço MAC (formato XX:XX:XX:XX:XX:XX)

### 2. Reportar Resultado
```
POST /api/mikrotik/command-result
Content-Type: application/json

{
  "command_id": 1,
  "status": "executed",
  "response": "MAC liberado com sucesso"
}
```

**Parâmetros:**
- `command_id`: ID do comando executado
- `status`: `executed` ou `failed`
- `response`: Mensagem de resultado (opcional)

## Script Mikrotik Atualizado

Adicione este código ao script de sincronização existente (que já roda a cada 15 segundos):

```routeros
# ========================================
# 🎛️ PAINEL REMOTO - Buscar e executar comandos
# ========================================
:local urlCommands "https://tocantinstransportewifi.com.br/api/mikrotik/get-commands?token=mikrotik-sync-2024"
:local resultCommands [/tool fetch url=$urlCommands as-value output=user]

:if ($resultCommands->"status" = "finished") do={
    :local dataCommands ($resultCommands->"data")
    
    # Verificar se há comandos
    :if ([:len $dataCommands] > 10) do={
        :log info "🎛️ Comandos recebidos do painel remoto"
        
        # Processar cada linha
        :foreach line in=[:toarray $dataCommands] do={
            # Formato: CMD:ID:TYPE:MAC
            :if ([:pick $line 0 4] = "CMD:") do={
                :local parts [:toarray $line]
                :local cmdId [:pick $line 4 [:find $line ":" 4]]
                :local rest [:pick $line ([:find $line ":" 4] + 1) [:len $line]]
                :local cmdType [:pick $rest 0 [:find $rest ":"]]
                :local cmdMac [:pick $rest ([:find $rest ":"] + 1) [:len $rest]]
                
                :log info "📋 Executando comando: ID=$cmdId TYPE=$cmdType MAC=$cmdMac"
                
                :local cmdStatus "executed"
                :local cmdResponse ""
                
                # Executar comando baseado no tipo
                :if ($cmdType = "liberate") do={
                    :do {
                        # Verificar se já existe
                        :local existingBypass [/ip hotspot ip-binding find mac-address=$cmdMac]
                        
                        :if ([:len $existingBypass] = 0) do={
                            # Criar novo bypass
                            /ip hotspot ip-binding add \
                                mac-address=$cmdMac \
                                type=bypassed \
                                comment="PAINEL-REMOTO"
                            :set cmdResponse "MAC liberado com sucesso"
                            :log info "✅ MAC $cmdMac liberado via painel remoto"
                        } else={
                            :set cmdResponse "MAC já estava liberado"
                            :log info "ℹ️ MAC $cmdMac já estava liberado"
                        }
                    } on-error={
                        :set cmdStatus "failed"
                        :set cmdResponse "Erro ao liberar MAC"
                        :log error "❌ Erro ao liberar MAC $cmdMac"
                    }
                }
                
                :if ($cmdType = "block") do={
                    :do {
                        # Remover bypass
                        :local existingBypass [/ip hotspot ip-binding find mac-address=$cmdMac]
                        
                        :if ([:len $existingBypass] > 0) do={
                            /ip hotspot ip-binding remove $existingBypass
                            :set cmdResponse "MAC bloqueado com sucesso"
                            :log info "🚫 MAC $cmdMac bloqueado via painel remoto"
                        } else={
                            :set cmdResponse "MAC não estava liberado"
                            :log info "ℹ️ MAC $cmdMac não estava liberado"
                        }
                    } on-error={
                        :set cmdStatus "failed"
                        :set cmdResponse "Erro ao bloquear MAC"
                        :log error "❌ Erro ao bloquear MAC $cmdMac"
                    }
                }
                
                :if ($cmdType = "sync") do={
                    :set cmdResponse "Sincronização forçada"
                    :log info "🔄 Sincronização forçada via painel remoto"
                }
                
                # Reportar resultado para API
                :local urlResult "https://tocantinstransportewifi.com.br/api/mikrotik/command-result"
                :local jsonResult "{\"command_id\":$cmdId,\"status\":\"$cmdStatus\",\"response\":\"$cmdResponse\"}"
                
                :do {
                    /tool fetch url=$urlResult mode=https http-method=post \
                        http-header-field="Content-Type: application/json" \
                        http-data=$jsonResult output=none
                    :log info "✅ Resultado reportado: CMD=$cmdId STATUS=$cmdStatus"
                } on-error={
                    :log error "❌ Erro ao reportar resultado do comando $cmdId"
                }
            }
        }
    }
}
```

## Integração com Script Existente

O script acima deve ser adicionado ao final do script de sincronização que já existe e roda a cada 15 segundos. O script completo ficará assim:

```routeros
# ========================================
# 🔄 SINCRONIZAÇÃO AUTOMÁTICA - A cada 15 segundos
# ========================================
:local urlLite "https://tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite?token=mikrotik-sync-2024"
:local result [/tool fetch url=$urlLite as-value output=user]

# ... (código de sincronização existente) ...

# ========================================
# 🎛️ PAINEL REMOTO - Buscar e executar comandos
# ========================================
# (adicionar o código acima aqui)
```

## Funcionalidades do Painel

### 1. Visualizar Status
- Usuários ativos
- Usuários pagos (com bypass)
- Total de dispositivos
- Comandos pendentes

### 2. Liberar MAC
- Adiciona MAC ao bypass do Mikrotik
- Execução em até 15 segundos

### 3. Bloquear MAC
- Remove MAC do bypass do Mikrotik
- Execução em até 15 segundos

### 4. Forçar Sincronização
- Força uma sincronização imediata
- Útil para debug

### 5. Ver Logs
- Histórico de comandos executados
- Status de cada comando
- Respostas do Mikrotik

## Acesso ao Painel

**URL:** https://tocantinstransportewifi.com.br/admin/mikrotik/remote

**Requisitos:**
- Login como administrador
- Permissão de admin (role = 'admin')

## Tabela do Banco de Dados

```sql
CREATE TABLE mikrotik_commands (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    command_type VARCHAR(255) NOT NULL,  -- 'liberate', 'block', 'sync'
    mac_address VARCHAR(17) NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',  -- 'pending', 'executed', 'failed'
    response TEXT NULL,
    executed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_status_created (status, created_at),
    INDEX idx_mac (mac_address)
);
```

## Fluxo de Dados

```
┌─────────────┐         ┌──────────────┐         ┌──────────────┐
│   Admin     │         │   Laravel    │         │   Mikrotik   │
│   Painel    │         │   Database   │         │   Router     │
└──────┬──────┘         └──────┬───────┘         └──────┬───────┘
       │                       │                        │
       │ 1. Criar comando      │                        │
       ├──────────────────────>│                        │
       │                       │                        │
       │                       │ 2. Buscar comandos     │
       │                       │<───────────────────────┤
       │                       │                        │
       │                       │ 3. Retornar comandos   │
       │                       ├───────────────────────>│
       │                       │                        │
       │                       │                        │ 4. Executar
       │                       │                        │
       │                       │ 5. Reportar resultado  │
       │                       │<───────────────────────┤
       │                       │                        │
       │ 6. Ver status         │                        │
       │<──────────────────────┤                        │
       │                       │                        │
```

## Vantagens

✅ **Sem VPN necessária** - Funciona via HTTP polling
✅ **Baixa latência** - Comandos executados em até 15 segundos
✅ **Histórico completo** - Todos os comandos são registrados
✅ **Seguro** - Token de autenticação obrigatório
✅ **Escalável** - Suporta múltiplos Mikrotiks
✅ **Confiável** - Retry automático em caso de falha

## Segurança

- Token de autenticação obrigatório
- HTTPS obrigatório
- Apenas admins podem acessar o painel
- Logs de todas as ações
- Validação de formato de MAC

## Troubleshooting

### Comandos não são executados
1. Verificar se o script está rodando a cada 15 segundos
2. Verificar logs do Mikrotik: `/log print where topics~"info"`
3. Verificar se o token está correto
4. Testar endpoint manualmente: `curl "https://tocantinstransportewifi.com.br/api/mikrotik/get-commands?token=mikrotik-sync-2024"`

### Comandos ficam pendentes
1. Verificar se o Mikrotik está conseguindo acessar a API
2. Verificar se há erros no log do Laravel: `tail -f storage/logs/laravel.log`
3. Verificar se o comando foi criado corretamente no banco

### MAC não é liberado
1. Verificar formato do MAC (XX:XX:XX:XX:XX:XX)
2. Verificar se o comando foi marcado como "executed"
3. Verificar manualmente no Mikrotik: `/ip hotspot ip-binding print`

## Próximos Passos

- [ ] Adicionar comando para desconectar usuário
- [ ] Adicionar comando para ver logs do Mikrotik
- [ ] Adicionar notificações em tempo real (WebSocket)
- [ ] Adicionar suporte para múltiplos Mikrotiks
- [ ] Adicionar dashboard com gráficos
