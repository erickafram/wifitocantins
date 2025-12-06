# ============================================================================
# MikroTik RouterOS 7.20.5 - WiFi Tocantins Hotspot Configuration
# Modelo: RBD52G-5HacD2HnD (hAP ac²)
# Data: 2025-12-02
# ============================================================================
# INSTRUCOES:
# 1. Resetar o MikroTik (segure reset por 5s ao ligar)
# 2. Acessar via WinBox (IP 192.168.88.1)
# 3. Ir em Files > Upload e enviar este arquivo
# 4. Abrir Terminal e executar: /import file-name=mikrotik-config-v7.20.5.rsc
# ============================================================================

# Aguardar sistema inicializar
:delay 2s

:log info "=== INICIANDO CONFIGURACAO WIFI TOCANTINS ==="

# ============================================================================
# PARTE 1: BRIDGES (usa a bridge padrao existente)
# ============================================================================
:log info "Configurando Bridges..."

# Renomear bridge padrao para bridgeLocal (se existir)
:do { /interface bridge set [find name=bridge] name=bridgeLocal } on-error={}

# Se nao existir bridgeLocal, criar
:if ([:len [/interface bridge find name=bridgeLocal]] = 0) do={
    /interface bridge add name=bridgeLocal auto-mac=no comment="Bridge para Internet/Uplink"
}

# Criar bridge para hotspot WiFi (se nao existir)
:if ([:len [/interface bridge find name=wifi-hotspot]] = 0) do={
    /interface bridge add name=wifi-hotspot fast-forward=no comment="Bridge para Hotspot WiFi"
}

# ============================================================================
# PARTE 2: BRIDGE PORTS (mover portas para bridgeLocal)
# ============================================================================
:log info "Configurando Bridge Ports..."

# Mover portas para bridgeLocal (nao remove, apenas move)
:do { /interface bridge port set [find interface=ether1] bridge=bridgeLocal } on-error={ :do { /interface bridge port add bridge=bridgeLocal interface=ether1 } on-error={} }
:do { /interface bridge port set [find interface=ether2] bridge=bridgeLocal } on-error={ :do { /interface bridge port add bridge=bridgeLocal interface=ether2 } on-error={} }
:do { /interface bridge port set [find interface=ether3] bridge=bridgeLocal } on-error={ :do { /interface bridge port add bridge=bridgeLocal interface=ether3 } on-error={} }
:do { /interface bridge port set [find interface=ether4] bridge=bridgeLocal } on-error={ :do { /interface bridge port add bridge=bridgeLocal interface=ether4 } on-error={} }
:do { /interface bridge port set [find interface=ether5] bridge=bridgeLocal } on-error={ :do { /interface bridge port add bridge=bridgeLocal interface=ether5 } on-error={} }

# ============================================================================
# PARTE 3: WIFI CONFIGURATION (RouterOS 7.x)
# ============================================================================
:log info "Configurando WiFi..."

# Datapath para WiFi -> Hotspot Bridge
:do { /interface wifi datapath add name=capdp bridge=wifi-hotspot comment="Datapath Hotspot" } on-error={}

# Seguranca aberta (sem senha)
:do { /interface wifi security add name=open-security authentication-types="" comment="Rede Aberta" } on-error={}

# Configuracao WiFi 2.4GHz
:do { /interface wifi configuration add name=tocantins-2g country=Brazil security=open-security ssid=TocantinsTransporteWiFi } on-error={}

# Configuracao WiFi 5GHz
:do { /interface wifi configuration add name=tocantins-5g country=Brazil security=open-security ssid=TocantinsTransporteWiFi-5G } on-error={}

# Aplicar configuracao nas interfaces WiFi
:do { /interface wifi set [find default-name=wifi1] configuration=tocantins-2g configuration.manager=local configuration.mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no } on-error={}
:do { /interface wifi set [find default-name=wifi2] configuration=tocantins-5g configuration.manager=local configuration.mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no } on-error={}

