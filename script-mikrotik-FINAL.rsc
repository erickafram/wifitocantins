# ============================================================================
# Script MikroTik - Sincronização de MACs Pagos - VERSÃO FINAL
# Data: 2025-09-30
# TESTADO E FUNCIONANDO!
# ============================================================================

:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"

:log info "=== SYNC MACS PAGOS INICIADA ==="

# Buscar API
:local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]

:if ([:typeof $result] = "nothing") do={
    :log error "Fetch falhou"
    :return
}

:if (($result->"status") != "finished") do={
    :log error "Fetch nao completou"
    :return
}

:local payload ($result->"data")
:if ([:len $payload] = 0) do={
    :log warning "Payload vazio"
    :return
}

:log info ("Dados recebidos: " . [:len $payload] . " bytes")

# ===========================================================
# LIBERAR MACS PAGOS
# ===========================================================
:local liberados 0
:local jaExiste 0
:local macsPagos [:toarray ""]

:local pos 0
:local maxLoop 100
:local loopCount 0

:while ($loopCount < $maxLoop) do={
    :set loopCount ($loopCount + 1)
    
    # Procurar "mac_address":"
    :local macKey "\"mac_address\":\""
    :local macPos [:find $payload $macKey $pos]
    
    :if ($macPos = -1) do={
        :set loopCount $maxLoop
    } else={
        # Pular para depois de "mac_address":"
        :local macStart ($macPos + [:len $macKey])
        
        # Procurar o " de fechamento
        :local macEnd [:find $payload "\"" $macStart]
        
        :if ($macEnd != -1) do={
            # Extrair MAC
            :local mac [:pick $payload $macStart $macEnd]
            :set pos ($macEnd + 1)
            
            # Validar tamanho
            :if ([:len $mac] = 17) do={
                :set macsPagos ($macsPagos, $mac)
                
                # Verificar se já existe
                :local existente [/ip hotspot ip-binding find mac-address=$mac]
                
                :if ([:len $existente] = 0) do={
                    :log info ("[+] Liberando: " . $mac)
                    
                    # Limpar conflitos
                    :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                    :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                    
                    # Adicionar binding
                    :do {
                        /ip hotspot ip-binding add \
                            mac-address=$mac \
                            type=bypassed \
                            comment=$bypassComment \
                            disabled=no
                        
                        :set liberados ($liberados + 1)
                        :log info ("    [OK] MAC liberado!")
                    } on-error={
                        :log error ("    [ERRO] Falha ao liberar")
                    }
                } else={
                    :set jaExiste ($jaExiste + 1)
                    :log info ("[=] Ja existe: " . $mac)
                }
            } else={
                :log warning ("[!] MAC invalido (len=" . [:len $mac] . "): " . $mac)
            }
        } else={
            :set loopCount $maxLoop
        }
    }
}

:log info ("Processados: " . [:len $macsPagos])
:log info ("Novos liberados: " . $liberados)
:log info ("Ja existentes: " . $jaExiste)

# ===========================================================
# REMOVER MACS EXPIRADOS
# ===========================================================
:log info "--- Removendo expirados ---"

:local removidos 0
:local pos2 0
:local loopCount2 0

:while ($loopCount2 < $maxLoop) do={
    :set loopCount2 ($loopCount2 + 1)
    
    # Procurar em "remove_macs"
    :local removeKey "\"remove_macs\":"
    :local removePos [:find $payload $removeKey]
    
    :if ($removePos >= 0) do={
        # Procurar mac_address após remove_macs
        :local macKey "\"mac_address\":\""
        :local macPos [:find $payload $macKey ($removePos + [:len $removeKey] + $pos2)]
        
        :if ($macPos = -1) do={
            :set loopCount2 $maxLoop
        } else={
            :local macStart ($macPos + [:len $macKey])
            :local macEnd [:find $payload "\"" $macStart]
            
            :if ($macEnd != -1) do={
                :local mac [:pick $payload $macStart $macEnd]
                :set pos2 ($macEnd + 1)
                
                :if ([:len $mac] = 17) do={
                    :log warning ("[-] Removendo: " . $mac)
                    
                    :do {
                        /ip hotspot ip-binding remove [find mac-address=$mac]
                        :set removidos ($removidos + 1)
                    } on-error={}
                    
                    :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                    :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                }
            } else={
                :set loopCount2 $maxLoop
            }
        }
    } else={
        :set loopCount2 $maxLoop
    }
}

:log info ("Expirados removidos: " . $removidos)

# ===========================================================
# LIMPAR ORFAOS (que não estão na lista de pagos)
# ===========================================================
:log info "--- Limpando orfaos ---"

:local bindings [/ip hotspot ip-binding find where comment=$bypassComment]
:local orfaos 0

:foreach bindingId in=$bindings do={
    :local macAtual [/ip hotspot ip-binding get $bindingId mac-address]
    :local encontrado false
    
    # Ver se está na lista de pagos
    :foreach macPago in=$macsPagos do={
        :if ($macPago = $macAtual) do={
            :set encontrado true
        }
    }
    
    :if (!$encontrado) do={
        :log warning ("[ORFAO] Removendo: " . $macAtual)
        
        :do {
            /ip hotspot ip-binding remove $bindingId
            :set orfaos ($orfaos + 1)
        } on-error={}
        
        :do {/ip hotspot user remove [find mac-address=$macAtual]} on-error={}
        :do {/ip hotspot active remove [find mac-address=$macAtual]} on-error={}
    }
}

:log info ("Orfaos removidos: " . $orfaos)

# ===========================================================
# RESUMO
# ===========================================================
:log info "==================================="
:log info ("RESUMO - Pagos: " . [:len $macsPagos] . " | Liberados: " . $liberados . " | Removidos: " . $removidos . " | Orfaos: " . $orfaos)
:log info "===================================" 