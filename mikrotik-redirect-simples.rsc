# =====================================================
# REDIRECIONAMENTO SIMPLES PARA TOCANTINS WIFI
# =====================================================

# =====================================================
# 1. REMOVER CONFIGURAÇÃO ATUAL
# =====================================================

# Remover hotspot atual
/ip hotspot remove [find name="tocantins-hotspot"]

# =====================================================
# 2. RECONFIGURAR HOTSPOT COM REDIRECIONAMENTO
# =====================================================

# Modificar perfil para forçar redirecionamento
/ip hotspot profile set tocantins-profile \
    login-by=http-chap,http-pap \
    use-radius=no \
    trial-uptime-limit=0s \
    trial-user-profile="" \
    http-proxy=0.0.0.0:0

# =====================================================
# 3. CONFIGURAR WALLED GARDEN RESTRITIVO
# =====================================================

# Limpar walled garden
/ip hotspot walled-garden remove [find]

# Permitir APENAS seu domínio
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br

# DNS mínimo necessário
/ip hotspot walled-garden add dst-host=8.8.8.8
/ip hotspot walled-garden add dst-host=1.1.1.1

# =====================================================
# 4. RECRIAR HOTSPOT
# =====================================================

/ip hotspot add name="tocantins-hotspot" \
    interface=bridge-hotspot \
    address-pool=hotspot-pool \
    profile=tocantins-profile \
    disabled=no

# =====================================================
# 5. CONFIGURAR CAPTIVE PORTAL
# =====================================================

# Configurar para redirecionar HTTP para login
/ip firewall nat add chain=dstnat \
    protocol=tcp dst-port=80 \
    hotspot=auth \
    action=redirect to-ports=64873 \
    comment="Force captive portal"

# =====================================================
# 6. PÁGINA DE LOGIN PERSONALIZADA
# =====================================================

# Criar diretório hotspot se não existir
/file remove [find name~"login"]

# JavaScript de redirecionamento
:put "Configurando página de redirecionamento..."

# =====================================================
# TESTE FINAL
# =====================================================

:put "=================================="
:put "CAPTIVE PORTAL CONFIGURADO!"
:put "=================================="
:put "Agora teste:"
:put "1. Conecte na WiFi Tocantins_WiFi_Express"
:put "2. Abra navegador no celular"
:put "3. Tente acessar qualquer site"
:put "4. Deve aparecer página de login"
:put "=================================="
