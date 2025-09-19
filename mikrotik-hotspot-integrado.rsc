# =====================================================
# CONFIGURAÇÃO MIKROTIK INTEGRADA COM SISTEMA DE PAGAMENTO
# WiFi Tocantins Express - Configuração Otimizada
# =====================================================

# ⚠️ IMPORTANTE: Este script configura o MikroTik para integração com o sistema Laravel
# Certifique-se de ter o sistema de pagamento rodando antes de aplicar

# =====================================================
# 1. CONFIGURAÇÃO BÁSICA DO SISTEMA
# =====================================================

# Definir identidade
/system identity set name="WiFi-Tocantins-Integrado"

# Configurar timezone
/system clock set time-zone-name=America/Sao_Paulo

# Configurar NTP
/system ntp client set enabled=yes primary-ntp=200.160.7.186 secondary-ntp=201.49.148.135

# =====================================================
# 2. CONFIGURAÇÃO DE INTERFACES
# =====================================================

# Interface WAN
/interface ethernet set ether1 name=wan-internet

# Interface LAN para hotspot
/interface ethernet set ether2 name=lan-hotspot
/interface ethernet set ether3 name=lan-hotspot2
/interface ethernet set ether4 name=lan-hotspot3
/interface ethernet set ether5 name=lan-hotspot4

# Configurar bridge para hotspot
/interface bridge add name=bridge-hotspot protocol-mode=rstp

# Adicionar interfaces ao bridge
/interface bridge port add bridge=bridge-hotspot interface=lan-hotspot
/interface bridge port add bridge=bridge-hotspot interface=lan-hotspot2  
/interface bridge port add bridge=bridge-hotspot interface=lan-hotspot3
/interface bridge port add bridge=bridge-hotspot interface=lan-hotspot4

# Configurar WiFi
/interface wireless set wlan1 mode=ap-bridge ssid="Tocantins_WiFi" band=2ghz-b/g/n frequency=auto channel-width=20/40mhz-XX wireless-protocol=802.11 security-profile=default master-interface=none

/interface wireless set wlan2 mode=ap-bridge ssid="Tocantins_WiFi_5G" band=5ghz-a/n/ac frequency=auto channel-width=20/40/80mhz-XXXX wireless-protocol=802.11 security-profile=default master-interface=none

# Adicionar WiFi ao bridge
/interface bridge port add bridge=bridge-hotspot interface=wlan1
/interface bridge port add bridge=bridge-hotspot interface=wlan2

# Habilitar interfaces WiFi
/interface wireless enable wlan1,wlan2

# =====================================================
# 3. CONFIGURAÇÃO DE ENDEREÇOS IP
# =====================================================

# WAN - DHCP Client
/ip dhcp-client add interface=wan-internet disabled=no

# Bridge hotspot - IP fixo
/ip address add address=10.10.10.1/24 interface=bridge-hotspot

# =====================================================
# 4. CONFIGURAÇÃO DO POOL DE IPS
# =====================================================

/ip pool add name=hotspot-pool ranges=10.10.10.100-10.10.10.200

# =====================================================
# 5. CONFIGURAÇÃO DO DHCP SERVER
# =====================================================

/ip dhcp-server add name=hotspot-dhcp interface=bridge-hotspot address-pool=hotspot-pool lease-time=1h disabled=no

/ip dhcp-server network add address=10.10.10.0/24 gateway=10.10.10.1 dns-server=8.8.8.8,1.1.1.1 domain=tocantinstransportewifi.com.br

# =====================================================
# 6. CONFIGURAÇÃO DO PERFIL HOTSPOT
# =====================================================

# Criar perfil personalizado
/ip hotspot profile add name="tocantins-profile" \
    dns-name=tocantinstransportewifi.com.br \
    html-directory=hotspot \
    http-proxy=0.0.0.0:0 \
    login-by=mac,http-chap,http-pap \
    mac-auth-mode=mac-as-username \
    trial-uptime-limit=5m \
    trial-user-profile=default \
    use-radius=no

