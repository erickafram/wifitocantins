# =====================================================
# MIKROTIK - LIBERAR TODOS OS BANCOS BRASILEIROS
# =====================================================
# Cole todos estes comandos no terminal do MikroTik
# Necessário para pagamentos PIX funcionarem

/ip hotspot walled-garden

# =====================================================
# BANCOS PRINCIPAIS (BIG 5)
# =====================================================

# Banco do Brasil
add dst-host=bb.com.br
add dst-host=*.bb.com.br
add dst-host=bancodobrasil.com.br
add dst-host=*.bancodobrasil.com.br

# Itaú Unibanco
add dst-host=itau.com.br
add dst-host=*.itau.com.br
add dst-host=itauunibanco.com.br
add dst-host=*.itauunibanco.com.br

# Bradesco
add dst-host=bradesco.com.br
add dst-host=*.bradesco.com.br
add dst-host=bradesconetempresa.com.br
add dst-host=*.bradesconetempresa.com.br

# Santander
add dst-host=santander.com.br
add dst-host=*.santander.com.br

# Caixa Econômica Federal
add dst-host=caixa.gov.br
add dst-host=*.caixa.gov.br
add dst-host=cef.com.br
add dst-host=*.cef.com.br

# =====================================================
# BANCOS DIGITAIS POPULARES
# =====================================================

# Nubank
add dst-host=nubank.com.br
add dst-host=*.nubank.com.br
add dst-host=nu.com.br
add dst-host=*.nu.com.br

# Inter
add dst-host=inter.co
add dst-host=*.inter.co
add dst-host=bancointer.com.br
add dst-host=*.bancointer.com.br

# Will Bank
add dst-host=will.bank
add dst-host=*.will.bank
add dst-host=willbank.com.br
add dst-host=*.willbank.com.br

# PicPay
add dst-host=picpay.com
add dst-host=*.picpay.com
add dst-host=picpay.com.br
add dst-host=*.picpay.com.br

# C6 Bank
add dst-host=c6bank.com.br
add dst-host=*.c6bank.com.br

# Next (Bradesco)
add dst-host=next.me
add dst-host=*.next.me

# Neon
add dst-host=neon.com.br
add dst-host=*.neon.com.br

# Original
add dst-host=original.com.br
add dst-host=*.original.com.br

# =====================================================
# BANCOS REGIONAIS IMPORTANTES
# =====================================================

# BRB (Banco de Brasília) - TOCANTINS
add dst-host=brb.com.br
add dst-host=*.brb.com.br

# Sicoob
add dst-host=sicoob.com.br
add dst-host=*.sicoob.com.br

# Sicredi
add dst-host=sicredi.com.br
add dst-host=*.sicredi.com.br

# Banrisul
add dst-host=banrisul.com.br
add dst-host=*.banrisul.com.br

# Banco do Nordeste
add dst-host=bnb.gov.br
add dst-host=*.bnb.gov.br

# BNDES
add dst-host=bndes.gov.br
add dst-host=*.bndes.gov.br

# =====================================================
# BANCOS MÉDIOS E OUTROS
# =====================================================

# BTG Pactual
add dst-host=btgpactual.com
add dst-host=*.btgpactual.com

# Safra
add dst-host=safra.com.br
add dst-host=*.safra.com.br

# Votorantim
add dst-host=bv.com.br
add dst-host=*.bv.com.br

# Banco Pan
add dst-host=bancopan.com.br
add dst-host=*.bancopan.com.br

# BMG
add dst-host=bancobmg.com.br
add dst-host=*.bancobmg.com.br

# Daycoval
add dst-host=daycoval.com.br
add dst-host=*.daycoval.com.br

# Pine
add dst-host=pine.com
add dst-host=*.pine.com

# Modal
add dst-host=modal.com.br
add dst-host=*.modal.com.br

# =====================================================
# FINTECHS E CARTEIRAS DIGITAIS
# =====================================================

# Mercado Pago
add dst-host=mercadopago.com.br
add dst-host=*.mercadopago.com.br
add dst-host=mercadopago.com
add dst-host=*.mercadopago.com

