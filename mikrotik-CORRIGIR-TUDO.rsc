# ============================================================
# SCRIPT DE CORREÇÃO COMPLETA - MIKROTIK
# Execute este script para corrigir TODOS os problemas
# ============================================================

:put "=========================================="
:put "INICIANDO CORREÇÃO COMPLETA"
:put "=========================================="
:put ""

# ============================================================
# PASSO 1: REMOVER SCRIPTS E SCHEDULERS ANTIGOS
# ============================================================

:put "1. Removendo scripts e schedulers antigos..."
/system script remove [find name="syncPagos"]
/system script remove [find name="registrarMacs"]
/system scheduler remove [find name="syncPagosScheduler"]
/system scheduler remove [find name="registrarMacsScheduler"]
:put "   OK - Scripts antigos removidos"
:put ""

# ============================================================
# PASSO 2: CRIAR SCRIPT PRINCIPAL (VERSÃO LIMPA E TESTADA)
# ============================================================

:put "2. Criando script syncPagos (versão limpa)..."

/system script add name="syncPagos" owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
# Configurações
:local apiUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite"
:local token "mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"

# Montar URL completa
:local url ($apiUrl . "?token=" . $token)

:do {
    # Fazer requisição à API
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    
    :if (($result->"status") = "finished") do={
        :local data ($result->"data")
        :local dataLen [:len $data]
        
        # Verificar se resposta começa com "OK"
        :if ([:pick $data 0 2] = "OK") do={
            :local liberados 0
            :local removidos 0
            :local pos 3
            
            # Processar cada linha
            :while ($pos < $dataLen) do={
                # Encontrar fim da linha
                :local lineEnd [:find $data "\n" $pos]
                :if ([:typeof $lineEnd] = "nil") do={
                    :set lineEnd $dataLen
                }
                
                # Extrair linha
                :local line [:pick $data $pos $lineEnd]
                :set pos ($lineEnd + 1)
                
                # Processar linha (formato: L:XX:XX:XX:XX:XX:XX ou R:XX:XX:XX:XX:XX:XX)
                :if ([:len $line] >= 19) do={
                    :local action [:pick $line 0 1]
                    :local mac [:pick $line 2 19]
                    
                    # Validar formato MAC (17 caracteres com :)
                    :if ([:len $mac] = 17) do={
                        
                        # LIBERAR MAC
                        :if ($action = "L") do={
                            :local existente [/ip hotspot ip-binding find mac-address=$mac]
                            
                            :if ([:len $existente] = 0) do={
                                # MAC não existe - criar binding
                                :do {
                                    /ip hotspot ip-binding add mac-address=$mac type=bypassed comment=$bypassComment disabled=no
                                    :set liberados ($liberados + 1)
                                    :log info ("SYNC: Liberado " . $mac)
                                } on-error={
                                    :log warning ("SYNC: Erro ao liberar " . $mac)
                                }
                            } else={
                                # MAC já existe - garantir que está habilitado e bypassed
                                :do {
                                    /ip hotspot ip-binding set $existente type=bypassed disabled=no
                                } on-error={}
                            }
                            
                            # Remover de hotspot active (forçar reconexão limpa)
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                        }
                        
                        # REMOVER MAC
                        :if ($action = "R") do={
                            :local binding [/ip hotspot ip-binding find mac-address=$mac comment=$bypassComment]
                            
                            :if ([:len $binding] > 0) do={
                                :do {
                                    /ip hotspot ip-binding remove $binding
                                    :set removidos ($removidos + 1)
                                    :log info ("SYNC: Removido " . $mac)
                                } on-error={}
                            }
                            
                            # Também remover de users e desconectar
                            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                        }
                    }
                }
            }
            
            # Log resumo (apenas se houve ações)
            :if (($liberados > 0) || ($removidos > 0)) do={
                :log info ("SYNC: Liberados=" . $liberados . " Removidos=" . $removidos)
            }
            
        } else={
            :log warning "SYNC: Resposta invalida da API"
        }
    } else={
        :log warning "SYNC: Fetch nao completou"
    }
} on-error={
    :log error "SYNC: Erro de conexao com API"
}
}

