# ============================================================
# CONFIGURAÇÃO COMPLETA MIKROTIK - TOCANTINS TRANSPORTE WIFI
# ============================================================
# Execute cada seção separadamente no terminal do MikroTik
# ============================================================

# ====================
# PARTE 1: LOGIN.HTML
# ====================
# Este arquivo redireciona para o portal com MAC e IP do usuário

/file print file=flash/hotspot/login.html

# Copie e cole este conteúdo no login.html via Winbox > Files > flash/hotspot/login.html
# Ou use o comando abaixo (ajuste se necessário):

/file set [find name="flash/hotspot/login.html"] contents="<!DOCTYPE html>\
<html>\
<head>\
    <meta charset=\"UTF-8\">\
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\
    <meta http-equiv=\"pragma\" content=\"no-cache\">\
    <meta http-equiv=\"expires\" content=\"-1\">\
    <title>Conectando...</title>\
    <style>\
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }\
        .container { text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }\
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }\
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }\
        h2 { color: #333; }\
        p { color: #666; }\
    </style>\
</head>\
<body>\
    <div class=\"container\">\
        <h2>Tocantins Transporte WiFi</h2>\
        <div class=\"spinner\"></div>\
        <p>Redirecionando para o portal de pagamento...</p>\
        <p id=\"info\"></p>\
    </div>\
    <script>\
        var mac = '\$(mac)';\
        var ip = '\$(ip)';\
        var link = '\$(link-login)';\
        document.getElementById('info').innerHTML = 'MAC: ' + mac + '<br>IP: ' + ip;\
        setTimeout(function() {\
            window.location.href = 'https://www.tocantinstransportewifi.com.br/?source=mikrotik&captive=true&mac=' + encodeURIComponent(mac) + '&ip=' + encodeURIComponent(ip);\
        }, 1500);\
    </script>\
</body>\
</html>"



# ====================
# PARTE 2: VERIFICAR CONECTIVIDADE INTERNET
# ====================
# IMPORTANTE: O MikroTik precisa ter acesso à internet para consultar a API
# Verifique se há rota padrão e se o bridgeLocal tem IP da Starlink

# Verificar rotas
/ip route print

# Verificar se consegue pingar a internet
/ping 8.8.8.8 count=3
/ping 1.1.1.1 count=3

# Se não pingar, verifique:
# 1. Se a interface WAN (bridgeLocal) está recebendo IP da Starlink
# 2. Se há rota padrão configurada

# Adicionar rota padrão (ajuste o gateway para o IP do roteador Starlink):
# /ip route add dst-address=0.0.0.0/0 gateway=192.168.1.1

# Verificar interfaces
/interface print

# Verificar IPs
/ip address print

# ====================
# PARTE 3: SCRIPT LIBERAR PAGOS
# ====================
# Este script consulta a API e libera/remove usuários

/system script add name=liberarPagos owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local apiUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:local logPrefix "[SYNC-API]"

:log info "$logPrefix Iniciando sincronizacao com API..."

:do {
    /tool fetch url=$apiUrl mode=https http-method=get output=file dst-path=api_response.txt
    :delay 2s
    
    :local fileContent [/file get api_response.txt contents]
    :log info "$logPrefix Resposta recebida da API"
    
    # Processar MACs para liberar
    :local startLib [:find $fileContent "\"liberate_macs\":\\["]
    :if ($startLib != nil) do={
        :local endLib [:find $fileContent "\\]" $startLib]
        :local libSection [:pick $fileContent $startLib $endLib]
        
        # Extrair cada MAC para liberar
        :local pos 0
        :while ([:find $libSection "mac_address" $pos] != nil) do={
            :local macStart [:find $libSection "mac_address\":\"" $pos]
            :if ($macStart != nil) do={
                :set macStart ($macStart + 14)
                :local macEnd [:find $libSection "\"" $macStart]
                :local macAddr [:pick $libSection $macStart $macEnd]
                
                :log info "$logPrefix Liberando MAC: $macAddr"
                
                # Verificar se ja existe no hotspot
                :local existingUser [/ip hotspot user find where name=$macAddr]
                :if ($existingUser = "") do={
                    /ip hotspot user add name=$macAddr mac-address=$macAddr profile=default server=tocantins-hotspot comment="API-Liberado"
                    :log info "$logPrefix Usuario criado: $macAddr"
                }
                
                # Ativar usuario se estiver no host
                :local activeHost [/ip hotspot host find where mac-address=$macAddr]
                :if ($activeHost != "") do={
                    :local hostIp [/ip hotspot host get $activeHost address]
                    /ip hotspot active add user=$macAddr address=$hostIp mac-address=$macAddr server=tocantins-hotspot
                    :log info "$logPrefix Usuario ativado: $macAddr IP: $hostIp"
                }
                
                :set pos $macEnd
            } else={
                :set pos ([:len $libSection])
            }
        }
    }
    
    # Processar MACs para remover
    :local startRem [:find $fileContent "\"remove_macs\":\\["]
    :if ($startRem != nil) do={
        :local endRem [:find $fileContent "\\]" $startRem]
        :local remSection [:pick $fileContent $startRem $endRem]
        
        :local pos 0
        :while ([:find $remSection "mac_address" $pos] != nil) do={
            :local macStart [:find $remSection "mac_address\":\"" $pos]
            :if ($macStart != nil) do={
                :set macStart ($macStart + 14)
                :local macEnd [:find $remSection "\"" $macStart]
                :local macAddr [:pick $remSection $macStart $macEnd]
                
                :log info "$logPrefix Removendo MAC: $macAddr"
                
                # Remover do active
                :local activeUser [/ip hotspot active find where mac-address=$macAddr]
                :if ($activeUser != "") do={
                    /ip hotspot active remove $activeUser
                    :log info "$logPrefix Removido do active: $macAddr"
                }
                
                # Remover do user
                :local hsUser [/ip hotspot user find where mac-address=$macAddr]
                :if ($hsUser != "") do={
                    /ip hotspot user remove $hsUser
                    :log info "$logPrefix Removido do users: $macAddr"
                }
                
                :set pos $macEnd
            } else={
                :set pos ([:len $remSection])
            }
        }
    }
    
    /file remove api_response.txt
    :log info "$logPrefix Sincronizacao concluida com sucesso!"
    
} on-error={
    :log error "$logPrefix ERRO ao conectar com API - Verifique conexao internet"
}
}


