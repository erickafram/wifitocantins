# ============================================================
# WALLED GARDEN - BANCOS BRASILEIROS
# Script para configurar acesso aos bancos no Hotspot
# ============================================================
#
# Este script configura o Walled Garden para permitir acesso
# aos aplicativos de bancos brasileiros sem autenticação,
# permitindo que os usuários façam pagamentos PIX.
#
# OPÇÃO 1: Executar este script manualmente
# OPÇÃO 2: Baixar da API automaticamente
#
# ============================================================

# ============================================================
# LIMPAR REGRAS ANTIGAS (evitar duplicatas)
# ============================================================

/ip hotspot walled-garden remove [find comment~"BANK"]
/ip hotspot walled-garden ip remove [find comment~"BANK"]

# ============================================================
# WALLED GARDEN - DOMÍNIOS
# ============================================================

/ip hotspot walled-garden

# Portal de pagamento
add dst-host=tocantinstransportewifi.com.br comment="Portal"
add dst-host=*.tocantinstransportewifi.com.br comment="Portal-Wild"

# Gateway de pagamento (Woovi/OpenPix)
add dst-host=*.woovi.com comment="BANK-Woovi"
add dst-host=*.openpix.com.br comment="BANK-OpenPix"

# ========== BANCOS DIGITAIS ==========
# Nubank
add dst-host=*.nubank.com.br comment="BANK-Nubank"
add dst-host=*.nubank.com comment="BANK-Nubank2"
add dst-host=*.nubankstatic.com comment="BANK-Nubank-CDN"

# PicPay
add dst-host=*.picpay.com comment="BANK-PicPay"

# Inter
add dst-host=*.bancointer.com.br comment="BANK-Inter"
add dst-host=*.inter.co comment="BANK-Inter2"

# C6 Bank
add dst-host=*.c6bank.com.br comment="BANK-C6"

# Neon
add dst-host=*.neon.com.br comment="BANK-Neon"

# Next
add dst-host=*.next.me comment="BANK-Next"

# Will Bank
add dst-host=*.willbank.com.br comment="BANK-Will"

# Original
add dst-host=*.original.com.br comment="BANK-Original"

# PagBank/PagSeguro
add dst-host=*.pagseguro.com.br comment="BANK-PagBank"
add dst-host=*.pagseguro.uol.com.br comment="BANK-PagBank2"

# Mercado Pago
add dst-host=*.mercadopago.com.br comment="BANK-MercadoPago"
add dst-host=*.mercadopago.com comment="BANK-MercadoPago2"

# ========== BANCOS TRADICIONAIS ==========
# Caixa Econômica Federal
add dst-host=*.caixa.gov.br comment="BANK-Caixa"
add dst-host=internetbanking.caixa.gov.br comment="BANK-Caixa-IB"
add dst-host=acessoseguro.caixa.gov.br comment="BANK-Caixa-Seg"

# Banco do Brasil
add dst-host=*.bb.com.br comment="BANK-BB"
add dst-host=*.bancodobrasil.com.br comment="BANK-BB2"

# Itaú
add dst-host=*.itau.com.br comment="BANK-Itau"

# Bradesco
add dst-host=*.bradesco.com.br comment="BANK-Bradesco"

# Santander
add dst-host=*.santander.com.br comment="BANK-Santander"

# BRB
add dst-host=*.brb.com.br comment="BANK-BRB"

# Banco da Amazônia
add dst-host=*.bancoamazonia.com.br comment="BANK-Amazonia"
add dst-host=*.basa.com.br comment="BANK-BASA"

# Sicoob
add dst-host=*.sicoob.com.br comment="BANK-Sicoob"

# Sicredi
add dst-host=*.sicredi.com.br comment="BANK-Sicredi"

# Banrisul
add dst-host=*.banrisul.com.br comment="BANK-Banrisul"

# Banco Central (PIX)
add dst-host=*.bcb.gov.br comment="BANK-BCB"

# ========== CDNs E SERVIÇOS ==========
add dst-host=*.googleapis.com comment="BANK-Google-API"
add dst-host=*.gstatic.com comment="BANK-Google-Static"
add dst-host=*.firebaseio.com comment="BANK-Firebase"

# ============================================================
# WALLED GARDEN IP - RANGES DE IP
# ============================================================

/ip hotspot walled-garden ip

