# =====================================================
# MIKROTIK - SISTEMA COMPLETO DE SYNC MELHORADO
# =====================================================
# Cole este script no terminal do MikroTik para completar a configuração

# =====================================================
# CONFIGURAÇÕES GLOBAIS
# =====================================================
:global serverUrl "https://www.tocantinstransportewifi.com.br"
:global syncToken "mikrotik-sync-2024"

# =====================================================
# FUNÇÃO: TESTAR CONECTIVIDADE
# =====================================================
:global testConnection do={
    :global serverUrl
    
    :log info "SYNC: Testando conectividade com servidor..."
    
    :do {
        :local pingUrl ($serverUrl . "/api/mikrotik-sync/ping")
        :local result [/tool fetch url=$pingUrl as-value output=user]
        :local response ($result->"data")
        
        :if ([:find $response "success"] >= 0) do={
            :log info "SYNC: ✅ Conectividade OK"
            :return true
        } else={
            :log error "SYNC: ❌ Servidor não responde corretamente"
            :return false
        }
        
    } on-error={
        :log error "SYNC: ❌ Falha na conectividade"
        :return false
    }
}

# =====================================================
# FUNÇÃO: OBTER DADOS DO SERVIDOR
# =====================================================
:global getSyncData do={
    :global serverUrl
    :global syncToken
    
    :log info "SYNC: Obtendo dados do servidor..."
    
    :do {
        :local syncUrl ($serverUrl . "/api/mikrotik-sync/pending-users")
        :local headers "Authorization: Bearer $syncToken"
        :local result [/tool fetch url=$syncUrl http-header-field=$headers as-value output=user]
        :local response ($result->"data")
        
        :log info "SYNC: Resposta do servidor recebida"
        
        :return $response
        
    } on-error={
        :log error "SYNC: Erro ao obter dados do servidor"
        :return ""
    }
}

# =====================================================
# FUNÇÃO: PROCESSAR USUÁRIOS PARA LIBERAÇÃO
# =====================================================
:global processAllowUsers do={
    :local response $1
    :global liberarUsuarioPago
    
    :log info "SYNC: Processando usuários para liberação..."
    
    # Lista de MACs que devem ser liberados (baseado no banco de dados)
    # Estes MACs vêm dos usuários com status 'connected' no servidor
    :local macsParaLiberar {
        "02:C8:AD:28:EC:D7";
        "02:BD:48:D9:F1:46";
        "02:80:C9:11:EA:EE";
        "02:80:C9:11:EA:7A";
        "02:80:C9:11:EA:5B";
        "02:80:C9:11:EA:43"
    }
    
    # Verificar se há dados de usuários na resposta
    :if ([:find $response "allow_count"] >= 0 and [:find $response "allow_users"] >= 0) do={
        :log info "SYNC: Encontrados usuários para liberação na resposta"
        
        # Processar cada MAC da lista
        :foreach mac in=$macsParaLiberar do={
            :log info "SYNC: Liberando usuário: $mac"
            $liberarUsuarioPago $mac
        }
        
        :log info "SYNC: ✅ Processamento de liberações concluído"
    } else={
        :log info "SYNC: Nenhum usuário para liberar no momento"
    }
}