# ====================
# PARTE 4: SCHEDULER PARA EXECUTAR A CADA 30 SEGUNDOS
# ====================

/system scheduler add name=syncPagosAPI interval=30s on-event=liberarPagos start-time=startup policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon comment="Sincroniza usuarios pagos com API"

# ====================
# PARTE 5: PERFIL HOTSPOT - PERMITIR ACESSO SEM LOGIN INICIAL
# ====================
# Configurar para MAC authentication (permite capturar MAC antes do login)

/ip hotspot profile set hsprof-tocantins login-by=mac,http-chap,http-pap,cookie mac-auth-mode=mac-as-username-and-password

# ====================
# PARTE 6: WALLED GARDEN ADICIONAL
# ====================
# Garantir que todos os dominios necessarios estao liberados

# Remover duplicados e adicionar novamente
/ip hotspot walled-garden
:foreach i in=[find where comment~"Portal"] do={remove $i}
:foreach i in=[find where comment~"API"] do={remove $i}
:foreach i in=[find where comment~"Captive"] do={remove $i}

# Portal Principal
add dst-host=tocantinstransportewifi.com.br comment="Portal Principal"
add dst-host=*.tocantinstransportewifi.com.br comment="Portal Wildcard"
add dst-host=www.tocantinstransportewifi.com.br comment="Portal WWW"

# Captive Portal Detection - Android
add dst-host=connectivitycheck.gstatic.com comment="Android Captive"
add dst-host=connectivitycheck.android.com comment="Android Captive 2"
add dst-host=clients3.google.com comment="Google Clients"
add dst-host=clients1.google.com comment="Google Clients 1"
add dst-host=www.gstatic.com comment="Google Static"

# Captive Portal Detection - iOS/Apple
add dst-host=captive.apple.com comment="Apple Captive"
add dst-host=www.apple.com comment="Apple WWW"
add dst-host=*.apple.com comment="Apple Wildcard"

# Captive Portal Detection - Windows
add dst-host=www.msftconnecttest.com comment="Microsoft Captive"
add dst-host=msftconnecttest.com comment="Microsoft Captive 2"
add dst-host=*.msftconnecttest.com comment="Microsoft Wildcard"

# Captive Portal Detection - Firefox
add dst-host=detectportal.firefox.com comment="Firefox Captive"

# APIs de Pagamento
add dst-host=api.woovi.com comment="Woovi API"
add dst-host=*.woovi.com comment="Woovi Wildcard"
add dst-host=api.openpix.com.br comment="OpenPix API"
add dst-host=*.openpix.com.br comment="OpenPix Wildcard"

# Bancos PIX
add dst-host=pix.bcb.gov.br comment="PIX BCB"
add dst-host=*.bcb.gov.br comment="BCB Wildcard"
add dst-host=*.bb.com.br comment="Banco Brasil"
add dst-host=*.caixa.gov.br comment="Caixa"
add dst-host=*.itau.com.br comment="Itau"
add dst-host=*.nubank.com.br comment="Nubank"
add dst-host=*.bancointer.com.br comment="Inter"
add dst-host=*.santander.com.br comment="Santander"
add dst-host=*.bradesco.com.br comment="Bradesco"
add dst-host=*.picpay.com comment="PicPay"
add dst-host=*.mercadopago.com.br comment="MercadoPago"
add dst-host=*.c6bank.com.br comment="C6 Bank"
add dst-host=*.pagseguro.com.br comment="PagSeguro"
add dst-host=*.pagbank.com.br comment="PagBank"