# Portal
add action=accept dst-address=138.68.255.122 comment="BANK-Portal"

# ========== AWS BRASIL (Nubank, PicPay, etc) ==========
add action=accept dst-address=18.228.0.0/14 comment="BANK-AWS-BR-1"
add action=accept dst-address=52.67.0.0/16 comment="BANK-AWS-BR-2"
add action=accept dst-address=54.207.0.0/16 comment="BANK-AWS-BR-3"
add action=accept dst-address=54.232.0.0/14 comment="BANK-AWS-BR-4"
add action=accept dst-address=177.71.128.0/17 comment="BANK-AWS-BR-5"
add action=accept dst-address=15.228.0.0/15 comment="BANK-AWS-BR-6"

# ========== CLOUDFLARE ==========
add action=accept dst-address=104.16.0.0/12 comment="BANK-CF-1"
add action=accept dst-address=172.64.0.0/13 comment="BANK-CF-2"
add action=accept dst-address=131.0.72.0/22 comment="BANK-CF-3"
add action=accept dst-address=141.101.64.0/18 comment="BANK-CF-4"
add action=accept dst-address=162.158.0.0/15 comment="BANK-CF-5"
add action=accept dst-address=188.114.96.0/20 comment="BANK-CF-6"
add action=accept dst-address=190.93.240.0/20 comment="BANK-CF-7"
add action=accept dst-address=198.41.128.0/17 comment="BANK-CF-8"

# ========== AKAMAI (CDN de bancos) ==========
add action=accept dst-address=23.0.0.0/12 comment="BANK-Akamai-1"
add action=accept dst-address=104.64.0.0/10 comment="BANK-Akamai-2"

# ========== AZURE BRASIL ==========
add action=accept dst-address=191.232.0.0/13 comment="BANK-Azure-1"
add action=accept dst-address=20.195.0.0/16 comment="BANK-Azure-2"
add action=accept dst-address=20.206.0.0/16 comment="BANK-Azure-3"

# ========== GOOGLE CLOUD BRASIL ==========
add action=accept dst-address=35.198.0.0/16 comment="BANK-GCP-1"
add action=accept dst-address=35.199.0.0/16 comment="BANK-GCP-2"

# ========== BANCOS ESPECÍFICOS ==========
# Caixa
add action=accept dst-address=200.201.0.0/16 comment="BANK-CEF-1"
add action=accept dst-address=161.148.0.0/16 comment="BANK-CEF-CDN"

# Banco do Brasil
add action=accept dst-address=170.66.0.0/16 comment="BANK-BB-1"
add action=accept dst-address=201.33.144.0/21 comment="BANK-BB-2"

# Itaú
add action=accept dst-address=200.196.144.0/20 comment="BANK-Itau-1"
add action=accept dst-address=138.59.160.0/22 comment="BANK-Itau-2"

# Bradesco
add action=accept dst-address=200.155.80.0/20 comment="BANK-Bradesco-1"
add action=accept dst-address=177.92.208.0/20 comment="BANK-Bradesco-2"

# Santander
add action=accept dst-address=200.220.176.0/20 comment="BANK-Santander-1"
add action=accept dst-address=200.232.64.0/19 comment="BANK-Santander-2"

# BRB
add action=accept dst-address=200.11.16.0/20 comment="BANK-BRB"

# Banco da Amazônia
add action=accept dst-address=45.5.204.0/22 comment="BANK-Amazonia"

# ========== AZION (CDN Caixa) ==========
add action=accept dst-address=179.191.160.0/20 comment="BANK-Azion-1"
add action=accept dst-address=186.195.64.0/20 comment="BANK-Azion-2"

# ========== FASTLY ==========
add action=accept dst-address=151.101.0.0/16 comment="BANK-Fastly-1"
add action=accept dst-address=199.232.0.0/16 comment="BANK-Fastly-2"

# ========== DNS ==========
add action=accept dst-address=1.1.1.1 comment="BANK-DNS-CF"
add action=accept dst-address=8.8.8.8 comment="BANK-DNS-Google"

# ============================================================
# FIM DO SCRIPT
# ============================================================
#
# Para verificar as regras criadas:
# /ip hotspot walled-garden print where comment~"BANK"
# /ip hotspot walled-garden ip print where comment~"BANK"
#
# ============================================================
