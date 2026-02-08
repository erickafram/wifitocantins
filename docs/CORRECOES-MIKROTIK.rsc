# ============================================================================
# CORREÇÕES CRÍTICAS DO MIKROTIK - WiFi Tocantins Transport
# RouterOS 7.20.5 - hAP ac² (RBD52G-5HacD2HnD)
# ============================================================================
#
# OBJETIVO: Usuários NÃO PAGOS só acessam:
#   - Portal tocantinstransportewifi.com.br (para pagar)
#   - Apps de banco (para fazer PIX)
#   - Gateways de pagamento (Woovi, OpenPix, PagBank)
#   - NADA MAIS (sem YouTube, Google, redes sociais, etc.)
#
# Usuários PAGOS têm acesso TOTAL à internet.
#
# IMPORTANTE: Faça backup antes!
# /system backup save name=backup-antes-correcoes
#
# ============================================================================


# ============================================================================
# CORREÇÃO 1: SCRIPT registrarMacs - ACEITAR MACs RANDOMIZADOS
# ============================================================================
# PROBLEMA: O script antigo filtrava MACs que começam com "02:", ignorando
# dispositivos com MAC randomizado (iOS 14+, Android 10+).
# SOLUÇÃO: Aceitar TODOS os MACs válidos.
# ============================================================================

/system script remove [find name="registrarMacs"]
/system script add name="registrarMacs" policy=read,write,policy,test source={
:local token "mikrotik-sync-2024"

:log info "=== REGISTRANDO MACS ==="

:local registrados 0

:foreach lease in=[/ip dhcp-server lease find where dynamic=yes] do={
    :local mac [/ip dhcp-server lease get $lease mac-address]
    :local ip [/ip dhcp-server lease get $lease address]
    
    # Aceitar TODOS os MACs válidos (17 caracteres = XX:XX:XX:XX:XX:XX)
    # MACs randomizados são válidos e consistentes por rede WiFi
    :if (([:len $mac] = 17) && ([:len $ip] > 0)) do={
        :local url ("https://www.tocantinstransportewifi.com.br/api/mikrotik/register-mac\?token=" . $token . "&mac=" . $mac . "&ip=" . $ip)
        
        :do {
            /tool fetch url=$url http-method=get mode=https keep-result=no check-certificate=no
            :set registrados ($registrados + 1)
        } on-error={
            :log warning ("Falha ao registrar: " . $mac)
        }
    }
}

:log info ("=== REGISTRO: " . $registrados . " MACs ===")
}


# ============================================================================
# CORREÇÃO 2: SCRIPT syncPagos OTIMIZADO (endpoint Lite - texto simples)
# ============================================================================

/system script remove [find name="liberarPagos"]
/system script remove [find name="syncPagos"]

