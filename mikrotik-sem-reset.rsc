# =====================================================
# SCRIPT TOCANTINS WiFi - SEM RESET (PARA TESTES)
# =====================================================

# Definir identidade
/system identity set name="WiFi-Tocantins-Express"

# Configurar timezone
/system clock set time-zone-name=America/Sao_Paulo

# =====================================================
# CONFIGURAÇÃO DE INTERFACES WiFi
# =====================================================

# Configurar WiFi 2.4GHz
/interface wireless set wlan1 mode=ap-bridge ssid="Tocantins_WiFi_Express" \
    band=2ghz-b/g/n frequency=auto security-profile=default disabled=no

# Configurar WiFi 5GHz  
/interface wireless set wlan2 mode=ap-bridge ssid="Tocantins_WiFi_5G" \
    band=5ghz-a/n/ac frequency=auto security-profile=default disabled=no

# =====================================================
# CONFIGURAÇÃO DE REDE
# =====================================================

# Criar bridge para hotspot
/interface bridge add name=bridge-hotspot

# Adicionar interfaces ao bridge
/interface bridge port add bridge=bridge-hotspot interface=wlan1
/interface bridge port add bridge=bridge-hotspot interface=wlan2
/interface bridge port add bridge=bridge-hotspot interface=ether2

# Configurar IP do hotspot
/ip address add address=10.10.10.1/24 interface=bridge-hotspot

# =====================================================
# CONFIGURAÇÃO DO HOTSPOT
# =====================================================

# Criar pool de IPs
/ip pool add name=hotspot-pool ranges=10.10.10.100-10.10.10.200

# DHCP Server
/ip dhcp-server add name=hotspot-dhcp interface=bridge-hotspot \
    address-pool=hotspot-pool lease-time=1h disabled=no

/ip dhcp-server network add address=10.10.10.0/24 gateway=10.10.10.1 \
    dns-server=8.8.8.8,1.1.1.1 domain=tocantinstransportewifi.com.br

# Perfil do hotspot
/ip hotspot profile add name="tocantins-profile" \
    dns-name=tocantinstransportewifi.com.br \
    login-by=mac,http-chap,http-pap \
    trial-uptime-limit=5m

# Servidor hotspot
/ip hotspot add name="tocantins-hotspot" \
    interface=bridge-hotspot \
    address-pool=hotspot-pool \
    profile=tocantins-profile \
    disabled=no

# =====================================================
# WALLED GARDEN
# =====================================================

# Permitir seu domínio
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br

# Permitir pagamentos
/ip hotspot walled-garden add dst-host=*.santander.com.br
/ip hotspot walled-garden add dst-host=*.pix.bcb.gov.br

# DNS essencial
/ip hotspot walled-garden add dst-host=8.8.8.8
/ip hotspot walled-garden add dst-host=1.1.1.1

# =====================================================
# API PARA INTEGRAÇÃO
# =====================================================

# Habilitar API
/ip service set api port=8728 disabled=no

# Usuário para API
/user add name=api-tocantins password=TocantinsWiFi2024! group=full

# =====================================================
# FINALIZAÇÃO
# =====================================================

:log info "Configuracao Tocantins concluida!"
:put "WiFi Tocantins Express configurado!"
:put "Redes: Tocantins_WiFi_Express e Tocantins_WiFi_5G"
:put "Portal: tocantinstransportewifi.com.br"