# =====================================================
# 7. CRIAR SERVIDOR HOTSPOT
# =====================================================

/ip hotspot add name="tocantins-hotspot" \
    interface=bridge-hotspot \
    address-pool=hotspot-pool \
    profile=tocantins-profile \
    disabled=no

# =====================================================
# 8. CONFIGURAÇÃO DO WALLED GARDEN
# =====================================================

# Permitir acesso ao sistema de pagamento (OBRIGATÓRIO)
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Sistema de Pagamento Principal"
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Sistema de Pagamento Direto"
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br comment="Sistema de Pagamento WWW"

# Permitir gateways de pagamento PIX
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Santander PIX"
/ip hotspot walled-garden add dst-host=*.woovi.com comment="Woovi PIX"
/ip hotspot walled-garden add dst-host=*.pix.bcb.gov.br comment="Sistema PIX Brasil"
/ip hotspot walled-garden add dst-host=*.bcb.gov.br comment="Banco Central"

# Permitir serviços essenciais para funcionamento
/ip hotspot walled-garden add dst-host=*.google.com comment="Google Services"
/ip hotspot walled-garden add dst-host=*.googleapis.com comment="Google APIs"
/ip hotspot walled-garden add dst-host=*.gstatic.com comment="Google Static"
/ip hotspot walled-garden add dst-host=*.cloudflare.com comment="Cloudflare CDN"
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="Google DNS"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="Cloudflare DNS"

# Permitir verificação de conectividade
/ip hotspot walled-garden add dst-host=captive.apple.com comment="Apple Captive Portal"
/ip hotspot walled-garden add dst-host=connectivitycheck.gstatic.com comment="Android Connectivity"
/ip hotspot walled-garden add dst-host=www.msftconnecttest.com comment="Windows Connectivity"

# =====================================================
# 9. CONFIGURAÇÃO DE FIREWALL E NAT
# =====================================================

# NAT para internet
/ip firewall nat add chain=srcnat out-interface=wan-internet action=masquerade comment="Internet NAT"

# Regras de firewall para hotspot
/ip firewall filter add chain=input action=accept connection-state=established,related comment="Allow established to router"

/ip firewall filter add chain=input action=accept protocol=udp dst-port=53 comment="Allow DNS to router"

/ip firewall filter add chain=input action=accept protocol=tcp dst-port=8728 src-address=10.10.10.0/24 comment="Allow API from hotspot network"

/ip firewall filter add chain=forward action=accept connection-state=established,related comment="Allow established connections"

# Permitir tráfego autenticado do hotspot
/ip firewall filter add chain=forward action=accept src-address=10.10.10.0/24 comment="Allow authenticated hotspot users"

# =====================================================
# 10. CONFIGURAÇÃO DA API (CRUCIAL PARA INTEGRAÇÃO)
# =====================================================

# Habilitar API na porta padrão
/ip service set api port=8728 disabled=no

# Habilitar API SSL (opcional, mas recomendado)
/ip service set api-ssl disabled=no port=8729

# Criar usuário específico para API
/user add name=api-tocantins password=TocantinsWiFi2024! group=full comment="Usuario API para sistema Laravel"

# Permitir acesso à API apenas da rede local
/ip service set api address=127.0.0.1,10.10.10.0/24

# =====================================================
# 11. CONFIGURAÇÃO DE DNS
# =====================================================

/ip dns set servers=8.8.8.8,1.1.1.1 cache-size=4096KiB max-concurrent-queries=150 max-concurrent-tcp-sessions=40

# =====================================================
# 12. OTIMIZAÇÕES DE PERFORMANCE
# =====================================================

# Configurar wireless para melhor performance
/interface wireless set wlan1 tx-power=20 tx-power-mode=default distance=indoors country=brazil installation=indoor

/interface wireless set wlan2 tx-power=20 tx-power-mode=default distance=indoors country=brazil installation=indoor