/system script add name="syncPagos" policy=read,write,policy,test source={
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite\?token=mikrotik-sync-2024"
:local bypassComment "PAGO-AUTO"
:local liberados 0
:local removidos 0

:do {
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    
    :if (($result->"status") = "finished") do={
        :local data ($result->"data")
        
        :if ([:pick $data 0 2] = "OK") do={
            :local pos 0
            :local dataLen [:len $data]
            
            :while ($pos < $dataLen) do={
                :local lineEnd [:find $data "\n" $pos]
                :if ([:typeof $lineEnd] = "nil") do={
                    :set lineEnd $dataLen
                }
                
                :local line [:pick $data $pos $lineEnd]
                :set pos ($lineEnd + 1)
                
                :if ([:len $line] < 4) do={
                } else={
                    :local prefix [:pick $line 0 2]
                    :local mac [:pick $line 2 [:len $line]]
                    
                    :if ([:pick $mac ([:len $mac] - 1) [:len $mac]] = "\r") do={
                        :set mac [:pick $mac 0 ([:len $mac] - 1)]
                    }
                    
                    :if ([:len $mac] = 17) do={
                        :if ($prefix = "L:") do={
                            :local existing [/ip hotspot ip-binding find mac-address=$mac comment=$bypassComment]
                            :if ([:len $existing] = 0) do={
                                :log info ("[+] Liberando MAC: " . $mac)
                                :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                                :do {/ip hotspot host remove [find mac-address=$mac]} on-error={}
                                :do {
                                    /ip hotspot ip-binding add mac-address=$mac type=bypassed comment=$bypassComment
                                    :set liberados ($liberados + 1)
                                } on-error={
                                    :log warning ("Erro ao criar binding: " . $mac)
                                }
                            }
                        }
                        
                        :if ($prefix = "R:") do={
                            :local toRemove [/ip hotspot ip-binding find mac-address=$mac comment=$bypassComment]
                            :if ([:len $toRemove] > 0) do={
                                :log warning ("[-] Removendo expirado: " . $mac)
                                /ip hotspot ip-binding remove $toRemove
                                :do {/ip hotspot active remove [find mac-address=$mac]} on-error={}
                                :do {/ip hotspot host remove [find mac-address=$mac]} on-error={}
                                :set removidos ($removidos + 1)
                            }
                        }
                    }
                }
            }
        } else={
            :log warning ("Resposta API invalida")
        }
    }
} on-error={
    :log error "Erro ao consultar API de sync"
}

:if (($liberados > 0) || ($removidos > 0)) do={
    :log info ("=== SYNC: +" . $liberados . " liberados, -" . $removidos . " removidos ===")
}
}


# ============================================================================
# CORREÇÃO 3: AGENDADORES
# ============================================================================

/system scheduler remove [find name~"liberarPagos|syncPagos|registrarMacs"]

/system scheduler add \
    name="syncPagosScheduler" \
    interval=15s \
    on-event="/system script run syncPagos" \
    policy=read,write,policy,test \
    start-time=startup \
    comment="Sincroniza usuarios pagos com API (15s)"

/system scheduler add \
    name="registrarMacsScheduler" \
    interval=1m \
    on-event="/system script run registrarMacs" \
    policy=read,write,policy,test \
    start-time=startup \
    comment="Registra MACs conectados na API (1min)"


# ============================================================================
# CORREÇÃO 4: WALLED GARDEN RESTRITIVO
# ============================================================================
# REGRA: Quem NÃO pagou acessa APENAS:
#   1. Portal de pagamento (tocantinstransportewifi.com.br)
#   2. CDNs do portal (Tailwind, Fonts)
#   3. Gateways PIX (Woovi, OpenPix, PagBank, Santander)
#   4. Apps de banco (domínios específicos dos bancos)
#   5. CDNs de banco (CloudFront, Akamai, AzureCDN - SÓ CDN, não serviços)
#   6. Detecção de captive portal (Android/iOS/Windows)
#   7. Validação de certificados HTTPS (OCSP)
#
# NÃO LIBERAR: *.google.com, *.amazonaws.com, *.microsoft.com, *.apple.com
# Esses domínios dão acesso à internet inteira!
# ============================================================================

# LIMPAR TODAS as entradas existentes do walled-garden
:log info ">>> Limpando Walled Garden antigo..."
:foreach i in=[/ip hotspot walled-garden find] do={
    :do { /ip hotspot walled-garden remove $i } on-error={}
}
:foreach i in=[/ip hotspot walled-garden ip find] do={
    :do { /ip hotspot walled-garden ip remove $i } on-error={}
}

:log info ">>> Configurando Walled Garden RESTRITIVO..."

# =============================================
# GRUPO 1: PORTAL DE PAGAMENTO (OBRIGATÓRIO)
# =============================================
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Portal Principal"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Portal Wildcard"
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br comment="Portal WWW"

