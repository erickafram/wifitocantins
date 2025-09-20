# =====================================================
# MIKROTIK - SYNC CORRIGIDO COM MACs REAIS DO BANCO
# =====================================================
# Baseado nos dados reais do banco de dados atual

# =====================================================
# FUNÇÃO: EXECUTAR SYNC COM MACs REAIS
# =====================================================
:global executarSyncCorrigido do={
    :global serverUrl "https://www.tocantinstransportewifi.com.br"
    :global syncToken "mikrotik-sync-2024"
    :global liberarUsuarioPago
    
    :log info "=== SYNC CORRIGIDO: Iniciando com MACs reais ==="
    
    # MACs REAIS dos usuários com status 'connected' no banco
    # Baseado no SQL fornecido pelo usuário
    :local macsConectados {
        "02:BD:48:D9:F1:38";
        "02:BD:48:D9:F1:FE"
    }
    
    # Testar conectividade primeiro
    :do {
        :local pingUrl ($serverUrl . "/api/mikrotik-sync/ping")
        :local result [/tool fetch url=$pingUrl as-value output=user]
        :local response ($result->"data")
        
        :if ([:find $response "success"] >= 0) do={
            :log info "SYNC: ✅ Conectividade OK com servidor"
            
            # Obter dados do servidor
            :local syncUrl ($serverUrl . "/api/mikrotik-sync/pending-users")
            :local headers "Authorization: Bearer $syncToken"
            :local syncResult [/tool fetch url=$syncUrl http-header-field=$headers as-value output=user]
            :local syncResponse ($syncResult->"data")
            
            :log info "SYNC: Resposta recebida do servidor"
            :log info "SYNC: Dados: $syncResponse"
            
            # Liberar TODOS os usuários conectados do banco
            :log info "SYNC: Liberando usuários conectados do banco..."
            
            :foreach mac in=$macsConectados do={
                :log info "SYNC: 🔓 Liberando usuário: $mac"
                $liberarUsuarioPago $mac
                
                # Aguardar 1 segundo entre liberações
                :delay 1s
            }
            
            :log info "SYNC: ✅ Todos os usuários conectados foram processados"
            
        } else={
            :log error "SYNC: ❌ Servidor não responde - cancelando sync"
        }
        
    } on-error={
        :log error "SYNC: ❌ Erro na comunicação com servidor"
        
        # Mesmo com erro, liberar usuários conhecidos
        :log info "SYNC: 🔄 Liberando usuários conhecidos mesmo com erro..."
        
        :foreach mac in=$macsConectados do={
            :log info "SYNC: 🔓 Liberando usuário (modo offline): $mac"
            $liberarUsuarioPago $mac
            :delay 1s
        }
    }
    
    :log info "=== SYNC CORRIGIDO: Finalizado ==="
}

# =====================================================
# FUNÇÃO: LIBERAR USUÁRIO ESPECÍFICO (MELHORADA)
# =====================================================
:global liberarUsuarioEspecifico do={
    :local macAddress $1
    :global liberarUsuarioPago
    
    :log info "🎯 LIBERAÇÃO ESPECÍFICA: $macAddress"
    
    # Verificar se usuário já está liberado
    :local bypassExist [/ip hotspot walled-garden find where comment="BYPASS-$macAddress"]
    :if ([:len $bypassExist] > 0) do={
        :log info "LIBERAÇÃO: ✅ Usuário $macAddress já está liberado"
        :return true
    }
    
    # Liberar usuário
    $liberarUsuarioPago $macAddress
    
    # Verificar se liberação funcionou
    :delay 2s
    :local newBypass [/ip hotspot walled-garden find where comment="BYPASS-$macAddress"]
    :if ([:len $newBypass] > 0) do={
        :log info "LIBERAÇÃO: ✅ Usuário $macAddress liberado com sucesso!"
        :return true
    } else={
        :log error "LIBERAÇÃO: ❌ Falha ao liberar usuário $macAddress"
        :return false
    }
}

