# ============================================
# MIKROTIK WALLED GARDEN - ATUALIZADO 2026
# ============================================
# Script para liberar acesso aos bancos e gateways de pagamento
# sem necessidade de autenticação no hotspot

# LIMPAR WALLED GARDEN EXISTENTE (CUIDADO!)
# /ip hotspot walled-garden remove [find]
# /ip hotspot walled-garden ip remove [find]

# ============================================
# PORTAL E INFRAESTRUTURA
# ============================================
/ip hotspot walled-garden
add comment="Portal Principal" dst-host=tocantinstransportewifi.com.br
add comment="Portal WWW" dst-host=*.tocantinstransportewifi.com.br
add comment="Portal: Tailwind CDN" dst-host=cdn.tailwindcss.com
add comment="Portal: Google Fonts API" dst-host=fonts.googleapis.com
add comment="Portal: Google Fonts Static" dst-host=fonts.gstatic.com
add comment="Portal: CDNJS" dst-host=cdnjs.cloudflare.com
add comment="Infra: Google APIs" dst-host=*.googleapis.com
add comment="Infra: Google Static" dst-host=*.gstatic.com
add comment="Infra: Cloudflare CDN" dst-host=*.cloudflare.com

# ============================================
# GATEWAYS DE PAGAMENTO PIX
# ============================================

# Woovi / OpenPix
add comment="PIX: Woovi" dst-host=*.woovi.com
add comment="PIX: Woovi API" dst-host=api.woovi.com
add comment="PIX: Woovi Developers" dst-host=developers.woovi.com
add comment="PIX: OpenPix" dst-host=*.openpix.com.br
add comment="PIX: OpenPix Developers" dst-host=developers.openpix.com.br

# PagBank / PagSeguro (Atualizado 2026)
add comment="PIX: PagBank" dst-host=*.pagbank.com.br
add comment="PIX: PagBank API" dst-host=api.pagbank.com.br
add comment="PIX: PagBank Acesso" dst-host=acesso.pagbank.com.br
add comment="PIX: PagSeguro" dst-host=*.pagseguro.com
add comment="PIX: PagSeguro UOL" dst-host=*.pagseguro.uol.com.br
add comment="PIX: PagSeguro API" dst-host=api.pagseguro.com
add comment="PIX: PagSeguro International" dst-host=*.international.pagseguro.com
add comment="PIX: PagSeguro Billing" dst-host=billing.boacompra.com

# QR Code e Utilitários PIX
add comment="PIX: QR Code API" dst-host=api.qrserver.com
add comment="PIX: QR Charts Google" dst-host=chart.googleapis.com
add comment="PIX: Banco Central" dst-host=*.bcb.gov.br
add comment="PIX: BCB Pix" dst-host=pix.bcb.gov.br

# ============================================
# BANCOS PRINCIPAIS
# ============================================

# Nubank (Maior banco digital 2026)
add comment="Banco: Nubank" dst-host=*.nubank.com.br
add comment="Banco: Nubank Global" dst-host=*.nubank.com
add comment="Banco: NuInvest" dst-host=*.nuinvest.com.br
add comment="Banco: Nu API" dst-host=api.nubank.com.br

# Itaú Unibanco
add comment="Banco: Itau" dst-host=*.itau.com.br
add comment="Banco: Itau Global" dst-host=*.itau.com
add comment="Banco: Itau Unibanco" dst-host=*.itau-unibanco.com.br
add comment="Banco: Iti" dst-host=*.iti.com.br
add comment="Banco: Itau NIC" dst-host=*.nic.itau

# Banco do Brasil
add comment="Banco: BB" dst-host=*.bb.com.br
add comment="Banco: BB Seguros" dst-host=*.bbseguros.com.br
add comment="Banco: BB API" dst-host=api.bb.com.br

# Caixa Econômica Federal
add comment="Banco: Caixa" dst-host=*.caixa.gov.br
add comment="Banco: Caixa Alt" dst-host=*.caixa.com.br
add comment="Banco: Caixa Seguridade" dst-host=*.caixaseguridade.com.br

# Santander
add comment="Banco: Santander" dst-host=*.santander.com.br
add comment="Banco: Santander API" dst-host=api.santander.com.br

