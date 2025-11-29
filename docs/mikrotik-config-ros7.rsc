# ============================================================================
# CONFIGURAÇÃO COMPLETA MIKROTIK - RouterOS 7.20.5
# Projeto: WiFi Tocantins Transport
# Data: 2025-11-29
# ============================================================================
# 
# INSTRUÇÕES:
# 1. Faça backup antes de aplicar: /system backup save name=backup-antes-config
# 2. Copie e cole este script no terminal do MikroTik
# 3. Ou importe via: /import file-name=mikrotik-config-ros7.rsc
#
# ============================================================================

# ============================================================================
# VARIÁVEIS DE CONFIGURAÇÃO - AJUSTE CONFORME NECESSÁRIO
# ============================================================================

:global portalUrl "https://www.tocantinstransportewifi.com.br"
:global apiToken "mikrotik-sync-2024"
:global hotspotInterface "wifi-hotspot"
:global hotspotNetwork "10.5.50.0/24"
:global hotspotGateway "10.5.50.1"
:global hotspotPoolStart "10.5.50.2"
:global hotspotPoolEnd "10.5.50.254"
:global dnsServers "8.8.8.8,1.1.1.1"

# ============================================================================
# 1. CONFIGURAÇÃO DE BRIDGE PARA HOTSPOT
# ============================================================================

:log info ">>> Configurando Bridge para Hotspot..."

# Criar bridge se não existir
:if ([:len [/interface bridge find name=$hotspotInterface]] = 0) do={
    /interface bridge add name=$hotspotInterface comment="Bridge para Hotspot WiFi"
}

# ============================================================================
# 2. CONFIGURAÇÃO DE IP E POOL
# ============================================================================

:log info ">>> Configurando IP e Pool..."

# Remover IP antigo se existir
:do {
    /ip address remove [find interface=$hotspotInterface]
} on-error={}

# Adicionar IP do gateway
/ip address add address=10.5.50.1/24 interface=$hotspotInterface comment="Gateway Hotspot"

# Criar pool de IPs
:if ([:len [/ip pool find name="hs-pool"]] = 0) do={
    /ip pool add name=hs-pool ranges=10.5.50.2-10.5.50.254
} else={
    /ip pool set [find name="hs-pool"] ranges=10.5.50.2-10.5.50.254
}

# ============================================================================
# 3. SERVIDOR DHCP
# ============================================================================

:log info ">>> Configurando DHCP Server..."

# Criar network DHCP
:if ([:len [/ip dhcp-server network find where address="10.5.50.0/24"]] = 0) do={
    /ip dhcp-server network add address=10.5.50.0/24 gateway=10.5.50.1 dns-server=8.8.8.8,1.1.1.1 comment="Hotspot Network"
}

# Criar servidor DHCP
:if ([:len [/ip dhcp-server find name="hotspot-dhcp"]] = 0) do={
    /ip dhcp-server add name=hotspot-dhcp interface=$hotspotInterface address-pool=hs-pool lease-time=1h disabled=no
}

# ============================================================================
# 4. DNS ESTÁTICO PARA PORTAL
# ============================================================================

:log info ">>> Configurando DNS Estático..."

# Remover TODAS as entradas DNS estáticas antigas relacionadas
:foreach i in=[/ip dns static find] do={
    :local dnsName [/ip dns static get $i name]
    :if ($dnsName~"tocantins" || $dnsName~"portal" || $dnsName~"conectar" || $dnsName~"login") do={
        /ip dns static remove $i
    }
}

# Adicionar entradas DNS (com verificação)
:if ([:len [/ip dns static find name="tocantinstransportewifi.com.br"]] = 0) do={
    /ip dns static add name=tocantinstransportewifi.com.br address=138.68.255.122 comment="Portal Principal"
}
:if ([:len [/ip dns static find name="www.tocantinstransportewifi.com.br"]] = 0) do={
    /ip dns static add name=www.tocantinstransportewifi.com.br address=138.68.255.122 comment="Portal WWW"
}
:if ([:len [/ip dns static find name="portal.wifi"]] = 0) do={
    /ip dns static add name=portal.wifi address=138.68.255.122 comment="Portal Curto"
}
:if ([:len [/ip dns static find name="conectar.wifi"]] = 0) do={
    /ip dns static add name=conectar.wifi address=138.68.255.122 comment="Portal Conectar"
}
:if ([:len [/ip dns static find name="login.tocantinswifi.local"]] = 0) do={
    /ip dns static add name=login.tocantinswifi.local address=10.5.50.1 comment="Login Local"
}

