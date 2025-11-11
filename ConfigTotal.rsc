# 2025-09-28 17:46:44 by RouterOS 7.20rc3
# software id = C058-2UQR
#
# model = RBD52G-5HacD2HnD
# serial number = HGJ09X2F8FD
/interface bridge
add admin-mac=D4:01:C3:C6:29:4A auto-mac=no comment=defconf name=bridgeLocal
add fast-forward=no name=wifi-hotspot
/interface wifi datapath
add bridge=wifi-hotspot name=capdp
/interface wifi security
add authentication-types="" name=open-security
/interface wifi configuration
add country=Brazil name=tocantins-2g security=open-security ssid=\
    TocantinsTransporteWiFi
add country=Brazil name=tocantins-5g security=open-security ssid=\
    TocantinsTransporteWiFi-5G
/interface wifi
set [ find default-name=wifi1 ] configuration=tocantins-2g \
    configuration.manager=local .mode=ap datapath=capdp datapath.bridge=\
    wifi-hotspot disabled=no
set [ find default-name=wifi2 ] configuration=tocantins-5g \
    configuration.manager=local .mode=ap datapath=capdp datapath.bridge=\
    wifi-hotspot disabled=no
/ip hotspot profile
set [ find default=yes ] http-proxy=127.0.0.1:0
add dns-name=login.tocantinswifi.local hotspot-address=10.5.50.1 \
    html-directory=flash/hotspot login-by=cookie,http-chap,http-pap name=\
    hsprof-tocantins
/ip pool
add name=wifi-pool ranges=10.10.10.100-10.10.10.200
add name=hs-pool ranges=10.5.50.10-10.5.50.250
/ip dhcp-server
add address-pool=hs-pool interface=wifi-hotspot name=hotspot-dhcp
/ip hotspot
add address-pool=hs-pool disabled=no interface=wifi-hotspot name=\
    tocantins-hotspot profile=hsprof-tocantins
/interface bridge port
add bridge=bridgeLocal comment=defconf interface=ether1
add bridge=bridgeLocal comment=defconf interface=ether2
add bridge=bridgeLocal comment=defconf interface=ether3
add bridge=bridgeLocal comment=defconf interface=ether4
add bridge=bridgeLocal comment=defconf interface=ether5
/interface wifi cap
set discovery-interfaces=bridgeLocal enabled=yes slaves-datapath=*1
/ip address
add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0
/ip dhcp-client
add comment=defconf interface=bridgeLocal
/ip dhcp-server network
add address=10.5.50.0/24 comment="hotspot network" dns-server=\
    10.5.50.1,8.8.8.8 gateway=10.5.50.1
add address=10.10.10.0/24 dns-server=8.8.8.8,1.1.1.1 gateway=10.10.10.1
/ip dns
set servers=8.8.8.8
/ip dns static
add address=10.5.50.1 comment="Captive Detection" name=\
    connectivitycheck.gstatic.com type=A
add address=10.5.50.1 comment="Captive Detection" name=clients3.google.com \
    type=A
add address=10.5.50.1 comment="Captive Detection" name=\
    detectportal.firefox.com type=A
add address=10.5.50.1 comment="Captive Detection Apple" name=\
    captive.apple.com type=A
add address=10.5.50.1 comment="Captive Detection Ubuntu" name=\
    connectivity-check.ubuntu.com type=A
add address=10.5.50.1 comment="Captive Portal" name=google.com type=A
add address=10.5.50.1 comment="Captive Portal" name=www.google.com type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=facebook.com \
    type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=youtube.com \
    type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=\
    google.com.br type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=\
    www.google.com.br type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=uol.com.br \
    type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=\
    www.uol.com.br type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=globo.com \
    type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=\
    www.globo.com type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=terra.com.br \
    type=A
add address=10.5.50.1 comment="Captive Portal" disabled=yes name=ig.com.br \
    type=A
add address=10.5.50.1 comment="Captive Detection" name=www.apple.com ttl=5m \
    type=A
add address=10.5.50.1 comment="Captive Detection" name=\
    www.apple.com/library/test/success.html ttl=5m type=A
add address=10.5.50.1 comment="Captive Detection" name=www.itools.info ttl=5m \
    type=A
add address=10.5.50.1 comment="Captive Detection" name=www.airport.us ttl=5m \
    type=A
add address=10.5.50.1 comment="Captive Detection" name=\
    www.appleiphonecell.com ttl=5m type=A
/ip firewall filter
add action=passthrough chain=unused-hs-chain comment=\
    "place hotspot rules here" disabled=yes