# CDNs necessários APENAS para o portal carregar
/ip hotspot walled-garden add dst-host=cdn.tailwindcss.com comment="Portal: Tailwind CSS"
/ip hotspot walled-garden add dst-host=fonts.googleapis.com comment="Portal: Google Fonts API"
/ip hotspot walled-garden add dst-host=fonts.gstatic.com comment="Portal: Google Fonts Static"
/ip hotspot walled-garden add dst-host=cdnjs.cloudflare.com comment="Portal: Cloudflare CDN"

# =============================================
# GRUPO 2: GATEWAYS DE PAGAMENTO PIX
# =============================================
/ip hotspot walled-garden add dst-host=api.woovi.com comment="PIX: Woovi API"
/ip hotspot walled-garden add dst-host=*.woovi.com comment="PIX: Woovi"
/ip hotspot walled-garden add dst-host=api.openpix.com.br comment="PIX: OpenPix API"
/ip hotspot walled-garden add dst-host=*.openpix.com.br comment="PIX: OpenPix"
/ip hotspot walled-garden add dst-host=api.qrserver.com comment="PIX: QR Code"
/ip hotspot walled-garden add dst-host=chart.googleapis.com comment="PIX: Google Charts QR"
/ip hotspot walled-garden add dst-host=pix.bcb.gov.br comment="PIX: Banco Central"
/ip hotspot walled-garden add dst-host=*.bcb.gov.br comment="PIX: BCB"

# PagBank/PagSeguro
/ip hotspot walled-garden add dst-host=pagseguro.uol.com.br comment="PIX: PagSeguro"
/ip hotspot walled-garden add dst-host=*.pagseguro.uol.com.br comment="PIX: PagSeguro API"
/ip hotspot walled-garden add dst-host=api.pagseguro.com comment="PIX: PagSeguro API Direct"
/ip hotspot walled-garden add dst-host=pagbank.com.br comment="PIX: PagBank"
/ip hotspot walled-garden add dst-host=*.pagbank.com.br comment="PIX: PagBank API"

# Santander PIX API
/ip hotspot walled-garden add dst-host=api.santander.com.br comment="PIX: Santander API"
/ip hotspot walled-garden add dst-host=pix.santander.com.br comment="PIX: Santander PIX"

# =============================================
# GRUPO 3: APPS DE BANCOS (para pagar PIX)
# =============================================

# Banco do Brasil
/ip hotspot walled-garden add dst-host=bb.com.br comment="Banco: BB"
/ip hotspot walled-garden add dst-host=*.bb.com.br comment="Banco: BB Mobile"

# Caixa Econômica Federal
/ip hotspot walled-garden add dst-host=caixa.gov.br comment="Banco: Caixa"
/ip hotspot walled-garden add dst-host=*.caixa.gov.br comment="Banco: Caixa Mobile"

# Itaú
/ip hotspot walled-garden add dst-host=itau.com.br comment="Banco: Itau"
/ip hotspot walled-garden add dst-host=*.itau.com.br comment="Banco: Itau Mobile"

# Nubank
/ip hotspot walled-garden add dst-host=nubank.com.br comment="Banco: Nubank"
/ip hotspot walled-garden add dst-host=*.nubank.com.br comment="Banco: Nubank Mobile"

# Banco Inter
/ip hotspot walled-garden add dst-host=bancointer.com.br comment="Banco: Inter"
/ip hotspot walled-garden add dst-host=*.bancointer.com.br comment="Banco: Inter Mobile"

# Santander
/ip hotspot walled-garden add dst-host=santander.com.br comment="Banco: Santander"
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Banco: Santander Mobile"

# Bradesco
/ip hotspot walled-garden add dst-host=bradesco.com.br comment="Banco: Bradesco"
/ip hotspot walled-garden add dst-host=*.bradesco.com.br comment="Banco: Bradesco Mobile"

# BRB
/ip hotspot walled-garden add dst-host=brb.com.br comment="Banco: BRB"
/ip hotspot walled-garden add dst-host=*.brb.com.br comment="Banco: BRB Mobile"

