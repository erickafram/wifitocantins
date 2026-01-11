# ============================================================
# CONFIGURAÇÃO MIKROTIK FINAL - TocantinsTransporte WiFi
# Data: 2026-01-11
# RouterOS 7.20.5 - hAP ac²
# ============================================================
#
# PROBLEMAS CORRIGIDOS:
# 1. Script de sincronização simplificado (usa endpoint lite)
# 2. Intervalo de sync aumentado para 30s (menos carga)
# 3. Walled Garden otimizado (sem duplicatas)
# 4. Ambas redes WiFi (2.4GHz e 5GHz) na mesma bridge
#
# ============================================================

# ============================================================
# PARTE 1: LIMPAR CONFIGURAÇÕES ANTIGAS
# ============================================================

/system script remove [find name~"sync"]
/system script remove [find name~"liberar"]
/system script remove [find name~"registrar"]
/system scheduler remove [find name~"sync"]
/system scheduler remove [find name~"registrar"]

# ============================================================
# PARTE 2: BRIDGES
# ============================================================

/interface bridge
add admin-mac=D4:01:C3:93:CA:4E auto-mac=no comment=defconf name=bridgeLocal
add comment="Bridge para Hotspot WiFi" name=wifi-hotspot

# ============================================================
# PARTE 3: CONFIGURAÇÃO WIRELESS (2.4GHz e 5GHz)
# ============================================================

/interface wireless
set [ find default-name=wlan1 ] \
    band=2ghz-b/g/n \
    channel-width=20/40mhz-Ce \
    country=brazil \
    disabled=no \
    distance=indoors \
    frequency=2437 \
    mode=ap-bridge \
    ssid=TocantinsTransporteWiFi \
    wireless-protocol=802.11

set [ find default-name=wlan2 ] \
    band=5ghz-a/n/ac \
    channel-width=20/40/80mhz-Ceee \
    country=brazil \
    disabled=no \
    distance=indoors \
    mode=ap-bridge \
    ssid=TocantinsTransporteWiFi-5G \
    wireless-protocol=802.11

# ============================================================
# PARTE 4: BRIDGE PORTS - AMBAS REDES NA MESMA BRIDGE
# ============================================================

/interface bridge port
add bridge=wifi-hotspot interface=wlan1 comment="WiFi 2.4GHz"
add bridge=wifi-hotspot interface=wlan2 comment="WiFi 5GHz"

# ============================================================
# PARTE 5: HOTSPOT PROFILE
# ============================================================

/ip hotspot profile
add dns-name=hotspot.wifi \
    hotspot-address=10.5.50.1 \
    html-directory=flash/hotspot \
    http-cookie-lifetime=1d \
    login-by=cookie,http-chap,http-pap \
    name=hsprof-tocantins

# ============================================================
# PARTE 6: IP POOL E DHCP
# ============================================================

/ip pool
add name=hs-pool ranges=10.5.50.10-10.5.50.250

/ip dhcp-server
add address-pool=hs-pool interface=wifi-hotspot lease-time=1d name=hotspot-dhcp

/ip dhcp-server network
add address=10.5.50.0/24 comment="hotspot network" dns-server=10.5.50.1,1.1.1.1,8.8.8.8 gateway=10.5.50.1

# ============================================================
# PARTE 7: HOTSPOT
# ============================================================

/ip hotspot
add address-pool=hs-pool disabled=no interface=wifi-hotspot name=tocantins-hotspot profile=hsprof-tocantins

# ============================================================
# PARTE 8: IP ADDRESS
# ============================================================

/ip address
add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0

# ============================================================
# PARTE 9: DNS
# ============================================================

/ip dns
set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8

/ip dns static
add address=138.68.255.122 comment="Portal Principal" name=tocantinstransportewifi.com.br type=A
add address=138.68.255.122 comment="Portal WWW" name=www.tocantinstransportewifi.com.br type=A
add address=10.5.50.1 comment="Hotspot Login" name=hotspot.wifi type=A

# ============================================================
# PARTE 10: FIREWALL NAT
# ============================================================

/ip firewall nat
add action=masquerade chain=srcnat comment="NAT Principal" out-interface=ether1

# Forçar DNS local
add action=redirect chain=dstnat comment="Force DNS UDP" dst-port=53 in-interface=wifi-hotspot protocol=udp to-ports=53
add action=redirect chain=dstnat comment="Force DNS TCP" dst-port=53 in-interface=wifi-hotspot protocol=tcp to-ports=53

# ============================================================
# PARTE 11: WALLED GARDEN (OTIMIZADO - SEM DUPLICATAS)
# ============================================================

/ip hotspot walled-garden
add comment="Portal" dst-host=tocantinstransportewifi.com.br
add comment="Portal Wildcard" dst-host=*.tocantinstransportewifi.com.br
add comment="Gateway" dst-host=10.5.50.1
add comment="Woovi PIX" dst-host=*.woovi.com
add comment="OpenPix" dst-host=*.openpix.com.br
add comment="PagBank" dst-host=*.pagseguro.com.br
add comment="Google Fonts" dst-host=fonts.googleapis.com
add comment="Google Fonts Static" dst-host=fonts.gstatic.com

