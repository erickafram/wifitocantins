# CONFIGURAÇÃO MIKROTIK PARA ENVIAR MAC REAL VIA HEADERS
# Execute este script no terminal do MikroTik

# 1. CONFIGURAR HOTSPOT PARA ENVIAR HEADERS COM MAC
/ip hotspot profile set default html-directory=hotspot html-directory-override=""

# 2. MODIFICAR PÁGINA DE LOGIN PARA ENVIAR MAC VIA HEADER
# Crie um arquivo de configuração HTTP personalizado
/ip hotspot add-walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow comment="PORTAL-COM-MAC-HEADER"

# 3. CONFIGURAR SCRIPT PARA INTERCEPTAR REQUESTS E ADICIONAR MAC
# Este script será executado quando usuários acessarem o portal
:global adicionarMacHeader do={
    :local macAddress [:tostr $"mac-address"];
    :local ipAddress [:tostr $"address"];
    
    # Log para debug
    :log info ("Enviando MAC via header: " . $macAddress . " para IP: " . $ipAddress);
    
    # Tentar adicionar header customizado (limitado no RouterOS)
    # Alternativa: usar redirecionamento com parâmetros
    :local urlPortal "https://www.tocantinstransportewifi.com.br/?mikrotik_mac=" . $macAddress . "&mikrotik_ip=" . $ipAddress;
    
    return $urlPortal;
}

# 4. CONFIGURAR REDIRECIONAMENTO COM MAC COMO PARÂMETRO
/ip hotspot profile set default login-by=http-pap,http-chap,trial,cookie redirect-to-link=""

# 5. ALTERNATIVA: CRIAR UM SCRIPT DE REDIRECIONAMENTO PERSONALIZADO
/system script add name=enviarMacPortal source={
    :local macAtual [/interface wireless registration-table get [find interface=wlan1] mac-address];
    :local ipAtual [/ip hotspot active get [find mac-address=$macAtual] address];
    
    :if ([:len $macAtual] > 0) do={
        :log info ("Redirecionando usuário com MAC: " . $macAtual);
        
        # Criar URL com MAC como parâmetro
        :local urlRedirect ("https://www.tocantinstransportewifi.com.br/?mac=" . $macAtual . "&source=mikrotik");
        
        # Configurar redirecionamento
        /ip hotspot profile set default redirect-to-link=$urlRedirect;
    }
}

# 6. EXECUTAR SCRIPT AUTOMATICAMENTE
/system scheduler add name=configurarMacHeader interval=5s on-event="/system script run enviarMacPortal"

:log info "✅ Configuração de envio de MAC via header/parâmetro concluída!"
:put "Configure o portal Laravel para capturar parâmetro 'mac' da URL"
