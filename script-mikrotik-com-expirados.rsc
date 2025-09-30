# =============================================================================
# Script MikroTik - Sincronização de MACs Pagos e Expirados
# Versão: 4.0 - CORRIGIDO
# Data: 2025-09-30
# =============================================================================
# 
# FUNCIONALIDADES:
# - Libera MACs pagos no ip-binding (bypass do hotspot)
# - Remove MACs expirados do ip-binding
# - Mantém apenas usuários com pagamento ativo
# - Log detalhado de todas as operações
#
# =============================================================================

:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"

:log info "========================================="
:log info "=== INICIANDO SINCRONIZACAO DE MACS ==="
:log info "========================================="

# -----------------------------------------------------------------------------
# ETAPA 1: Buscar dados da API
# -----------------------------------------------------------------------------
:log info "[1/4] Buscando dados da API..."

:local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]

# Verificar se fetch retornou algo
:if ([:typeof $result] = "nothing") do={
    :log error "  [ERRO] Fetch nao retornou dados"
    :return
}

# Verificar status do fetch
:local status ($result->"status")
:if ($status != "finished") do={
    :log error ("  [ERRO] Fetch falhou com status: " . $status)
    :return
}

# Obter payload
:local payload ($result->"data")
:if ([:len $payload] = 0) do={
    :log warning "  [AVISO] API retornou payload vazio"
    :return
}

:log info ("  [OK] Dados recebidos: " . [:len $payload] . " caracteres")

# -----------------------------------------------------------------------------
# ETAPA 2: Extrair seção liberate_macs
# -----------------------------------------------------------------------------
:log info "[2/4] Processando MACs para LIBERAR..."

:local liberateStart [:find $payload "\"liberate_macs\":["]
:local liberateEnd [:find $payload "],\"remove_macs\"" $liberateStart]

:local macsPagos [:toarray ""]
:local liberados 0
:local jaExistentes 0

:if ($liberateStart >= 0 and $liberateEnd >= 0) do={
    :set liberateStart ($liberateStart + 18)
    :local liberateContent [:pick $payload $liberateStart $liberateEnd]
    
    # Se não estiver vazio, processar
    :if ([:len $liberateContent] > 5) do={
        :log info "  Encontrados MACs para processar..."
        
        :local pos 0
        :local maxIterations 50
        :local iteration 0
        
        :while ($iteration < $maxIterations) do={
            :set iteration ($iteration + 1)
            
            # Procurar próximo MAC
            :local macStart [:find $liberateContent "\"mac_address\":\"" $pos]
            
            :if ($macStart = -1) do={
                :set iteration $maxIterations
            } else={
                :set macStart ($macStart + 16)
                :local macEnd [:find $liberateContent "\"" $macStart]
                
                :if ($macEnd != -1) do={
                    :local mac [:pick $liberateContent $macStart $macEnd]
                    :set pos ($macEnd + 1)
                    
                    # Validar formato do MAC
                    :if ([:len $mac] = 17) do={
                        # Converter para uppercase para consistência
                        :local macUpper [:tostr $mac]
                        :set macsPagos ($macsPagos, $macUpper)
                        
                        # Verificar se já existe
                        :local existing [/ip hotspot ip-binding find mac-address=$macUpper]
                        
                        :if ([:len $existing] = 0) do={
                            :log info ("  [+] Liberando: " . $macUpper)
                            
                            # Limpar possíveis conflitos
                            :do {/ip hotspot user remove [find mac-address=$macUpper]} on-error={}
                            :do {/ip hotspot active remove [find mac-address=$macUpper]} on-error={}
                            
                            # Adicionar ao ip-binding
                            :do {
                                /ip hotspot ip-binding add \
                                    mac-address=$macUpper \
                                    type=bypassed \
                                    comment=$bypassComment \
                                    disabled=no
                                
                                :set liberados ($liberados + 1)
                                :log info ("      [OK] MAC liberado com sucesso!")
                            } on-error={
                                :log error ("      [ERRO] Falha ao adicionar binding")
                            }
                        } else={
                            :set jaExistentes ($jaExistentes + 1)
                            :log info ("  [=] Ja existe: " . $macUpper)
                        }
                    } else={
                        :log warning ("  [!] MAC invalido (len=" . [:len $mac] . "): " . $mac)
                    }
                } else={
                    :set iteration $maxIterations
                }
            }
        }
    } else={
        :log info "  Lista liberate_macs vazia"
    }
} else={
    :log warning "  [AVISO] Secao liberate_macs nao encontrada no JSON"
}

