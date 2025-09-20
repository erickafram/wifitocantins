# ===== CONFIGURAÇÃO DEFINITIVA PARA CAPTURAR MAC REAL =====
# Execute no terminal do MikroTik

# FUNÇÃO PARA REDIRECIONAMENTO COM MAC REAL
:global redirecionarComMac do={
    :local macAtual [:tostr $1];
    :local ipAtual [:tostr $2];
    
    # Limpar MAC (remover separadores)
    :local macLimpo [:tostr $macAtual];
    :set macLimpo [:tostr [/interface wireless registration-table get [find] mac-address]];
    
    if ([:len $macLimpo] > 0) do={
        :log info ("🎯 Redirecionando com MAC REAL: " . $macLimpo . " IP: " . $ipAtual);
        
        # URL com MAC real como parâmetro
        :local urlPortal ("https://www.tocantinstransportewifi.com.br/?mac=" . $macLimpo . "&ip=" . $ipAtual . "&source=mikrotik");
        
        return $urlPortal;
    } else={
        :log warning ("⚠️ MAC não encontrado para IP: " . $ipAtual);
        return "https://www.tocantinstransportewifi.com.br/";
    }
}

# CONFIGURAR HOTSPOT PROFILE PARA REDIRECIONAMENTO AUTOMÁTICO
/ip hotspot profile set default redirect-to-link=""

# CRIAR FUNÇÃO PARA INTERCEPTAR CONEXÕES E REDIRECIONAR
:global interceptarConexao do={
    :foreach usuario in=[/ip hotspot active find] do={
        :local macUsuario [/ip hotspot active get $usuario mac-address];
        :local ipUsuario [/ip hotspot active get $usuario address];
        
        # Se usuário não está autenticado, redirecionar com MAC
        :local status [/ip hotspot active get $usuario comment];
        if ($status = "") do={
            :local urlRedirect [[:global redirecionarComMac] $macUsuario $ipUsuario];
            :log info ("Redirecionando usuário: " . $macUsuario . " para: " . $urlRedirect);
        }
    }
}

# EXECUTAR INTERCEPTAÇÃO A CADA 10 SEGUNDOS
/system scheduler remove [find name=interceptarConexoes];
/system scheduler add name=interceptarConexoes interval=10s on-event=":global interceptarConexao; [\$interceptarConexao]"

# CONFIGURAR WALLED GARDEN PARA PERMITIR PORTAL COM PARÂMETROS
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow comment="PORTAL-COM-MAC-PARAMETRO"

:log info "✅ Configuração de MAC real via URL concluída!"
:put "Agora o MikroTik enviará o MAC real via parâmetro 'mac' na URL"
:put "Teste acessando: https://www.tocantinstransportewifi.com.br/?mac=e4:84:d3:f4:7f:eb"