# Habilitar DNS
/ip dns set allow-remote-requests=yes servers=8.8.8.8,1.1.1.1

# ============================================================================
# 5. PERFIL DO HOTSPOT
# ============================================================================

:log info ">>> Configurando Perfil Hotspot..."

# Remover perfil antigo e criar novo
:do { /ip hotspot profile remove [find name="hsprof-tocantins"] } on-error={}

/ip hotspot profile add \
    name=hsprof-tocantins \
    hotspot-address=10.5.50.1 \
    dns-name=login.tocantinswifi.local \
    html-directory=flash/hotspot \
    login-by=http-chap,http-pap,cookie,mac-cookie \
    http-cookie-lifetime=1d \
    split-user-domain=no \
    use-radius=no

# ============================================================================
# 6. SERVIDOR HOTSPOT
# ============================================================================

:log info ">>> Configurando Servidor Hotspot..."

# Remover hotspot antigo se existir
:do { /ip hotspot remove [find name="tocantins-hotspot"] } on-error={}
:do { /ip hotspot remove [find interface=$hotspotInterface] } on-error={}

# Criar servidor hotspot (com verificação)
:if ([:len [/ip hotspot find name="tocantins-hotspot"]] = 0) do={
/ip hotspot add \
    name=tocantins-hotspot \
    interface=$hotspotInterface \
    address-pool=hs-pool \
    profile=hsprof-tocantins \
    disabled=no
}

# ============================================================================
# 7. WALLED GARDEN - SITES LIBERADOS SEM LOGIN
# ============================================================================

:log info ">>> Configurando Walled Garden..."

# Limpar walled garden antigo (manter apenas regras essenciais)
:foreach i in=[/ip hotspot walled-garden find] do={
    :do { /ip hotspot walled-garden remove $i } on-error={}
}

# =====================
# PORTAL E CDNs
# =====================
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Portal Principal"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Portal Wildcard"
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br comment="Portal WWW"
/ip hotspot walled-garden add dst-host=cdn.tailwindcss.com comment="Tailwind CSS"
/ip hotspot walled-garden add dst-host=fonts.googleapis.com comment="Google Fonts API"
/ip hotspot walled-garden add dst-host=fonts.gstatic.com comment="Google Fonts Static"
/ip hotspot walled-garden add dst-host=cdnjs.cloudflare.com comment="Cloudflare CDN"
/ip hotspot walled-garden add dst-host=ajax.googleapis.com comment="Google AJAX"

# =====================
# PAGAMENTOS PIX
# =====================
/ip hotspot walled-garden add dst-host=api.woovi.com comment="Woovi API"
/ip hotspot walled-garden add dst-host=*.woovi.com comment="Woovi Subdomains"
/ip hotspot walled-garden add dst-host=app.woovi.com comment="Woovi Dashboard"
/ip hotspot walled-garden add dst-host=api.openpix.com.br comment="OpenPix API"
/ip hotspot walled-garden add dst-host=*.openpix.com.br comment="OpenPix Subdomains"
/ip hotspot walled-garden add dst-host=api.qrserver.com comment="QR Code Generator"
/ip hotspot walled-garden add dst-host=chart.googleapis.com comment="Google Charts QR"
/ip hotspot walled-garden add dst-host=pix.bcb.gov.br comment="PIX Banco Central"
/ip hotspot walled-garden add dst-host=*.bcb.gov.br comment="Banco Central BR"

# =====================
# BANCOS - APPS E INTERNET BANKING
# =====================

# Banco do Brasil
/ip hotspot walled-garden add dst-host=bb.com.br comment="Banco do Brasil"
/ip hotspot walled-garden add dst-host=*.bb.com.br comment="BB Mobile"
/ip hotspot walled-garden add dst-host=www37.bb.com.br comment="BB App"
/ip hotspot walled-garden add dst-host=api.bb.com.br comment="BB API"
/ip hotspot walled-garden add dst-host=mobile.bb.com.br comment="BB Mobile App"
/ip hotspot walled-garden add dst-host=aapj.bb.com.br comment="BB App Android"
/ip hotspot walled-garden add dst-host=seg.bb.com.br comment="BB Segurança"

