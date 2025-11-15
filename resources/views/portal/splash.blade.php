<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Tocantins - Conectando...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-pulse-text {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-spin-slow {
            animation: spin 3s linear infinite;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #10B981 0%, #059669 50%, #047857 100%);
        }
        
        .icon-circle {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .loading-dots span {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .loading-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .loading-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center overflow-hidden">
    
    <!-- Formas decorativas de fundo -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-white/5 rounded-full blur-2xl"></div>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="relative z-10 text-center px-4">
        
        <!-- Ícone/Logo -->
        <div class="mb-8 flex justify-center animate-fade-in animate-float">
            <div class="icon-circle">
                <svg class="w-20 h-20 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
            </div>
        </div>
        
        <!-- Texto de Boas-vindas -->
        <div class="mb-6 space-y-3 animate-fade-in" style="animation-delay: 0.2s;">
            <h1 class="text-white text-4xl md:text-5xl font-bold tracking-tight">
                Bem-vindo ao
            </h1>
            <h2 class="text-white text-3xl md:text-4xl font-extrabold">
                WiFi Tocantins
            </h2>
            <div class="w-24 h-1 bg-white mx-auto rounded-full"></div>
        </div>
        
        <!-- Tagline -->
        <p class="text-white/90 text-lg md:text-xl mb-8 animate-fade-in" style="animation-delay: 0.4s;">
            Internet a bordo durante toda a viagem
        </p>
        
        <!-- Loading Indicator -->
        <div class="animate-fade-in" style="animation-delay: 0.6s;">
            <div class="inline-flex items-center justify-center space-x-2 bg-white/20 backdrop-blur-md px-6 py-3 rounded-full">
                <div class="w-2 h-2 bg-white rounded-full animate-spin-slow"></div>
                <span class="text-white font-medium animate-pulse-text">Conectando ao WiFi</span>
                <span class="loading-dots text-white font-bold">
                    <span>.</span><span>.</span><span>.</span>
                </span>
            </div>
        </div>
        
        <!-- Informações adicionais -->
        <div class="mt-12 flex flex-wrap justify-center gap-4 animate-fade-in" style="animation-delay: 0.8s;">
            <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                <span class="text-white text-sm">⚡ Alta Velocidade</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                <span class="text-white text-sm">🔒 Conexão Segura</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                <span class="text-white text-sm">🚌 WiFi a Bordo</span>
            </div>
        </div>
    </div>
    
    <script>
        // Configurações
        const SPLASH_DISPLAY_TIME = 5000; // 5 segundos de splash antes de redirecionar
        
        let startTime = Date.now();
        
        console.log('🚀 Splash iniciada');
        console.log('⏱️ Tempo de exibição:', SPLASH_DISPLAY_TIME / 1000, 'segundos');
        console.log('🔗 URL do MikroTik:', '{{ $mikrotik_url }}');
        
        // Após 5 segundos, redirecionar para o MikroTik
        // O MikroTik vai capturar MAC/IP e redirecionar de volta para o site
        setTimeout(function() {
            console.log('✅ Redirecionando para MikroTik para captura de MAC/IP...');
            console.log('⏱️ Tempo decorrido:', Math.round((Date.now() - startTime) / 1000), 'segundos');
            
            // Redirecionar para o MikroTik
            // Ele vai capturar MAC/IP e redirecionar de volta com os parâmetros
            window.location.href = '{{ $mikrotik_url }}';
        }, SPLASH_DISPLAY_TIME);
        
        // Log de progresso a cada segundo
        const progressInterval = setInterval(function() {
            const elapsed = Math.round((Date.now() - startTime) / 1000);
            const remaining = Math.max(0, Math.round(SPLASH_DISPLAY_TIME / 1000) - elapsed);
            console.log('⏳ Tempo decorrido:', elapsed + 's', '| Restante:', remaining + 's');
            
            if (remaining === 0) {
                clearInterval(progressInterval);
            }
        }, 1000);
    </script>
</body>
</html>