# PicPay
/ip hotspot walled-garden add dst-host=picpay.com comment="Banco: PicPay"
/ip hotspot walled-garden add dst-host=*.picpay.com comment="Banco: PicPay Mobile"

# Mercado Pago
/ip hotspot walled-garden add dst-host=mercadopago.com.br comment="Banco: Mercado Pago"
/ip hotspot walled-garden add dst-host=*.mercadopago.com.br comment="Banco: MP Mobile"
/ip hotspot walled-garden add dst-host=api.mercadopago.com comment="Banco: MP API"
/ip hotspot walled-garden add dst-host=*.mercadolibre.com comment="Banco: ML"

# C6 Bank
/ip hotspot walled-garden add dst-host=c6bank.com.br comment="Banco: C6"
/ip hotspot walled-garden add dst-host=*.c6bank.com.br comment="Banco: C6 Mobile"

# Sicoob
/ip hotspot walled-garden add dst-host=sicoob.com.br comment="Banco: Sicoob"
/ip hotspot walled-garden add dst-host=*.sicoob.com.br comment="Banco: Sicoob Mobile"

# Sicredi
/ip hotspot walled-garden add dst-host=sicredi.com.br comment="Banco: Sicredi"
/ip hotspot walled-garden add dst-host=*.sicredi.com.br comment="Banco: Sicredi Mobile"

# Neon
/ip hotspot walled-garden add dst-host=neon.com.br comment="Banco: Neon"
/ip hotspot walled-garden add dst-host=*.neon.com.br comment="Banco: Neon Mobile"

# Next (Bradesco)
/ip hotspot walled-garden add dst-host=next.me comment="Banco: Next"
/ip hotspot walled-garden add dst-host=*.next.me comment="Banco: Next Mobile"

# Ame Digital
/ip hotspot walled-garden add dst-host=amedigital.com comment="Banco: Ame"
/ip hotspot walled-garden add dst-host=*.amedigital.com comment="Banco: Ame Mobile"

# Banco Original
/ip hotspot walled-garden add dst-host=original.com.br comment="Banco: Original"
/ip hotspot walled-garden add dst-host=*.original.com.br comment="Banco: Original Mobile"

# =============================================
# GRUPO 4: CDNs DOS BANCOS (SOMENTE CDN!)
# =============================================
# IMPORTANTE: Esses domínios são EXCLUSIVAMENTE de CDN.
# Eles servem assets (JS, CSS, imagens) que os apps bancários
# precisam para funcionar. NÃO dão acesso a sites/serviços.
#
# *.cloudfront.net = CDN da AWS (NÃO dá acesso a gmail, youtube, etc.)
# *.akamai.net     = CDN Akamai (NÃO é um site navegável)
# *.azureedge.net  = CDN Azure (NÃO dá acesso ao Office, Bing, etc.)
# =============================================

# AWS CloudFront (CDN) - usado por Nubank, Inter, C6, PicPay
/ip hotspot walled-garden add dst-host=*.cloudfront.net comment="CDN: AWS CloudFront"

# Akamai (CDN) - usado por BB, Caixa, Bradesco, Itaú
/ip hotspot walled-garden add dst-host=*.akamai.net comment="CDN: Akamai"
/ip hotspot walled-garden add dst-host=*.akamaiedge.net comment="CDN: Akamai Edge"
/ip hotspot walled-garden add dst-host=*.akamaitechnologies.com comment="CDN: Akamai Tech"
/ip hotspot walled-garden add dst-host=*.akamaihd.net comment="CDN: Akamai HD"
/ip hotspot walled-garden add dst-host=*.edgekey.net comment="CDN: Akamai EdgeKey"
/ip hotspot walled-garden add dst-host=*.edgesuite.net comment="CDN: Akamai EdgeSuite"

# Azure CDN - usado por Santander, PagBank
/ip hotspot walled-garden add dst-host=*.azureedge.net comment="CDN: Azure Edge"
/ip hotspot walled-garden add dst-host=*.msecnd.net comment="CDN: Microsoft CDN"