# Caixa Econômica Federal
/ip hotspot walled-garden add dst-host=caixa.gov.br comment="Caixa CEF"
/ip hotspot walled-garden add dst-host=*.caixa.gov.br comment="Caixa Mobile"
/ip hotspot walled-garden add dst-host=internetbanking.caixa.gov.br comment="Caixa Internet Banking"
/ip hotspot walled-garden add dst-host=m.caixa.gov.br comment="Caixa Mobile Site"
/ip hotspot walled-garden add dst-host=acessoseguro.caixa.gov.br comment="Caixa Acesso Seguro"

# Itaú Unibanco
/ip hotspot walled-garden add dst-host=itau.com.br comment="Itau Unibanco"
/ip hotspot walled-garden add dst-host=*.itau.com.br comment="Itau Mobile"
/ip hotspot walled-garden add dst-host=banco.itau.com.br comment="Itau Banking"
/ip hotspot walled-garden add dst-host=mobile.itau.com.br comment="Itau Mobile App"
/ip hotspot walled-garden add dst-host=ww2.itau.com.br comment="Itau WW2"
/ip hotspot walled-garden add dst-host=bankline.itau.com.br comment="Itau Bankline"

# Nubank
/ip hotspot walled-garden add dst-host=nubank.com.br comment="Nubank"
/ip hotspot walled-garden add dst-host=*.nubank.com.br comment="Nubank API"
/ip hotspot walled-garden add dst-host=prod-s0-webapp-proxy.nubank.com.br comment="Nubank App"
/ip hotspot walled-garden add dst-host=prod-global-webapp-proxy.nubank.com.br comment="Nubank Global"

# Banco Inter
/ip hotspot walled-garden add dst-host=bancointer.com.br comment="Banco Inter"
/ip hotspot walled-garden add dst-host=*.bancointer.com.br comment="Inter Mobile"
/ip hotspot walled-garden add dst-host=internetbanking.bancointer.com.br comment="Inter Banking"
/ip hotspot walled-garden add dst-host=cdpj.bancointer.com.br comment="Inter CDPJ"

# Santander
/ip hotspot walled-garden add dst-host=santander.com.br comment="Santander Brasil"
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Santander Mobile"
/ip hotspot walled-garden add dst-host=internetbanking.santander.com.br comment="Santander Banking"
/ip hotspot walled-garden add dst-host=www.santander.com.br comment="Santander WWW"
/ip hotspot walled-garden add dst-host=api.santander.com.br comment="Santander API"

# Bradesco
/ip hotspot walled-garden add dst-host=bradesco.com.br comment="Bradesco"
/ip hotspot walled-garden add dst-host=*.bradesco.com.br comment="Bradesco Mobile"
/ip hotspot walled-garden add dst-host=mobile.bradesco.com.br comment="Bradesco Mobile App"
/ip hotspot walled-garden add dst-host=banco.bradesco.com.br comment="Bradesco Banco"
/ip hotspot walled-garden add dst-host=www.bradesco.com.br comment="Bradesco WWW"

# BRB
/ip hotspot walled-garden add dst-host=brb.com.br comment="BRB Banco"
/ip hotspot walled-garden add dst-host=*.brb.com.br comment="BRB Mobile"

# PicPay
/ip hotspot walled-garden add dst-host=picpay.com comment="PicPay"
/ip hotspot walled-garden add dst-host=*.picpay.com comment="PicPay API"
/ip hotspot walled-garden add dst-host=api.picpay.com comment="PicPay API Direct"

# Mercado Pago
/ip hotspot walled-garden add dst-host=mercadopago.com.br comment="Mercado Pago"
/ip hotspot walled-garden add dst-host=*.mercadopago.com.br comment="Mercado Pago API"
/ip hotspot walled-garden add dst-host=api.mercadopago.com comment="MP API"
/ip hotspot walled-garden add dst-host=*.mercadolibre.com comment="Mercado Libre"

# C6 Bank
/ip hotspot walled-garden add dst-host=c6bank.com.br comment="C6 Bank"
/ip hotspot walled-garden add dst-host=*.c6bank.com.br comment="C6 Bank Mobile"

