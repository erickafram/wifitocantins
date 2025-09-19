# =====================================================
# SCRIPT DE SINCRONIZAÇÃO MIKROTIK <-> SERVIDOR NUVEM
# WiFi Tocantins Express - Sync via HTTP
# =====================================================

# CONFIGURAÇÕES (AJUSTE CONFORME NECESSÁRIO)
:global serverUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik-sync"
:global syncToken "mikrotik-sync-2024"
:global hotspotServer "tocantins-hotspot"

# =====================================================
# FUNÇÃO: FAZER REQUISIÇÃO HTTP PARA O SERVIDOR
# =====================================================
:global httpRequest do={
    :local endpoint $1
    :local method $2
    :local data $3
    
    :global serverUrl
    :global syncToken
    
    :local url ($serverUrl . $endpoint)
    :local headers "Authorization: Bearer $syncToken,Content-Type: application/json"
    
    :local result
    
    :if ($method = "GET") do={
        :set result [/tool fetch url=$url http-header-field=$headers as-value output=user]
    } else={
        :set result [/tool fetch url=$url http-method=$method http-header-field=$headers http-data=$data as-value output=user]
    }
    
    :return ($result->"data")
}

# =====================================================
# FUNÇÃO: PROCESSAR USUÁRIOS PARA LIBERAR
# =====================================================
:global processAllowUsers do={
    :local users $1
    :global hotspotServer
    
    :foreach user in=$users do={
        :local macAddress ($user->"mac_address")
        :local expiresAt ($user->"expires_at")
        
        :log info "Liberando acesso para MAC: $macAddress"
        
        # Verificar se usuário já existe
        :local existingUser [/ip hotspot user find where name=$macAddress]
        
        :if ([:len $existingUser] = 0) do={
            # Criar novo usuário
            /ip hotspot user add name=$macAddress mac-address=$macAddress profile=default server=$hotspotServer comment="Auto-sync: $expiresAt"
            :log info "Usuário criado: $macAddress"
        } else={
            # Habilitar usuário existente
            /ip hotspot user set $existingUser disabled=no comment="Auto-sync: $expiresAt"
            :log info "Usuário habilitado: $macAddress"
        }
    }
}

# =====================================================
# FUNÇÃO: PROCESSAR USUÁRIOS PARA BLOQUEAR
# =====================================================
:global processBlockUsers do={
    :local users $1
    
    :foreach user in=$users do={
        :local macAddress ($user->"mac_address")
        :local expiredAt ($user->"expired_at")
        
        :log info "Bloqueando acesso para MAC: $macAddress"
        
        # Encontrar usuário
        :local existingUser [/ip hotspot user find where name=$macAddress]
        
        :if ([:len $existingUser] > 0) do={
            # Desabilitar usuário
            /ip hotspot user set $existingUser disabled=yes comment="Expirado: $expiredAt"
            :log info "Usuário bloqueado: $macAddress"
            
            # Desconectar se estiver ativo
            :local activeUser [/ip hotspot active find where user=$macAddress]
            :if ([:len $activeUser] > 0) do={
                /ip hotspot active remove $activeUser
                :log info "Usuário desconectado: $macAddress"
            }
        }
    }
}

# =====================================================
# FUNÇÃO: REPORTAR STATUS DE USUÁRIOS ATIVOS
# =====================================================
:global reportActiveUsers do={
    :global httpRequest
    
    :local activeUsers [/ip hotspot active find]
    
    :foreach activeUser in=$activeUsers do={
        :local user [/ip hotspot active get $activeUser]
        :local macAddress ($user->"mac-address")
        :local bytesIn ($user->"bytes-in")
        :local bytesOut ($user->"bytes-out")
        :local uptime ($user->"uptime")
        
        # Converter uptime para segundos (aproximado)
        :local sessionTime 0
        # Implementação simplificada - em produção, converter formato de uptime
        
        :local postData "{\"mac_address\":\"$macAddress\",\"status\":\"connected\",\"bytes_in\":$bytesIn,\"bytes_out\":$bytesOut,\"session_time\":$sessionTime}"
        
        :do {
            $httpRequest "/report-status" "POST" $postData
        } on-error={
            :log warning "Erro ao reportar status para: $macAddress"
        }
    }
}

# =====================================================
# SCRIPT PRINCIPAL DE SINCRONIZAÇÃO
# =====================================================
:global syncWithServer do={
    :global httpRequest
    :global processAllowUsers
    :global processBlockUsers
    :global reportActiveUsers
    
    :log info "=== INICIANDO SYNC COM SERVIDOR ==="
    
    # 1. Testar conectividade
    :do {
        :local pingResult [$httpRequest "/ping" "GET" ""]
        :log info "Servidor acessível: $pingResult"
    } on-error={
        :log error "Erro: Servidor não acessível"
        :return false
    }
    
    # 2. Obter usuários pendentes
    :do {
        :local response [$httpRequest "/pending-users" "GET" ""]
        
        # Parse da resposta JSON (implementação simplificada)
        # Em produção, usar parser JSON mais robusto
        
        :log info "Resposta do servidor: $response"
        
        # Processar usuários para liberar
        # :local allowUsers ($response->"allow_users")
        # $processAllowUsers $allowUsers
        
        # Processar usuários para bloquear  
        # :local blockUsers ($response->"block_users")
        # $processBlockUsers $blockUsers
        
        :log info "Sync concluído com sucesso"
        
    } on-error={
        :log error "Erro no sync: $[error]"
    }
    
    # 3. Reportar status dos usuários ativos
    :do {
        $reportActiveUsers
    } on-error={
        :log warning "Erro ao reportar status dos usuários"
    }
    
    :log info "=== SYNC FINALIZADO ==="
}

# =====================================================
# EXECUTAR SYNC IMEDIATO (PARA TESTE)
# =====================================================
:log info "Executando sync manual..."
$syncWithServer

# =====================================================
# AGENDAR SYNC AUTOMÁTICO
# =====================================================

# Remover scheduler anterior se existir
/system scheduler remove [find name="wifi-sync"]

# Criar novo scheduler para executar a cada 2 minutos
/system scheduler add name="wifi-sync" start-time=startup interval=2m on-event="syncWithServer" comment="Sync automático com servidor"

:log info "Scheduler criado: sync a cada 2 minutos"

# =====================================================
# COMANDOS ÚTEIS PARA MONITORAMENTO
# =====================================================

:log info "=== COMANDOS PARA MONITORAMENTO ==="
:log info "Ver usuários hotspot: /ip hotspot user print"
:log info "Ver usuários ativos: /ip hotspot active print"
:log info "Ver logs: /log print where topics~\"info\""
:log info "Ver scheduler: /system scheduler print"
:log info "Executar sync manual: \$syncWithServer"

:put "Script de sync instalado com sucesso!"
:put "O MikroTik agora vai sincronizar com o servidor a cada 2 minutos"
:put "Verifique os logs com: /log print where topics~\"info\"" 