# ============================================================================
# PARTE 4: IP ADDRESSING
# ============================================================================
:log info "Configurando IPs..."

# IP do Hotspot Gateway (se nao existir)
:if ([:len [/ip address find address="10.5.50.1/24"]] = 0) do={
    /ip address add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0 comment="Hotspot Gateway"
}

# ============================================================================
# PARTE 5: DHCP CLIENT (para receber internet)
# ============================================================================
:log info "Configurando DHCP Client..."

# Atualizar DHCP client existente ou criar novo
# Configurar DHCP client na bridgeLocal
:do {
    :local existingDhcp [/ip dhcp-client find]
    :if ([:len $existingDhcp] > 0) do={
        /ip dhcp-client set ($existingDhcp->0) interface=bridgeLocal disabled=no
    } else={
        /ip dhcp-client add interface=bridgeLocal disabled=no comment="Internet Uplink"
    }
} on-error={
    :do { /ip dhcp-client add interface=bridgeLocal disabled=no comment="Internet Uplink" } on-error={}
}

# ============================================================================
# PARTE 6: IP POOL
# ============================================================================
:log info "Configurando IP Pool..."

# Criar pool se nao existir
:if ([:len [/ip pool find name=hs-pool]] = 0) do={
    /ip pool add name=hs-pool ranges=10.5.50.10-10.5.50.250 comment="Pool Hotspot"
}

# ============================================================================
# PARTE 7: DHCP SERVER
# ============================================================================
:log info "Configurando DHCP Server..."

:if ([:len [/ip dhcp-server find name=hotspot-dhcp]] = 0) do={
    /ip dhcp-server add name=hotspot-dhcp address-pool=hs-pool interface=wifi-hotspot disabled=no
}

:if ([:len [/ip dhcp-server network find address="10.5.50.0/24"]] = 0) do={
    /ip dhcp-server network add address=10.5.50.0/24 gateway=10.5.50.1 dns-server=1.1.1.1,8.8.8.8 comment="Hotspot Network"
}

# ============================================================================
# PARTE 8: DNS
# ============================================================================
:log info "Configurando DNS..."

/ip dns set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8

# DNS Estatico para o Portal
:do { /ip dns static add name=tocantinstransportewifi.com.br address=138.68.255.122 type=A comment="Portal Principal" } on-error={}
:do { /ip dns static add name=www.tocantinstransportewifi.com.br address=138.68.255.122 type=A comment="Portal WWW" } on-error={}
:do { /ip dns static add name=portal.wifi address=138.68.255.122 type=A comment="Portal Curto" } on-error={}
:do { /ip dns static add name=conectar.wifi address=138.68.255.122 type=A comment="Portal Curto" } on-error={}

# ============================================================================
# PARTE 9: HOTSPOT PROFILE
# ============================================================================
:log info "Configurando Hotspot Profile..."

:if ([:len [/ip hotspot profile find name=hsprof-tocantins]] = 0) do={
    /ip hotspot profile add name=hsprof-tocantins hotspot-address=10.5.50.1 dns-name=login.tocantinswifi.local html-directory=flash/hotspot http-cookie-lifetime=1d login-by=cookie,http-chap,http-pap
}

# ============================================================================
# PARTE 10: HOTSPOT
# ============================================================================
:log info "Configurando Hotspot..."

:if ([:len [/ip hotspot find name=tocantins-hotspot]] = 0) do={
    /ip hotspot add name=tocantins-hotspot interface=wifi-hotspot address-pool=hs-pool profile=hsprof-tocantins disabled=no
}

# ============================================================================
# PARTE 11: FIREWALL NAT
# ============================================================================
:log info "Configurando NAT..."

:do { /ip firewall nat add chain=srcnat out-interface=bridgeLocal action=masquerade comment="NAT-GERAL" } on-error={}
:do { /ip firewall nat add chain=srcnat src-address=10.5.50.0/24 out-interface=bridgeLocal action=masquerade comment="HOTSPOT-MASQUERADE" } on-error={}