# PagBank/PagSeguro
/ip hotspot walled-garden add dst-host=pagseguro.uol.com.br comment="PagSeguro"
/ip hotspot walled-garden add dst-host=*.pagseguro.uol.com.br comment="PagSeguro API"
/ip hotspot walled-garden add dst-host=api.pagseguro.com comment="PagSeguro API Direct"
/ip hotspot walled-garden add dst-host=pagbank.com.br comment="PagBank"
/ip hotspot walled-garden add dst-host=*.pagbank.com.br comment="PagBank API"

# Sicoob
/ip hotspot walled-garden add dst-host=sicoob.com.br comment="Sicoob"
/ip hotspot walled-garden add dst-host=*.sicoob.com.br comment="Sicoob Mobile"

# Sicredi
/ip hotspot walled-garden add dst-host=sicredi.com.br comment="Sicredi"
/ip hotspot walled-garden add dst-host=*.sicredi.com.br comment="Sicredi Mobile"

# Original
/ip hotspot walled-garden add dst-host=original.com.br comment="Banco Original"
/ip hotspot walled-garden add dst-host=*.original.com.br comment="Original Mobile"

# Neon
/ip hotspot walled-garden add dst-host=neon.com.br comment="Banco Neon"
/ip hotspot walled-garden add dst-host=*.neon.com.br comment="Neon Mobile"

# Next (Bradesco)
/ip hotspot walled-garden add dst-host=next.me comment="Next Bank"
/ip hotspot walled-garden add dst-host=*.next.me comment="Next Mobile"

# Ame Digital
/ip hotspot walled-garden add dst-host=amedigital.com comment="Ame Digital"
/ip hotspot walled-garden add dst-host=*.amedigital.com comment="Ame API"

# =====================
# GOV.BR E SERVIÇOS PÚBLICOS
# =====================
/ip hotspot walled-garden add dst-host=sso.acesso.gov.br comment="Login Gov.br"
/ip hotspot walled-garden add dst-host=*.acesso.gov.br comment="Acesso Gov.br"
/ip hotspot walled-garden add dst-host=gov.br comment="Gov.br"
/ip hotspot walled-garden add dst-host=*.gov.br comment="Serviços Gov.br"

# =====================
# DNS PÚBLICOS
# =====================
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="DNS Google"
/ip hotspot walled-garden add dst-host=8.8.4.4 comment="DNS Google 2"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="DNS Cloudflare"
/ip hotspot walled-garden add dst-host=1.0.0.1 comment="DNS Cloudflare 2"
/ip hotspot walled-garden add dst-host=208.67.222.222 comment="DNS OpenDNS"
/ip hotspot walled-garden add dst-host=208.67.220.220 comment="DNS OpenDNS 2"

# =====================
# WALLED GARDEN IP (para IPs diretos)
# =====================
/ip hotspot walled-garden ip add dst-address=138.68.255.122 action=accept comment="Portal IP"
/ip hotspot walled-garden ip add dst-address=104.0.0.0/8 action=accept comment="Cloudflare Range"

# ============================================================================
# 8. NAT E FIREWALL
# ============================================================================

:log info ">>> Configurando NAT e Firewall..."

# Masquerade para saída de internet
:if ([:len [/ip firewall nat find where comment="NAT-MIKROTIK"]] = 0) do={
    /ip firewall nat add chain=srcnat out-interface=ether1 action=masquerade comment="NAT-MIKROTIK"
}

# Permitir DNS
:if ([:len [/ip firewall filter find where comment="Allow DNS"]] = 0) do={
    /ip firewall filter add chain=forward protocol=udp dst-port=53 action=accept comment="Allow DNS"
    /ip firewall filter add chain=forward protocol=tcp dst-port=53 action=accept comment="Allow DNS TCP"
}

# ============================================================================
# 9. SCRIPTS DE SINCRONIZAÇÃO COM API
# ============================================================================

:log info ">>> Criando Scripts de Sincronização..."