:put "   OK - Script syncPagos criado"
:put ""

# ============================================================
# PASSO 3: CRIAR SCHEDULER
# ============================================================

:put "3. Criando scheduler (30 segundos)..."
/system scheduler add name="syncPagosScheduler" interval=30s on-event=syncPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup comment="Sincroniza MACs pagos"
:put "   OK - Scheduler criado"
:put ""

# ============================================================
# PASSO 4: CORRIGIR WIFI 5GHz
# ============================================================

:put "4. Corrigindo WiFi 5GHz..."

# Verificar se wlan2 existe e está na bridge
:local wlan2Exists [/interface wireless find default-name=wlan2]
:if ([:len $wlan2Exists] > 0) do={
    # Habilitar wlan2
    /interface wireless set wlan2 disabled=no
    
    # Verificar se está na bridge
    :local wlan2InBridge [/interface bridge port find interface=wlan2]
    :if ([:len $wlan2InBridge] = 0) do={
        /interface bridge port add bridge=wifi-hotspot interface=wlan2
        :put "   OK - wlan2 adicionado à bridge wifi-hotspot"
    } else={
        :put "   OK - wlan2 já está na bridge"
    }
} else={
    :put "   AVISO - wlan2 não encontrado (interface wireless antiga)"
    :put "   Tentando configuração WiFi nova..."
    
    # Verificar se existe configuração WiFi nova
    :local wifi5gConfig [/interface wifi configuration find name="tocantins-5g"]
    :if ([:len $wifi5gConfig] > 0) do={
        :put "   OK - Configuração WiFi 5G existe"
    } else={
        :put "   ERRO - Configuração WiFi 5G não encontrada"
    }
}

:put ""

# ============================================================
# PASSO 5: TESTAR CONEXÃO COM API
# ============================================================

:put "5. Testando conexão com API..."
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite?token=mikrotik-sync-2024"
:do {
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    :if (($result->"status") = "finished") do={
        :put "   OK - Conexão com API funcionando"
        :put "   Resposta:"
        :put ("   " . ($result->"data"))
    } else={
        :put "   ERRO - Conexão falhou"
    }
} on-error={
    :put "   ERRO - Não foi possível conectar"
}
:put ""

# ============================================================
# PASSO 6: EXECUTAR SYNC IMEDIATAMENTE
# ============================================================

:put "6. Executando sync pela primeira vez..."
:do {
    /system script run syncPagos
    :delay 2s
    :put "   OK - Sync executado"
} on-error={
    :put "   ERRO - Falha ao executar sync"
}
:put ""

# ============================================================
# PASSO 7: VERIFICAR RESULTADOS
# ============================================================

:put "7. Verificando resultados..."
:local bindingCount [/ip hotspot ip-binding print count-only where comment="PAGO-AUTO"]
:put ("   Bindings PAGO-AUTO criados: " . $bindingCount)

:if ($bindingCount > 0) do={
    :put "   Lista de MACs liberados:"
    /ip hotspot ip-binding print where comment="PAGO-AUTO"
}
:put ""

# ============================================================
# PASSO 8: VERIFICAR LOGS
# ============================================================

:put "8. Últimos logs de SYNC:"
/log print where message~"SYNC"
:put ""

:put "=========================================="
:put "CORREÇÃO CONCLUÍDA!"
:put "=========================================="
:put ""
:put "PRÓXIMOS PASSOS:"
:put "1. Verifique se a rede 5GHz aparece no celular"
:put "2. Conecte um dispositivo e faça um pagamento teste"
:put "3. Aguarde até 30 segundos para o sync automático"
:put "4. Verifique os logs: /log print where message~\"SYNC\""
:put ""
