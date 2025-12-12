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




lista de bancos
# ============================================
# BANCOS DIGITAIS ADICIONAIS
# ============================================

# PagBank / PagSeguro
/ip hotspot walled-garden add dst-host=pagseguro.com.br comment="PagSeguro"
/ip hotspot walled-garden add dst-host=*.pagseguro.com.br comment="PagSeguro API"
/ip hotspot walled-garden add dst-host=pagbank.com.br comment="PagBank"
/ip hotspot walled-garden add dst-host=*.pagbank.com.br comment="PagBank API"
/ip hotspot walled-garden add dst-host=pagseguro.uol.com.br comment="PagSeguro UOL"
/ip hotspot walled-garden add dst-host=*.pagseguro.uol.com.br comment="PagSeguro UOL API"

# Neon
/ip hotspot walled-garden add dst-host=neon.com.br comment="Banco Neon"
/ip hotspot walled-garden add dst-host=*.neon.com.br comment="Neon API"

# Next (Bradesco Digital)
/ip hotspot walled-garden add dst-host=next.me comment="Next Bank"
/ip hotspot walled-garden add dst-host=*.next.me comment="Next API"

# Original
/ip hotspot walled-garden add dst-host=original.com.br comment="Banco Original"
/ip hotspot walled-garden add dst-host=*.original.com.br comment="Original API"

# Sicoob
/ip hotspot walled-garden add dst-host=sicoob.com.br comment="Sicoob"
/ip hotspot walled-garden add dst-host=*.sicoob.com.br comment="Sicoob API"

# Sicredi
/ip hotspot walled-garden add dst-host=sicredi.com.br comment="Sicredi"
/ip hotspot walled-garden add dst-host=*.sicredi.com.br comment="Sicredi API"

# Banco Pan
/ip hotspot walled-garden add dst-host=bancopan.com.br comment="Banco Pan"
/ip hotspot walled-garden add dst-host=*.bancopan.com.br comment="Pan API"

# BMG
/ip hotspot walled-garden add dst-host=bancobmg.com.br comment="Banco BMG"
/ip hotspot walled-garden add dst-host=*.bancobmg.com.br comment="BMG API"

# Safra
/ip hotspot walled-garden add dst-host=safra.com.br comment="Banco Safra"
/ip hotspot walled-garden add dst-host=*.safra.com.br comment="Safra API"

# BTG Pactual
/ip hotspot walled-garden add dst-host=btgpactual.com comment="BTG Pactual"
/ip hotspot walled-garden add dst-host=*.btgpactual.com comment="BTG API"

# Banco do Nordeste
/ip hotspot walled-garden add dst-host=bnb.gov.br comment="Banco Nordeste"
/ip hotspot walled-garden add dst-host=*.bnb.gov.br comment="BNB API"

# Banco da Amazonia
/ip hotspot walled-garden add dst-host=bancoamazonia.com.br comment="Banco Amazonia"
/ip hotspot walled-garden add dst-host=*.bancoamazonia.com.br comment="Amazonia API"

# Banrisul
/ip hotspot walled-garden add dst-host=banrisul.com.br comment="Banrisul"
/ip hotspot walled-garden add dst-host=*.banrisul.com.br comment="Banrisul API"

# Ame Digital
/ip hotspot walled-garden add dst-host=amedigital.com comment="Ame Digital"
/ip hotspot walled-garden add dst-host=*.amedigital.com comment="Ame API"

# RecargaPay
/ip hotspot walled-garden add dst-host=recargapay.com.br comment="RecargaPay"
/ip hotspot walled-garden add dst-host=*.recargapay.com.br comment="RecargaPay API"

# 99Pay
/ip hotspot walled-garden add dst-host=99app.com comment="99Pay"
/ip hotspot walled-garden add dst-host=*.99app.com comment="99Pay API"

# Will Bank (antigo Agibank)
/ip hotspot walled-garden add dst-host=willbank.com.br comment="Will Bank"
/ip hotspot walled-garden add dst-host=*.willbank.com.br comment="Will API"