:log info ("  TOTAL processados: " . [:len $macsPagos])
:log info ("  Liberados agora: " . $liberados)
:log info ("  Ja existentes: " . $jaExistentes)

# -----------------------------------------------------------------------------
# ETAPA 3: Extrair seção remove_macs (CORRIGIDO!)
# -----------------------------------------------------------------------------
:log info "[3/4] Processando MACs para REMOVER..."

# ⚠️ CORRIGIDO: Procurar por "remove_macs" em vez de "block_macs"
:local removeStart [:find $payload "\"remove_macs\":[" $liberateEnd]
:local removeEnd [:find $payload "]" $removeStart]

:local macsRemovidos 0

:if ($removeStart >= 0 and $removeEnd >= 0) do={
    :set removeStart ($removeStart + 16)
    :local removeContent [:pick $payload $removeStart $removeEnd]
    
    :if ([:len $removeContent] > 5) do={
        :log info "  Encontrados MACs expirados para remover..."
        
    :local pos 0
        :local maxIterations 50
        :local iteration 0
        
        :while ($iteration < $maxIterations) do={
            :set iteration ($iteration + 1)
            
            :local macStart [:find $removeContent "\"mac_address\":\"" $pos]
            
            :if ($macStart = -1) do={
                :set iteration $maxIterations
            } else={
                :set macStart ($macStart + 16)
                :local macEnd [:find $removeContent "\"" $macStart]
                
                :if ($macEnd != -1) do={
                    :local mac [:pick $removeContent $macStart $macEnd]
                    :set pos ($macEnd + 1)

                    :if ([:len $mac] = 17) do={
                        :log warning ("  [-] Removendo expirado: " . $mac)
                        
                        # Remover de todos os lugares
                        :do {
                            /ip hotspot ip-binding remove [find mac-address=$mac]
                            :set macsRemovidos ($macsRemovidos + 1)
                            :log info ("      [OK] Binding removido")
                        } on-error={}
                        
            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                    }
                } else={
                    :set iteration $maxIterations
                }
            }
        }
    } else={
        :log info "  Lista remove_macs vazia"
    }
} else={
    :log info "  Secao remove_macs nao encontrada (ou vazia)"
}

:log info ("  TOTAL removidos: " . $macsRemovidos)

# -----------------------------------------------------------------------------
# ETAPA 4: Limpar bindings órfãos (que não estão na lista de pagos)
# -----------------------------------------------------------------------------
:log info "[4/4] Limpando bindings orfaos..."

:local bindings [/ip hotspot ip-binding find where comment=$bypassComment]
:local orfaosRemovidos 0

:log info ("  Total de bindings AUTO-PAGO: " . [:len $bindings])

:foreach bindingId in=$bindings do={
    :local macAtual [/ip hotspot ip-binding get $bindingId mac-address]
    :local encontrado false
    
    # Verificar se está na lista de MACs pagos
    :foreach macPago in=$macsPagos do={
        :if ($macPago = $macAtual) do={
            :set encontrado true
        }
    }
    
    :if (!$encontrado) do={
        :log warning ("  [ORFAO] Removendo: " . $macAtual)
        
        :do {
            /ip hotspot ip-binding remove $bindingId
            :set orfaosRemovidos ($orfaosRemovidos + 1)
        } on-error={
            :log error ("  [ERRO] Falha ao remover orfao")
        }
        
        # Limpar usuário e sessões ativas
        :do {/ip hotspot user remove [find mac-address=$macAtual]} on-error={}
        :do {/ip hotspot active remove [find mac-address=$macAtual]} on-error={}
    }
}

:log info ("  Orfaos removidos: " . $orfaosRemovidos)

# -----------------------------------------------------------------------------
# RESUMO FINAL
# -----------------------------------------------------------------------------
:log info "========================================="
:log info "===      SINCRONIZACAO CONCLUIDA     ==="
:log info "========================================="
:log info ("  MACs pagos ativos: " . [:len $macsPagos])
:log info ("  Novos liberados: " . $liberados)
:log info ("  Ja existentes: " . $jaExistentes)
:log info ("  Expirados removidos: " . $macsRemovidos)
:log info ("  Orfaos limpos: " . $orfaosRemovidos)
:log info "========================================="