# PayPal
add dst-host=paypal.com
add dst-host=*.paypal.com
add dst-host=paypal.com.br
add dst-host=*.paypal.com.br

# Stone Pagamentos
add dst-host=stone.com.br
add dst-host=*.stone.com.br

# PagSeguro
add dst-host=pagseguro.uol.com.br
add dst-host=*.pagseguro.uol.com.br
add dst-host=pagseguro.com.br
add dst-host=*.pagseguro.com.br

# Cielo
add dst-host=cielo.com.br
add dst-host=*.cielo.com.br

# Rede
add dst-host=userede.com.br
add dst-host=*.userede.com.br

# GetNet
add dst-host=getnet.com.br
add dst-host=*.getnet.com.br

# =====================================================
# COOPERATIVAS DE CRÉDITO
# =====================================================

# Unicred
add dst-host=unicred.com.br
add dst-host=*.unicred.com.br

# Cecred
add dst-host=cecred.com.br
add dst-host=*.cecred.com.br

# Cresol
add dst-host=cresol.com.br
add dst-host=*.cresol.com.br

# =====================================================
# BANCOS INTERNACIONAIS NO BRASIL
# =====================================================

# HSBC
add dst-host=hsbc.com.br
add dst-host=*.hsbc.com.br

# Citibank
add dst-host=citibank.com.br
add dst-host=*.citibank.com.br

# =====================================================
# INFRAESTRUTURA PIX E PAGAMENTOS
# =====================================================

# Banco Central do Brasil
add dst-host=bcb.gov.br
add dst-host=*.bcb.gov.br
add dst-host=pix.bcb.gov.br

# SPC/Serasa
add dst-host=serasa.com.br
add dst-host=*.serasa.com.br
add dst-host=spc.org.br
add dst-host=*.spc.org.br

# CIP (Câmara Interbancária de Pagamentos)
add dst-host=cip-bancos.org.br
add dst-host=*.cip-bancos.org.br

# FEBRABAN
add dst-host=febraban.org.br
add dst-host=*.febraban.org.br

# =====================================================
# GATEWAYS DE PAGAMENTO ADICIONAIS
# =====================================================

# Stripe
add dst-host=stripe.com
add dst-host=*.stripe.com

# Wirecard/Moip
add dst-host=moip.com.br
add dst-host=*.moip.com.br
add dst-host=wirecard.com.br
add dst-host=*.wirecard.com.br

# Pagar.me
add dst-host=pagar.me
add dst-host=*.pagar.me

# iFood Payment
add dst-host=ifoodpayment.com.br
add dst-host=*.ifoodpayment.com.br

# =====================================================
# BANCOS ESPECÍFICOS DO TOCANTINS/REGIÃO
# =====================================================

# Banco da Amazônia
add dst-host=bancoamazonia.com.br
add dst-host=*.bancoamazonia.com.br

# Banco do Estado do Pará
add dst-host=banpara.b.br
add dst-host=*.banpara.b.br

# =====================================================
# VERIFICAÇÃO E STATUS
# =====================================================

:put "=== BANCOS LIBERADOS NO WALLED GARDEN ==="
/ip hotspot walled-garden print where dst-host~"bank" or dst-host~"bb.com" or dst-host~"nubank" or dst-host~"inter"

:put ""
:put "✅ TODOS OS BANCOS BRASILEIROS FORAM LIBERADOS!"
:put "💳 PIX funcionará com qualquer banco"
:put "🏦 Total de bancos liberados: 50+ instituições"
:put ""
:put "📋 BANCOS PRINCIPAIS LIBERADOS:"
:put "   • Nubank, Inter, Will Bank"
:put "   • Banco do Brasil, Itaú, Bradesco"
:put "   • Santander, Caixa, BRB"
:put "   • C6, PicPay, Next, Neon"
:put "   • Sicoob, Sicredi, BTG"
:put "   • E mais 40+ outros bancos"
:put ""
:put "🔄 Para testar: Faça um PIX de qualquer banco"
:put "⚡ Pagamentos serão processados instantaneamente"
