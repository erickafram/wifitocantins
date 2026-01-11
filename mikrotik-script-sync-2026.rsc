# ============================================================
# SCRIPT MIKROTIK - SINCRONIZAÇÃO DE PAGAMENTOS
# Versão: 2026.01.11
# RouterOS 7.x - Testado em hAP ac²
# ============================================================
#
# PROBLEMA RESOLVIDO:
# - Usuário paga na rede 2.4GHz mas não consegue acessar na 5GHz
# - SOLUÇÃO: O MAC é do DISPOSITIVO, não da rede! O mesmo MAC
#   funciona em ambas as redes. O problema era o script não
#   estar liberando corretamente.
#
# COMO FUNCIONA:
# 1. Script consulta API a cada 30 segundos
# 2. API retorna lista de MACs para LIBERAR (L:) e REMOVER (R:)
# 3. Script cria ip-binding type=bypassed para MACs pagos
# 4. Usuário com ip-binding bypassed tem acesso direto à internet
#
# ============================================================

# ============================================================
# PASSO 1: REMOVER SCRIPTS E SCHEDULERS ANTIGOS
# ============================================================

/system script remove [find name~"sync"]
/system script remove [find name~"liberar"]
/system script remove [find name~"registrar"]
/system scheduler remove [find name~"sync"]
/system scheduler remove [find name~"registrar"]

# ============================================================
# PASSO 2: CRIAR SCRIPT PRINCIPAL DE SINCRONIZAÇÃO
# ============================================================

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
                            
                            # Remover de hotspot user e active (forçar reconexão limpa)
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

# ============================================================
# PASSO 3: CRIAR SCHEDULER (A CADA 30 SEGUNDOS)
# ============================================================

/system scheduler add name="syncPagosScheduler" interval=30s on-event=syncPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup comment="Sincroniza MACs pagos com API"

# ============================================================
# PASSO 4: SCRIPT DE REGISTRO DE MACS (OPCIONAL)
# Envia MACs dos dispositivos conectados para a API
# ============================================================

/system script add name="registrarMacs" owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local token "mikrotik-sync-2024"
:local baseUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/register-mac"

:foreach lease in=[/ip dhcp-server lease find where dynamic=yes] do={
    :local mac [/ip dhcp-server lease get $lease mac-address]
    :local ip [/ip dhcp-server lease get $lease address]
    
    # Ignorar MACs randomizados (começam com 02:, 06:, 0A:, 0E:)
    :local firstByte [:pick $mac 0 2]
    :local isRandom (($firstByte = "02") || ($firstByte = "06") || ($firstByte = "0A") || ($firstByte = "0E"))
    
    :if ((!$isRandom) && ([:len $mac] = 17) && ([:len $ip] > 0)) do={
        :local url ($baseUrl . "?token=" . $token . "&mac=" . $mac . "&ip=" . $ip)
        :do {
            /tool fetch url=$url http-method=get mode=https keep-result=no check-certificate=no
        } on-error={}
    }
}
}

# Scheduler para registro - A cada 2 minutos
/system scheduler add name="registrarMacsScheduler" interval=2m on-event=registrarMacs policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup comment="Registra MACs na API"

# ============================================================
# PASSO 5: EXECUTAR SYNC IMEDIATAMENTE
# ============================================================

/system script run syncPagos

# ============================================================
# COMANDOS ÚTEIS PARA DEBUG
# ============================================================
#
# Ver logs de sincronização:
# /log print where message~"SYNC"
#
# Ver MACs liberados:
# /ip hotspot ip-binding print where comment="PAGO-AUTO"
#
# Testar script manualmente:
# /system script run syncPagos
#
# Ver usuários ativos no hotspot:
# /ip hotspot active print
#
# Forçar reconexão de um MAC:
# /ip hotspot active remove [find mac-address="XX:XX:XX:XX:XX:XX"]
#
# ============================================================
