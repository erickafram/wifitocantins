# =====================================================
# CONFIGURAÇÃO DE REDIRECIONAMENTO AUTOMÁTICO
# Para tocantinstransportewifi.com.br
# =====================================================

# =====================================================
# 1. CONFIGURAR PERFIL DO HOTSPOT PARA REDIRECIONAMENTO
# =====================================================

# Modificar perfil para redirecionamento HTTP
/ip hotspot profile set tocantins-profile \
    http-proxy=0.0.0.0:0 \
    http-cookie-lifetime=3d \
    login-by=http-chap,http-pap,cookie,trial \
    use-radius=no \
    nas-port-type=wireless-802.11 \
    trial-uptime-limit=1m \
    trial-user-profile=default

# =====================================================
# 2. CONFIGURAR REDIRECIONAMENTO PARA SEU DOMÍNIO
# =====================================================

# Remover walled garden existente
/ip hotspot walled-garden remove [find]

# Adicionar walled garden APENAS para seu domínio
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Sistema de Pagamento"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Sistema de Pagamento"

# Permitir DNS essencial (só o mínimo necessário)
/ip hotspot walled-garden add dst-host=8.8.8.8 comment="DNS Google"
/ip hotspot walled-garden add dst-host=1.1.1.1 comment="DNS Cloudflare"

# Permitir resolução de DNS
/ip hotspot walled-garden add dst-host=*.google.com comment="DNS Resolution"

# =====================================================
# 3. CONFIGURAR PÁGINA DE LOGIN PERSONALIZADA
# =====================================================

# Criar arquivo de redirecionamento HTML personalizado
/file remove [find name="hotspot/login.html"]

# Script para criar página de login personalizada
:local loginHtml "<!DOCTYPE html>
<html>
<head>
    <title>WiFi Tocantins Express</title>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <script>
        // Redirecionar automaticamente para seu site
        window.location.href = 'http://tocantinstransportewifi.com.br';
    </script>
</head>
<body>
    <p>Redirecionando para WiFi Tocantins Express...</p>
    <p>Se não for redirecionado automaticamente, <a href=\"http://tocantinstransportewifi.com.br\">clique aqui</a></p>
</body>
</html>"

# Salvar arquivo HTML
/file add name="login.html" contents=$loginHtml

# =====================================================
# 4. CONFIGURAR FIREWALL PARA REDIRECIONAMENTO
# =====================================================

# Remover regras de firewall existentes do hotspot
/ip firewall nat remove [find comment="hotspot redirect"]

# Adicionar redirecionamento HTTP para seu domínio
/ip firewall nat add chain=dstnat protocol=tcp dst-port=80 \
    src-address=10.10.10.0/24 \
    action=redirect to-ports=80 \
    comment="Hotspot HTTP redirect"

# Redirecionamento para HTTPS também
/ip firewall nat add chain=dstnat protocol=tcp dst-port=443 \
    src-address=10.10.10.0/24 \
    action=redirect to-ports=80 \
    comment="Hotspot HTTPS redirect"

# =====================================================
# 5. CONFIGURAR DNS PARA REDIRECIONAMENTO
# =====================================================

# Configurar DNS estático para redirecionar tudo para seu servidor
/ip dns static remove [find]
/ip dns static add address=SEU_IP_DO_SERVIDOR name=tocantinstransportewifi.com.br

# =====================================================
# 6. REINICIAR SERVIÇOS
# =====================================================

# Reiniciar hotspot para aplicar mudanças
/ip hotspot remove tocantins-hotspot
:delay 2

# Recriar hotspot com nova configuração
/ip hotspot add name="tocantins-hotspot" \
    interface=bridge-hotspot \
    address-pool=hotspot-pool \
    profile=tocantins-profile \
    disabled=no

# =====================================================
# 7. CONFIGURAÇÃO AVANÇADA DE CAPTIVE PORTAL
# =====================================================

# Configurar servidor web interno do MikroTik
/ip firewall nat add chain=dstnat protocol=tcp dst-port=80 \
    hotspot=auth \
    action=redirect to-ports=64873 \
    comment="Hotspot captive portal"

# =====================================================
# FINALIZADO
# =====================================================

:put "======================================="
:put "REDIRECIONAMENTO CONFIGURADO!"
:put "======================================="
:put "Agora qualquer acesso será redirecionado"
:put "para: tocantinstransportewifi.com.br"
:put ""
:put "Teste conectando um dispositivo na WiFi"
:put "e abrindo qualquer site no navegador"
:put "======================================="