# no interface
# no interface
add action=accept chain=forward comment="Allow DNS" dst-port=53 in-interface=\
    *A protocol=udp
/ip firewall nat
add action=passthrough chain=unused-hs-chain comment=\
    "place hotspot rules here" disabled=yes
add action=masquerade chain=srcnat comment=HOTSPOT-MASQUERADE out-interface=\
    bridgeLocal src-address=10.5.50.0/24
/ip hotspot ip-binding
add comment=AUTO-PAGO mac-address=D6:DE:C4:66:F2:84 server=tocantins-hotspot \
    type=bypassed
/ip hotspot walled-garden
add comment="place hotspot rules here" disabled=yes
add comment="Portal Tailwind" dst-host=cdn.tailwindcss.com server=\
    tocantins-hotspot
add comment="Google Fonts" dst-host=fonts.googleapis.com server=\
    tocantins-hotspot
add comment="Google Fonts Static" dst-host=fonts.gstatic.com server=\
    tocantins-hotspot
add comment="Woovi API" dst-host=api.woovi.com server=tocantins-hotspot
add comment="QR Codes" dst-host=api.qrserver.com server=tocantins-hotspot
add comment="PIX BACEN" dst-host=pix.bcb.gov.br server=tocantins-hotspot
add comment="Tailwind CSS" dst-host=cdn.tailwindcss.com
add comment="Google Fonts API" dst-host=fonts.googleapis.com
add comment="Google Fonts Static" dst-host=fonts.gstatic.com
add comment="Cloudflare CDN" dst-host=cdnjs.cloudflare.com
add comment="Google AJAX" dst-host=ajax.googleapis.com
add comment="Portal Principal" dst-host=tocantinstransportewifi.com.br
add comment="Portal Wildcard" dst-host=*.tocantinstransportewifi.com.br
add comment="Portal WWW" dst-host=www.tocantinstransportewifi.com.br
add comment="Gateway Local" dst-host=10.5.50.1
add comment="Woovi API" dst-host=api.woovi.com
add comment="Woovi Domain" dst-host=woovi.com
add comment="Woovi Subdomains" dst-host=*.woovi.com
add comment="Woovi Dashboard" dst-host=app.woovi.com
add comment="OpenPix API" dst-host=api.openpix.com.br
add comment="OpenPix Domain" dst-host=openpix.com.br
add comment="OpenPix Subdomains" dst-host=*.openpix.com.br
add comment="QR Code Generator" dst-host=api.qrserver.com
add comment="QR Code Domain" dst-host=qrserver.com
add comment="Google Charts QR" dst-host=chart.googleapis.com
add comment="PIX Banco Central" dst-host=pix.bcb.gov.br
add comment="Banco Central BR" dst-host=*.bcb.gov.br
add comment="Banco do Brasil" dst-host=bb.com.br
add comment="BB Mobile" dst-host=*.bb.com.br
add comment="BB App" dst-host=www37.bb.com.br
add comment="BB API" dst-host=api.bb.com.br
add comment="BB Mobile App" dst-host=mobile.bb.com.br
add comment="Caixa CEF" dst-host=caixa.gov.br
add comment="Caixa Mobile" dst-host=*.caixa.gov.br
add comment="Caixa Internet Banking" dst-host=internetbanking.caixa.gov.br
add comment="Itau Unibanco" dst-host=itau.com.br
add comment="Itau Mobile" dst-host=*.itau.com.br
add comment="Itau Banking" dst-host=banco.itau.com.br
add comment="Itau Mobile App" dst-host=mobile.itau.com.br
add comment=Nubank dst-host=nubank.com.br
add comment="Nubank API" dst-host=*.nubank.com.br
add comment="Nubank App" dst-host=prod-s0-webapp-proxy.nubank.com.br
add comment="Banco Inter" dst-host=bancointer.com.br
add comment="Inter Mobile" dst-host=*.bancointer.com.br
add comment="Inter Banking" dst-host=internetbanking.bancointer.com.br
add comment="Santander Brasil" dst-host=santander.com.br
add comment="Santander Mobile" dst-host=*.santander.com.br
add comment="Santander Banking" dst-host=internetbanking.santander.com.br
add comment="BRB Banco" dst-host=brb.com.br
add comment="BRB Mobile" dst-host=*.brb.com.br
add comment=Bradesco dst-host=bradesco.com.br
add comment="Bradesco Mobile" dst-host=*.bradesco.com.br
add comment="Bradesco Mobile App" dst-host=mobile.bradesco.com.br
add comment=PicPay dst-host=picpay.com
add comment="PicPay API" dst-host=*.picpay.com
add comment="Mercado Pago" dst-host=mercadopago.com.br
add comment="Mercado Pago API" dst-host=*.mercadopago.com.br
add comment="C6 Bank" dst-host=c6bank.com.br
add comment="C6 Bank Mobile" dst-host=*.c6bank.com.br
add comment="Login Gov.br" dst-host=sso.acesso.gov.br
add comment="Acesso Gov.br" dst-host=*.acesso.gov.br
add comment="DNS Google" dst-host=8.8.8.8
add comment="DNS Cloudflare" dst-host=1.1.1.1
add comment="DNS OpenDNS" dst-host=208.67.222.222
/ip hotspot walled-garden ip
add action=accept comment="Portal HTTPS" disabled=no dst-address=\
    172.67.210.11 server=tocantins-hotspot
