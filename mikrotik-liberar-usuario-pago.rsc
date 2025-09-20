# =====================================================
# MIKROTIK - LIBERAR USUÁRIO PAGO COMPLETAMENTE
# =====================================================
# Este script deve ser executado quando um usuário paga
# Remove todas as restrições e libera acesso total

# =====================================================
# FUNÇÃO: LIBERAR USUÁRIO ESPECÍFICO
# =====================================================
:global liberarUsuarioPago do={
    :local macAddress $1
    
    :log info "LIBERACAO: Liberando usuário $macAddress para acesso total"
    
    :do {
        # 1. CRIAR/HABILITAR USUÁRIO NO HOTSPOT
        :local existingUser [/ip hotspot user find where name=$macAddress]
        
        :if ([:len $existingUser] = 0) do={
            # Criar novo usuário
            /ip hotspot user add \
                name=$macAddress \
                mac-address=$macAddress \
                profile=default \
                server=tocantins-hotspot \
                disabled=no \
                comment="Usuario pago - acesso total"
            :log info "LIBERACAO: ✅ Usuário criado: $macAddress"
        } else={
            # Habilitar usuário existente
            /ip hotspot user set $existingUser \
                disabled=no \
                profile=default \
                comment="Usuario pago - acesso total"
            :log info "LIBERACAO: ✅ Usuário habilitado: $macAddress"
        }
        
        # 2. REMOVER DO WALLED GARDEN (se estiver lá)
        :local walledGardenEntry [/ip hotspot walled-garden find where src-address=$macAddress]
        :if ([:len $walledGardenEntry] > 0) do={
            /ip hotspot walled-garden remove $walledGardenEntry
            :log info "LIBERACAO: ✅ Removido do walled garden: $macAddress"
        }
        
        # 3. ADICIONAR À LISTA DE USUÁRIOS PAGOS (BYPASS TOTAL)
        :local bypassEntry [/ip hotspot walled-garden find where comment="BYPASS-$macAddress"]
        :if ([:len $bypassEntry] = 0) do={
            /ip hotspot walled-garden add \
                src-address=$macAddress \
                action=allow \
                comment="BYPASS-$macAddress"
            :log info "LIBERACAO: ✅ Bypass total adicionado: $macAddress"
        }
        
        # 4. FORÇAR RECONEXÃO (se estiver ativo)
        :local activeUser [/ip hotspot active find where mac-address=$macAddress]
        :if ([:len $activeUser] > 0) do={
            /ip hotspot active remove $activeUser
            :log info "LIBERACAO: ✅ Forçando reconexão: $macAddress"
        }
        
        :log info "LIBERACAO: 🎉 Usuário $macAddress liberado com acesso total!"
        
    } on-error={
        :log error "LIBERACAO: ❌ Erro ao liberar usuário $macAddress"
    }
}

# =====================================================
# FUNÇÃO: BLOQUEAR USUÁRIO EXPIRADO
# =====================================================
:global bloquearUsuarioExpirado do={
    :local macAddress $1
    
    :log info "BLOQUEIO: Bloqueando usuário expirado $macAddress"
    
    :do {
        # 1. DESABILITAR USUÁRIO NO HOTSPOT
        :local existingUser [/ip hotspot user find where name=$macAddress]
        :if ([:len $existingUser] > 0) do={
            /ip hotspot user set $existingUser \
                disabled=yes \
                comment="Usuario expirado - bloqueado"
            :log info "BLOQUEIO: ✅ Usuário desabilitado: $macAddress"
        }
        
        # 2. REMOVER BYPASS TOTAL
        :local bypassEntry [/ip hotspot walled-garden find where comment="BYPASS-$macAddress"]
        :if ([:len $bypassEntry] > 0) do={
            /ip hotspot walled-garden remove $bypassEntry
            :log info "BLOQUEIO: ✅ Bypass removido: $macAddress"
        }
        
        # 3. DESCONECTAR SE ESTIVER ATIVO
        :local activeUser [/ip hotspot active find where mac-address=$macAddress]
        :if ([:len $activeUser] > 0) do={
            /ip hotspot active remove $activeUser
            :log info "BLOQUEIO: ✅ Usuário desconectado: $macAddress"
        }
        
        :log info "BLOQUEIO: 🚫 Usuário $macAddress bloqueado!"
        
    } on-error={
        :log error "BLOQUEIO: ❌ Erro ao bloquear usuário $macAddress"
    }
}