# ============================================================================
# PARTE 12: FIREWALL FILTER
# ============================================================================
:log info "Configurando Firewall..."

:do { /ip firewall filter add chain=forward protocol=udp dst-port=53 action=accept comment="Allow DNS" } on-error={}

# ============================================================================
# PARTE 13: WALLED GARDEN (Sites liberados antes do login)
# ============================================================================
:log info "Configurando Walled Garden..."

# Portal Principal
:do { /ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Portal Principal" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Portal Wildcard" } on-error={}
:do { /ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br comment="Portal WWW" } on-error={}
:do { /ip hotspot walled-garden add dst-host=10.5.50.1 comment="Gateway Local" } on-error={}

# CDNs para CSS/JS do Portal
:do { /ip hotspot walled-garden add dst-host=cdn.tailwindcss.com comment="Tailwind CSS" } on-error={}
:do { /ip hotspot walled-garden add dst-host=fonts.googleapis.com comment="Google Fonts API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=fonts.gstatic.com comment="Google Fonts Static" } on-error={}
:do { /ip hotspot walled-garden add dst-host=cdnjs.cloudflare.com comment="Cloudflare CDN" } on-error={}
:do { /ip hotspot walled-garden add dst-host=ajax.googleapis.com comment="Google AJAX" } on-error={}

# APIs de Pagamento PIX - Woovi/OpenPix
:do { /ip hotspot walled-garden add dst-host=api.woovi.com comment="Woovi API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=woovi.com comment="Woovi Domain" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.woovi.com comment="Woovi Subdomains" } on-error={}
:do { /ip hotspot walled-garden add dst-host=app.woovi.com comment="Woovi Dashboard" } on-error={}
:do { /ip hotspot walled-garden add dst-host=api.openpix.com.br comment="OpenPix API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=openpix.com.br comment="OpenPix Domain" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.openpix.com.br comment="OpenPix Subdomains" } on-error={}

# QR Code Generators
:do { /ip hotspot walled-garden add dst-host=api.qrserver.com comment="QR Code Generator" } on-error={}
:do { /ip hotspot walled-garden add dst-host=qrserver.com comment="QR Code Domain" } on-error={}
:do { /ip hotspot walled-garden add dst-host=chart.googleapis.com comment="Google Charts QR" } on-error={}

# PIX Banco Central
:do { /ip hotspot walled-garden add dst-host=pix.bcb.gov.br comment="PIX Banco Central" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.bcb.gov.br comment="Banco Central BR" } on-error={}

# Bancos - Banco do Brasil
:do { /ip hotspot walled-garden add dst-host=bb.com.br comment="Banco do Brasil" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.bb.com.br comment="BB Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=www37.bb.com.br comment="BB App" } on-error={}
:do { /ip hotspot walled-garden add dst-host=api.bb.com.br comment="BB API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=mobile.bb.com.br comment="BB Mobile App" } on-error={}

# Bancos - Caixa
:do { /ip hotspot walled-garden add dst-host=caixa.gov.br comment="Caixa CEF" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.caixa.gov.br comment="Caixa Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=internetbanking.caixa.gov.br comment="Caixa Internet Banking" } on-error={}

# Bancos - Itau
:do { /ip hotspot walled-garden add dst-host=itau.com.br comment="Itau Unibanco" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.itau.com.br comment="Itau Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=banco.itau.com.br comment="Itau Banking" } on-error={}
:do { /ip hotspot walled-garden add dst-host=mobile.itau.com.br comment="Itau Mobile App" } on-error={}

# Bancos - Nubank
:do { /ip hotspot walled-garden add dst-host=nubank.com.br comment="Nubank" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.nubank.com.br comment="Nubank API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=prod-s0-webapp-proxy.nubank.com.br comment="Nubank App" } on-error={}

# Bancos - Inter
:do { /ip hotspot walled-garden add dst-host=bancointer.com.br comment="Banco Inter" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.bancointer.com.br comment="Inter Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=internetbanking.bancointer.com.br comment="Inter Banking" } on-error={}

# Bancos - Santander
:do { /ip hotspot walled-garden add dst-host=santander.com.br comment="Santander Brasil" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.santander.com.br comment="Santander Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=internetbanking.santander.com.br comment="Santander Banking" } on-error={}

# Bancos - Bradesco
:do { /ip hotspot walled-garden add dst-host=bradesco.com.br comment="Bradesco" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.bradesco.com.br comment="Bradesco Mobile" } on-error={}
:do { /ip hotspot walled-garden add dst-host=mobile.bradesco.com.br comment="Bradesco Mobile App" } on-error={}

# Bancos - BRB
:do { /ip hotspot walled-garden add dst-host=brb.com.br comment="BRB Banco" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.brb.com.br comment="BRB Mobile" } on-error={}

# Bancos - C6
:do { /ip hotspot walled-garden add dst-host=c6bank.com.br comment="C6 Bank" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.c6bank.com.br comment="C6 Bank Mobile" } on-error={}

# Carteiras Digitais
:do { /ip hotspot walled-garden add dst-host=picpay.com comment="PicPay" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.picpay.com comment="PicPay API" } on-error={}
:do { /ip hotspot walled-garden add dst-host=mercadopago.com.br comment="Mercado Pago" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.mercadopago.com.br comment="Mercado Pago API" } on-error={}

# Gov.br
:do { /ip hotspot walled-garden add dst-host=sso.acesso.gov.br comment="Login Gov.br" } on-error={}
:do { /ip hotspot walled-garden add dst-host=*.acesso.gov.br comment="Acesso Gov.br" } on-error={}

# DNS Publicos
:do { /ip hotspot walled-garden add dst-host=8.8.8.8 comment="DNS Google" } on-error={}
:do { /ip hotspot walled-garden add dst-host=1.1.1.1 comment="DNS Cloudflare" } on-error={}
:do { /ip hotspot walled-garden add dst-host=208.67.222.222 comment="DNS OpenDNS" } on-error={}

# ============================================================================
# PARTE 14: WALLED GARDEN IP (IPs liberados)
# ============================================================================
:log info "Configurando Walled Garden IP..."

:do { /ip hotspot walled-garden ip add action=accept dst-address=138.68.255.122 comment="Servidor Portal" } on-error={}
:do { /ip hotspot walled-garden ip add action=accept dst-address=104.16.0.0/12 comment="Cloudflare Range" } on-error={}
:do { /ip hotspot walled-garden ip add action=accept dst-address=172.67.210.11 comment="Portal HTTPS Cloudflare" } on-error={}
:do { /ip hotspot walled-garden ip add action=accept dst-address=104.21.65.182 comment="Portal HTTPS Cloudflare" } on-error={}

# ============================================================================
# PARTE 15: SYSTEM CLOCK E NTP
# ============================================================================
:log info "Configurando Relogio e NTP..."

/system clock set time-zone-name=America/Araguaina
/system ntp client set enabled=yes
:do { /system ntp client servers add address=pool.ntp.br } on-error={}
:do { /system ntp client servers add address=a.ntp.br } on-error={}

# ============================================================================
# PARTE 16: LOGGING
# ============================================================================
:log info "Configurando Logging..."

:do { /system logging add topics=hotspot } on-error={}
:do { /system logging add topics=dhcp } on-error={}

# ============================================================================
# PARTE 17: SCRIPTS
# ============================================================================
:log info "Configurando Scripts..."

# Remover scripts antigos se existirem
:do { /system script remove [find name=registrarMacsAutomatico] } on-error={}
:do { /system script remove [find name=liberarPagos] } on-error={}

# Script para registrar MACs automaticamente
/system script add name=registrarMacsAutomatico owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":local token \"mikrotik-sync-2024\"\r\
    \n:foreach lease in=[/ip dhcp-server lease find where dynamic=yes] do={\r\
    \n    :local mac [/ip dhcp-server lease get \$lease mac-address]\r\
    \n    :local ip [/ip dhcp-server lease get \$lease address]\r\
    \n    :if (([:len \$mac] = 17) && ([:pick \$mac 0 3] != \"02:\") && ([:len \$ip] > 0)) do={\r\
    \n        :local url (\"https://www.tocantinstransportewifi.com.br/api/mikrotik/register-mac\?token=\" . \$token . \"&mac=\" . \$mac . \"&ip=\" . \$ip)\r\
    \n        :do {\r\
    \n            /tool fetch url=\$url http-method=get mode=https keep-result=no check-certificate=no\r\
    \n        } on-error={\r\
    \n            :log warning (\"Falha ao registrar MAC \" . \$mac)\r\
    \n        }\r\
    \n    }\r\
    \n}"

# Script para liberar usuarios pagos
/system script add name=liberarPagos owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":local url \"https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users\?token=mikrotik-sync-2024\"\r\
    \n:local bypassComment \"PAGO-AUTO\"\r\
    \n:log info \"=== SYNC MACS PAGOS INICIADA ===\"\r\
    \n\r\
    \n:local result [/tool fetch url=\$url mode=https http-method=get output=user check-certificate=no as-value]\r\
    \n:if ([:typeof \$result] = \"nothing\") do={\r\
    \n    :log error \"Fetch falhou\"\r\
    \n    :return\r\
    \n}\r\
    \n:if ((\$result->\"status\") != \"finished\") do={\r\
    \n    :log error \"Fetch nao completou\"\r\
    \n    :return\r\
    \n}\r\
    \n\r\
    \n:local payload (\$result->\"data\")\r\
    \n:if ([:len \$payload] = 0) do={\r\
    \n    :log warning \"Payload vazio\"\r\
    \n    :return\r\
    \n}\r\
    \n:log info (\"Dados recebidos: \" . [:len \$payload] . \" bytes\")\r\
    \n\r\
    \n:local liberados 0\r\
    \n:local removidos 0\r\
    \n:local macsLiberados \"\"\r\
    \n\r\
    \n:local removePos [:find \$payload \"\\\"remove_macs\\\"\"]\r\
    \n:if ([:typeof \$removePos] = \"nil\") do={ :set removePos [:len \$payload] }\r\
    \n\r\
    \n:local pos 0\r\
    \n:local maxLoop 50\r\
    \n:local loopCount 0\r\
    \n\r\
    \n:while (\$loopCount < \$maxLoop) do={\r\
    \n    :set loopCount (\$loopCount + 1)\r\
    \n    :local macKey \"\\\"mac_address\\\":\\\"\"\r\
    \n    :local macPos [:find \$payload \$macKey \$pos]\r\
    \n    :if ([:typeof \$macPos] = \"nil\" || \$macPos >= \$removePos) do={\r\
    \n        :set loopCount \$maxLoop\r\
    \n    } else={\r\
    \n        :local macStart (\$macPos + 15)\r\
    \n        :local macEnd [:find \$payload \"\\\"\" \$macStart]\r\
    \n        :if ([:typeof \$macEnd] != \"nil\") do={\r\
    \n            :local mac [:pick \$payload \$macStart \$macEnd]\r\
    \n            :set pos (\$macEnd + 1)\r\
    \n            :if ([:len \$mac] = 17) do={\r\
    \n                :set macsLiberados (\$macsLiberados . \$mac . \",\")\r\
    \n                :local existente [/ip hotspot ip-binding find mac-address=\$mac]\r\
    \n                :if ([:len \$existente] = 0) do={\r\
    \n                    :log info (\"[+] Liberando: \" . \$mac)\r\
    \n                    :do {\r\
    \n                        /ip hotspot ip-binding add mac-address=\$mac type=bypassed comment=\$bypassComment disabled=no\r\
    \n                        :set liberados (\$liberados + 1)\r\
    \n                    } on-error={}\r\
    \n                }\r\
    \n            }\r\
    \n        } else={\r\
    \n            :set loopCount \$maxLoop\r\
    \n        }\r\
    \n    }\r\
    \n}\r\
    \n\r\
    \n:local pos2 (\$removePos + 15)\r\
    \n:local loopCount2 0\r\
    \n:while (\$loopCount2 < \$maxLoop) do={\r\
    \n    :set loopCount2 (\$loopCount2 + 1)\r\
    \n    :local macKey \"\\\"mac_address\\\":\\\"\"\r\
    \n    :local macPos [:find \$payload \$macKey \$pos2]\r\
    \n    :if ([:typeof \$macPos] = \"nil\") do={\r\
    \n        :set loopCount2 \$maxLoop\r\
    \n    } else={\r\
    \n        :local macStart (\$macPos + 15)\r\
    \n        :local macEnd [:find \$payload \"\\\"\" \$macStart]\r\
    \n        :if ([:typeof \$macEnd] != \"nil\") do={\r\
    \n            :local mac [:pick \$payload \$macStart \$macEnd]\r\
    \n            :set pos2 (\$macEnd + 1)\r\
    \n            :if ([:len \$mac] = 17) do={\r\
    \n                :if ([:find \$macsLiberados \$mac] < 0) do={\r\
    \n                    :log warning (\"[-] Removendo: \" . \$mac)\r\
    \n                    :do {\r\
    \n                        /ip hotspot ip-binding remove [find mac-address=\$mac]\r\
    \n                        :set removidos (\$removidos + 1)\r\
    \n                    } on-error={}\r\
    \n                } else={\r\
    \n                    :log info (\"[PROTEGIDO] \" . \$mac)\r\
    \n                }\r\
    \n            }\r\
    \n        } else={\r\
    \n            :set loopCount2 \$maxLoop\r\
    \n        }\r\
    \n    }\r\
    \n}\r\
    \n\r\
    \n:log info (\"Liberados: \" . \$liberados . \" | Removidos: \" . \$removidos)\r\
    \n:log info \"=== SYNC FINALIZADA ==\""

# ============================================================================
# PARTE 18: SCHEDULERS
# ============================================================================
:log info "Configurando Schedulers..."

# Remover schedulers antigos se existirem
:do { /system scheduler remove [find name=registrarMacsScheduler] } on-error={}
:do { /system scheduler remove [find name=liberarPagosScheduler] } on-error={}

/system scheduler add name=registrarMacsScheduler interval=30s on-event=registrarMacsAutomatico policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup

/system scheduler add name=liberarPagosScheduler interval=1m on-event=liberarPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon start-time=startup

# ============================================================================
# PARTE 19: HOTSPOT USERS
# ============================================================================
:log info "Configurando Usuarios Hotspot..."

:do { /ip hotspot user add name=test comment="Usuario de Teste" } on-error={}
:do { /ip hotspot user add name=guest comment="Usuario Convidado" } on-error={}

# ============================================================================
# PARTE 20: INTERFACE CAP (CAPsMAN)
# ============================================================================
:log info "Configurando CAP..."

:do { /interface wifi cap set discovery-interfaces=bridgeLocal enabled=yes slaves-datapath=capdp } on-error={}

# ============================================================================
# FINALIZACAO
# ============================================================================
:log info "=== CONFIGURACAO WIFI TOCANTINS CONCLUIDA ==="
:log info "Redes WiFi: TocantinsTransporteWiFi (2.4GHz) e TocantinsTransporteWiFi-5G (5GHz)"
:log info "Captive Portal: login.tocantinswifi.local"
:log info "API Sync: Ativa (30s registro, 1min liberacao)"

:beep frequency=1000 length=200ms
:delay 300ms
:beep frequency=1500 length=200ms