# Script principal: Sincronizar usuários pagos
/system script remove [find name="liberarPagos"]
/system script add name="liberarPagos" policy=read,write,policy,test source={
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"

:log info "=== SYNC MACS PAGOS INICIADA ==="

:do {
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    
    :if (($result->"status") = "finished") do={
        :local payload ($result->"data")
        :log info ("Dados recebidos: " . [:len $payload] . " bytes")
        
        # Processar MACs para liberar
        :local pos 0
        :local maxLoop 50
        :local loopCount 0
        :local liberados 0
        
        :while ($loopCount < $maxLoop) do={
            :set loopCount ($loopCount + 1)
            
            :local macKey "\"mac_address\":\""
            :local macPos [:find $payload $macKey $pos]
            
            :if ([:typeof $macPos] = "nil") do={
                :set loopCount $maxLoop
            } else={
                :local macStart ($macPos + [:len $macKey])
                :local macEnd [:find $payload "\"" $macStart]
                
                :if ([:typeof $macEnd] != "nil") do={
                    :local mac [:pick $payload $macStart $macEnd]
                    :set pos ($macEnd + 1)
                    
                    :if ([:len $mac] = 17) do={
                        # Verificar se já existe
                        :local existente [/ip hotspot ip-binding find mac-address=$mac]
                        
                        :if ([:len $existente] = 0) do={
                            :log info ("[+] Liberando: " . $mac)
                            
                            # Limpar conflitos
                            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                            
                            # Criar bypass
                            :do {
                                /ip hotspot ip-binding add mac-address=$mac type=bypassed comment=$bypassComment disabled=no
                                :set liberados ($liberados + 1)
                            } on-error={}
                        }
                    }
                }
            }
        }
        
        :log info ("Total liberados: " . $liberados)
        
        # Processar MACs para remover (expirados)
        :local removePos [:find $payload "\"remove_macs\":"]
        :if ([:typeof $removePos] != "nil") do={
            :local pos2 $removePos
            :local loopCount2 0
            :local removidos 0
            
            :while ($loopCount2 < $maxLoop) do={
                :set loopCount2 ($loopCount2 + 1)
                
                :local macKey "\"mac_address\":\""
                :local macPos [:find $payload $macKey $pos2]
                
                :if ([:typeof $macPos] = "nil") do={
                    :set loopCount2 $maxLoop
                } else={
                    :local macStart ($macPos + [:len $macKey])
                    :local macEnd [:find $payload "\"" $macStart]
                    
                    :if ([:typeof $macEnd] != "nil") do={
                        :local mac [:pick $payload $macStart $macEnd]
                        :set pos2 ($macEnd + 1)
                        
                        :if ([:len $mac] = 17) do={
                            :log warning ("[-] Removendo expirado: " . $mac)
                            :do {/ip hotspot ip-binding remove [find mac-address=$mac]} on-error={}
                            :do {/ip hotspot user remove [find mac-address=$mac]} on-error={}
                            :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                            :set removidos ($removidos + 1)
                        }
                    }
                }
            }
            :log info ("Total removidos: " . $removidos)
        }
    }
} on-error={
    :log error "Erro ao consultar API"
}

:log info "=== SYNC MACS PAGOS FINALIZADA ==="
}

# Script: Registrar MACs conectados
/system script remove [find name="registrarMacs"]
/system script add name="registrarMacs" policy=read,write,policy,test source={
:local token "mikrotik-sync-2024"

:log info "=== REGISTRANDO MACS ==="

:foreach lease in=[/ip dhcp-server lease find where dynamic=yes] do={
    :local mac [/ip dhcp-server lease get $lease mac-address]
    :local ip [/ip dhcp-server lease get $lease address]
    
    # Ignorar MACs virtuais
    :if (([:len $mac] = 17) && ([:pick $mac 0 3] != "02:") && ([:len $ip] > 0)) do={
        :local url ("https://www.tocantinstransportewifi.com.br/api/mikrotik/register-mac?token=" . $token . "&mac=" . $mac . "&ip=" . $ip)
        
        :do {
            /tool fetch url=$url http-method=get mode=https keep-result=no check-certificate=no
        } on-error={
            :log warning ("Falha ao registrar: " . $mac)
        }
    }
}

:log info "=== REGISTRO FINALIZADO ==="
}

# ============================================================================
# 10. AGENDADORES (SCHEDULERS)
# ============================================================================

:log info ">>> Configurando Agendadores..."

# Remover agendadores antigos
/system scheduler remove [find name~"liberarPagos"]
/system scheduler remove [find name~"registrarMacs"]

# Sincronizar usuários pagos a cada 10 segundos
/system scheduler add \
    name="liberarPagosScheduler" \
    interval=10s \
    on-event="/system script run liberarPagos" \
    policy=read,write,policy,test \
    start-time=startup \
    comment="Sincroniza usuarios pagos com API"

