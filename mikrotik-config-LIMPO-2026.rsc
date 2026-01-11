# ============================================================
# CONFIGURAÇÃO LIMPA - TocantinsTransporte WiFi
# Data: 2026-01-10
# VERSÃO OTIMIZADA - Sem encher memória
# ============================================================

# PARTE 1: BRIDGES
/interface bridge
add admin-mac=D4:01:C3:C6:29:4A auto-mac=no comment=defconf name=bridgeLocal
add fast-forward=no name=wifi-hotspot

# PARTE 2: WIFI DATAPATH E SECURITY
/interface wifi datapath
add bridge=wifi-hotspot name=capdp

/interface wifi security
add authentication-types="" name=open-security

/interface wifi configuration
add country=Brazil name=tocantins-2g security=open-security ssid=TocantinsTransporteWiFi
add country=Brazil name=tocantins-5g security=open-security ssid=TocantinsTransporteWiFi-5G

# PARTE 3: CONFIGURAR WIFI
/interface wifi
set [ find default-name=wifi1 ] configuration=tocantins-2g configuration.manager=local .mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no
set [ find default-name=wifi2 ] configuration=tocantins-5g configuration.manager=local .mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no

# PARTE 4: HOTSPOT PROFILE
/ip hotspot profile
add dns-name=hotspot.wifi hotspot-address=10.5.50.1 html-directory=flash/hotspot http-cookie-lifetime=1d login-by=cookie,http-chap,http-pap name=hsprof-tocantins

# PARTE 5: POOLS
/ip pool
add name=hs-pool ranges=10.5.50.10-10.5.50.250

# PARTE 6: DHCP SERVER
/ip dhcp-server
add address-pool=hs-pool interface=wifi-hotspot name=hotspot-dhcp

# PARTE 7: HOTSPOT
/ip hotspot
add address-pool=hs-pool disabled=no interface=wifi-hotspot name=tocantins-hotspot profile=hsprof-tocantins

# PARTE 8: BRIDGE PORTS
/interface bridge port
add bridge=bridgeLocal comment=defconf interface=ether1
add bridge=bridgeLocal comment=defconf interface=ether2
add bridge=bridgeLocal comment=defconf interface=ether3
add bridge=bridgeLocal comment=defconf interface=ether4
add bridge=bridgeLocal comment=defconf interface=ether5

# PARTE 9: WIFI CAP
/interface wifi cap
set discovery-interfaces=bridgeLocal enabled=yes slaves-datapath=capdp

# PARTE 10: IP ADDRESS
/ip address
add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0

# PARTE 11: DHCP CLIENT (WAN)
/ip dhcp-client
add comment=defconf interface=bridgeLocal

# PARTE 12: DHCP SERVER NETWORK
/ip dhcp-server network
add address=10.5.50.0/24 comment="hotspot network" dns-server=1.1.1.1,8.8.8.8 gateway=10.5.50.1

# PARTE 13: DNS
/ip dns
set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8

# PARTE 14: DNS ESTÁTICO (MÍNIMO)
/ip dns static
add address=138.68.255.122 name=tocantinstransportewifi.com.br
add address=138.68.255.122 name=www.tocantinstransportewifi.com.br
add address=10.5.50.1 name=hotspot.wifi

# PARTE 15: NAT (MÍNIMO)
/ip firewall nat
add action=masquerade chain=srcnat out-interface=bridgeLocal comment="NAT-GERAL"

# PARTE 16: WALLED GARDEN - APENAS O ESSENCIAL
/ip hotspot walled-garden
add dst-host=tocantinstransportewifi.com.br server=tocantins-hotspot comment="Portal"
add dst-host=*.tocantinstransportewifi.com.br server=tocantins-hotspot comment="Portal Wildcard"
add dst-host=www.tocantinstransportewifi.com.br server=tocantins-hotspot comment="Portal WWW"
add dst-host=10.5.50.1 server=tocantins-hotspot comment="Gateway"
add dst-host=api.woovi.com server=tocantins-hotspot comment="Woovi PIX"
add dst-host=*.woovi.com server=tocantins-hotspot comment="Woovi"
add dst-host=api.openpix.com.br server=tocantins-hotspot comment="OpenPix"
add dst-host=*.openpix.com.br server=tocantins-hotspot comment="OpenPix"
add dst-host=fonts.googleapis.com server=tocantins-hotspot comment="Fonts"
add dst-host=fonts.gstatic.com server=tocantins-hotspot comment="Fonts"
add dst-host=cdn.tailwindcss.com server=tocantins-hotspot comment="CSS"

# PARTE 17: WALLED GARDEN IP - APENAS O ESSENCIAL
/ip hotspot walled-garden ip
add action=accept dst-address=138.68.255.122 server=tocantins-hotspot comment="Portal IP"
add action=accept dst-address=104.16.0.0/12 comment="Cloudflare"
add action=accept dst-address=10.5.50.1 server=tocantins-hotspot comment="Gateway"

# PARTE 18: CLOCK
/system clock
set time-zone-name=America/Araguaina

# PARTE 19: LOGGING (MÍNIMO - só memória)
/system logging
set 0 action=memory
set 1 action=memory
set 2 action=memory

# PARTE 20: NETWATCH
/tool netwatch
add host=8.8.8.8 timeout=3s type=simple