# Digio
/ip hotspot walled-garden add dst-host=digio.com.br comment="Digio"
/ip hotspot walled-garden add dst-host=*.digio.com.br comment="Digio API"

# Banco Votorantim
/ip hotspot walled-garden add dst-host=bv.com.br comment="Banco BV"
/ip hotspot walled-garden add dst-host=*.bv.com.br comment="BV API"

# Agibank
/ip hotspot walled-garden add dst-host=agibank.com.br comment="Agibank"
/ip hotspot walled-garden add dst-host=*.agibank.com.br comment="Agibank API"

# Banco Daycoval
/ip hotspot walled-garden add dst-host=daycoval.com.br comment="Daycoval"
/ip hotspot walled-garden add dst-host=*.daycoval.com.br comment="Daycoval API"

# Stone / Ton
/ip hotspot walled-garden add dst-host=stone.com.br comment="Stone"
/ip hotspot walled-garden add dst-host=*.stone.com.br comment="Stone API"
/ip hotspot walled-garden add dst-host=ton.com.br comment="Ton"
/ip hotspot walled-garden add dst-host=*.ton.com.br comment="Ton API"

# Cielo
/ip hotspot walled-garden add dst-host=cielo.com.br comment="Cielo"
/ip hotspot walled-garden add dst-host=*.cielo.com.br comment="Cielo API"

# Rede (Itau)
/ip hotspot walled-garden add dst-host=userede.com.br comment="Rede"
/ip hotspot walled-garden add dst-host=*.userede.com.br comment="Rede API"

# GetNet (Santander)
/ip hotspot walled-garden add dst-host=getnet.com.br comment="GetNet"
/ip hotspot walled-garden add dst-host=*.getnet.com.br comment="GetNet API"

# Cora
/ip hotspot walled-garden add dst-host=cora.com.br comment="Cora"
/ip hotspot walled-garden add dst-host=*.cora.com.br comment="Cora API"

# Asaas
/ip hotspot walled-garden add dst-host=asaas.com comment="Asaas"
/ip hotspot walled-garden add dst-host=*.asaas.com comment="Asaas API"

# Juno/Ebanx
/ip hotspot walled-garden add dst-host=juno.com.br comment="Juno"
/ip hotspot walled-garden add dst-host=*.juno.com.br comment="Juno API"
/ip hotspot walled-garden add dst-host=ebanx.com comment="Ebanx"
/ip hotspot walled-garden add dst-host=*.ebanx.com comment="Ebanx API"

# Gerencianet / EfiBank
/ip hotspot walled-garden add dst-host=gerencianet.com.br comment="Gerencianet"
/ip hotspot walled-garden add dst-host=*.gerencianet.com.br comment="Gerencianet API"
/ip hotspot walled-garden add dst-host=efi.com.br comment="EfiBank"
/ip hotspot walled-garden add dst-host=*.efi.com.br comment="EfiBank API"

# Banco Sofisa
/ip hotspot walled-garden add dst-host=sofisa.com.br comment="Sofisa"
/ip hotspot walled-garden add dst-host=*.sofisa.com.br comment="Sofisa API"

# Banco Modal
/ip hotspot walled-garden add dst-host=modal.com.br comment="Modal"
/ip hotspot walled-garden add dst-host=*.modal.com.br comment="Modal API"

# XP Investimentos
/ip hotspot walled-garden add dst-host=xpi.com.br comment="XP"
/ip hotspot walled-garden add dst-host=*.xpi.com.br comment="XP API"

# Rico
/ip hotspot walled-garden add dst-host=rico.com.vc comment="Rico"
/ip hotspot walled-garden add dst-host=*.rico.com.vc comment="Rico API"

# Clear
/ip hotspot walled-garden add dst-host=clear.com.br comment="Clear"
/ip hotspot walled-garden add dst-host=*.clear.com.br comment="Clear API"

# Banco ABC Brasil
/ip hotspot walled-garden add dst-host=abcbrasil.com.br comment="ABC Brasil"
/ip hotspot walled-garden add dst-host=*.abcbrasil.com.br comment="ABC API"