# Bradesco
add comment="Banco: Bradesco" dst-host=*.bradesco.com.br
add comment="Banco: Bradesco Card" dst-host=*.bradescocard.com.br
add comment="Banco: Next" dst-host=*.next.me

# Inter
add comment="Banco: Inter" dst-host=*.bancointer.com.br
add comment="Banco: Inter Global" dst-host=*.inter.co

# BRB (Banco de Brasília)
add comment="Banco: BRB" dst-host=*.brb.com.br
add comment="Banco: BRB Servicos" dst-host=*.brbservicos.com.br
add comment="Banco: BRB Card" dst-host=*.brbcard.com.br
add comment="Banco: BRB Seguros" dst-host=*.brbseguros.com.br

# PicPay
add comment="Banco: PicPay" dst-host=*.picpay.com
add comment="Banco: PicPay BR" dst-host=*.picpay.com.br
add comment="Banco: PicPay API" dst-host=api.picpay.com

# Mercado Pago / Mercado Livre
add comment="Banco: Mercado Pago" dst-host=*.mercadopago.com.br
add comment="Banco: Mercado Pago Global" dst-host=*.mercadopago.com
add comment="Banco: Mercado Livre" dst-host=*.mercadolivre.com.br
add comment="Banco: Mercado Libre" dst-host=*.mercadolibre.com

# C6 Bank
add comment="Banco: C6" dst-host=*.c6bank.com.br
add comment="Banco: C6 API" dst-host=api.c6bank.com.br

# Cooperativas
add comment="Banco: Sicoob" dst-host=*.sicoob.com.br
add comment="Banco: Sicredi" dst-host=*.sicredi.com.br

# Outros Bancos Digitais
add comment="Banco: Neon" dst-host=*.neon.com.br
add comment="Banco: Ame Digital" dst-host=*.amedigital.com
add comment="Banco: Original" dst-host=*.original.com.br
add comment="Banco: Safra" dst-host=*.safra.com.br
add comment="Banco: BMG" dst-host=*.bancobmg.com.br
add comment="Banco: Banrisul" dst-host=*.banrisul.com.br
add comment="Banco: Pan" dst-host=*.bancopan.com.br

# Adquirentes e Processadores
add comment="Banco: Cielo" dst-host=*.cielo.com.br
add comment="Banco: Stone" dst-host=*.stone.com.br
add comment="Banco: Elo" dst-host=*.elo.com.br
add comment="Banco: Rede" dst-host=*.userede.com.br
add comment="Banco: GetNet" dst-host=*.getnet.com.br

# ============================================
# CERTIFICADOS SSL (OCSP/CRL)
# ============================================
add comment="SSL: DigiCert OCSP" dst-host=ocsp.digicert.com
add comment="SSL: DigiCert CRL" dst-host=crl3.digicert.com
add comment="SSL: DigiCert CRL4" dst-host=crl4.digicert.com
add comment="SSL: VeriSign" dst-host=ocsp.verisign.com
add comment="SSL: GlobalSign OCSP" dst-host=ocsp.globalsign.com
add comment="SSL: GlobalSign CRL" dst-host=crl.globalsign.com
add comment="SSL: Google PKI" dst-host=ocsp.pki.goog
add comment="SSL: Google PKI Alt" dst-host=pki.goog
add comment="SSL: Sectigo OCSP" dst-host=ocsp.sectigo.com
add comment="SSL: Sectigo CRL" dst-host=crl.sectigo.com
add comment="SSL: Comodo" dst-host=ocsp.comodoca.com
add comment="SSL: UserTrust" dst-host=ocsp.usertrust.com
add comment="SSL: Let's Encrypt" dst-host=r3.o.lencr.org
add comment="SSL: Let's Encrypt CRL" dst-host=x1.c.lencr.org

# ============================================
# IPs DIRETOS (Walled Garden IP)
# ============================================
/ip hotspot walled-garden ip
add action=accept comment="IP: Portal" dst-address=104.248.185.39
add action=accept comment="IP: Router DNS" dst-address=10.5.50.1
add action=accept comment="IP: Google DNS" dst-address=8.8.8.8
add action=accept comment="IP: Google DNS 2" dst-address=8.8.4.4
add action=accept comment="IP: Cloudflare DNS" dst-address=1.1.1.1
add action=accept comment="IP: Cloudflare DNS 2" dst-address=1.0.0.1

:log info "Walled Garden atualizado com sucesso - 2026"