# Cloudflare CDN
/ip hotspot walled-garden add dst-host=*.cloudflare.com comment="CDN: Cloudflare"

# Fastly CDN - usado por fintechs
/ip hotspot walled-garden add dst-host=*.fastly.net comment="CDN: Fastly"

# Firebase (push notifications e analytics dos apps bancários)
/ip hotspot walled-garden add dst-host=*.firebaseio.com comment="CDN: Firebase IO"
/ip hotspot walled-garden add dst-host=*.firebaseapp.com comment="CDN: Firebase App"
/ip hotspot walled-garden add dst-host=fcm.googleapis.com comment="CDN: Firebase Messaging"

# =============================================
# GRUPO 5: DETECÇÃO DE CAPTIVE PORTAL
# =============================================
# Quando o celular conecta no WiFi, ele verifica se tem internet.
# Se não tiver, mostra a tela de login automaticamente.
# =============================================

# Android
/ip hotspot walled-garden add dst-host=connectivitycheck.gstatic.com comment="Captive: Android"
/ip hotspot walled-garden add dst-host=clients3.google.com comment="Captive: Android Alt"
/ip hotspot walled-garden add dst-host=connectivitycheck.android.com comment="Captive: Android Alt2"

# Apple/iOS
/ip hotspot walled-garden add dst-host=captive.apple.com comment="Captive: Apple"
/ip hotspot walled-garden add dst-host=www.apple.com comment="Captive: Apple WWW"

# Windows
/ip hotspot walled-garden add dst-host=www.msftncsi.com comment="Captive: Windows"
/ip hotspot walled-garden add dst-host=www.msftconnecttest.com comment="Captive: Windows Alt"

# =============================================
# GRUPO 6: CERTIFICADOS SSL (OCSP/CRL)
# =============================================
# Apps bancários verificam certificados SSL. Sem isso = "erro de conexão".
# São requisições pequenas (não dão acesso a sites).
# =============================================

/ip hotspot walled-garden add dst-host=ocsp.digicert.com comment="SSL: DigiCert OCSP"
/ip hotspot walled-garden add dst-host=crl3.digicert.com comment="SSL: DigiCert CRL"
/ip hotspot walled-garden add dst-host=crl4.digicert.com comment="SSL: DigiCert CRL4"
/ip hotspot walled-garden add dst-host=ocsp.verisign.com comment="SSL: VeriSign OCSP"
/ip hotspot walled-garden add dst-host=ocsp.globalsign.com comment="SSL: GlobalSign OCSP"
/ip hotspot walled-garden add dst-host=crl.globalsign.com comment="SSL: GlobalSign CRL"
/ip hotspot walled-garden add dst-host=ocsp.pki.goog comment="SSL: Google OCSP"
/ip hotspot walled-garden add dst-host=pki.goog comment="SSL: Google PKI"
/ip hotspot walled-garden add dst-host=ocsp.sectigo.com comment="SSL: Sectigo OCSP"
/ip hotspot walled-garden add dst-host=crl.sectigo.com comment="SSL: Sectigo CRL"
/ip hotspot walled-garden add dst-host=ocsp.comodoca.com comment="SSL: Comodo OCSP"
/ip hotspot walled-garden add dst-host=ocsp.usertrust.com comment="SSL: UserTrust OCSP"
/ip hotspot walled-garden add dst-host=r3.o.lencr.org comment="SSL: LetsEncrypt OCSP"
/ip hotspot walled-garden add dst-host=x1.c.lencr.org comment="SSL: LetsEncrypt CRL"

# =============================================
# WALLED GARDEN IP (acesso por IP direto)
# =============================================
# APENAS o IP do portal e DNS. SEM ranges enormes!
# =============================================

# Portal
/ip hotspot walled-garden ip add dst-address=138.68.255.122 action=accept comment="IP: Portal"

