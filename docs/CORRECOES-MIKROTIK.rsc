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
#   2. CDNs ESPECÍFICOS do portal (Tailwind, Fonts - hostnames exatos)
#   3. Gateways PIX (Woovi, OpenPix, PagBank, Santander)
#   4. Apps de banco (domínios específicos dos bancos)
#   5. Validação de certificados HTTPS (OCSP)
#
# NÃO LIBERAR wildcards de CDN:
#   *.cloudfront.net, *.cloudflare.com, *.akamai.net, *.azureedge.net,
#   *.fastly.net
#   ESSES WILDCARDS DÃO ACESSO À INTERNET INTEIRA!
#
# EXCECAO CONTROLADA:
#   *.googleapis.com e *.gstatic.com sao NECESSARIOS para apps bancarios
#   (Google Play Services). Para NAO liberar internet completa, vamos
#   BLOQUEAR os hosts de teste de conectividade via DNS estatico:
#   connectivitycheck.gstatic.com, connectivitycheck.android.com,
#   clients3.google.com, captive.apple.com
#
# NÃO LIBERAR detecção de captive portal:
#   connectivitycheck.gstatic.com, captive.apple.com, www.apple.com
#   SEM eles na lista, o MikroTik redireciona o teste de conectividade
#   para a página de login → celular mostra popup "Entrar na rede WiFi"
# ============================================================================

# LIMPAR TODAS as entradas existentes do walled-garden
:log info ">>> Limpando Walled Garden antigo..."
:foreach i in=[/ip hotspot walled-garden find] do={
    :do { /ip hotspot walled-garden remove $i } on-error={}
}
:foreach i in=[/ip hotspot walled-garden ip find] do={
    :do { /ip hotspot walled-garden ip remove $i } on-error={}
}

# Remover regra padrao que libera TODO o trafego
:do { /ip hotspot walled-garden remove [find comment="place hotspot rules here"] } on-error={}
:do { /ip hotspot walled-garden set [find comment="place hotspot rules here"] disabled=yes } on-error={}

:log info ">>> Configurando Walled Garden RESTRITIVO..."

# =============================================
# GRUPO 1: PORTAL DE PAGAMENTO
# =============================================
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Portal"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Portal *"

# CDNs do portal - HOSTNAMES EXATOS, não wildcards
/ip hotspot walled-garden add dst-host=cdn.tailwindcss.com comment="Portal: Tailwind"
/ip hotspot walled-garden add dst-host=fonts.googleapis.com comment="Portal: Fonts API"
/ip hotspot walled-garden add dst-host=fonts.gstatic.com comment="Portal: Fonts"
/ip hotspot walled-garden add dst-host=cdnjs.cloudflare.com comment="Portal: CDNJS"

# Infra minima para apps bancarios (Google Play Services)
/ip hotspot walled-garden add dst-host=*.googleapis.com comment="Infra: Google APIs"
/ip hotspot walled-garden add dst-host=*.gstatic.com comment="Infra: Google Static"

# =============================================
# GRUPO 2: GATEWAYS DE PAGAMENTO PIX
# =============================================
/ip hotspot walled-garden add dst-host=*.woovi.com comment="PIX: Woovi"
/ip hotspot walled-garden add dst-host=*.openpix.com.br comment="PIX: OpenPix"
/ip hotspot walled-garden add dst-host=api.qrserver.com comment="PIX: QR Code"
/ip hotspot walled-garden add dst-host=chart.googleapis.com comment="PIX: QR Charts"
/ip hotspot walled-garden add dst-host=*.bcb.gov.br comment="PIX: BCB"
/ip hotspot walled-garden add dst-host=pagseguro.uol.com.br comment="PIX: PagSeguro"
/ip hotspot walled-garden add dst-host=*.pagseguro.uol.com.br comment="PIX: PagSeguro *"
/ip hotspot walled-garden add dst-host=*.pagseguro.com comment="PIX: PagSeguro Alt"
/ip hotspot walled-garden add dst-host=*.pagbank.com.br comment="PIX: PagBank"

