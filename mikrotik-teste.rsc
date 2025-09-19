# =====================================================
# SCRIPT TOCANTINS WiFi - VERSÃO TESTE (SEM RESET)
# Para testar sem perder configuração atual
# =====================================================

# Definir identidade
/system identity set name="WiFi-Tocantins-Express"

# Configurar timezone
/system clock set time-zone-name=America/Sao_Paulo

# =====================================================
# CONFIGURAR WIRELESS
# =====================================================

# WiFi 2.4GHz
/interface wireless set wlan1 mode=ap-bridge ssid="Tocantins_WiFi_Express" band=2ghz-b/g/n frequency=auto security-profile=default disabled=no

# WiFi 5GHz
/interface wireless set wlan2 mode=ap-bridge ssid="Tocantins_WiFi_5G" band=5ghz-a/n/ac frequency=auto security-profile=default disabled=no

# =====================================================
# CONFIGURAR HOTSPOT
# =====================================================

# Criar bridge para hotspot
/interface bridge add name=bridge-hotspot

# Adicionar WiFi ao bridge
/interface bridge port add bridge=bridge-hotspot interface=wlan1
/interface bridge port add bridge=bridge-hotspot interface=wlan2

# IP do hotspot
/ip address add address=10.10.10.1/24 interface=bridge-hotspot

# Pool de IPs
/ip pool add name=hotspot-pool ranges=10.10.10.100-10.10.10.200

# DHCP Server
/ip dhcp-server add name=hotspot-dhcp interface=bridge-hotspot address-pool=hotspot-pool lease-time=1h disabled=no

/ip dhcp-server network add address=10.10.10.0/24 gateway=10.10.10.1 dns-server=8.8.8.8,1.1.1.1 domain=tocantinstransportewifi.com.br

# Perfil do hotspot
/ip hotspot profile add name="tocantins-profile" dns-name=tocantinstransportewifi.com.br login-by=mac,http-chap,http-pap trial-uptime-limit=5m

# Servidor hotspot
/ip hotspot add name="tocantins-hotspot" interface=bridge-hotspot address-pool=hotspot-pool profile=tocantins-profile disabled=no

# =====================================================
# WALLED GARDEN
# =====================================================

# Permitir seu domínio
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Sistema Pagamento"
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Sistema Pagamento"

# Permitir pagamentos
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Santander"
/ip hotspot walled-garden add dst-host=*.pix.bcb.gov.br comment="PIX"

# DNS essencial
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="Google DNS"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="Cloudflare DNS"

# =====================================================
# API PARA INTEGRAÇÃO
# =====================================================

# Habilitar API
/ip service set api port=8728 disabled=no

# Usuário para API
/user add name=api-tocantins password=TocantinsWiFi2024! group=full comment="API User"

# =====================================================
# LOGS
# =====================================================

/system logging add topics=hotspot action=memory
/system logging add topics=wireless action=memory

# =====================================================
# FINALIZADO
# =====================================================

:put "====================================="
:put "WiFi Tocantins Express CONFIGURADO!"
:put "====================================="
:put "Redes WiFi criadas:"
:put "- Tocantins_WiFi_Express (2.4GHz)"
:put "- Tocantins_WiFi_5G (5GHz)" 
:put ""
:put "Portal: tocantinstransportewifi.com.br"
:put "Hotspot IP: 10.10.10.1"
:put "====================================="
