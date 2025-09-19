# =====================================================
# CONFIGURAÇÃO COMPLETA CAPTIVE PORTAL COM HTML
# =====================================================

# =====================================================
# 1. CONFIGURAR CAPTIVE PORTAL
# =====================================================

# Reconfigurar perfil do hotspot
/ip hotspot profile set tocantins-profile \
    login-by=http-chap,http-pap \
    use-radius=no \
    trial-uptime-limit=0s \
    html-directory=hotspot \
    http-proxy=0.0.0.0:0

# =====================================================
# 2. CONFIGURAR WALLED GARDEN RESTRITIVO
# =====================================================

# Limpar walled garden atual
/ip hotspot walled-garden remove [find]

# Permitir apenas o essencial
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Site Principal"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Site Principal"
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="DNS Google"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="DNS Cloudflare"

# =====================================================
# 3. CRIAR ESTRUTURA DE ARQUIVOS
# =====================================================

# Verificar se arquivo login.html existe
:if ([/file find name="login.html"] != "") do={
    # Mover para diretório hotspot
    /file set [find name="login.html"] name="hotspot/login.html"
    :put "Arquivo login.html movido para hotspot/"
} else={
    :put "ERRO: Arquivo login.html não encontrado!"
    :put "Faça upload do arquivo login.html primeiro"
}

# =====================================================
# 4. CONFIGURAR REDIRECIONAMENTO FORÇADO
# =====================================================

# Remover regras NAT antigas do hotspot
/ip firewall nat remove [find comment~"hotspot"]

# Adicionar redirecionamento HTTP
/ip firewall nat add chain=dstnat \
    protocol=tcp dst-port=80 \
    hotspot=auth \
    action=redirect to-ports=64873 \
    comment="Hotspot captive portal"

# =====================================================
# 5. REINICIAR SERVIÇOS
# =====================================================

# Reiniciar hotspot para aplicar mudanças
/ip hotspot set tocantins-hotspot disabled=yes
:delay 2
/ip hotspot set tocantins-hotspot disabled=no

# =====================================================
# 6. TESTE
# =====================================================

:put "======================================"
:put "CAPTIVE PORTAL HTML CONFIGURADO!"
:put "======================================"
:put "Arquivo usado: hotspot/login.html"
:put ""
:put "TESTE AGORA:"
:put "1. Conecte celular na WiFi"
:put "2. Abra navegador"
:put "3. Digite: google.com"
:put "4. Deve aparecer sua página HTML"
:put "5. Redirecionamento para tocantinstransportewifi.com.br"
:put "======================================"