# Configurar QoS básico para hotspot
/queue type add name=hotspot-down kind=pcq pcq-rate=5M pcq-limit=50 pcq-classifier=dst-address
/queue type add name=hotspot-up kind=pcq pcq-rate=1M pcq-limit=50 pcq-classifier=src-address

# =====================================================
# 13. CONFIGURAÇÃO DE MONITORAMENTO
# =====================================================

# Configurar logging detalhado
/system logging add topics=hotspot action=memory
/system logging add topics=wireless action=memory  
/system logging add topics=dhcp action=memory
/system logging add topics=info action=memory

# =====================================================
# 14. SCRIPTS DE AUTOMAÇÃO
# =====================================================

# Script para limpar usuários expirados (executar diariamente)
/system script add name=cleanup-expired-users source={
    :log info "Iniciando limpeza de usuarios expirados";
    :local count 0;
    :foreach user in=[/ip hotspot user find where comment~"Auto-created"] do={
        :local username [/ip hotspot user get $user name];
        :local lastSeen [/ip hotspot active get [find where user=$username] last-seen];
        :if ([:len $lastSeen] = 0) do={
            :if ([/ip hotspot user get $user disabled] = "yes") do={
                /ip hotspot user remove $user;
                :set count ($count + 1);
                :log info ("Usuario removido: " . $username);
            }
        }
    };
    :log info ("Limpeza concluida. " . $count . " usuarios removidos");
}

# Agendar limpeza diária às 3h da manhã
/system scheduler add name=cleanup-users start-time=03:00:00 interval=1d on-event=cleanup-expired-users

# Script de backup automático
/system script add name=backup-daily source={
    :local date [/system clock get date];
    :local time [/system clock get time];
    :local filename ("backup-tocantins-" . $date . "-" . $time);
    /system backup save name=$filename;
    :log info ("Backup salvo: " . $filename);
}

# Agendar backup diário às 2h da manhã  
/system scheduler add name=backup-automatico start-time=02:00:00 interval=1d on-event=backup-daily

# =====================================================
# 15. CONFIGURAÇÕES FINAIS DE SEGURANÇA
# =====================================================

# Desabilitar serviços desnecessários
/ip service set telnet disabled=yes
/ip service set ftp disabled=yes
/ip service set www disabled=no
/ip service set ssh disabled=no
/ip service set winbox disabled=no

# Configurar timeouts
/ip hotspot profile set tocantins-profile session-timeout=24h idle-timeout=5m

# =====================================================
# TESTE DE CONECTIVIDADE
# =====================================================

:log info "=== CONFIGURACAO CONCLUIDA ===";
:log info "Sistema: WiFi Tocantins Express";
:log info "Hotspot: tocantins-hotspot";  
:log info "API Usuario: api-tocantins";
:log info "Portal: tocantinstransportewifi.com.br";
:log info "Rede: 10.10.10.0/24";
:log info "Gateway: 10.10.10.1";

:put "=== CONFIGURACAO MIKROTIK CONCLUIDA ===";
:put "1. Verifique se o sistema Laravel está rodando";
:put "2. Configure as variáveis de ambiente no Laravel:";
:put "   MIKROTIK_HOST=10.10.10.1";
:put "   MIKROTIK_USERNAME=api-tocantins";  
:put "   MIKROTIK_PASSWORD=TocantinsWiFi2024!";
:put "   MIKROTIK_API_ENABLED=true";
:put "3. Teste a conexão entre os sistemas";
:put "4. Sistema pronto para receber pagamentos!";

# =====================================================
# COMANDOS DE TESTE (EXECUTAR MANUALMENTE DEPOIS)
# =====================================================

# Para testar a API:
# /ip hotspot user print
# /ip hotspot active print  
# /system logging print

# Para criar usuário de teste:
# /ip hotspot user add name=teste-mac mac-address=00:11:22:33:44:55 profile=default comment="Teste manual"

# Para verificar logs:
# /log print where topics~"hotspot" 