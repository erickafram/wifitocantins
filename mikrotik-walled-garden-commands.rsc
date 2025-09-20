# =====================================================
# MIKROTIK - COMANDOS PARA DESBLOQUEAR DOMÍNIOS
# =====================================================
# Cole todos estes comandos no terminal do MikroTik
# Permite acesso aos recursos externos ANTES do pagamento

# =====================================================
# 1. DOMÍNIOS PRINCIPAIS DO SISTEMA
# =====================================================

# Servidor principal da aplicação
/ip hotspot walled-garden
add dst-host=tocantinstransportewifi.com.br
add dst-host=www.tocantinstransportewifi.com.br
add dst-host=*.tocantinstransportewifi.com.br

# =====================================================
# 2. RECURSOS DE ESTILO E FONTES
# =====================================================

# Tailwind CSS (CDN)
add dst-host=cdn.tailwindcss.com
add dst-host=*.tailwindcss.com

# Google Fonts
add dst-host=fonts.googleapis.com
add dst-host=fonts.gstatic.com
add dst-host=*.googleapis.com
add dst-host=*.gstatic.com

# =====================================================
# 3. WHATSAPP (LINKS DIRETOS)
# =====================================================

# WhatsApp Web e API
add dst-host=wa.me
add dst-host=*.whatsapp.com
add dst-host=web.whatsapp.com
add dst-host=api.whatsapp.com

# =====================================================
# 4. RECURSOS DE PAGAMENTO PIX
# =====================================================

# Woovi (OpenPix) - Gateway PIX
add dst-host=api.woovi.com
add dst-host=*.woovi.com
add dst-host=openpix.com.br
add dst-host=*.openpix.com.br

# Banco Central do Brasil (PIX)
add dst-host=bcb.gov.br
add dst-host=*.bcb.gov.br
add dst-host=pix.bcb.gov.br

# Bancos principais para PIX
add dst-host=*.itau.com.br
add dst-host=*.bradesco.com.br
add dst-host=*.bb.com.br
add dst-host=*.santander.com.br
add dst-host=*.caixa.gov.br
add dst-host=*.nubank.com.br
add dst-host=*.inter.co
add dst-host=*.picpay.com

# =====================================================
# 5. RECURSOS ESSENCIAIS PARA FUNCIONAMENTO
# =====================================================

# jQuery e bibliotecas JavaScript
add dst-host=ajax.googleapis.com
add dst-host=code.jquery.com
add dst-host=cdn.jsdelivr.net
add dst-host=cdnjs.cloudflare.com
add dst-host=unpkg.com

# Imagens e assets
add dst-host=images.unsplash.com
add dst-host=*.unsplash.com

# =====================================================
# 6. DNS E CONECTIVIDADE
# =====================================================

# Servidores DNS públicos
add dst-host=8.8.8.8
add dst-host=8.8.4.4
add dst-host=1.1.1.1
add dst-host=1.0.0.1

# Cloudflare
add dst-host=*.cloudflare.com
add dst-host=cdnjs.cloudflare.com

# =====================================================
# 7. RECURSOS ADICIONAIS DE SEGURANÇA
# =====================================================

# Let's Encrypt (certificados SSL)
add dst-host=letsencrypt.org
add dst-host=*.letsencrypt.org

# Certificados SSL
add dst-host=ssl.gstatic.com
add dst-host=*.gstatic.com

# =====================================================
# 8. REDES SOCIAIS (PARA FUNCIONALIDADE INSTAGRAM)
# =====================================================

# Instagram
add dst-host=instagram.com
add dst-host=*.instagram.com
add dst-host=*.cdninstagram.com

# Facebook (proprietário do Instagram)
add dst-host=facebook.com
add dst-host=*.facebook.com
add dst-host=*.fbcdn.net

# =====================================================
# 9. OUTROS RECURSOS IMPORTANTES
# =====================================================

# GitHub (para possíveis recursos)
add dst-host=github.com
add dst-host=*.github.com
add dst-host=raw.githubusercontent.com

# Microsoft (para possíveis integrações)
add dst-host=*.microsoft.com
add dst-host=*.live.com

# =====================================================
# 10. COMANDOS DE VERIFICAÇÃO
# =====================================================

# Listar todas as regras criadas
:put "=== WALLED GARDEN RULES CRIADAS ==="
/ip hotspot walled-garden print

# Verificar se hotspot está funcionando
:put "=== HOTSPOT STATUS ==="
/ip hotspot print

:put "✅ Todos os domínios foram desbloqueados!"
:put "🌐 Agora os usuários podem acessar o portal mesmo antes de pagar"
:put "💡 Para testar: conecte um dispositivo no WiFi e acesse o portal"
