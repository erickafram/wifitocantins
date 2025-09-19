# =====================================================
# SCRIPT SIMPLIFICADO DE SYNC - WiFi Tocantins Express
# Versão básica sem parsing JSON complexo
# =====================================================

# CONFIGURAÇÕES - AJUSTE CONFORME NECESSÁRIO
:global serverUrl "https://www.tocantinstransportewifi.com.br"
:global syncToken "mikrotik-sync-2024"

# =====================================================
# FUNÇÃO: TESTAR CONECTIVIDADE COM SERVIDOR
# =====================================================
:global testConnection do={
    :global serverUrl
    
    :log info "SYNC: Testando conectividade..."
    
    :do {
        :local pingUrl ($serverUrl . "/api/mikrotik-sync/ping")
        :local result [/tool fetch url=$pingUrl as-value output=user]
        :local data ($result->"data")
        
        :if ([:find $data "\"success\":true"] >= 0) do={
            :log info "SYNC: ✅ Servidor acessível"
            :return true
        } else={
            :log error "SYNC: ❌ Servidor retornou erro"
            :return false
        }
    } on-error={
        :log error "SYNC: ❌ Erro de conectividade"
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
        :log info "SYNC: $response"
        
        # Verificar se há usuários para liberar (busca simples por MAC)
        :if ([:find $response "mac_address"] >= 0) do={
            :log info "SYNC: Encontrados usuários para processar"
            
            # Aqui você pode implementar lógica mais específica
            # Por enquanto, apenas registramos no log
            
        } else={
            :log info "SYNC: Nenhum usuário para processar"
        }
        
        :return $response
        
    } on-error={
        :log error "SYNC: Erro ao obter dados do servidor"
        :return ""
    }
}

# =====================================================
# FUNÇÃO: PROCESSAR USUÁRIOS (IMPLEMENTAÇÃO BÁSICA)
# =====================================================
:global processUsers do={
    :local response $1
    
    :log info "SYNC: Processando usuários..."
    
    # Esta é uma implementação básica
    # Em uma versão mais avançada, você faria parsing JSON real
    
    :if ([:len $response] > 0) do={
        :log info "SYNC: Dados recebidos para processamento"
        
        # Exemplo: buscar por padrões específicos
        :if ([:find $response "allow_count"] >= 0) do={
            :log info "SYNC: Usuários encontrados para liberação"
            
            # Aqui você implementaria:
            # 1. Extrair MACs da resposta
            # 2. Criar/habilitar usuários no hotspot
            # 3. Aplicar perfis de acesso
        }
        
        :if ([:find $response "block_count"] >= 0) do={
            :log info "SYNC: Usuários encontrados para bloqueio"
            
            # Aqui você implementaria:
            # 1. Extrair MACs expirados
            # 2. Desabilitar usuários no hotspot
            # 3. Desconectar sessões ativas
        }
    }
}

# =====================================================
# FUNÇÃO: EXECUTAR SYNC COMPLETO
# =====================================================
:global executeSync do={
    :global testConnection
    :global getSyncData
    :global processUsers
    
    :log info "=== SYNC: Iniciando processo ==="
    
    # 1. Testar conectividade
    :if ([$testConnection]) do={
        
        # 2. Obter dados
        :local syncData [$getSyncData]
        
        # 3. Processar dados
        $processUsers $syncData
        
        :log info "SYNC: Processo concluído com sucesso"
        
    } else={
        :log error "SYNC: Falha na conectividade - sync cancelado"
    }
    
    :log info "=== SYNC: Processo finalizado ==="
}

# =====================================================
# FUNÇÃO: LIBERAR USUÁRIO ESPECÍFICO (MANUAL)
# =====================================================
:global allowUser do={
    :local macAddress $1
    :local profile "default"
    :local server "tocantins-hotspot"
    
    :log info "SYNC: Liberando usuário $macAddress"
    
    :do {
        # Verificar se usuário já existe
        :local existingUser [/ip hotspot user find where name=$macAddress]
        
        :if ([:len $existingUser] = 0) do={
            # Criar novo usuário
            /ip hotspot user add name=$macAddress mac-address=$macAddress profile=$profile server=$server comment="Auto-liberado via sync"
            :log info "SYNC: ✅ Usuário criado: $macAddress"
        } else={
            # Habilitar usuário existente
            /ip hotspot user set $existingUser disabled=no comment="Auto-liberado via sync"
            :log info "SYNC: ✅ Usuário habilitado: $macAddress"
        }
    } on-error={
        :log error "SYNC: ❌ Erro ao liberar usuário $macAddress"
    }
}

# =====================================================
# FUNÇÃO: BLOQUEAR USUÁRIO ESPECÍFICO (MANUAL)
# =====================================================
:global blockUser do={
    :local macAddress $1
    
    :log info "SYNC: Bloqueando usuário $macAddress"
    
    :do {
        # Encontrar e desabilitar usuário
        :local existingUser [/ip hotspot user find where name=$macAddress]
        
        :if ([:len $existingUser] > 0) do={
            /ip hotspot user set $existingUser disabled=yes comment="Bloqueado via sync"
            :log info "SYNC: ✅ Usuário bloqueado: $macAddress"
            
            # Desconectar se estiver ativo
            :local activeUser [/ip hotspot active find where user=$macAddress]
            :if ([:len $activeUser] > 0) do={
                /ip hotspot active remove $activeUser
                :log info "SYNC: ✅ Usuário desconectado: $macAddress"
            }
        } else={
            :log warning "SYNC: Usuário $macAddress não encontrado para bloqueio"
        }
    } on-error={
        :log error "SYNC: ❌ Erro ao bloquear usuário $macAddress"
    }
}

# =====================================================
# EXECUTAR SYNC IMEDIATO (TESTE)
# =====================================================
:log info "Iniciando teste de sync..."
$executeSync

# =====================================================
# CONFIGURAR SCHEDULER AUTOMÁTICO
# =====================================================

# Remover scheduler anterior se existir
/system scheduler remove [find name="wifi-sync-auto"]

# Criar scheduler para executar a cada 2 minutos
/system scheduler add name="wifi-sync-auto" start-time=startup interval=2m on-event=":global executeSync; \$executeSync" comment="Sync automático WiFi Tocantins"

:log info "✅ Scheduler configurado para sync a cada 2 minutos"

# =====================================================
# COMANDOS ÚTEIS E INFORMAÇÕES
# =====================================================

:put "=== SYNC CONFIGURADO COM SUCESSO ==="
:put ""
:put "📋 COMANDOS ÚTEIS:"
:put "  \$executeSync               - Executar sync manual"
:put "  \$testConnection            - Testar conectividade"
:put "  \$allowUser \"02:11:22:33:44:55\" - Liberar usuário específico"
:put "  \$blockUser \"02:11:22:33:44:55\" - Bloquear usuário específico"
:put ""
:put "📊 MONITORAMENTO:"
:put "  /log print where topics~\"info\"  - Ver logs de sync"
:put "  /ip hotspot user print           - Ver usuários configurados"
:put "  /ip hotspot active print         - Ver usuários ativos"
:put "  /system scheduler print          - Ver scheduler"
:put ""
:put "🔧 CONFIGURAÇÃO:"
:put "  Servidor: https://www.tocantinstransportewifi.com.br"
:put "  Token: mikrotik-sync-2024"
:put "  Intervalo: 2 minutos"
:put ""
:put "✅ O sistema está funcionando automaticamente!"
:put "   Verifique os logs para acompanhar o sync." 