add action=accept comment="Portal HTTPS" disabled=no dst-address=\
    104.21.65.182 server=tocantins-hotspot
add action=accept comment="Portal HTTPS" disabled=no dst-address=\
    206.189.217.189 server=tocantins-hotspot
add action=accept comment="Cloudflare Range" disabled=no dst-address=\
    104.16.0.0/12
add action=accept comment="Google Range" disabled=no dst-address=\
    172.217.0.0/16
add action=accept comment="Google APIs Range" disabled=no dst-address=\
    142.250.0.0/15
add action=accept comment="Servidor Portal" disabled=no dst-address=\
    206.189.217.189
/system clock
set time-zone-name=America/Araguaina
/system logging
add topics=hotspot
add topics=firewall
add topics=dhcp
add topics=interface
/system scheduler
add comment="Monitor de MAC real a cada 30 segundos" disabled=yes interval=\
    30s name=macRealScheduler on-event=liberarUsuariosPagos policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-09-23 start-time=12:23:29
add interval=30s name=registrarMacsScheduler on-event=registrarMacsAutomatico \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-09-24 start-time=01:24:26
add disabled=yes interval=15s name=sistemaCompletoScheduler on-event=\
    sistemaCompleto policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-09-24 start-time=01:24:46
add interval=1m name=liberarPagosScheduler on-event=liberarPagos policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-09-24 start-time=15:10:50
/system script
add dont-require-permissions=no name=sistemaCompleto owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source="\
    \n\
    \n    :log info \"=== SISTEMA COMPLETO INICIADO ===\"\
    \n\
    \n    \
    \n\
    \n    # PARTE 1: REGISTRAR TODOS OS MACS CONECTADOS\
    \n\
    \n    :log info \"= Registrando MACs conectados...\"\
    \n\
    \n    \
    \n\
    \n    :foreach lease in=[/ip dhcp-server lease find dynamic=yes] do={\
    \n\
    \n        :local macAddr [/ip dhcp-server lease get \$lease mac-address]\
    \n\
    \n        :local ipAddr [/ip dhcp-server lease get \$lease address]\
    \n\
    \n        :local hostName \"\"\
    \n\
    \n        \
    \n\
    \n        # Tentar obter hostname se disponvel\
    \n\
    \n        :do {\
    \n\
    \n            :set hostName [/ip dhcp-server lease get \$lease host-name]\
    \n\
    \n        } on-error={ :set hostName \"unknown\" }\
    \n\
    \n        \
    \n\
    \n        # S registrar MACs reais (no virtuais)\
    \n\
    \n        :if ([:pick \$macAddr 0 3] != \"02:\" && [:pick \$macAddr 0 8] !\
    = \"00:00:00\") do={\
    \n\
    \n            :do {\
    \n\
    \n                :local url (\"https://www.tocantinstransportewifi.com.br\
    /api/mikrotik/register-mac\?mac=\" . \$macAddr . \"&ip=\" . \$ipAddr . \"&\
    hostname=\" . \$hostName . \"&token=mikrotik-sync-2024\")\
    \n\
    \n                /tool fetch url=\$url http-method=get\
    \n\
    \n                :log info (\" MAC registrado: \" . \$macAddr . \" -> \" \
    . \$ipAddr)\
    \n\
    \n            } on-error={\
    \n\
    \n                :log error (\"L Erro ao registrar: \" . \$macAddr)\
    \n\
    \n            }\
    \n\
    \n        }\
    \n\
    \n    }\
    \n\
    \n    \
    \n\
    \n    # PARTE 2: CONSULTAR USURIOS PAGOS E LIBERAR\
    \n\
    \n    :log info \"= Consultando usurios pagos...\"\
    \n\
    \n    \
    \n\
    \n    :do {\
    \n\
    \n        :local result [/tool fetch url=\"https://www.tocantinstransporte\
    wifi.com.br/api/mikrotik/check-paid-users\?token=mikrotik-sync-2024\" http\
    -method=get as-value output=user]\
    \n\
    \n        :local data (\$result->\"data\")\
    \n\
    \n        \
    \n\
    \n        # Processar MACs para liberar\
    \n\
    \n        :local startPos 0\
    \n\
    \n        :while ([:find \$data \"\\\"mac_address\\\":\\\"\" \$startPos] >\
    = 0) do={\
    \n\
    \n            :set startPos [:find \$data \"\\\"mac_address\\\":\\\"\" \$s\
    tartPos]\
    \n\
    \n            \
    \n\
    \n            :if (\$startPos >= 0) do={\
    \n\
    \n                :set startPos (\$startPos + 15)\
    \n\
    \n                :local endPos [:find \$data \"\\\"\" \$startPos]\
    \n\
    \n                \
    \n\
    \n                :if (\$endPos >= 0) do={\
    \n\
    \n                    :local macToLiberate [:pick \$data \$startPos \$endP\
    os]\
    \n\
    \n                    \
    \n\
    \n                    :log info (\"= LIBERANDO: \" . \$macToLiberate)\
    \n\
    \n                    \
    \n\
    \n                    # Remover usurio antigo\
    \n\
    \n                    :do {\
    \n\
    \n                        /ip hotspot user remove [find mac-address=\$macT\
    oLiberate]\
    \n\
    \n                    } on-error={}\
    \n\
    \n                    \
    \n\
    \n                    # Remover IP binding antigo\
    \n\
    \n                    :do {\
    \n\
    \n                        /ip hotspot ip-binding remove [find mac-address=\
    \$macToLiberate]\
    \n\
    \n                    } on-error={}\
    \n\
    \n                    \
    \n\
    \n                    # Criar IP binding bypass\
    \n\
    \n                    :do {\
    \n\
    \n                        /ip hotspot ip-binding add mac-address=\$macToLi\
    berate type=bypassed comment=\"PAGO-AUTO\"\
    \n\
    \n                        :log info (\" BYPASS criado: \" . \$macToLiberat\
    e)\
    \n\
    \n                    } on-error={}\
    \n\
    \n                    \
    \n\
    \n                    # Criar usurio como backup\
    \n\
    \n                    :do {\
    \n\
    \n                        :local userName (\"auto_\" . [:pick \$macToLiber\
    ate 12 14] . [:pick \$macToLiberate 15 17])\
    \n\
    \n                        /ip hotspot user add name=\$userName mac-address\
    =\$macToLiberate profile=default comment=\"Auto-liberado\"\
    \n\
    \n                    } on-error={}\
    \n\
    \n                    \
    \n\
    \n                    :set startPos (\$endPos + 1)\
    \n\
    \n                } else={\
    \n\
    \n                    :set startPos (\$startPos + 1)\
    \n\
    \n                }\
    \n\
    \n            }\
    \n\
    \n        }\
    \n\
    \n    } on-error={\
    \n\
    \n        :log error \"L Erro ao consultar usurios pagos\"\
    \n\
    \n    }\
    \n\
    \n    \
    \n\
    \n    :log info \"=== SISTEMA COMPLETO FINALIZADO ===\"\
    \n\
    \n"