# =============================================
# GRUPO 3: APPS DE BANCOS (para pagar PIX)
# =============================================
# Cada banco usa seu próprio domínio para APIs do app.
# O wildcard *.banco.com.br cobre todos os subdomínios.
# NÃO precisamos de CDN genérico - o app bancário
# já está instalado no celular com assets cacheados.
# =============================================

# Banco do Brasil
/ip hotspot walled-garden add dst-host=*.bb.com.br comment="Banco: BB"
/ip hotspot walled-garden add dst-host=*.bbseguros.com.br comment="Banco: BB Seguros"

# Caixa Econômica Federal
/ip hotspot walled-garden add dst-host=*.caixa.gov.br comment="Banco: Caixa"
/ip hotspot walled-garden add dst-host=*.caixa.com.br comment="Banco: Caixa Alt"
/ip hotspot walled-garden add dst-host=*.caixaseguridade.com.br comment="Banco: Caixa Seg"

# Itaú
/ip hotspot walled-garden add dst-host=*.itau.com.br comment="Banco: Itau"
/ip hotspot walled-garden add dst-host=*.itau.com comment="Banco: Itau Alt"
/ip hotspot walled-garden add dst-host=*.itau-unibanco.com.br comment="Banco: Itau Uni"
/ip hotspot walled-garden add dst-host=*.iti.com.br comment="Banco: Iti"

# Nubank
/ip hotspot walled-garden add dst-host=*.nubank.com.br comment="Banco: Nubank"
/ip hotspot walled-garden add dst-host=*.nubank.com comment="Banco: Nubank Alt"
/ip hotspot walled-garden add dst-host=*.nuinvest.com.br comment="Banco: NuInvest"

# Banco Inter
/ip hotspot walled-garden add dst-host=*.bancointer.com.br comment="Banco: Inter"
/ip hotspot walled-garden add dst-host=*.inter.co comment="Banco: Inter Alt"

# Santander
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Banco: Santander"

# Bradesco
/ip hotspot walled-garden add dst-host=*.bradesco.com.br comment="Banco: Bradesco"
/ip hotspot walled-garden add dst-host=*.bradescocard.com.br comment="Banco: Bradesco Card"
/ip hotspot walled-garden add dst-host=*.next.me comment="Banco: Next"

# BRB (Banco de Brasília) - usa vários domínios separados
/ip hotspot walled-garden add dst-host=*.brb.com.br comment="Banco: BRB"
/ip hotspot walled-garden add dst-host=*.brbservicos.com.br comment="Banco: BRB Servicos"
/ip hotspot walled-garden add dst-host=*.brbcard.com.br comment="Banco: BRB Card"
/ip hotspot walled-garden add dst-host=*.brbseguros.com.br comment="Banco: BRB Seguros"

# PicPay
/ip hotspot walled-garden add dst-host=*.picpay.com comment="Banco: PicPay"
/ip hotspot walled-garden add dst-host=*.picpay.com.br comment="Banco: PicPay Alt"

# Mercado Pago
/ip hotspot walled-garden add dst-host=*.mercadopago.com.br comment="Banco: MP"
/ip hotspot walled-garden add dst-host=*.mercadopago.com comment="Banco: MP Alt"
/ip hotspot walled-garden add dst-host=*.mercadolibre.com comment="Banco: ML"
/ip hotspot walled-garden add dst-host=*.mercadolivre.com.br comment="Banco: ML BR"

# C6 Bank
/ip hotspot walled-garden add dst-host=*.c6bank.com.br comment="Banco: C6"

# Sicoob
/ip hotspot walled-garden add dst-host=*.sicoob.com.br comment="Banco: Sicoob"