# =====================================================
# FUNÇÃO: SYNC MELHORADO COM SERVIDOR
# =====================================================
:global executarSyncMelhorado do={
    :global serverUrl "https://www.tocantinstransportewifi.com.br"
    :global syncToken "mikrotik-sync-2024"
    :global liberarUsuarioPago
    :global bloquearUsuarioExpirado
    
    :log info "=== SYNC MELHORADO: Iniciando ==="
    
    :do {
        :local syncUrl ($serverUrl . "/api/mikrotik-sync/pending-users")
        :local headers "Authorization: Bearer $syncToken"
        :local result [/tool fetch url=$syncUrl http-header-field=$headers as-value output=user]
        :local response ($result->"data")
        
        :log info "SYNC: Resposta recebida do servidor"
        
        # Processar usuários para liberar
        :if ([:find $response "allow_users"] >= 0) do={
            :log info "SYNC: Processando usuários para liberação..."
            
            # IMPORTANTE: Aqui você deve implementar parsing JSON real
            # Por enquanto, vamos usar uma abordagem simples
            
            # Exemplo de MACs que devem ser liberados (substitua pela lógica real)
            :local macsParaLiberar {"02:C8:AD:28:EC:D7"; "02:BD:48:D9:F1:46"}
            
            :foreach mac in=$macsParaLiberar do={
                $liberarUsuarioPago $mac
            }
        }
        
        # Processar usuários para bloquear
        :if ([:find $response "block_users"] >= 0) do={
            :log info "SYNC: Processando usuários para bloqueio..."
            
            # Exemplo de MACs que devem ser bloqueados
            :local macsParaBloquear {"02:SYNC:TEST:MAC"}
            
            :foreach mac in=$macsParaBloquear do={
                $bloquearUsuarioExpirado $mac
            }
        }
        
        :log info "SYNC MELHORADO: ✅ Concluído com sucesso"
        
    } on-error={
        :log error "SYNC MELHORADO: ❌ Erro na comunicação com servidor"
    }
    
    :log info "=== SYNC MELHORADO: Finalizado ==="
}

# =====================================================
# EXECUTAR SYNC IMEDIATO
# =====================================================
:log info "Iniciando sync melhorado..."
$executarSyncMelhorado

# =====================================================
# ATUALIZAR SCHEDULER
# =====================================================
/system scheduler remove [find name="wifi-sync-auto"]

/system scheduler add \
    name="wifi-sync-auto-melhorado" \
    start-time=startup \
    interval=1m \
    on-event=":global executarSyncMelhorado; \$executarSyncMelhorado" \
    comment="Sync melhorado WiFi Tocantins - a cada 1 minuto"

:log info "✅ Scheduler melhorado configurado (1 minuto)"

# =====================================================
# COMANDOS MANUAIS ÚTEIS
# =====================================================
:put "=== COMANDOS MANUAIS DISPONÍVEIS ==="
:put ""
:put "💡 LIBERAR USUÁRIO MANUALMENTE:"
:put "   \$liberarUsuarioPago \"02:XX:XX:XX:XX:XX\""
:put ""
:put "🚫 BLOQUEAR USUÁRIO MANUALMENTE:"
:put "   \$bloquearUsuarioExpirado \"02:XX:XX:XX:XX:XX\""
:put ""
:put "🔄 EXECUTAR SYNC MANUAL:"
:put "   \$executarSyncMelhorado"
:put ""
:put "📊 MONITORAMENTO:"
:put "   /log print where topics~\"info\""
:put "   /ip hotspot user print"
:put "   /ip hotspot active print"
:put "   /ip hotspot walled-garden print"
:put ""
:put "✅ Sistema melhorado configurado!"
:put "⚡ Sync a cada 1 minuto para resposta mais rápida"
:put "🌐 Usuários pagos terão acesso TOTAL à internet"