add dont-require-permissions=no name=check-paid-users owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    log info \"=== CONSULTANDO USUARIOS PAGOS ===\";\
    \n\
    \n:local url \"https://www.tocantinstransportewifi.com.br/api/mikrotik/che\
    ck-paid-users\?token=mikrotik-sync-2024\";\
    \n\
    \n:log info (\"URL: \" . \$url);\
    \n\
    \n/tool fetch url=\$url http-method=get dst-path=paid-users.json;\
    \n\
    \n:delay 3s;\
    \n\
    \n:if ([/file find name=\"paid-users.json\"] != \"\") do={\
    \n\
    \n  :local content [/file get paid-users.json contents];\
    \n\
    \n  :log info (\"API Response: \" . \$content);\
    \n\
    \n  :if (\$content~\"liberate_macs\") do={\
    \n\
    \n    :log info \">>> ENCONTROU MACS PARA LIBERAR! <<<\";\
    \n\
    \n    :if (\$content~\"D4:01:C3:C6:29:4A\") do={\
    \n\
    \n      :log info \">>> MAC D4:01:C3:C6:29:4A ENCONTRADO PARA LIBERACAO! <\
    <<\";\
    \n\
    \n      /ip firewall address-list add list=paid-users address=D4:01:C3:C6:\
    29:4A comment=\"Auto-liberado via API\";\
    \n\
    \n      :log info \">>> MAC D4:01:C3:C6:29:4A ADICIONADO A LISTA PAID-USER\
    S! <<<\";\
    \n\
    \n    };\
    \n\
    \n  };\
    \n\
    \n} else={\
    \n\
    \n  :log error \"Arquivo paid-users.json nao foi criado!\";\
    \n\
    \n};"