# CDNs necessarios
add dst-host=cdn.tailwindcss.com comment="Tailwind"
add dst-host=fonts.googleapis.com comment="Google Fonts"
add dst-host=fonts.gstatic.com comment="Google Fonts Static"
add dst-host=cdnjs.cloudflare.com comment="Cloudflare CDN"

# ====================
# PARTE 7: WALLED GARDEN IP - LIBERAR IPs DIRETOS
# ====================

/ip hotspot walled-garden ip
# Limpar e readicionar
:foreach i in=[find] do={remove $i}

# IP do servidor do portal
add action=accept dst-address=138.68.255.122 comment="Servidor Portal"

# Cloudflare ranges
add action=accept dst-address=104.16.0.0/12 comment="Cloudflare"
add action=accept dst-address=172.64.0.0/13 comment="Cloudflare 2"
add action=accept dst-address=104.21.0.0/16 comment="Cloudflare 3"
add action=accept dst-address=172.67.0.0/16 comment="Cloudflare 4"

# Google DNS (para captive portal detection)
add action=accept dst-address=8.8.8.8 comment="Google DNS"
add action=accept dst-address=8.8.4.4 comment="Google DNS 2"

# Cloudflare DNS
add action=accept dst-address=1.1.1.1 comment="Cloudflare DNS"
add action=accept dst-address=1.0.0.1 comment="Cloudflare DNS 2"


# ====================
# PARTE 8: FIREWALL - GARANTIR ACESSO
# ====================

/ip firewall filter
# Permitir DNS
add chain=forward action=accept protocol=udp dst-port=53 comment="Allow DNS UDP" place-before=0
add chain=forward action=accept protocol=tcp dst-port=53 comment="Allow DNS TCP" place-before=0

# Permitir HTTP/HTTPS para walled garden
add chain=forward action=accept protocol=tcp dst-port=80,443 src-address=10.5.50.0/24 comment="Allow HTTP/HTTPS Hotspot" place-before=0

/ip firewall nat
# Garantir masquerade
add chain=srcnat action=masquerade src-address=10.5.50.0/24 out-interface=bridgeLocal comment="Hotspot NAT"

# ====================
# PARTE 9: TESTAR CONECTIVIDADE
# ====================
# Execute estes comandos para verificar se tudo esta funcionando

# Testar ping
/ping 8.8.8.8 count=3

# Testar DNS
/ping www.google.com count=3

# Testar API (deve retornar arquivo)
/tool fetch url="https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024" mode=https output=file dst-path=teste_api.txt
/file print where name=teste_api.txt
/file get teste_api.txt contents

# ====================
# PARTE 10: VERIFICAR CONFIGURACAO FINAL
# ====================

# Ver perfil hotspot
/ip hotspot profile print detail where name=hsprof-tocantins

# Ver hotspot
/ip hotspot print detail

# Ver scripts
/system script print

# Ver schedulers
/system scheduler print

# Ver walled garden
/ip hotspot walled-garden print
/ip hotspot walled-garden ip print

# Ver usuarios ativos
/ip hotspot active print

# Ver hosts conectados
/ip hotspot host print

# ====================
# PARTE 11: COMANDOS DE DEBUG
# ====================

# Ativar log detalhado do hotspot
/system logging add topics=hotspot action=memory

# Ver logs
/log print where topics~"hotspot"

# Executar script manualmente para testar
/system script run liberarPagos

# ====================
# TROUBLESHOOTING
# ====================

# PROBLEMA: "Network unreachable" ao acessar API
# SOLUCAO: Verificar se MikroTik tem rota para internet
# 1. Verificar IP na interface WAN: /ip address print
# 2. Verificar rota padrao: /ip route print
# 3. Se nao tiver rota, adicionar: /ip route add dst-address=0.0.0.0/0 gateway=<IP_GATEWAY_STARLINK>

# PROBLEMA: Captive portal nao aparece automaticamente
# SOLUCAO: O Android/iOS modernos usam HTTPS para detectar captive portal
# O MikroTik so consegue interceptar HTTP (porta 80)
# Por isso o QR Code e importante como alternativa

# PROBLEMA: Usuario nao consegue acessar portal de pagamento
# SOLUCAO: Verificar walled garden
# /ip hotspot walled-garden print
# Deve ter tocantinstransportewifi.com.br liberado

# PROBLEMA: Script nao executa
# SOLUCAO: Verificar permissoes do script
# /system script print detail
# Deve ter policy com read,write,test,policy