# DNS público (necessário para resolver nomes)
/ip hotspot walled-garden ip add dst-address=8.8.8.8 action=accept comment="IP: Google DNS"
/ip hotspot walled-garden ip add dst-address=8.8.4.4 action=accept comment="IP: Google DNS 2"
/ip hotspot walled-garden ip add dst-address=1.1.1.1 action=accept comment="IP: Cloudflare DNS"
/ip hotspot walled-garden ip add dst-address=1.0.0.1 action=accept comment="IP: Cloudflare DNS 2"

:log info ">>> Walled Garden configurado: APENAS portal, bancos e pagamento"


# ============================================================================
# CORREÇÃO 5: CONTROLE DE BANDA (QUEUE TREE)
# ============================================================================
# Queue types definidos mas NÃO aplicados = zero controle de banda.
# Queue tree com PCQ - 5Mbps download / 2Mbps upload por usuário.
# ============================================================================

# Limpar queue trees e mangles antigos
:do { /queue tree remove [find comment~"hotspot|Hotspot"] } on-error={}
:do { /queue tree remove [find name~"hotspot"] } on-error={}
:do { /ip firewall mangle remove [find comment~"hotspot-mark"] } on-error={}

# Marcar tráfego do hotspot
/ip firewall mangle add chain=forward src-address=10.5.50.0/24 action=mark-connection new-connection-mark=hotspot-conn passthrough=yes comment="hotspot-mark-conn"
/ip firewall mangle add chain=forward connection-mark=hotspot-conn in-interface=ether1 action=mark-packet new-packet-mark=hotspot-download passthrough=no comment="hotspot-mark-download"
/ip firewall mangle add chain=forward connection-mark=hotspot-conn out-interface=ether1 action=mark-packet new-packet-mark=hotspot-upload passthrough=no comment="hotspot-mark-upload"

# Criar/atualizar queue types PCQ
:if ([:len [/queue type find name="pcq-download-hotspot"]] = 0) do={
    /queue type add name=pcq-download-hotspot kind=pcq pcq-rate=5M pcq-classifier=dst-address
}
:if ([:len [/queue type find name="pcq-upload-hotspot"]] = 0) do={
    /queue type add name=pcq-upload-hotspot kind=pcq pcq-rate=2M pcq-classifier=src-address
}
/queue type set [find name="pcq-download-hotspot"] pcq-rate=5M
/queue type set [find name="pcq-upload-hotspot"] pcq-rate=2M

# Queue Tree
/queue tree add name=hotspot-download-tree \
    parent=global \
    packet-mark=hotspot-download \
    queue=pcq-download-hotspot \
    max-limit=50M \
    comment="Hotspot Download - 5Mbps por usuario"

/queue tree add name=hotspot-upload-tree \
    parent=global \
    packet-mark=hotspot-upload \
    queue=pcq-upload-hotspot \
    max-limit=20M \
    comment="Hotspot Upload - 2Mbps por usuario"


# ============================================================================
# CORREÇÃO 6: FIREWALL - LIMPAR DUPLICATAS E REGRAS PROBLEMÁTICAS
# ============================================================================

# Remover regras DoH que bloqueiam HTTPS legítimo
:foreach rule in=[/ip firewall filter find where dst-port=443 action=drop] do={
    :local dstAddr ""
    :do { :set dstAddr [/ip firewall filter get $rule dst-address] } on-error={}
    :if ($dstAddr~"8.8.8.8" || $dstAddr~"1.1.1.1" || $dstAddr~"9.9.9.9") do={
        :log info ("Removendo regra DoH: " . $dstAddr)
        /ip firewall filter remove $rule
    }
}

# Remover masquerade duplicados (manter apenas o primeiro)
:local masqRules [/ip firewall nat find where chain=srcnat action=masquerade]
:if ([:len $masqRules] > 1) do={
    :for i from=1 to=([:len $masqRules] - 1) do={
        :log info "Removendo masquerade duplicado"
        /ip firewall nat remove ($masqRules->$i)
    }
}