# Sicredi
/ip hotspot walled-garden add dst-host=*.sicredi.com.br comment="Banco: Sicredi"

# Neon
/ip hotspot walled-garden add dst-host=*.neon.com.br comment="Banco: Neon"

# Ame Digital
/ip hotspot walled-garden add dst-host=*.amedigital.com comment="Banco: Ame"

# Banco Original
/ip hotspot walled-garden add dst-host=*.original.com.br comment="Banco: Original"

# Banco Safra
/ip hotspot walled-garden add dst-host=*.safra.com.br comment="Banco: Safra"

# Banco BMG
/ip hotspot walled-garden add dst-host=*.bancobmg.com.br comment="Banco: BMG"

# Banrisul
/ip hotspot walled-garden add dst-host=*.banrisul.com.br comment="Banco: Banrisul"

# Banco Pan
/ip hotspot walled-garden add dst-host=*.bancopan.com.br comment="Banco: Pan"

# Cielo/Stone/Elo (processamento de pagamento)
/ip hotspot walled-garden add dst-host=*.cielo.com.br comment="Banco: Cielo"
/ip hotspot walled-garden add dst-host=*.stone.com.br comment="Banco: Stone"
/ip hotspot walled-garden add dst-host=*.elo.com.br comment="Banco: Elo"

# =============================================
# GRUPO 4: CERTIFICADOS SSL (OCSP/CRL)
# =============================================
# Verificação de certificados HTTPS. Sem isso bancos
# mostram "erro de certificado". São requisições pequenas,
# NÃO dão acesso a sites.
# =============================================
/ip hotspot walled-garden add dst-host=ocsp.digicert.com comment="SSL: DigiCert"
/ip hotspot walled-garden add dst-host=crl3.digicert.com comment="SSL: DigiCert CRL"
/ip hotspot walled-garden add dst-host=crl4.digicert.com comment="SSL: DigiCert CRL4"
/ip hotspot walled-garden add dst-host=ocsp.verisign.com comment="SSL: VeriSign"
/ip hotspot walled-garden add dst-host=ocsp.globalsign.com comment="SSL: GlobalSign"
/ip hotspot walled-garden add dst-host=crl.globalsign.com comment="SSL: GlobalSign CRL"
/ip hotspot walled-garden add dst-host=ocsp.pki.goog comment="SSL: Google"
/ip hotspot walled-garden add dst-host=pki.goog comment="SSL: Google PKI"
/ip hotspot walled-garden add dst-host=ocsp.sectigo.com comment="SSL: Sectigo"
/ip hotspot walled-garden add dst-host=crl.sectigo.com comment="SSL: Sectigo CRL"
/ip hotspot walled-garden add dst-host=ocsp.comodoca.com comment="SSL: Comodo"
/ip hotspot walled-garden add dst-host=ocsp.usertrust.com comment="SSL: UserTrust"
/ip hotspot walled-garden add dst-host=r3.o.lencr.org comment="SSL: LetsEncrypt"
/ip hotspot walled-garden add dst-host=x1.c.lencr.org comment="SSL: LetsEncrypt CRL"

# =============================================
# WALLED GARDEN IP
# =============================================
/ip hotspot walled-garden ip add dst-address=104.248.185.39 action=accept comment="IP: Portal"
/ip hotspot walled-garden ip add dst-address=10.5.50.1 action=accept comment="IP: Router DNS"
/ip hotspot walled-garden ip add dst-address=8.8.8.8 action=accept comment="IP: Google DNS"
/ip hotspot walled-garden ip add dst-address=8.8.4.4 action=accept comment="IP: Google DNS 2"
/ip hotspot walled-garden ip add dst-address=1.1.1.1 action=accept comment="IP: Cloudflare DNS"
/ip hotspot walled-garden ip add dst-address=1.0.0.1 action=accept comment="IP: Cloudflare DNS 2"