# Banco Pine
/ip hotspot walled-garden add dst-host=pine.com comment="Pine"
/ip hotspot walled-garden add dst-host=*.pine.com comment="Pine API"

# Banco Fibra
/ip hotspot walled-garden add dst-host=bancofibra.com.br comment="Fibra"
/ip hotspot walled-garden add dst-host=*.bancofibra.com.br comment="Fibra API"

# Banco Rendimento
/ip hotspot walled-garden add dst-host=rendimento.com.br comment="Rendimento"
/ip hotspot walled-garden add dst-host=*.rendimento.com.br comment="Rendimento API"

# Banco Topazio
/ip hotspot walled-garden add dst-host=bancotopazio.com.br comment="Topazio"
/ip hotspot walled-garden add dst-host=*.bancotopazio.com.br comment="Topazio API"

# Banco BS2
/ip hotspot walled-garden add dst-host=bs2.com comment="BS2"
/ip hotspot walled-garden add dst-host=*.bs2.com comment="BS2 API"

# Banco Arbi
/ip hotspot walled-garden add dst-host=arbi.com.br comment="Arbi"
/ip hotspot walled-garden add dst-host=*.arbi.com.br comment="Arbi API"

# Superdigital
/ip hotspot walled-garden add dst-host=superdigital.com.br comment="Superdigital"
/ip hotspot walled-garden add dst-host=*.superdigital.com.br comment="Superdigital API"

# Banco Bari
/ip hotspot walled-garden add dst-host=bancobari.com.br comment="Bari"
/ip hotspot walled-garden add dst-host=*.bancobari.com.br comment="Bari API"

# Banco Master
/ip hotspot walled-garden add dst-host=bancomaster.com.br comment="Master"
/ip hotspot walled-garden add dst-host=*.bancomaster.com.br comment="Master API"

# Banco Semear
/ip hotspot walled-garden add dst-host=bancosemear.com.br comment="Semear"
/ip hotspot walled-garden add dst-host=*.bancosemear.com.br comment="Semear API"

# Banco Paulista
/ip hotspot walled-garden add dst-host=bancopaulista.com.br comment="Paulista"
/ip hotspot walled-garden add dst-host=*.bancopaulista.com.br comment="Paulista API"

# Banco Triângulo
/ip hotspot walled-garden add dst-host=tribanco.com.br comment="Tribanco"
/ip hotspot walled-garden add dst-host=*.tribanco.com.br comment="Tribanco API"

# Banco Cetelem
/ip hotspot walled-garden add dst-host=cetelem.com.br comment="Cetelem"
/ip hotspot walled-garden add dst-host=*.cetelem.com.br comment="Cetelem API"

# Banco Losango
/ip hotspot walled-garden add dst-host=losango.com.br comment="Losango"
/ip hotspot walled-garden add dst-host=*.losango.com.br comment="Losango API"

# Banco Carrefour
/ip hotspot walled-garden add dst-host=bancocarrefour.com.br comment="Carrefour"
/ip hotspot walled-garden add dst-host=*.bancocarrefour.com.br comment="Carrefour API"

# Banco Rodobens
/ip hotspot walled-garden add dst-host=rfrodobens.com.br comment="Rodobens"
/ip hotspot walled-garden add dst-host=*.rodobens.com.br comment="Rodobens API"

# Banco Volkswagen
/ip hotspot walled-garden add dst-host=bancovw.com.br comment="VW Bank"
/ip hotspot walled-garden add dst-host=*.bancovw.com.br comment="VW API"

# Banco Toyota
/ip hotspot walled-garden add dst-host=bancotoyota.com.br comment="Toyota"
/ip hotspot walled-garden add dst-host=*.bancotoyota.com.br comment="Toyota API"

# Banco Honda
/ip hotspot walled-garden add dst-host=bancohonda.com.br comment="Honda"
/ip hotspot walled-garden add dst-host=*.bancohonda.com.br comment="Honda API"

