# ============================================================================
# CORREÇÃO MIKROTIK - WiFi Tocantins
# RouterOS 7.20.5 - RBD52G-5HacD2HnD
# Data: 2025-12-02
# ============================================================================
# 
# INSTRUÇÕES:
# 1. Faça backup: /system backup save name=backup-antes-fix
# 2. Execute os comandos abaixo NO TERMINAL do MikroTik
# 3. OU importe: /import file-name=mikrotik-fix.rsc
#
# ============================================================================

# ============================================================================
# PASSO 1: REMOVER CONFIGURAÇÕES CONFLITANTES
# ============================================================================

# Remover interfaces wireless da bridge (estão usando interface errada)
/interface bridge port remove [find interface=wlan1]
/interface bridge port remove [find interface=wlan2]

# Desabilitar interfaces wireless legadas
/interface wireless set [find default-name=wlan1] disabled=yes
/interface wireless set [find default-name=wlan2] disabled=yes

# ============================================================================
# PASSO 2: CONFIGURAR WIFI MODERNO (igual ao que funciona)
# ============================================================================

# Criar datapath para hotspot
:if ([:len [/interface wifi datapath find name=capdp]] = 0) do={
    /interface wifi datapath add bridge=wifi-hotspot name=capdp
}

# Criar security profile aberto
:if ([:len [/interface wifi security find name=open-security]] = 0) do={
    /interface wifi security add authentication-types="" name=open-security
}

# Criar configurações WiFi
:if ([:len [/interface wifi configuration find name=tocantins-2g]] = 0) do={
    /interface wifi configuration add country=Brazil name=tocantins-2g security=open-security ssid=TocantinsTransporteWiFi
}

:if ([:len [/interface wifi configuration find name=tocantins-5g]] = 0) do={
    /interface wifi configuration add country=Brazil name=tocantins-5g security=open-security ssid=TocantinsTransporteWiFi-5G
}

# Configurar interfaces WiFi modernas
/interface wifi set [find default-name=wifi1] configuration=tocantins-2g configuration.manager=local .mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no
/interface wifi set [find default-name=wifi2] configuration=tocantins-5g configuration.manager=local .mode=ap datapath=capdp datapath.bridge=wifi-hotspot disabled=no

# ============================================================================
# PASSO 3: CONFIGURAR BRIDGE E HOTSPOT
# ============================================================================

# Garantir que bridge wifi-hotspot existe
:if ([:len [/interface bridge find name=wifi-hotspot]] = 0) do={
    /interface bridge add fast-forward=no name=wifi-hotspot
}

# Garantir IP do hotspot
:if ([:len [/ip address find interface=wifi-hotspot]] = 0) do={
    /ip address add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0
}

# Garantir pool do hotspot
:if ([:len [/ip pool find name=hs-pool]] = 0) do={
    /ip pool add name=hs-pool ranges=10.5.50.10-10.5.50.250
}

# Garantir DHCP server do hotspot
:if ([:len [/ip dhcp-server find name=hotspot-dhcp]] = 0) do={
    /ip dhcp-server add address-pool=hs-pool interface=wifi-hotspot name=hotspot-dhcp
}

# Garantir rede DHCP do hotspot
:if ([:len [/ip dhcp-server network find address="10.5.50.0/24"]] = 0) do={
    /ip dhcp-server network add address=10.5.50.0/24 comment="hotspot network" dns-server=1.1.1.1,8.8.8.8 gateway=10.5.50.1
}

# ============================================================================
# PASSO 4: CONFIGURAR HOTSPOT PROFILE E SERVER
# ============================================================================

# Remover hotspot existente para recriar
:do { /ip hotspot remove [find name=tocantins-hotspot] } on-error={}

# Garantir profile do hotspot
:if ([:len [/ip hotspot profile find name=hsprof-tocantins]] = 0) do={
    /ip hotspot profile add dns-name=login.tocantinswifi.local hotspot-address=10.5.50.1 html-directory=flash/hotspot http-cookie-lifetime=1d login-by=cookie,http-chap,http-pap name=hsprof-tocantins
}

# Criar hotspot server
/ip hotspot add address-pool=hs-pool disabled=no interface=wifi-hotspot name=tocantins-hotspot profile=hsprof-tocantins

# ============================================================================
# PASSO 5: CONFIGURAR DNS
# ============================================================================

/ip dns set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8

# DNS estático para o portal
:if ([:len [/ip dns static find name=tocantinstransportewifi.com.br]] = 0) do={
    /ip dns static add address=138.68.255.122 comment="Portal Principal" name=tocantinstransportewifi.com.br type=A
}
:if ([:len [/ip dns static find name=www.tocantinstransportewifi.com.br]] = 0) do={
    /ip dns static add address=138.68.255.122 comment="Portal WWW" name=www.tocantinstransportewifi.com.br type=A
}

# ============================================================================
# PASSO 6: CONFIGURAR NAT (CRÍTICO PARA INTERNET)
# ============================================================================

# Remover NAT duplicados
/ip firewall nat remove [find comment~"HOTSPOT"]
/ip firewall nat remove [find comment~"hotspot"]

# Adicionar NAT correto
:if ([:len [/ip firewall nat find comment="NAT-GERAL"]] = 0) do={
    /ip firewall nat add action=masquerade chain=srcnat comment="NAT-GERAL" out-interface=bridgeLocal
}

:if ([:len [/ip firewall nat find comment="HOTSPOT-MASQUERADE"]] = 0) do={
    /ip firewall nat add action=masquerade chain=srcnat comment="HOTSPOT-MASQUERADE" out-interface=bridgeLocal src-address=10.5.50.0/24
}