add dont-require-permissions=no name=liberarPagos owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local url \"https://www.tocantinstransportewifi.com.br/api/mikrotik/check-\
    paid-users\?token=mikrotik-sync-2024\";\
    \n    :local result [/tool fetch url=\$url mode=https http-method=get outp\
    ut=user check-certificate=no as-value];\
    \n\
    \n    :if ([:typeof \$result] = \"nothing\") do={\
    \n        :log error \"Fetch sem retorno\";\
    \n        :return;\
    \n    }\
    \n\
    \n    :local status (\$result->\"status\");\
    \n    :if (\$status != \"finished\") do={\
    \n        :log error (\"Fetch falhou: \" . \$status);\
    \n        :return;\
    \n    }\
    \n\
    \n    :local payload (\$result->\"data\");\
    \n    :if ([:len \$payload] = 0) do={\
    \n        :log info \"Nenhum dado recebido\";\
    \n        :return;\
    \n    }\
    \n\
    \n    :local marker \"\\\"mac_address\\\":\\\"\";\
    \n    :local markerLen [:len \$marker];\
    \n    :local pos 0;\
    \n    :local liberados 0;\
    \n\
    \n    :while (true) do={\
    \n        :set pos [:find \$payload \$marker \$pos];\
    \n        :if (\$pos = -1) do={ :break; }\
    \n\
    \n        :local start (\$pos + \$markerLen);\
    \n        :local end [:find \$payload \"\\\"\" \$start];\
    \n        :if (\$end = -1) do={ :break; }\
    \n\
    \n        :local mac [:pick \$payload \$start \$end];\
    \n        :set pos (\$end + 1);\
    \n\
    \n        :if ([:len \$mac] = 17) do={\
    \n            :local existing [/ip hotspot ip-binding find mac-address=\$m\
    ac];\
    \n\
    \n            :if (\$existing = \"\") do={\
    \n                :set liberados (\$liberados + 1);\
    \n                :log info (\"Liberando MAC \" . \$mac);\
    \n\
    \n                /ip hotspot user remove [find mac-address=\$mac];\
    \n                /ip hotspot ip-binding add mac-address=\$mac type=bypass\
    ed comment=\"AUTO-PAGO\" disabled=no server=\"tocantins-hotspot\";\
    \n                /ip hotspot active remove [find mac-address=\$mac];\
    \n            } else={\
    \n                :log info (\"MAC \" . \$mac . \" j\C3\A1 liberado, pulan\
    do\");\
    \n            }\
    \n        } else={\
    \n            :log warning (\"MAC inv\C3\A1lido recebido: \" . \$mac);\
    \n        }\
    \n    }\
    \n\
    \n    :if (\$liberados = 0) do={\
    \n        :log info \"Nenhum MAC para liberar neste ciclo\";\
    \n    } else={\
    \n        :log info (\"Total liberado: \" . \$liberados);\
    \n    }"
add dont-require-permissions=no name=registrarMacsAutomatico owner=admin \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    source=":local token \"mikrotik-sync-2024\"; :foreach lease in=[/ip dhcp-s\
    erver lease find where dynamic=yes] do={ :local mac [/ip dhcp-server lease\
    \_get \$lease mac-address]; :local ip [/ip dhcp-server lease get \$lease a\
    ddress]; :if (([:len \$mac] = 17) && ([:pick \$mac 0 3] != \"02:\") && ([:\
    len \$ip] > 0)) do={ :local url (\"https://www.tocantinstransportewifi.com\
    .br/api/mikrotik/register-mac\?token=\" . \$token . \"&mac=\" . \$mac . \"\
    &ip=\" . \$ip); :do {/tool fetch url=\$url http-method=get mode=https keep\
    -result=no check-certificate=no} on-error={ :log warning (\"Falha ao regis\
    trar MAC \" . \$mac); } } }"
/tool netwatch
# Warning: probe waiting startup-delay=5m; 2m51s remaining
add host=8.8.8.8 timeout=3s type=simple