# Registrar MACs a cada 30 segundos
/system scheduler add \
    name="registrarMacsScheduler" \
    interval=30s \
    on-event="/system script run registrarMacs" \
    policy=read,write,policy,test \
    start-time=startup \
    comment="Registra MACs conectados na API"

# ============================================================================
# 11. CONFIGURAÇÃO WiFi (RouterOS 7.x)
# ============================================================================

:log info ">>> Configurando WiFi..."

# Verificar se existe interface wifi
:if ([:len [/interface wifi find]] > 0) do={
    
    # Remover configurações antigas
    :do {/interface wifi configuration remove [find]} on-error={}
    :do {/interface wifi datapath remove [find]} on-error={}
    :do {/interface wifi security remove [find]} on-error={}
    
    # Criar datapath
    /interface wifi datapath add name=hotspot-datapath bridge=$hotspotInterface
    
    # Criar configuração 2.4GHz
    /interface wifi configuration add \
        name=tocantins-2g \
        mode=ap \
        ssid="TocantinsTransporteWiFi" \
        country=Brazil \
        channel.band=2ghz-ax \
        manager=local
    
    # Criar configuração 5GHz
    /interface wifi configuration add \
        name=tocantins-5g \
        mode=ap \
        ssid="TocantinsTransporteWiFi-5G" \
        country=Brazil \
        channel.band=5ghz-ax \
        manager=local
    
    # Aplicar nas interfaces
    :foreach wifiIface in=[/interface wifi find] do={
        :local ifaceName [/interface wifi get $wifiIface name]
        
        :if ($ifaceName = "wifi1") do={
            /interface wifi set $wifiIface configuration=tocantins-2g datapath=hotspot-datapath disabled=no
        }
        
        :if ($ifaceName = "wifi2") do={
            /interface wifi set $wifiIface configuration=tocantins-5g datapath=hotspot-datapath disabled=no
        }
    }
    
    :log info "WiFi configurado com sucesso!"
} else={
    :log warning "Nenhuma interface WiFi encontrada"
}

# ============================================================================
# 12. ADICIONAR PORTAS ETHERNET À BRIDGE (se necessário)
# ============================================================================

:log info ">>> Configurando portas na bridge..."

# Adicionar ether2-ether5 à bridge do hotspot (exceto ether1 que é WAN)
:foreach port in={"ether2"; "ether3"; "ether4"; "ether5"} do={
    :if ([:len [/interface bridge port find interface=$port bridge=$hotspotInterface]] = 0) do={
        :do {
            /interface bridge port add bridge=$hotspotInterface interface=$port
            :log info ("Porta " . $port . " adicionada à bridge")
        } on-error={}
    }
}

# ============================================================================
# 13. CERTIFICADO SSL PARA HOTSPOT
# ============================================================================

:log info ">>> Configurando Certificado SSL..."

# Criar CA local se não existir
:if ([:len [/certificate find name="local-ca"]] = 0) do={
    /certificate add name=local-ca common-name="WiFi Tocantins CA" key-size=2048 days-valid=3650 key-usage=key-cert-sign,crl-sign
    /certificate sign local-ca
}

# Criar certificado do hotspot
:if ([:len [/certificate find name="hotspot-cert"]] = 0) do={
    /certificate add name=hotspot-cert common-name=login.tocantinswifi.local key-size=2048 days-valid=3650
    /certificate sign hotspot-cert ca=local-ca
}

# Aplicar certificado no perfil do hotspot
:do {
    /ip hotspot profile set [find name="hsprof-tocantins"] ssl-certificate=hotspot-cert
} on-error={}

# ============================================================================
# FINALIZAÇÃO
# ============================================================================

:log info "=============================================="
:log info "CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!"
:log info "=============================================="
:log info "Portal: https://www.tocantinstransportewifi.com.br"
:log info "Hotspot: login.tocantinswifi.local"
:log info "Rede: 10.5.50.0/24"
:log info "Gateway: 10.5.50.1"
:log info "=============================================="
:log info "Scripts ativos:"
:log info "- liberarPagos (a cada 10s)"
:log info "- registrarMacs (a cada 30s)"
:log info "=============================================="

# Mostrar status
/ip hotspot print
/interface wifi print
