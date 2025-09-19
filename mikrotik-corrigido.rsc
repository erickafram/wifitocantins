# =====================================================
# SCRIPT DE CONFIGURAÇÃO AUTOMÁTICA CORRIGIDO
# WiFi Tocantins Express - MikroTik hAP ac2
# =====================================================

# ⚠️ ATENÇÃO: Este script vai resetar o MikroTik!
# Use apenas se quiser começar do zero

# Resetar configuração para estado limpo
/system reset-configuration no-defaults=yes skip-backup=yes

# Aguardar reinicialização
:delay 30

# =====================================================
# 1. CONFIGURAÇÃO BÁSICA DO SISTEMA
# =====================================================

# Definir identidade
/system identity set name="WiFi-Tocantins-Express"

# Configurar timezone
/system clock set time-zone-name=America/Sao_Paulo

# Configurar NTP
/system ntp client set enabled=yes primary-ntp=200.160.7.186 secondary-ntp=201.49.148.135

# =====================================================
# 2. CONFIGURAÇÃO DE INTERFACES
# =====================================================

# Interface WAN (Starlink)
/interface ethernet set ether1 name=wan-starlink

# Interface LAN
/interface ethernet set ether2 name=lan-local
/interface ethernet set ether3 name=lan-local2
/interface ethernet set ether4 name=lan-local3
/interface ethernet set ether5 name=lan-local4

# Configurar WiFi
/interface wireless set wlan1 mode=ap-bridge ssid="Tocantins_WiFi_Express" band=2ghz-b/g/n frequency=auto channel-width=20/40mhz-XX wireless-protocol=802.11 security-profile=default

/interface wireless set wlan2 mode=ap-bridge ssid="Tocantins_WiFi_5G" band=5ghz-a/n/ac frequency=auto channel-width=20/40/80mhz-XXXX wireless-protocol=802.11 security-profile=default

# Habilitar interfaces WiFi
/interface wireless enable wlan1,wlan2

# =====================================================
# 3. CONFIGURAÇÃO DE ENDEREÇOS IP
# =====================================================

# WAN - DHCP Client para Starlink
/ip dhcp-client add interface=wan-starlink disabled=no

# Hotspot Network
/ip address add address=10.10.10.1/24 interface=lan-local

# =====================================================
# 4. CONFIGURAÇÃO DO HOTSPOT
# =====================================================

# Criar pool de IPs para hotspot
/ip pool add name=hotspot-pool ranges=10.10.10.100-10.10.10.200

# Configurar DHCP Server para hotspot
/ip dhcp-server add name=hotspot-dhcp interface=lan-local address-pool=hotspot-pool lease-time=1h disabled=no

/ip dhcp-server network add address=10.10.10.0/24 gateway=10.10.10.1 dns-server=8.8.8.8,1.1.1.1 domain=tocantinstransportewifi.com.br

# Criar perfil do hotspot
/ip hotspot profile add name="tocantins-profile" dns-name=tocantinstransportewifi.com.br html-directory=hotspot http-proxy=0.0.0.0:0 login-by=mac,http-chap,http-pap mac-auth-mode=mac-as-username trial-uptime-limit=5m trial-user-profile=default

# Criar servidor hotspot
/ip hotspot add name="tocantins-hotspot" interface=lan-local address-pool=hotspot-pool profile=tocantins-profile disabled=no

# =====================================================
# 5. WALLED GARDEN (Sites permitidos sem pagamento)
# =====================================================

# Permitir acesso ao sistema de pagamento
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Sistema de Pagamento"
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Sistema de Pagamento"

# Permitir Santander PIX
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Pagamento Santander"
/ip hotspot walled-garden add dst-host=*.pix.bcb.gov.br comment="PIX"

# Permitir serviços essenciais
/ip hotspot walled-garden add dst-host=*.google.com comment="DNS Resolution"
/ip hotspot walled-garden add dst-host=*.cloudflare.com comment="DNS Resolution"
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="Google DNS"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="Cloudflare DNS"

# =====================================================
# 6. CONFIGURAÇÃO DE FIREWALL
# =====================================================

# Permitir acesso à internet para usuários autenticados
/ip firewall filter add chain=forward action=accept connection-state=established,related comment="Allow established connections"

/ip firewall filter add chain=forward action=accept connection-state=new src-address=10.10.10.0/24 comment="Allow hotspot users"

# Bloquear acesso direto sem autenticação
/ip firewall filter add chain=forward action=drop src-address=10.10.10.0/24 comment="Drop non-authenticated users"

# NAT para internet
/ip firewall nat add chain=srcnat out-interface=wan-starlink action=masquerade comment="Internet NAT"

# =====================================================
# 7. CONFIGURAÇÃO DA API (Para integração externa)
# =====================================================

# Habilitar API
/ip service set api port=8728 disabled=no

# Criar usuário para API
/user add name=api-tocantins password=TocantinsWiFi2024! group=full comment="Usuario para API externa"

# Configurar acesso seguro
/ip service set api-ssl disabled=no port=8729

# =====================================================
# 8. CONFIGURAÇÃO DE DNS
# =====================================================

# Configurar DNS
/ip dns set servers=8.8.8.8,1.1.1.1 cache-size=2048KiB max-concurrent-queries=100 max-concurrent-tcp-sessions=20

# =====================================================
# 9. CONFIGURAÇÕES DE PERFORMANCE
# =====================================================

# Otimizar wireless
/interface wireless set wlan1 tx-power=20 tx-power-mode=default distance=indoors country=brazil installation=indoor

/interface wireless set wlan2 tx-power=20 tx-power-mode=default distance=indoors country=brazil installation=indoor

# Configurar QoS básico
/queue type add name=pcq-down kind=pcq pcq-rate=2M pcq-limit=50
/queue type add name=pcq-up kind=pcq pcq-rate=512k pcq-limit=50

# =====================================================
# 10. MONITORAMENTO E LOGS
# =====================================================

# Configurar logging
/system logging add topics=hotspot action=memory
/system logging add topics=wireless action=memory
/system logging add topics=dhcp action=memory

# =====================================================
# 11. BACKUP AUTOMÁTICO
# =====================================================

# Script para backup automático
/system script add name=backup-automatico source={/system backup save name=("backup-tocantins-" . [/system clock get date]); :log info "Backup automatico realizado"}

# Agendar backup diário
/system scheduler add name=backup-diario start-time=03:00:00 interval=1d on-event=backup-automatico

# =====================================================
# CONFIGURAÇÃO CONCLUÍDA
# =====================================================

:log info "Configuracao WiFi Tocantins Express concluida!"
:put "Sistema configurado com sucesso!"
:put "Acesse: tocantinstransportewifi.com.br"