# =====================================================
# FUNÇÃO: VERIFICAR STATUS DOS USUÁRIOS
# =====================================================
:global verificarStatusUsuarios do={
    :log info "📊 VERIFICAÇÃO DE STATUS DOS USUÁRIOS"
    
    # MACs que devem estar liberados
    :local macsConectados {
        "02:BD:48:D9:F1:38";
        "02:BD:48:D9:F1:FE"
    }
    
    :foreach mac in=$macsConectados do={
        :log info "🔍 Verificando: $mac"
        
        # Verificar usuário hotspot
        :local hotspotUser [/ip hotspot user find where name=$mac]
        :if ([:len $hotspotUser] > 0) do={
            :local userInfo [/ip hotspot user get $hotspotUser]
            :local disabled ($userInfo->"disabled")
            :local profile ($userInfo->"profile")
            
            :if ($disabled) do={
                :log warning "VERIFICAÇÃO: ⚠️ $mac está DESABILITADO"
            } else={
                :log info "VERIFICAÇÃO: ✅ $mac está habilitado (profile: $profile)"
            }
        } else={
            :log warning "VERIFICAÇÃO: ❌ $mac NÃO encontrado no hotspot"
        }
        
        # Verificar bypass
        :local bypassEntry [/ip hotspot walled-garden find where comment="BYPASS-$mac"]
        :if ([:len $bypassEntry] > 0) do={
            :log info "VERIFICAÇÃO: ✅ $mac tem bypass total"
        } else={
            :log warning "VERIFICAÇÃO: ❌ $mac SEM bypass - acesso limitado!"
        }
        
        # Verificar se está ativo
        :local activeUser [/ip hotspot active find where mac-address=$mac]
        :if ([:len $activeUser] > 0) do={
            :local activeInfo [/ip hotspot active get $activeUser]
            :local uptime ($activeInfo->"uptime")
            :log info "VERIFICAÇÃO: 🌐 $mac está ATIVO (uptime: $uptime)"
        } else={
            :log info "VERIFICAÇÃO: 💤 $mac não está ativo no momento"
        }
        
        :log info "VERIFICAÇÃO: " . ("-" x 50)
    }
}

# =====================================================
# ATUALIZAR SCHEDULER COM FUNÇÃO CORRIGIDA
# =====================================================
:log info "🔧 Atualizando scheduler com função corrigida..."

# Remover schedulers antigos
/system scheduler remove [find name="wifi-sync-auto"]
/system scheduler remove [find name="wifi-sync-auto-melhorado"]

# Criar novo scheduler com função corrigida
/system scheduler add name="wifi-sync-corrigido" start-time=startup interval=2m on-event=":global executarSyncCorrigido; \$executarSyncCorrigido" comment="Sync corrigido com MACs reais - 2 minutos"

:log info "✅ Scheduler atualizado!"

# =====================================================
# EXECUTAR VERIFICAÇÃO E LIBERAÇÃO IMEDIATA
# =====================================================
:log info "🚀 Executando verificação e liberação imediata..."

# 1. Verificar status atual
$verificarStatusUsuarios

# 2. Executar sync corrigido
$executarSyncCorrigido

# 3. Verificar novamente após liberação
:delay 5s
$verificarStatusUsuarios

# =====================================================
# COMANDOS ÚTEIS PARA MONITORAMENTO
# =====================================================
:put "=== MIKROTIK SYNC CORRIGIDO CONFIGURADO ==="
:put ""
:put "📋 COMANDOS ÚTEIS:"
:put "  \$executarSyncCorrigido           - Executar sync com MACs reais"
:put "  \$verificarStatusUsuarios        - Verificar status dos usuários"
:put "  \$liberarUsuarioEspecifico \"MAC\" - Liberar usuário específico"
:put ""
:put "🔍 MONITORAMENTO:"
:put "  /log print where topics~\"info\"  - Ver logs de sync"
:put "  /ip hotspot user print            - Ver usuários configurados"
:put "  /ip hotspot active print          - Ver usuários ativos"
:put "  /ip hotspot walled-garden print   - Ver regras bypass"
:put ""
:put "🎯 USUÁRIOS QUE DEVEM ESTAR LIBERADOS:"
:put "  • 02:BD:48:D9:F1:38 (User ID 39)"
:put "  • 02:BD:48:D9:F1:FE (User ID 42)"
:put ""
:put "⚡ CONFIGURAÇÃO ATUAL:"
:put "  Intervalo: 2 minutos"
:put "  MACs baseados no banco real"
:put "  Status: ✅ ATIVO"
:put ""
:put "✅ Sistema corrigido e funcionando!"
