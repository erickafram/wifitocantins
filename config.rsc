# 2025-11-14 04:52:07 by RouterOS 7.20rc3
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
/ip firewall layer7-protocol
add name=https-protocol regexp="^..(\\x16\\x03|\\x16\\xfe)"
/ip hotspot profile
add dns-name=login.tocantinswifi.local hotspot-address=10.5.50.1 \
    html-directory=flash/hotspot http-cookie-lifetime=1d login-by=\
    cookie,http-chap,http-pap name=hsprof-tocantins
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
add address=10.5.50.0/24 comment="hotspot network" dns-server=1.1.1.1,8.8.8.8 \
    gateway=10.5.50.1
add address=10.10.10.0/24 dns-server=8.8.8.8,1.1.1.1 gateway=10.10.10.1
/ip dns
set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8
/ip dns static
add address=206.189.217.189 comment="Portal Principal" name=\
    tocantinstransportewifi.com.br type=A
add address=206.189.217.189 comment="Portal WWW" name=\
    www.tocantinstransportewifi.com.br type=A
add address=206.189.217.189 comment="Portal Curto" name=portal.wifi type=A
add address=206.189.217.189 comment="Portal Curto" name=conectar.wifi type=A
/ip firewall filter
add action=passthrough chain=unused-hs-chain comment=\
    "place hotspot rules here" disabled=yes dst-address=138.68.255.122
# no interface
# no interface
add action=accept chain=forward comment="Allow DNS" dst-port=53 in-interface=\
    *A protocol=udp
/ip firewall nat
add action=passthrough chain=unused-hs-chain comment=\
    "place hotspot rules here" disabled=yes
add action=masquerade chain=srcnat comment=NAT-GERAL out-interface=\
    bridgeLocal
add action=masquerade chain=srcnat comment=HOTSPOT-MASQUERADE out-interface=\
    bridgeLocal src-address=10.5.50.0/24