# ============================================================================
# PASSO 7: LIMPAR WALLED-GARDEN DUPLICADOS
# ============================================================================

# Remover todas as entradas duplicadas do walled-garden
/ip hotspot walled-garden remove [find]

# Adicionar apenas as essenciais
/ip hotspot walled-garden add comment="Portal Principal" dst-host=tocantinstransportewifi.com.br
/ip hotspot walled-garden add comment="Portal Wildcard" dst-host=*.tocantinstransportewifi.com.br
/ip hotspot walled-garden add comment="Portal WWW" dst-host=www.tocantinstransportewifi.com.br
/ip hotspot walled-garden add comment="Gateway Local" dst-host=10.5.50.1
/ip hotspot walled-garden add comment="Tailwind CSS" dst-host=cdn.tailwindcss.com
/ip hotspot walled-garden add comment="Google Fonts" dst-host=fonts.googleapis.com
/ip hotspot walled-garden add comment="Google Fonts Static" dst-host=fonts.gstatic.com
/ip hotspot walled-garden add comment="Cloudflare CDN" dst-host=cdnjs.cloudflare.com
/ip hotspot walled-garden add comment="Woovi API" dst-host=api.woovi.com
/ip hotspot walled-garden add comment="Woovi Domain" dst-host=woovi.com
/ip hotspot walled-garden add comment="Woovi Subdomains" dst-host=*.woovi.com
/ip hotspot walled-garden add comment="OpenPix API" dst-host=api.openpix.com.br
/ip hotspot walled-garden add comment="OpenPix Subdomains" dst-host=*.openpix.com.br
/ip hotspot walled-garden add comment="QR Code Generator" dst-host=api.qrserver.com
/ip hotspot walled-garden add comment="PIX Banco Central" dst-host=pix.bcb.gov.br
/ip hotspot walled-garden add comment="Banco Central BR" dst-host=*.bcb.gov.br

# Bancos principais
/ip hotspot walled-garden add comment="Banco do Brasil" dst-host=bb.com.br
/ip hotspot walled-garden add comment="BB Mobile" dst-host=*.bb.com.br
/ip hotspot walled-garden add comment="Caixa CEF" dst-host=caixa.gov.br
/ip hotspot walled-garden add comment="Caixa Mobile" dst-host=*.caixa.gov.br
/ip hotspot walled-garden add comment="Itau" dst-host=itau.com.br
/ip hotspot walled-garden add comment="Itau Mobile" dst-host=*.itau.com.br
/ip hotspot walled-garden add comment="Nubank" dst-host=nubank.com.br
/ip hotspot walled-garden add comment="Nubank API" dst-host=*.nubank.com.br
/ip hotspot walled-garden add comment="Banco Inter" dst-host=bancointer.com.br
/ip hotspot walled-garden add comment="Inter Mobile" dst-host=*.bancointer.com.br
/ip hotspot walled-garden add comment="Santander" dst-host=santander.com.br
/ip hotspot walled-garden add comment="Santander Mobile" dst-host=*.santander.com.br
/ip hotspot walled-garden add comment="Bradesco" dst-host=bradesco.com.br
/ip hotspot walled-garden add comment="Bradesco Mobile" dst-host=*.bradesco.com.br
/ip hotspot walled-garden add comment="C6 Bank" dst-host=c6bank.com.br
/ip hotspot walled-garden add comment="C6 Mobile" dst-host=*.c6bank.com.br
/ip hotspot walled-garden add comment="PicPay" dst-host=picpay.com
/ip hotspot walled-garden add comment="PicPay API" dst-host=*.picpay.com
/ip hotspot walled-garden add comment="Mercado Pago" dst-host=mercadopago.com.br
/ip hotspot walled-garden add comment="Mercado Pago API" dst-host=*.mercadopago.com.br
/ip hotspot walled-garden add comment="BRB" dst-host=brb.com.br
/ip hotspot walled-garden add comment="BRB Mobile" dst-host=*.brb.com.br

# Walled-garden IP
/ip hotspot walled-garden ip remove [find]
/ip hotspot walled-garden ip add action=accept comment="Portal HTTPS" dst-address=138.68.255.122
/ip hotspot walled-garden ip add action=accept comment="Cloudflare Range" dst-address=104.16.0.0/12

# ============================================================================
# PASSO 8: CONFIGURAR WIFI CAP
# ============================================================================

/interface wifi cap set discovery-interfaces=bridgeLocal enabled=yes slaves-datapath=capdp

# ============================================================================
# PASSO 9: CONFIGURAR TIMEZONE E NTP
# ============================================================================

/system clock set time-zone-name=America/Araguaina
/system ntp client set enabled=yes
/system ntp client servers add address=pool.ntp.br
/system ntp client servers add address=a.ntp.br

# ============================================================================
# PASSO 10: CONFIGURAR LOGGING
# ============================================================================

/system logging add topics=hotspot
/system logging add topics=dhcp

# ============================================================================
# PASSO 11: SCRIPTS E SCHEDULERS
# ============================================================================

# Scheduler para liberar pagos
/system scheduler set [find name=liberarPagosScheduler] interval=1m

# Habilitar registro de MACs
/system scheduler set [find name=registrarMacsScheduler] disabled=no interval=30s

# ============================================================================
# FINALIZADO!
# ============================================================================

:log info "=== CONFIGURACAO CORRIGIDA COM SUCESSO ==="
:log info "Reinicie o MikroTik para aplicar todas as mudancas: /system reboot"

