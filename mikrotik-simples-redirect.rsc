# ===== MÉTODO SIMPLES: REDIRECIONAMENTO DIRETO =====

# 1. LIMPAR CONFIGURAÇÕES ANTIGAS
/system scheduler remove [find name=redirecionamentoAutoComMac];
/system scheduler remove [find name=interceptarConexoes];

# 2. DEFINIR URL PADRÃO COM SEU MAC ESPECÍFICO
# (Temporário para teste - depois ajustaremos para dinâmico)
/ip hotspot profile set default redirect-to-link="https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F&source=mikrotik"

# 3. CONFIGURAR HOTSPOT PARA SEMPRE REDIRECIONAR
/ip hotspot profile set default login-by=cookie,http-chap

# 4. SCRIPT PARA ATUALIZAR MAC DINAMICAMENTE A CADA 30 SEGUNDOS
:global atualizarRedirectComMac do={
    :local macAtivo "";
    
    # Pegar MAC do primeiro usuário conectado (seu dispositivo)
    :foreach usuario in=[/ip hotspot active find] do={
        :local macUsuario [/ip hotspot active get $usuario mac-address];
        if ([:len $macUsuario] > 0 && $macUsuario != "00:00:00:00:00:00") do={
            :set macAtivo $macUsuario;
            :log info ("📱 MAC ativo detectado: " . $macAtivo);
            
            # Atualizar URL de redirecionamento
            :local novaUrl ("https://www.tocantinstransportewifi.com.br/?mac=" . $macAtivo . "&source=mikrotik");
            /ip hotspot profile set default redirect-to-link=$novaUrl;
            :log info ("🔄 URL atualizada: " . $novaUrl);
            
            # Sair do loop (usar apenas primeiro MAC encontrado)
            :return;
        }
    }
    
    # Se não encontrou MAC ativo, usar registro wireless
    if ([:len $macAtivo] = 0) do={
        :foreach regEntry in=[/interface wireless registration-table find] do={
            :set macAtivo [/interface wireless registration-table get $regEntry mac-address];
            if ([:len $macAtivo] > 0) do={
                :local novaUrl ("https://www.tocantinstransportewifi.com.br/?mac=" . $macAtivo . "&source=mikrotik");
                /ip hotspot profile set default redirect-to-link=$novaUrl;
                :log info ("🔄 URL atualizada via wireless: " . $novaUrl);
                :return;
            }
        }
    }
}

# 5. EXECUTAR ATUALIZAÇÃO A CADA 30 SEGUNDOS
/system scheduler add name=atualizarRedirectMac interval=30s on-event=":global atualizarRedirectComMac; [\$atualizarRedirectComMac]"

# 6. EXECUTAR UMA VEZ AGORA
:global atualizarRedirectComMac; [$atualizarRedirectComMac]

:log info "✅ Redirecionamento simples configurado!"
:put "Agora qualquer acesso ao portal será redirecionado com MAC!"
:put "Teste: acesse https://www.tocantinstransportewifi.com.br/"