add action=masquerade chain=srcnat comment=NAT-MIKROTIK out-interface=\
    bridgeLocal src-address=10.75.28.0/23
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
add action=accept comment="Servidor Portal" disabled=no dst-address=\
    206.189.217.189
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
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source="#\
    \_========================================================================\
    ====\
    \n# Script MikroTik - Sincroniza\C3\A7\C3\A3o de MACs Pagos - VERS\C3\83O \
    FINAL\
    \n# Data: 2025-09-30\
    \n# TESTADO E FUNCIONANDO!\
    \n# ======================================================================\
    ======\
    \n\
    \n:local url \"https://www.tocantinstransportewifi.com.br/api/mikrotik/che\
    ck-paid-users\?token=mikrotik-sync-2024\"\
    \n:local bypassComment \"PAGO-AUTO\"\
    \n\
    \n:log info \"=== SYNC MACS PAGOS INICIADA ===\"\
    \n\
    \n# Buscar API\
    \n:local result [/tool fetch url=\$url mode=https http-method=get output=u\
    ser check-certificate=no as-value]\
    \n\
    \n:if ([:typeof \$result] = \"nothing\") do={\
    \n    :log error \"Fetch falhou\"\
    \n    :return\
    \n}\
    \n\
    \n:if ((\$result->\"status\") != \"finished\") do={\
    \n    :log error \"Fetch nao completou\"\
    \n    :return\
    \n}\
    \n\
    \n:local payload (\$result->\"data\")\
    \n:if ([:len \$payload] = 0) do={\
    \n    :log warning \"Payload vazio\"\
    \n    :return\
    \n}\
    \n\
    \n:log info (\"Dados recebidos: \" . [:len \$payload] . \" bytes\")\
    \n\
    \n# ===========================================================\
    \n# LIBERAR MACS PAGOS\
    \n# ===========================================================\
    \n:local liberados 0\
    \n:local jaExiste 0\
    \n:local macsPagos [:toarray \"\"]\
    \n\
    \n:local pos 0\
    \n:local maxLoop 100\
    \n:local loopCount 0\
    \n\
    \n:while (\$loopCount < \$maxLoop) do={\
    \n    :set loopCount (\$loopCount + 1)\
    \n    \
    \n    # Procurar \"mac_address\":\"\
    \n    :local macKey \"\\\"mac_address\\\":\\\"\"\
    \n    :local macPos [:find \$payload \$macKey \$pos]\
    \n    \
    \n    :if (\$macPos = -1) do={\
    \n        :set loopCount \$maxLoop\
    \n    } else={\
    \n        # Pular para depois de \"mac_address\":\"\
    \n        :local macStart (\$macPos + [:len \$macKey])\
    \n        \
    \n        # Procurar o \" de fechamento\
    \n        :local macEnd [:find \$payload \"\\\"\" \$macStart]\
    \n        \
    \n        :if (\$macEnd != -1) do={\
    \n            # Extrair MAC\
    \n            :local mac [:pick \$payload \$macStart \$macEnd]\
    \n            :set pos (\$macEnd + 1)\
    \n            \
    \n            # Validar tamanho\
    \n            :if ([:len \$mac] = 17) do={\
    \n                :set macsPagos (\$macsPagos, \$mac)\
    \n                \
    \n                # Verificar se j\C3\A1 existe\
    \n                :local existente [/ip hotspot ip-binding find mac-addres\
    s=\$mac]\
    \n                \
    \n                :if ([:len \$existente] = 0) do={\
    \n                    :log info (\"[+] Liberando: \" . \$mac)\
    \n                    \
    \n                    # Limpar conflitos\
    \n                    :do {/ip hotspot user remove [find mac-address=\$mac\
    ]} on-error={}\
    \n                    :do {/ip hotspot active remove [find mac-address=\$m\
    ac]} on-error={}\
    \n                    \
    \n                    # Adicionar binding\
    \n                    :do {\
    \n                        /ip hotspot ip-binding add \\\
    \n                            mac-address=\$mac \\\
    \n                            type=bypassed \\\
    \n                            comment=\$bypassComment \\\
    \n                            disabled=no\
    \n                        \
    \n                        :set liberados (\$liberados + 1)\
    \n                        :log info (\"    [OK] MAC liberado!\")\
    \n                    } on-error={\
    \n                        :log error (\"    [ERRO] Falha ao liberar\")\
    \n                    }\
    \n                } else={\
    \n                    :set jaExiste (\$jaExiste + 1)\
    \n                    :log info (\"[=] Ja existe: \" . \$mac)\
    \n                }\
    \n            } else={\
    \n                :log warning (\"[!] MAC invalido (len=\" . [:len \$mac] \
    . \"): \" . \$mac)\
    \n            }\
    \n        } else={\
    \n            :set loopCount \$maxLoop\
    \n        }\
    \n    }\
    \n}\
    \n\
    \n:log info (\"Processados: \" . [:len \$macsPagos])\
    \n:log info (\"Novos liberados: \" . \$liberados)\
    \n:log info (\"Ja existentes: \" . \$jaExiste)\
    \n\
    \n# ===========================================================\
    \n# REMOVER MACS EXPIRADOS\
    \n# ===========================================================\
    \n:log info \"--- Removendo expirados ---\"\
    \n\
    \n:local removidos 0\
    \n:local pos2 0\
    \n:local loopCount2 0\
    \n\
    \n:while (\$loopCount2 < \$maxLoop) do={\
    \n    :set loopCount2 (\$loopCount2 + 1)\
    \n    \
    \n    # Procurar em \"remove_macs\"\
    \n    :local removeKey \"\\\"remove_macs\\\":\"\
    \n    :local removePos [:find \$payload \$removeKey]\
    \n    \
    \n    :if (\$removePos >= 0) do={\
    \n        # Procurar mac_address ap\C3\B3s remove_macs\
    \n        :local macKey \"\\\"mac_address\\\":\\\"\"\
    \n        :local macPos [:find \$payload \$macKey (\$removePos + [:len \$r\
    emoveKey] + \$pos2)]\
    \n        \
    \n        :if (\$macPos = -1) do={\
    \n            :set loopCount2 \$maxLoop\
    \n        } else={\
    \n            :local macStart (\$macPos + [:len \$macKey])\
    \n            :local macEnd [:find \$payload \"\\\"\" \$macStart]\
    \n            \
    \n            :if (\$macEnd != -1) do={\
    \n                :local mac [:pick \$payload \$macStart \$macEnd]\
    \n                :set pos2 (\$macEnd + 1)\
    \n                \
    \n                :if ([:len \$mac] = 17) do={\
    \n                    :log warning (\"[-] Removendo: \" . \$mac)\
    \n                    \
    \n                    :do {\
    \n                        /ip hotspot ip-binding remove [find mac-address=\
    \$mac]\
    \n                        :set removidos (\$removidos + 1)\
    \n                    } on-error={}\
    \n                    \
    \n                    :do {/ip hotspot user remove [find mac-address=\$mac\
    ]} on-error={}\
    \n                    :do {/ip hotspot active remove [find mac-address=\$m\
    ac]} on-error={}\
    \n                }\
    \n            } else={\
    \n                :set loopCount2 \$maxLoop\
    \n            }\
    \n        }\
    \n    } else={\
    \n        :set loopCount2 \$maxLoop\
    \n    }\
    \n}\
    \n\
    \n:log info (\"Expirados removidos: \" . \$removidos)\
    \n\
    \n# ===========================================================\
    \n# LIMPAR ORFAOS (que n\C3\A3o est\C3\A3o na lista de pagos)\
    \n# ===========================================================\
    \n:log info \"--- Limpando orfaos ---\"\
    \n\
    \n:local bindings [/ip hotspot ip-binding find where comment=\$bypassComme\
    nt]\
    \n:local orfaos 0\
    \n\
    \n:foreach bindingId in=\$bindings do={\
    \n    :local macAtual [/ip hotspot ip-binding get \$bindingId mac-address]\
    \n    :local encontrado false\
    \n    \
    \n    # Ver se est\C3\A1 na lista de pagos\
    \n    :foreach macPago in=\$macsPagos do={\
    \n        :if (\$macPago = \$macAtual) do={\
    \n            :set encontrado true\
    \n        }\
    \n    }\
    \n    \
    \n    :if (!\$encontrado) do={\
    \n        :log warning (\"[ORFAO] Removendo: \" . \$macAtual)\
    \n        \
    \n        :do {\
    \n            /ip hotspot ip-binding remove \$bindingId\
    \n            :set orfaos (\$orfaos + 1)\
    \n        } on-error={}\
    \n        \
    \n        :do {/ip hotspot user remove [find mac-address=\$macAtual]} on-e\
    rror={}\
    \n        :do {/ip hotspot active remove [find mac-address=\$macAtual]} on\
    -error={}\
    \n    }\
    \n}\
    \n\
    \n:log info (\"Orfaos removidos: \" . \$orfaos)\
    \n\
    \n# ===========================================================\
    \n# RESUMO\
    \n# ===========================================================\
    \n:log info \"===================================\"\
    \n:log info (\"RESUMO - Pagos: \" . [:len \$macsPagos] . \" | Liberados: \
    \" . \$liberados . \" | Removidos: \" . \$removidos . \" | Orfaos: \" . \$\
    orfaos)\
    \n:log info \"===================================\" "
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
add host=8.8.8.8 timeout=3s type=simple