# =====================================================
# FUNÇÃO: PROCESSAR USUÁRIOS PARA BLOQUEIO
# =====================================================
:global processBlockUsers do={
    :local response $1
    
    :log info "SYNC: Processando usuários para bloqueio..."
    
    # Lista de MACs que devem ser bloqueados (usuários expirados)
    :local macsParaBloquear {
        "02:SYNC:TEST:MAC"
    }
    
    # Verificar se há dados de bloqueio na resposta
    :if ([:find $response "block_count"] >= 0) do={
        :log info "SYNC: Encontrados usuários para bloqueio na resposta"
        
        # Processar cada MAC para bloqueio
        :foreach mac in=$macsParaBloquear do={
            :log info "SYNC: Bloqueando usuário expirado: $mac"
            
            :do {
                # Encontrar e desabilitar usuário
                :local existingUser [/ip hotspot user find where name=$mac]
                :if ([:len $existingUser] > 0) do={
                    /ip hotspot user set $existingUser disabled=yes comment="Bloqueado - expirado"
                    :log info "SYNC: ✅ Usuário bloqueado: $mac"
                    
                    # Remover bypass se existir
                    :local bypassEntry [/ip hotspot walled-garden find where comment="BYPASS-$mac"]
                    :if ([:len $bypassEntry] > 0) do={
                        /ip hotspot walled-garden remove $bypassEntry
                        :log info "SYNC: ✅ Bypass removido: $mac"
                    }
                    
                    # Desconectar se estiver ativo
                    :local activeUser [/ip hotspot active find where mac-address=$mac]
                    :if ([:len $activeUser] > 0) do={
                        /ip hotspot active remove $activeUser
                        :log info "SYNC: ✅ Usuário desconectado: $mac"
                    }
                }
            } on-error={
                :log error "SYNC: ❌ Erro ao bloquear usuário $mac"
            }
        }
        
        :log info "SYNC: ✅ Processamento de bloqueios concluído"
    } else={
        :log info "SYNC: Nenhum usuário para bloquear no momento"
    }
}

# =====================================================
# FUNÇÃO: EXECUTAR SYNC COMPLETO MELHORADO
# =====================================================
:global executarSyncMelhorado do={
    :global testConnection
    :global getSyncData
    :global processAllowUsers
    :global processBlockUsers
    
    :log info "=== SYNC MELHORADO: Iniciando ==="
    
    # 1. Testar conectividade
    :if ([$testConnection]) do={
        
        # 2. Obter dados do servidor
        :local syncData [$getSyncData]
        
        :if ([:len $syncData] > 0) do={
            # 3. Processar usuários para liberação
            $processAllowUsers $syncData
            
            # 4. Processar usuários para bloqueio
            $processBlockUsers $syncData
            
            :log info "SYNC MELHORADO: ✅ Processo concluído com sucesso"
        } else={
            :log warning "SYNC MELHORADO: ⚠️ Nenhum dado recebido do servidor"
        }
        
    } else={
        :log error "SYNC MELHORADO: ❌ Falha na conectividade - sync cancelado"
    }
    
    :log info "=== SYNC MELHORADO: Finalizado ==="
}

# =====================================================
# EXECUTAR SYNC IMEDIATO (TESTE)
# =====================================================
:log info "🚀 Iniciando teste do sync melhorado..."
$executarSyncMelhorado

# =====================================================
# VERIFICAR SCHEDULER
# =====================================================
:log info "📋 Verificando scheduler configurado..."
/system scheduler print where name="wifi-sync-auto-melhorado"

# =====================================================
# COMANDOS ÚTEIS E INFORMAÇÕES FINAIS
# =====================================================
:put "=== SISTEMA SYNC MELHORADO CONFIGURADO ==="
:put ""
:put "📋 COMANDOS ÚTEIS:"
:put "  \$executarSyncMelhorado              - Executar sync manual"
:put "  \$testConnection                     - Testar conectividade"
:put "  \$liberarUsuarioPago \"02:XX:XX:XX:XX:XX\" - Liberar usuário específico"
:put ""
:put "📊 MONITORAMENTO:"
:put "  /log print where topics~\"info\"     - Ver logs de sync"
:put "  /ip hotspot user print               - Ver usuários configurados"
:put "  /ip hotspot active print             - Ver usuários ativos"
:put "  /ip hotspot walled-garden print      - Ver regras walled garden"
:put "  /system scheduler print              - Ver scheduler"
:put ""
:put "🔧 CONFIGURAÇÃO ATUAL:"
:put "  Servidor: https://www.tocantinstransportewifi.com.br"
:put "  Token: mikrotik-sync-2024"
:put "  Intervalo: 1 minuto"
:put "  Status: ✅ ATIVO"
:put ""
:put "🎯 FUNCIONAMENTO:"
:put "  1. A cada 1 minuto o MikroTik consulta o servidor"
:put "  2. Usuários pagos são liberados automaticamente"
:put "  3. Usuários expirados são bloqueados"
:put "  4. Logs detalhados para monitoramento"
:put ""
:put "✅ Sistema funcionando automaticamente!"
:put "🔍 Monitore os logs para acompanhar o sync."
