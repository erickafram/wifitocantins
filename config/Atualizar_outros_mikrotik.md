# Adicionar DNS para o domínio .local resolver
/ip dns static add address=10.5.50.1 name=login.tocantinswifi.local comment="Hotspot Login Local"

# Limpar cache
/ip dns cache flush


# 1. ADICIONAR DNS ESTÁTICO PARA O LOGIN LOCAL
/ip dns static add address=10.5.50.1 name=login.tocantinswifi.local comment="Hotspot Login Local"

# 2. ADICIONAR ALTERNATIVAS DE DNS (para evitar problemas com .local)
/ip dns static add address=10.5.50.1 name=login.wifi comment="Login Alternativo"
/ip dns static add address=10.5.50.1 name=hotspot.wifi comment="Login Hotspot"

# 3. ADICIONAR NO WALLED GARDEN
/ip hotspot walled-garden add dst-host=login.tocantinswifi.local server=tocantins-hotspot comment="Login Local"
/ip hotspot walled-garden add dst-host=login.wifi server=tocantins-hotspot comment="Login Alternativo"
/ip hotspot walled-garden add dst-host=hotspot.wifi server=tocantins-hotspot comment="Login Hotspot"
/ip hotspot walled-garden add dst-host=10.5.50.1 server=tocantins-hotspot comment="Gateway IP Direto"

# 4. WALLED GARDEN IP - Permitir acesso ao gateway
/ip hotspot walled-garden ip add action=accept dst-address=10.5.50.1 server=tocantins-hotspot comment="Gateway Local"

# 5. AJUSTAR O PERFIL DO HOTSPOT (usar IP ao invés de domínio .local)
/ip hotspot profile set hsprof-tocantins dns-name=hotspot.wifi

# 6. GARANTIR QUE O DNS DO HOTSPOT ESTÁ CORRETO
/ip dhcp-server network set [find address=10.5.50.0/24] dns-server=10.5.50.1


# Verificar DNS estático
/ip dns static print

# Verificar walled garden
/ip hotspot walled-garden print

# Verificar perfil do hotspot
/ip hotspot profile print

# Testar resolução DNS
:put [:resolve login.tocantinswifi.local]
:put [:resolve hotspot.wifi]


SIMPLIFICADO
# ============================================
# CORREÇÃO COMPLETA - COPIAR E COLAR
# ============================================

# DNS Estático
/ip dns static add address=10.5.50.1 name=login.tocantinswifi.local comment="Hotspot Login Local"
/ip dns static add address=10.5.50.1 name=login.wifi comment="Login Alternativo"
/ip dns static add address=10.5.50.1 name=hotspot.wifi comment="Login Hotspot"

# Walled Garden
/ip hotspot walled-garden add dst-host=login.tocantinswifi.local server=tocantins-hotspot comment="Login Local"
/ip hotspot walled-garden add dst-host=login.wifi server=tocantins-hotspot comment="Login Alternativo"
/ip hotspot walled-garden add dst-host=hotspot.wifi server=tocantins-hotspot comment="Login Hotspot"
/ip hotspot walled-garden add dst-host=10.5.50.1 server=tocantins-hotspot comment="Gateway IP Direto"
/ip hotspot walled-garden ip add action=accept dst-address=10.5.50.1 server=tocantins-hotspot comment="Gateway Local"

# Ajustar perfil e DNS
/ip hotspot profile set hsprof-tocantins dns-name=hotspot.wifi
/ip dhcp-server network set [find address=10.5.50.0/24] dns-server=10.5.50.1

# Limpar cache
/ip dns cache flush

# Verificar
:put [:resolve login.tocantinswifi.local]
:put [:resolve hotspot.wifi]