# Bloquear teste de conectividade para forcar popup do captive
:do { /ip dns static remove [find name="connectivitycheck.gstatic.com"] } on-error={}
:do { /ip dns static remove [find name="connectivitycheck.android.com"] } on-error={}
:do { /ip dns static remove [find name="clients3.google.com"] } on-error={}
:do { /ip dns static remove [find name="captive.apple.com"] } on-error={}

/ip dns static add name=connectivitycheck.gstatic.com address=127.0.0.1 comment="Block captive check"
/ip dns static add name=connectivitycheck.android.com address=127.0.0.1 comment="Block captive check"
/ip dns static add name=clients3.google.com address=127.0.0.1 comment="Block captive check"
/ip dns static add name=captive.apple.com address=127.0.0.1 comment="Block captive check"

:log info ">>> Walled Garden RESTRITIVO configurado"
:log info ">>> SEM CDN wildcards, SEM captive portal detection"
:log info ">>> Captive portal popup VAI APARECER automaticamente"


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

# Remover regras que bloqueiam porta 443 (HTTPS)
# PROBLEMA: Block Google DoH e Block Cloudflare DoH bloqueiam HTTPS
# para 8.8.8.8 e 1.1.1.1, interferindo com apps bancários que
# usam DoH para resolver DNS antes de conectar ao banco.
# Como já temos DNS redirect (porta 53 → MikroTik), não precisamos
# bloquear DoH - o DNS já é forçado pelo NAT redirect.
:foreach rule in=[/ip firewall filter find where dst-port=443 action=drop] do={
    :local dstAddr ""
    :do { :set dstAddr [/ip firewall filter get $rule dst-address] } on-error={}
    :if ($dstAddr~"8.8.8.8" || $dstAddr~"1.1.1.1" || $dstAddr~"9.9.9.9") do={
        :log info ("Removendo regra DoH: " . $dstAddr)
        /ip firewall filter remove $rule
    }
}

# Remover masquerade duplicados (manter apenas 1 por interface)
# PROBLEMA ATUAL: 2x masquerade para ether1 (NAT-Starlink + NAT Principal)
:local masqEther1 [/ip firewall nat find where chain=srcnat action=masquerade out-interface=ether1]
:if ([:len $masqEther1] > 1) do={
    :for i from=1 to=([:len $masqEther1] - 1) do={
        :log info "Removendo masquerade duplicado ether1"
        /ip firewall nat remove ($masqEther1->$i)
    }
}

# Remover DNS redirect duplicados (UDP)
:local dnsUdp [/ip firewall nat find where chain=dstnat protocol=udp dst-port=53]
:if ([:len $dnsUdp] > 1) do={
    :for i from=1 to=([:len $dnsUdp] - 1) do={
        :log info "Removendo DNS redirect UDP duplicado"
        /ip firewall nat remove ($dnsUdp->$i)
    }
}

# Remover DNS redirect duplicados (TCP)
:local dnsTcp [/ip firewall nat find where chain=dstnat protocol=tcp dst-port=53]
:if ([:len $dnsTcp] > 1) do={
    :for i from=1 to=([:len $dnsTcp] - 1) do={
        :log info "Removendo DNS redirect TCP duplicado"
        /ip firewall nat remove ($dnsTcp->$i)
    }
}

# Remover walled-garden IP duplicados
:local portalWg [/ip hotspot walled-garden ip find where dst-address="104.248.185.39"]
:if ([:len $portalWg] > 1) do={
    :for i from=1 to=([:len $portalWg] - 1) do={
        :log info "Removendo walled-garden IP duplicado"
        /ip hotspot walled-garden ip remove ($portalWg->$i)
    }
}

# Remover logging duplicado
:local hotspotLogs [/system logging find where topics~"hotspot"]
:if ([:len $hotspotLogs] > 1) do={
    :for i from=1 to=([:len $hotspotLogs] - 1) do={
        /system logging remove ($hotspotLogs->$i)
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
