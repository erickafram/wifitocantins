# CORREÇÃO WALLED GARDEN - WOOVI QR CODE
# Execute estes comandos no terminal do MikroTik:

# 1. Remover regras duplicadas (se existirem)
/ip hotspot walled-garden remove [find comment~"Woovi"]

# 2. Adicionar regras corretas para Woovi
/ip hotspot walled-garden add dst-host=api.openpix.com.br action=allow comment="Woovi API - QR Code"
/ip hotspot walled-garden add dst-host=openpix.com.br action=allow comment="Woovi Domain"
/ip hotspot walled-garden add dst-host=*.openpix.com.br action=allow comment="Woovi Subdomains"

# 3. Adicionar CDNs para imagens
/ip hotspot walled-garden add dst-host=*.cloudflare.com action=allow comment="Cloudflare CDN"
/ip hotspot walled-garden add dst-host=*.amazonaws.com action=allow comment="AWS CDN"

# 4. Permitir HTTPS para APIs
/ip hotspot walled-garden add dst-host=api.openpix.com.br dst-port=443 protocol=tcp action=allow comment="Woovi HTTPS"

# 5. Verificar regras
/ip hotspot walled-garden print

:log info "✅ Walled Garden corrigido para Woovi QR Code"