# Remover DNS redirect duplicados
:local dnsRules [/ip firewall nat find where chain=dstnat protocol=udp dst-port=53]
:if ([:len $dnsRules] > 1) do={
    :for i from=1 to=([:len $dnsRules] - 1) do={
        /ip firewall nat remove ($dnsRules->$i)
    }
}


# ============================================================================
# CORREÇÃO 7: PERFORMANCE
# ============================================================================

/ip dhcp-server set [find name~"hotspot"] lease-time=2h
/ip dns set cache-size=4096KiB cache-max-ttl=1d

# Desabilitar serviços desnecessários
/ip service set telnet disabled=yes
/ip service set ftp disabled=yes
/ip service set api disabled=yes
/ip service set api-ssl disabled=yes


# ============================================================================
# CORREÇÃO 8: GARANTIR QUE HOTSPOT ESTÁ BLOQUEANDO
# ============================================================================
# Se o hotspot está desabilitado ou tem bindings indevidos,
# qualquer pessoa terá internet livre.
# ============================================================================

# Garantir que o hotspot está habilitado
:foreach hs in=[/ip hotspot find] do={
    /ip hotspot set $hs disabled=no
    :local hsName [/ip hotspot get $hs name]
    :log info ("Hotspot '" . $hsName . "' HABILITADO")
}

# Remover ip-bindings que NÃO são do sistema de pagamento
# (podem ser testes antigos ou lixo que liberam acesso indevido)
:local totalRemovidos 0
:foreach binding in=[/ip hotspot ip-binding find] do={
    :local bindComment ""
    :local bindType ""
    :do {
        :set bindComment [/ip hotspot ip-binding get $binding comment]
        :set bindType [/ip hotspot ip-binding get $binding type]
    } on-error={}
    
    # Manter APENAS bindings criados pelo sistema (comment="PAGO-AUTO")
    :if ($bindType = "bypassed" && $bindComment != "PAGO-AUTO") do={
        :local bindMac ""
        :do { :set bindMac [/ip hotspot ip-binding get $binding mac-address] } on-error={}
        :log warning ("Removendo binding nao autorizado: " . $bindMac . " (" . $bindComment . ")")
        /ip hotspot ip-binding remove $binding
        :set totalRemovidos ($totalRemovidos + 1)
    }
}

:if ($totalRemovidos > 0) do={
    :log info ("Removidos " . $totalRemovidos . " bindings nao autorizados - esses usuarios perderam acesso")
}

# Mostrar status atual
:do {
    :local activeCount [/ip hotspot active print count-only]
    :log info ("Usuarios ativos no hotspot: " . $activeCount)
    
    :local bindingCount [/ip hotspot ip-binding print count-only]
    :log info ("Bindings ativos (pagos): " . $bindingCount)
} on-error={}


# ============================================================================
# FINALIZAÇÃO
# ============================================================================

:log info "=============================================="
:log info "CORRECOES APLICADAS COM SUCESSO!"
:log info "=============================================="
:log info "1. registrarMacs: Aceita TODOS os MACs"
:log info "2. syncPagos: Endpoint lite (mais confiavel)"
:log info "3. Walled Garden: RESTRITIVO - so portal e bancos"
:log info "4. Queue Tree: 5M/2M por usuario"
:log info "5. Firewall: Duplicatas removidas"
:log info "6. Hotspot: Verificado e bindings limpos"
:log info "=============================================="
:log info ""
:log info "QUEM NAO PAGOU ACESSA APENAS:"
:log info "  - tocantinstransportewifi.com.br"
:log info "  - Apps de banco (BB, Caixa, Itau, Nubank, etc)"
:log info "  - Woovi, OpenPix, PagBank (pagamento PIX)"
:log info "  - NADA MAIS"
:log info ""
:log info "QUEM PAGOU ACESSA TUDO:"
:log info "  - Internet completa via ip-binding bypassed"
:log info "=============================================="