# Banco Mercedes
/ip hotspot walled-garden add dst-host=bancomercedes-benz.com.br comment="Mercedes"
/ip hotspot walled-garden add dst-host=*.bancomercedes-benz.com.br comment="Mercedes API"

# Banco BMW
/ip hotspot walled-garden add dst-host=bmwbank.com.br comment="BMW Bank"
/ip hotspot walled-garden add dst-host=*.bmwbank.com.br comment="BMW API"

# Banco Volvo
/ip hotspot walled-garden add dst-host=volvofinancialservices.com.br comment="Volvo"
/ip hotspot walled-garden add dst-host=*.volvofinancialservices.com.br comment="Volvo API"

# Banco John Deere
/ip hotspot walled-garden add dst-host=johndeere.com.br comment="John Deere"
/ip hotspot walled-garden add dst-host=*.johndeere.com.br comment="John Deere API"

# Banco CNH Industrial
/ip hotspot walled-garden add dst-host=cnhindustrial.com comment="CNH"
/ip hotspot walled-garden add dst-host=*.cnhindustrial.com comment="CNH API"

# Banco Randon
/ip hotspot walled-garden add dst-host=bancorandon.com.br comment="Randon"
/ip hotspot walled-garden add dst-host=*.bancorandon.com.br comment="Randon API"

# Banco Fidis
/ip hotspot walled-garden add dst-host=fidis.com.br comment="Fidis"
/ip hotspot walled-garden add dst-host=*.fidis.com.br comment="Fidis API"

# Banco Moneo
/ip hotspot walled-garden add dst-host=moneo.com.br comment="Moneo"
/ip hotspot walled-garden add dst-host=*.moneo.com.br comment="Moneo API"

# Banco Guanabara
/ip hotspot walled-garden add dst-host=bancoguanabara.com.br comment="Guanabara"
/ip hotspot walled-garden add dst-host=*.bancoguanabara.com.br comment="Guanabara API"

# Banco Industrial
/ip hotspot walled-garden add dst-host=bancoindustrial.com.br comment="Industrial"
/ip hotspot walled-garden add dst-host=*.bancoindustrial.com.br comment="Industrial API"

# Banco Luso Brasileiro
/ip hotspot walled-garden add dst-host=lusobrasileiro.com.br comment="Luso"
/ip hotspot walled-garden add dst-host=*.lusobrasileiro.com.br comment="Luso API"

# Banco Ourinvest
/ip hotspot walled-garden add dst-host=ourinvest.com.br comment="Ourinvest"
/ip hotspot walled-garden add dst-host=*.ourinvest.com.br comment="Ourinvest API"

# Banco Ribeirão Preto
/ip hotspot walled-garden add dst-host=brp.com.br comment="BRP"
/ip hotspot walled-garden add dst-host=*.brp.com.br comment="BRP API"

# Banco Crefisa
/ip hotspot walled-garden add dst-host=crefisa.com.br comment="Crefisa"
/ip hotspot walled-garden add dst-host=*.crefisa.com.br comment="Crefisa API"

# Banco Olé
/ip hotspot walled-garden add dst-host=oleconsiganado.com.br comment="Ole"
/ip hotspot walled-garden add dst-host=*.oleconsiganado.com.br comment="Ole API"

# Banco Paraná
/ip hotspot walled-garden add dst-host=bancoparana.com.br comment="Parana"
/ip hotspot walled-garden add dst-host=*.bancoparana.com.br comment="Parana API"

# Banco Pecúnia
/ip hotspot walled-garden add dst-host=bancopecunia.com.br comment="Pecunia"
/ip hotspot walled-garden add dst-host=*.bancopecunia.com.br comment="Pecunia API"

# Banco Pine
/ip hotspot walled-garden add dst-host=pine.com comment="Pine"
/ip hotspot walled-garden add dst-host=*.pine.com comment="Pine API"

