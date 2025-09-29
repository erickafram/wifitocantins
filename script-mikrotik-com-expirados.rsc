# Script MikroTik - Liberar usuários pagos e remover usuários expirados
# Versão: 2.0 - Com suporte a remoção de expirados
# Compatível com RouterOS 7.x

:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:local hotspotServer "tocantins-hotspot"
:local bypassComment "AUTO-PAGO"

:local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
:if ([:typeof $result] = "nothing") do={
    :log error "Fetch sem retorno"
    :return
}

:local status ($result->"status")
:if ($status != "finished") do={
    :log error ("Fetch falhou: " . $status)
    :return
}

:local payload ($result->"data")
:if ([:len $payload] = 0) do={
    :log info "Nenhum dado recebido"
    :return
}

:local liberateToken "\"liberate_macs\":["
:local removeToken "\"remove_macs\":["
:local macMarker "\"mac_address\":\""
:local macMarkerLen [:len $macMarker]

:local liberados 0
:local removidos 0

# =====================================
# PROCESSAMENTO DE LIBERAÇÕES (PAGOS)
# =====================================
:local liberateArray ""
:local liberateStart [:find $payload $liberateToken]
:if ($liberateStart != -1) do={
    :local start ($liberateStart + [:len $liberateToken])
    :local end [:find $payload "]" $start]
    :if ($end != -1) do={
        :set liberateArray [:pick $payload $start $end]
    }
}

:if ([:find $liberateArray "\"mac_address\""] != -1) do={
    :local pos 0
    :while (true) do={
        :set pos [:find $liberateArray $macMarker $pos]
        :if ($pos = -1) do={ :break }

        :local start ($pos + $macMarkerLen)
        :local end [:find $liberateArray "\"" $start]
        :if ($end = -1) do={ :break }

        :local mac [:toupper [:pick $liberateArray $start $end]]
        :set pos ($end + 1)

        :if ([:len $mac] != 17) do={
            :log warning ("MAC invalido em liberate_macs: " . $mac)
            :continue
        }

        # Verificar se já está liberado
        :local existing [/ip hotspot ip-binding find mac-address=$mac]
        :if ($existing = "") do={
            # Limpar registros anteriores
            :do {/ip hotspot ip-binding remove [find mac-address=$mac]} on-error={}
            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}

            # Adicionar como bypassed
            :local addOk true
            :do {
                /ip hotspot ip-binding add mac-address=$mac type=bypassed comment=$bypassComment disabled=no server=$hotspotServer
            } on-error={
                :set addOk false
                :log error ("Falha ao adicionar binding para " . $mac)
            }

            :if ($addOk) do={
                :set liberados ($liberados + 1)
                :log info ("✅ Liberado via API: " . $mac)
            }
        } else={
            :log info ("MAC " . $mac . " já liberado, pulando")
        }
    }
}

# =====================================
# PROCESSAMENTO DE REMOÇÕES (EXPIRADOS)
# =====================================
:local removeArray ""
:local removeStart [:find $payload $removeToken]
:if ($removeStart != -1) do={
    :local start ($removeStart + [:len $removeToken])
    :local end [:find $payload "]" $start]
    :if ($end != -1) do={
        :set removeArray [:pick $payload $start $end]
    }
}

:if ([:find $removeArray "\"mac_address\""] != -1) do={
    :local pos 0
    :while (true) do={
        :set pos [:find $removeArray $macMarker $pos]
        :if ($pos = -1) do={ :break }

        :local start ($pos + $macMarkerLen)
        :local end [:find $removeArray "\"" $start]
        :if ($end = -1) do={ :break }

        :local mac [:toupper [:pick $removeArray $start $end]]
        :set pos ($end + 1)

        :if ([:len $mac] != 17) do={
            :log warning ("MAC invalido em remove_macs: " . $mac)
            :continue
        }

        # ⚠️ REMOÇÃO COMPLETA (NÃO BLOQUEAR) - usuário volta ao estado inicial
        :do {/ip hotspot ip-binding remove [find mac-address=$mac]} on-error={}
        :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
        :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}

        :set removidos ($removidos + 1)
        :log info ("🗑️ Removido (expirado): " . $mac . " - volta ao estado inicial")
    }
}

# =====================================
# RESUMO DA SINCRONIZAÇÃO
# =====================================
:log info ("📊 Resumo da sincronização:")
:log info ("   ✅ Liberados: " . $liberados)
:log info ("   🗑️ Removidos: " . $removidos)
:log info ("   ⏰ Próxima sync: 2 minutos")
