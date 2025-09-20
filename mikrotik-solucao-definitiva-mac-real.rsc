# =====================================================
# SOLUÇÃO DEFINITIVA PARA CAPTURA DE MAC REAL
# Funciona para TODOS os casos:
# 1. Redirecionamento automático (com MAC na URL)
# 2. Acesso direto ao portal (detecção automática)
# =====================================================

:put "🚀 CONFIGURANDO SOLUÇÃO DEFINITIVA PARA MAC REAL..."

# 1. REMOVER CONFIGURAÇÕES ANTIGAS (limpeza)
:do {
    /ip hotspot walled-garden remove [find comment~"PORTAL-MAC"]
} on-error={}

:do {
    /file remove [find name~"hotspot/login.html"]
} on-error={}

# 2. CRIAR DIRETÓRIO HOTSPOT
:do {
    /file add name="hotspot" type=directory
} on-error={
    :put "📁 Diretório hotspot já existe"
}

# 3. CRIAR PÁGINA DE LOGIN PERSONALIZADA AVANÇADA
/file add name="hotspot/login.html" contents="<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>WiFi Tocantins - Conectando</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; 
            text-align: center; 
            padding: 30px 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .container { max-width: 400px; margin: 0 auto; }
        .logo { font-size: 32px; margin-bottom: 20px; }
        .loading { font-size: 20px; margin: 15px 0; }
        .info { opacity: 0.8; font-size: 14px; margin: 10px 0; }
        .spinner { 
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .debug { 
            position: fixed; 
            bottom: 10px; 
            left: 10px; 
            font-size: 10px; 
            opacity: 0.5;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='logo'>🚌 WiFi Tocantins</div>
        <div class='loading'>Conectando automaticamente...</div>
        <div class='spinner'></div>
        <div class='info'>Aguarde, redirecionando para o portal</div>
        <div class='info'>📶 Internet de alta velocidade</div>
    </div>
    
    <div class='debug' id='debug'></div>
    
    <script>
        // CAPTURAR VARIÁVEIS DO MIKROTIK
        var clientMac = '\$(client-mac)';
        var clientIP = '\$(client-ip)';
        var linkOrig = '\$(link-orig)';
        var linkOrigEsc = '\$(link-orig-esc)';
        var username = '\$(username)';
        var serverName = '\$(server-name)';
        
        // DEBUG INFO
        var debugInfo = 'MAC: ' + clientMac + '\\nIP: ' + clientIP + '\\nOrig: ' + linkOrig;
        document.getElementById('debug').textContent = debugInfo;
        
        // CONSTRUIR URL DO PORTAL
        var baseUrl = 'https://www.tocantinstransportewifi.com.br/';
        var portalUrl = baseUrl;
        
        // ADICIONAR PARÂMETROS APENAS SE VÁLIDOS
        var params = [];
        
        // MAC ADDRESS (mais importante)
        if (clientMac && clientMac.length > 10 && clientMac !== '\$(client-mac)') {
            params.push('mac=' + encodeURIComponent(clientMac));
        }
        
        // IP ADDRESS
        if (clientIP && clientIP !== '0.0.0.0' && clientIP !== '\$(client-ip)') {
            params.push('ip=' + encodeURIComponent(clientIP));
        }
        
        // URL ORIGINAL (onde o usuário queria ir)
        if (linkOrig && linkOrig !== '' && linkOrig !== '\$(link-orig)') {
            params.push('orig=' + encodeURIComponent(linkOrig));
        }
        
        // INDICADOR DE REDIRECIONAMENTO AUTOMÁTICO
        params.push('mikrotik=1');
        
        // CONSTRUIR URL FINAL
        if (params.length > 0) {
            portalUrl += '?' + params.join('&');
        }
        
        // LOG PARA DEBUG
        console.log('🔍 MikroTik Redirect Debug:');
        console.log('MAC:', clientMac);
        console.log('IP:', clientIP);
        console.log('Original URL:', linkOrig);
        console.log('Portal URL:', portalUrl);
        
        // FUNÇÃO DE REDIRECIONAMENTO
        function redirect() {
            console.log('🚀 Redirecionando para:', portalUrl);
            window.location.href = portalUrl;
        }
        
        // REDIRECIONAMENTO AUTOMÁTICO APÓS 3 SEGUNDOS
        setTimeout(redirect, 3000);
        
        // REDIRECIONAMENTO IMEDIATO SE USUÁRIO CLICAR/TOCAR
        document.addEventListener('click', redirect);
        document.addEventListener('touchstart', redirect);
        document.addEventListener('keypress', redirect);
        
        // REDIRECIONAMENTO DE EMERGÊNCIA (caso algo falhe)
        setTimeout(function() {
            if (window.location.href.indexOf('tocantinstransportewifi.com.br') === -1) {
                window.location.href = baseUrl;
            }
        }, 10000);
    </script>
</body>
</html>"

# 4. CONFIGURAR HOTSPOT PARA USAR PÁGINA PERSONALIZADA
/ip hotspot profile set tocantins-profile html-directory=hotspot login-by=http-chap

# 5. GARANTIR ACESSO AO PORTAL NO WALLED GARDEN
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br action=allow comment="PORTAL-DEFINITIVO-WWW"
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br action=allow comment="PORTAL-DEFINITIVO-ROOT"
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br dst-port=443 action=allow comment="PORTAL-HTTPS-WWW"
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br dst-port=443 action=allow comment="PORTAL-HTTPS-ROOT"

# 6. ADICIONAR CDNs NECESSÁRIOS PARA O PORTAL
/ip hotspot walled-garden add dst-host=cdn.tailwindcss.com action=allow comment="TAILWIND-CSS"
/ip hotspot walled-garden add dst-host=fonts.googleapis.com action=allow comment="GOOGLE-FONTS"
/ip hotspot walled-garden add dst-host=fonts.gstatic.com action=allow comment="GOOGLE-FONTS-STATIC"

# 7. CONFIGURAR FUNÇÃO GLOBAL PARA LOG DE REDIRECIONAMENTOS
:global logRedirect do={
    :local mac $1
    :local ip $2
    :local url $3
    
    :log info "🔗 REDIRECT: MAC=$mac IP=$ip -> $url"
}

# 8. TESTAR CONFIGURAÇÃO
:put ""
:put "✅ CONFIGURAÇÃO DEFINITIVA CONCLUÍDA!"
:put ""
:put "📋 COMO FUNCIONA:"
:put "1️⃣  Usuário conecta WiFi → MikroTik redireciona com MAC real"
:put "2️⃣  Usuário acessa direto → Backend detecta MAC automaticamente"  
:put "3️⃣  Ambos casos capturam MAC REAL para pagamento"
:put ""
:put "🧪 TESTES:"
:put "• Conecte dispositivo no WiFi (deve redirecionar automaticamente)"
:put "• Acesse direto: https://www.tocantinstransportewifi.com.br/"
:put "• Verifique MAC na URL ou detecção automática"
:put ""
:put "📊 MONITORAMENTO:"
:put "• /log print where topics~\"info\" (ver redirecionamentos)"
:put "• /ip hotspot active print (usuários conectados)"
:put "• /ip hotspot walled-garden print (regras portal)"
:put ""
:put "🎯 PRÓXIMOS PASSOS:"
:put "1. Teste conectar um dispositivo"
:put "2. Verifique se MAC aparece na URL do portal"
:put "3. Faça pagamento teste para confirmar fluxo completo"