# Banco Rabobank
/ip hotspot walled-garden add dst-host=rabobank.com.br comment="Rabobank"
/ip hotspot walled-garden add dst-host=*.rabobank.com.br comment="Rabobank API"

# Banco Sumitomo
/ip hotspot walled-garden add dst-host=br.smbc.co.jp comment="Sumitomo"
/ip hotspot walled-garden add dst-host=*.smbc.co.jp comment="Sumitomo API"

# Banco Mizuho
/ip hotspot walled-garden add dst-host=mizuhogroup.com comment="Mizuho"
/ip hotspot walled-garden add dst-host=*.mizuhogroup.com comment="Mizuho API"

# Banco MUFG
/ip hotspot walled-garden add dst-host=mufg.jp comment="MUFG"
/ip hotspot walled-garden add dst-host=*.mufg.jp comment="MUFG API"

# Banco Credit Suisse
/ip hotspot walled-garden add dst-host=credit-suisse.com comment="Credit Suisse"
/ip hotspot walled-garden add dst-host=*.credit-suisse.com comment="Credit Suisse API"

# Banco UBS
/ip hotspot walled-garden add dst-host=ubs.com comment="UBS"
/ip hotspot walled-garden add dst-host=*.ubs.com comment="UBS API"

# Banco JP Morgan
/ip hotspot walled-garden add dst-host=jpmorgan.com comment="JP Morgan"
/ip hotspot walled-garden add dst-host=*.jpmorgan.com comment="JP Morgan API"

# Banco Citibank
/ip hotspot walled-garden add dst-host=citibank.com.br comment="Citibank"
/ip hotspot walled-garden add dst-host=*.citibank.com.br comment="Citibank API"

# Banco HSBC
/ip hotspot walled-garden add dst-host=hsbc.com.br comment="HSBC"
/ip hotspot walled-garden add dst-host=*.hsbc.com.br comment="HSBC API"

# Banco BNP Paribas
/ip hotspot walled-garden add dst-host=bnpparibas.com.br comment="BNP"
/ip hotspot walled-garden add dst-host=*.bnpparibas.com.br comment="BNP API"

# Banco Société Générale
/ip hotspot walled-garden add dst-host=sgbrasil.com.br comment="SG"
/ip hotspot walled-garden add dst-host=*.sgbrasil.com.br comment="SG API"

# Banco Deutsche
/ip hotspot walled-garden add dst-host=db.com comment="Deutsche"
/ip hotspot walled-garden add dst-host=*.db.com comment="Deutsche API"

# Banco Barclays
/ip hotspot walled-garden add dst-host=barclays.com comment="Barclays"
/ip hotspot walled-garden add dst-host=*.barclays.com comment="Barclays API"

# Banco ING
/ip hotspot walled-garden add dst-host=ing.com comment="ING"
/ip hotspot walled-garden add dst-host=*.ing.com comment="ING API"

# Banco Scotiabank
/ip hotspot walled-garden add dst-host=scotiabank.com comment="Scotiabank"
/ip hotspot walled-garden add dst-host=*.scotiabank.com comment="Scotiabank API"

# Banco Bank of America
/ip hotspot walled-garden add dst-host=bankofamerica.com comment="BoA"
/ip hotspot walled-garden add dst-host=*.bankofamerica.com comment="BoA API"

# Banco Wells Fargo
/ip hotspot walled-garden add dst-host=wellsfargo.com comment="Wells Fargo"
/ip hotspot walled-garden add dst-host=*.wellsfargo.com comment="Wells Fargo API"

# Banco Goldman Sachs
/ip hotspot walled-garden add dst-host=goldmansachs.com comment="Goldman"
/ip hotspot walled-garden add dst-host=*.goldmansachs.com comment="Goldman API"

# Banco Morgan Stanley
/ip hotspot walled-garden add dst-host=morganstanley.com comment="Morgan Stanley"
/ip hotspot walled-garden add dst-host=*.morganstanley.com comment="Morgan Stanley API"

# Fintech - Creditas
/ip hotspot walled-garden add dst-host=creditas.com comment="Creditas"
/ip hotspot walled-garden add dst-host=*.creditas.com comment="Creditas API"

