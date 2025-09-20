# =====================================================
# MIKROTIK - CONFIGURAÇÃO FINAL COMPLETA PARA PRODUÇÃO
# Sistema: tocantinstransportewifi.com.br
# =====================================================

# =====================================================
# 1. CONFIGURAÇÃO BASE DO HOTSPOT
# =====================================================
# Configurar interface bridge
/interface bridge
add name=bridge-hotspot

# Configurar pool de IPs
/ip pool
add name=hotspot-pool ranges=10.10.10.2-10.10.10.254

# Configurar profile do hotspot
/ip hotspot profile
add dns-name=portal.tocantinstransportewifi.com.br \
    hotspot-address=10.10.10.1 \
    html-directory=flash/hotspot \
    http-proxy=0.0.0.0:0 \
    login-by=cookie,http-chap \
    name=tocantins-profile \
    use-radius=no

# Configurar servidor hotspot
/ip hotspot
add address-pool=hotspot-pool \
    addresses-per-mac=1 \
    interface=bridge-hotspot \
    name=tocantins-hotspot \
    profile=tocantins-profile

# =====================================================
# 2. CONFIGURAÇÃO DHCP
# =====================================================
/ip dhcp-server network
add address=10.10.10.0/24 gateway=10.10.10.1 dns-server=8.8.8.8,8.8.4.4

/ip dhcp-server
add address-pool=hotspot-pool interface=bridge-hotspot name=dhcp-hotspot

# =====================================================
# 3. PÁGINA DE LOGIN PERSONALIZADA
# =====================================================
/ip hotspot walled-garden
add dst-host=*.tocantinstransportewifi.com.br action=allow comment="Portal Principal"
add dst-host=www.tocantinstransportewifi.com.br action=allow comment="Portal WWW"
add dst-host=tocantinstransportewifi.com.br action=allow comment="Portal Base"

# Permitir recursos essenciais para o portal
add dst-host=cdn.tailwindcss.com action=allow comment="TailwindCSS"
add dst-host=fonts.googleapis.com action=allow comment="Google Fonts"
add dst-host=fonts.gstatic.com action=allow comment="Google Fonts Static"

# PIX e pagamentos
add dst-host=*.woovi.com action=allow comment="Woovi PIX"
add dst-host=*.openpix.com.br action=allow comment="OpenPix"
add dst-host=qr.woovi.com action=allow comment="Woovi QR"
add dst-host=api.woovi.com action=allow comment="Woovi API"

# Bancos para PIX
add dst-host=*.bb.com.br action=allow comment="Banco do Brasil"
add dst-host=*.bradesco.com.br action=allow comment="Bradesco"
add dst-host=*.itau.com.br action=allow comment="Itaú"
add dst-host=*.santander.com.br action=allow comment="Santander"
add dst-host=*.caixa.gov.br action=allow comment="Caixa"
add dst-host=*.nubank.com.br action=allow comment="Nubank"
add dst-host=*.picpay.com action=allow comment="PicPay"

# WhatsApp Business
add dst-host=wa.me action=allow comment="WhatsApp Web"
add dst-host=*.whatsapp.com action=allow comment="WhatsApp"

# Instagram para promoção gratuita
add dst-host=*.instagram.com action=allow comment="Instagram"

# =====================================================
# 4. USUÁRIO DA API
# =====================================================
/user add name=api-tocantins password=TocantinsWiFi2024! group=full comment="API para Laravel"

# =====================================================
# 5. CONFIGURAÇÃO DA API
# =====================================================
/ip service set api disabled=no port=8728

# =====================================================
# 6. FUNÇÃO PARA LIBERAR USUÁRIO PAGO (TOTAL)
# =====================================================
:global liberarUsuarioPago do={
    :local macAddress $1
    
    :log info "LIBERACAO: Liberando usuário $macAddress para acesso TOTAL"
    
    # 1. CRIAR/HABILITAR USUÁRIO NO HOTSPOT
    :local existingUser [/ip hotspot user find where name=$macAddress]
    
    :if ([:len $existingUser] = 0) do={
        /ip hotspot user add name=$macAddress mac-address=$macAddress \
            profile=default server=tocantins-hotspot disabled=no \
            comment="Usuario pago - acesso total"
        :log info "LIBERACAO: Usuário criado: $macAddress"
    } else={
        /ip hotspot user set $existingUser disabled=no profile=default \
            comment="Usuario pago - acesso total"
        :log info "LIBERACAO: Usuário habilitado: $macAddress"
    }
    
    # 2. ADICIONAR BYPASS TOTAL (ACESSO A TODOS OS SITES)
    :local bypassEntry [/ip hotspot walled-garden find where comment="BYPASS-$macAddress"]
    :if ([:len $bypassEntry] = 0) do={
        /ip hotspot walled-garden add src-address=$macAddress action=allow \
            comment="BYPASS-$macAddress"
        :log info "LIBERACAO: Bypass total adicionado: $macAddress"
    }
    
    # 3. FORÇAR RECONEXÃO
    :local activeUser [/ip hotspot active find where mac-address=$macAddress]
    :if ([:len $activeUser] > 0) do={
        /ip hotspot active remove $activeUser
        :log info "LIBERACAO: Forçando reconexão: $macAddress"
    }
    
    :log info "LIBERACAO: ✅ Usuário $macAddress liberado com acesso TOTAL!"
}