# Bancos para PIX (essenciais)
add comment="Nubank" dst-host=*.nubank.com.br
add comment="Caixa" dst-host=*.caixa.gov.br
add comment="BB" dst-host=*.bb.com.br
add comment="Itau" dst-host=*.itau.com.br
add comment="Bradesco" dst-host=*.bradesco.com.br
add comment="Santander" dst-host=*.santander.com.br
add comment="Inter" dst-host=*.bancointer.com.br
add comment="C6" dst-host=*.c6bank.com.br
add comment="PicPay" dst-host=*.picpay.com
add comment="MercadoPago" dst-host=*.mercadopago.com.br

# ============================================================
# PARTE 12: WALLED GARDEN IP (OTIMIZADO)
# ============================================================

/ip hotspot walled-garden ip
add action=accept comment="Portal IP" dst-address=138.68.255.122
add action=accept comment="Gateway" dst-address=10.5.50.1
add action=accept comment="Cloudflare" dst-address=104.16.0.0/12
add action=accept comment="Cloudflare 2" dst-address=172.64.0.0/13
add action=accept comment="AWS BR" dst-address=18.228.0.0/14
add action=accept comment="DNS Cloudflare" dst-address=1.1.1.1
add action=accept comment="DNS Google" dst-address=8.8.8.8

# ============================================================
# PARTE 13: CLOCK E TIMEZONE
# ============================================================

/system clock
set time-zone-name=America/Araguaina

# ============================================================
# PARTE 14: SCRIPT DE SINCRONIZAÇÃO (SIMPLIFICADO)
# ============================================================

/system script add name="syncPagos" owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local apiUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite"
:local token "mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"
:local url ($apiUrl . "?token=" . $token)

:do {
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    
    :if (($result->"status") = "finished") do={
        :local data ($result->"data")
        :local dataLen [:len $data]
        
        :if ([:pick $data 0 2] = "OK") do={
            :local liberados 0
            :local removidos 0
            :local pos 3
            
            :while ($pos < $dataLen) do={
                :local lineEnd [:find $data "\n" $pos]
                :if ([:typeof $lineEnd] = "nil") do={ :set lineEnd $dataLen }
                
                :local line [:pick $data $pos $lineEnd]
                :set pos ($lineEnd + 1)
                
                :if ([:len $line] >= 19) do={
                    :local action [:pick $line 0 1]
                    :local mac [:pick $line 2 19]
                    
                    :if ([:len $mac] = 17) do={
                        :if ($action = "L") do={
                            :local existente [/ip hotspot ip-binding find mac-address=$mac]
                            :if ([:len $existente] = 0) do={
                                :do {
                                    /ip hotspot ip-binding add mac-address=$mac type=bypassed comment=$bypassComment disabled=no
                                    :set liberados ($liberados + 1)
                                    :log info ("SYNC: Liberado " . $mac)
                                } on-error={ :log warning ("SYNC: Erro ao liberar " . $mac) }
                            } else={
                                :do { /ip hotspot ip-binding set $existente type=bypassed disabled=no } on-error={}
                            }
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                        }
                        
                        :if ($action = "R") do={
                            :local binding [/ip hotspot ip-binding find mac-address=$mac comment=$bypassComment]
                            :if ([:len $binding] > 0) do={
                                :do {
                                    /ip hotspot ip-binding remove $binding
                                    :set removidos ($removidos + 1)
                                    :log info ("SYNC: Removido " . $mac)
                                } on-error={}
                            }
                            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                        }
                    }
                }
            }
            
            :if (($liberados > 0) || ($removidos > 0)) do={
                :log info ("SYNC: L=" . $liberados . " R=" . $removidos)
            }
        } else={
            :log warning "SYNC: Resposta invalida"
        }
    }
} on-error={
    :log error "SYNC: Erro de conexao"
}
}

# ============================================================
# PARTE 15: SCHEDULER (A CADA 30 SEGUNDOS)
# ============================================================

/system scheduler add name="syncPagosScheduler" interval=30s on-event=syncPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup comment="Sincroniza MACs pagos"

# ============================================================
# PARTE 16: SCRIPT DE REGISTRO DE MACS (OPCIONAL)
# ============================================================

/system script add name="registrarMacs" owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local token "mikrotik-sync-2024"
:local baseUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/register-mac"

:foreach lease in=[/ip dhcp-server lease find where dynamic=yes] do={
    :local mac [/ip dhcp-server lease get $lease mac-address]
    :local ip [/ip dhcp-server lease get $lease address]
    :local firstByte [:pick $mac 0 2]
    :local isRandom (($firstByte = "02") || ($firstByte = "06") || ($firstByte = "0A") || ($firstByte = "0E"))
    
    :if ((!$isRandom) && ([:len $mac] = 17) && ([:len $ip] > 0)) do={
        :local url ($baseUrl . "?token=" . $token . "&mac=" . $mac . "&ip=" . $ip)
        :do { /tool fetch url=$url http-method=get mode=https keep-result=no check-certificate=no } on-error={}
    }
}
}

/system scheduler add name="registrarMacsScheduler" interval=2m on-event=registrarMacs policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup comment="Registra MACs na API"

# ============================================================
# PARTE 17: EXECUTAR SYNC IMEDIATAMENTE
# ============================================================

/system script run syncPagos

# ============================================================
# FIM DA CONFIGURAÇÃO
# ============================================================
#
# COMANDOS ÚTEIS:
#
# Ver logs: /log print where message~"SYNC"
# Ver MACs liberados: /ip hotspot ip-binding print where comment="PAGO-AUTO"
# Testar sync: /system script run syncPagos
# Ver ativos: /ip hotspot active print
#
# ============================================================