# Fintech - Geru
/ip hotspot walled-garden add dst-host=geru.com.br comment="Geru"
/ip hotspot walled-garden add dst-host=*.geru.com.br comment="Geru API"

# Fintech - Rebel
/ip hotspot walled-garden add dst-host=rebel.com.br comment="Rebel"
/ip hotspot walled-garden add dst-host=*.rebel.com.br comment="Rebel API"

# Fintech - Lendico
/ip hotspot walled-garden add dst-host=lendico.com.br comment="Lendico"
/ip hotspot walled-garden add dst-host=*.lendico.com.br comment="Lendico API"

# Fintech - Simplic
/ip hotspot walled-garden add dst-host=simplic.com.br comment="Simplic"
/ip hotspot walled-garden add dst-host=*.simplic.com.br comment="Simplic API"

# Fintech - Bom Pra Crédito
/ip hotspot walled-garden add dst-host=bfrompracredito.com.br comment="Bom Pra Credito"
/ip hotspot walled-garden add dst-host=*.bompracredito.com.br comment="Bom Pra Credito API"

# Fintech - Juros Baixos
/ip hotspot walled-garden add dst-host=jurosbaixos.com.br comment="Juros Baixos"
/ip hotspot walled-garden add dst-host=*.jurosbaixos.com.br comment="Juros Baixos API"

# Fintech - Meutudo
/ip hotspot walled-garden add dst-host=meutudo.com.br comment="Meutudo"
/ip hotspot walled-garden add dst-host=*.meutudo.com.br comment="Meutudo API"

# Fintech - Noverde
/ip hotspot walled-garden add dst-host=noverde.com.br comment="Noverde"
/ip hotspot walled-garden add dst-host=*.noverde.com.br comment="Noverde API"

# Fintech - Provu
/ip hotspot walled-garden add dst-host=prfrovu.com.br comment="Provu"
/ip hotspot walled-garden add dst-host=*.provu.com.br comment="Provu API"

# Fintech - Jeitto
/ip hotspot walled-garden add dst-host=jeitto.com.br comment="Jeitto"
/ip hotspot walled-garden add dst-host=*.jeitto.com.br comment="Jeitto API"

# Fintech - Cresol
/ip hotspot walled-garden add dst-host=cresol.com.br comment="Cresol"
/ip hotspot walled-garden add dst-host=*.cresol.com.br comment="Cresol API"

# Fintech - Unicred
/ip hotspot walled-garden add dst-host=unicred.com.br comment="Unicred"
/ip hotspot walled-garden add dst-host=*.unicred.com.br comment="Unicred API"

# Fintech - Ailos
/ip hotspot walled-garden add dst-host=ailos.coop.br comment="Ailos"
/ip hotspot walled-garden add dst-host=*.ailos.coop.br comment="Ailos API"

# Fintech - Viacredi
/ip hotspot walled-garden add dst-host=viacredi.coop.br comment="Viacredi"
/ip hotspot walled-garden add dst-host=*.viacredi.coop.br comment="Viacredi API"

# Fintech - Uniprime
/ip hotspot walled-garden add dst-host=uniprime.com.br comment="Uniprime"
/ip hotspot walled-garden add dst-host=*.uniprime.com.br comment="Uniprime API"

# Fintech - Cecred
/ip hotspot walled-garden add dst-host=cecred.coop.br comment="Cecred"
/ip hotspot walled-garden add dst-host=*.cecred.coop.br comment="Cecred API"

# Fintech - Credisis
/ip hotspot walled-garden add dst-host=credisis.com.br comment="Credisis"
/ip hotspot walled-garden add dst-host=*.credisis.com.br comment="Credisis API"

# Fintech - Confesol
/ip hotspot walled-garden add dst-host=confesol.com.br comment="Confesol"
/ip hotspot walled-garden add dst-host=*.confesol.com.br comment="Confesol API"

# Verificar
/ip hotspot walled-garden print count-only