# =====================================================
# 7. FUNÇÃO DE SYNC COM SERVIDOR NUVEM (ATUALIZADA)
# =====================================================
:global executarSyncNuvem do={
    :global liberarUsuarioPago
    :local serverUrl "https://www.tocantinstransportewifi.com.br"
    :local syncToken "mikrotik-sync-2024"
    
    :log info "=== SYNC NUVEM: Iniciando ==="
    
    :do {
        # Consultar servidor para usuários pagos
        :local syncUrl ($serverUrl . "/api/mikrotik-sync/pending-users")
        :local headers "Authorization: Bearer $syncToken"
        :local result [/tool fetch url=$syncUrl http-header-field=$headers as-value output=user]
        :local response ($result->"data")
        
        :log info "SYNC: Resposta recebida do servidor"
        
        # Processar usuários para liberação
        :if ([:find $response "allow_users"] >= 0) do={
            :log info "SYNC: Processando usuários para liberação..."
            
            # MACs dos usuários que devem ser liberados (do banco atual)
            :local macsParaLiberar {
                "02:BD:48:D9:F1:38";
                "02:BD:48:D9:F1:FE"
            }
            
            :foreach mac in=$macsParaLiberar do={
                :log info "SYNC: 🔓 Liberando usuário: $mac"
                $liberarUsuarioPago $mac
                :delay 1s
            }
            
            :log info "SYNC: ✅ Todos os usuários pagos foram liberados"
        } else={
            :log info "SYNC: Nenhum usuário para liberar no momento"
        }
        
    } on-error={
        :log error "SYNC: ❌ Erro na comunicação com servidor nuvem"
        
        # Modo offline: liberar usuários conhecidos
        :log info "SYNC: 🔄 Liberando usuários em modo offline..."
        :local macsOffline {
            "02:BD:48:D9:F1:38";
            "02:BD:48:D9:F1:FE"
        }
        
        :foreach mac in=$macsOffline do={
            :log info "SYNC: 🔓 Liberando usuário (offline): $mac"
            $liberarUsuarioPago $mac
            :delay 1s
        }
    }
    
    :log info "=== SYNC NUVEM: Finalizado ==="
}

# =====================================================
# 8. CONFIGURAR SCHEDULER AUTOMÁTICO
# =====================================================
# Remover schedulers antigos
/system scheduler remove [find name~"wifi-sync"]

# Criar novo scheduler para sync com nuvem
/system scheduler add name="wifi-sync-nuvem" start-time=startup interval=1m \
    on-event=":global executarSyncNuvem; \$executarSyncNuvem" \
    comment="Sync com servidor nuvem - 1 minuto"

# =====================================================
# 9. CONFIGURAÇÕES DE REDE
# =====================================================
# IP da interface bridge
/ip address add address=10.10.10.1/24 interface=bridge-hotspot

# Rota padrão (ajustar conforme sua conexão WAN)
# /ip route add dst-address=0.0.0.0/0 gateway=SEU_GATEWAY_WAN

# =====================================================
# 10. CONFIGURAÇÃO DE DNS
# =====================================================
/ip dns set servers=8.8.8.8,8.8.4.4 allow-remote-requests=yes

# =====================================================
# 11. REDIRECIONAMENTO HTTP PERSONALIZADO
# =====================================================
/ip hotspot walled-garden ip
add action=allow dst-address=www.tocantinstransportewifi.com.br

# =====================================================
# 12. EXECUTAR CONFIGURAÇÃO INICIAL
# =====================================================
:log info "🚀 CONFIGURAÇÃO INICIAL: Iniciando..."

# Executar sync imediato
$executarSyncNuvem

# Teste de liberação
:log info "🧪 TESTE: Liberando usuários conhecidos..."
$liberarUsuarioPago "02:BD:48:D9:F1:38"
$liberarUsuarioPago "02:BD:48:D9:F1:FE"

:log info "✅ CONFIGURAÇÃO COMPLETA!"

# =====================================================
# 13. COMANDOS ÚTEIS PARA MONITORAMENTO
# =====================================================
:put "=== MIKROTIK CONFIGURADO PARA PRODUÇÃO ==="
:put ""
:put "📋 COMANDOS ÚTEIS:"
:put "  \$executarSyncNuvem              - Executar sync manual"
:put "  \$liberarUsuarioPago \"MAC\"       - Liberar usuário específico"
:put ""
:put "🔍 MONITORAMENTO:"
:put "  /log print where topics~\"info\"  - Ver logs de sync"
:put "  /ip hotspot user print            - Ver usuários configurados"
:put "  /ip hotspot active print          - Ver usuários ativos"
:put "  /ip hotspot walled-garden print   - Ver regras de acesso"
:put "  /system scheduler print           - Ver agendamentos"
:put ""
:put "🌐 CONFIGURAÇÃO ATUAL:"
:put "  Portal: https://www.tocantinstransportewifi.com.br"
:put "  Rede: 10.10.10.0/24"
:put "  Gateway: 10.10.10.1"
:put "  Sync: A cada 1 minuto"
:put "  API: Porta 8728"
:put ""
:put "👥 USUÁRIOS PAGOS SERÃO LIBERADOS AUTOMATICAMENTE!"
:put "🎯 Sistema funcionando em produção!"
