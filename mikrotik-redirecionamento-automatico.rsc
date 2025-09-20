# ===== REDIRECIONAMENTO AUTOMÁTICO COM MAC REAL =====
# Esta configuração garante que SEMPRE o MAC real seja enviado

# 1. REMOVER CONFIGURAÇÕES ANTIGAS
/system scheduler remove [find name=interceptarConexoes];
/ip hotspot profile set default redirect-to-link=""

# 2. FUNÇÃO PARA OBTER MAC DE USUÁRIO CONECTADO
:global obterMacUsuario do={
    :local ipUsuario [:tostr $1];
    :local macEncontrado "";
    
    # Buscar MAC na tabela ARP
    :foreach arpEntry in=[/ip arp find where address=$ipUsuario] do={
        :set macEncontrado [/ip arp get $arpEntry mac-address];
    }
    
    # Se não encontrou na ARP, buscar na tabela wireless
    if ([:len $macEncontrado] = 0) do={
        :foreach regEntry in=[/interface wireless registration-table find] do={
            :local regMac [/interface wireless registration-table get $regEntry mac-address];
            :local regIp [/ip dhcp-server lease get [find mac-address=$regMac] address];
            if ($regIp = $ipUsuario) do={
                :set macEncontrado $regMac;
            }
        }
    }
    
    :return $macEncontrado;
}

# 3. SCRIPT DE INTERCEPTAÇÃO E REDIRECIONAMENTO
:global interceptarERedirecionarComMac do={
    :log info "=== INICIANDO INTERCEPTAÇÃO DE USUÁRIOS ===";
    
    :foreach usuario in=[/ip hotspot active find] do={
        :local ipUsuario [/ip hotspot active get $usuario address];
        :local macUsuario [/ip hotspot active get $usuario mac-address];
        :local statusUsuario [/ip hotspot active get $usuario comment];
        
        # Se usuário não tem status ou não está autenticado
        if ([:len $statusUsuario] = 0 || $statusUsuario = "") do={
            
            # Se MAC não foi capturado pelo hotspot, buscar manualmente
            if ([:len $macUsuario] = 0 || $macUsuario = "00:00:00:00:00:00") do={
                :set macUsuario [[:global obterMacUsuario] $ipUsuario];
            }
            
            if ([:len $macUsuario] > 0 && $macUsuario != "00:00:00:00:00:00") do={
                # Criar URL com MAC real
                :local urlRedirect ("https://www.tocantinstransportewifi.com.br/?mac=" . $macUsuario . "&ip=" . $ipUsuario . "&source=mikrotik");
                
                :log info ("🎯 REDIRECIONANDO: IP=" . $ipUsuario . " MAC=" . $macUsuario . " URL=" . $urlRedirect);
                
                # MÉTODO 1: Definir redirecionamento via profile
                /ip hotspot profile set default redirect-to-link=$urlRedirect;
                
                # MÉTODO 2: Forçar logout e relogin com redirecionamento
                /ip hotspot active remove $usuario;
                
            } else={
                :log warning ("⚠️ MAC não encontrado para IP: " . $ipUsuario);
            }
        } else={
            :log info ("✅ Usuário já autenticado: IP=" . $ipUsuario . " Status=" . $statusUsuario);
        }
    }
}

# 4. EXECUTAR INTERCEPTAÇÃO A CADA 5 SEGUNDOS
/system scheduler add name=redirecionamentoAutoComMac interval=5s on-event=":global interceptarERedirecionarComMac; [\$interceptarERedirecionarComMac]"

# 5. CONFIGURAR WALLED GARDEN PARA PERMITIR PORTAL
/ip hotspot walled-garden remove [find comment="PORTAL-COM-MAC-PARAMETRO"];
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow comment="PORTAL-COM-MAC-PARAMETRO"

# 6. CONFIGURAR HOTSPOT PARA USAR LOGIN BY COOKIE (mais eficiente)
/ip hotspot profile set default login-by=cookie,http-chap

:log info "✅ Redirecionamento automático configurado!"
:log info "Agora TODOS os acessos serão redirecionados com MAC real"
:put "Configuração aplicada! Teste acessando o portal diretamente."
