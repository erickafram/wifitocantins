# =====================================================
# CONFIGURAÇÃO SIMPLES CAPTIVE PORTAL COM HTML
# =====================================================

# =====================================================
# 1. CONFIGURAR PERFIL DO HOTSPOT
# =====================================================

# Configurar perfil para usar HTML personalizado
/ip hotspot profile set tocantins-profile \
    login-by=http-chap,http-pap \
    use-radius=no \
    trial-uptime-limit=0s \
    html-directory=hotspot

# =====================================================
# 2. CONFIGURAR WALLED GARDEN
# =====================================================

# Limpar walled garden
/ip hotspot walled-garden remove [find]

# Adicionar apenas sites essenciais
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br
/ip hotspot walled-garden add dst-host=8.8.8.8
/ip hotspot walled-garden add dst-host=1.1.1.1

# =====================================================
# 3. MOVER ARQUIVO HTML
# =====================================================

# Renomear arquivo para diretório hotspot
/file set login.html name=hotspot/login.html

# =====================================================
# 4. CONFIGURAR REDIRECIONAMENTO
# =====================================================

# Adicionar regra NAT para captive portal
/ip firewall nat add chain=dstnat protocol=tcp dst-port=80 \
    hotspot=auth action=redirect to-ports=64873 \
    comment="Captive Portal HTML"

# =====================================================
# 5. REINICIAR HOTSPOT
# =====================================================

/ip hotspot set tocantins-hotspot disabled=yes
/ip hotspot set tocantins-hotspot disabled=no

# =====================================================
# FINALIZADO
# =====================================================

:put "===================================="
:put "CAPTIVE PORTAL HTML CONFIGURADO!"
:put "===================================="
:put "Teste agora:"
:put "1. Conecte na WiFi"
:put "2. Abra navegador"
:put "3. Digite qualquer site"
:put "4. Deve aparecer sua pagina HTML"
:put "===================================="
