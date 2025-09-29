<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Tocantins - Conecte-se à Internet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $forceLogin = config('wifi.mikrotik.force_login_redirect', false);
        $skipLogin = request()->boolean('skip_login');
        $hasContext = request()->hasAny(['mac', 'mikrotik_mac', 'client_mac'])
            || request()->boolean('from_login')
            || request()->boolean('captive')
            || request()->boolean('from_router');
        $loginUrl = config('wifi.mikrotik.login_url', 'http://login.tocantinswifi.local/login');
    @endphp
    @if ($forceLogin && !$skipLogin && !$hasContext)
        <meta http-equiv="refresh" content="0;url={{ $loginUrl }}?dst={{ urlencode(request()->fullUrl()) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tocantins-gold': '#FFD700',
                        'tocantins-green': '#228B22',
                        'tocantins-light-cream': '#FFF8DC',
                        'tocantins-dark-green': '#006400',
                        'tocantins-light-yellow': '#FFE55C',
                        'tocantins-gray-green': '#2F4F2F',
                        'modern-purple': '#7C3AED',
                        'modern-cyan': '#06B6D4',
                        'modern-pink': '#EC4899'
                    },
                    fontFamily: {
                        'jakarta': ['Plus Jakarta Sans', 'sans-serif'],
                        'space': ['Space Grotesk', 'sans-serif']
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'gradient-x': 'gradient-x 15s ease infinite',
                        'gradient-y': 'gradient-y 15s ease infinite',
                        'gradient-xy': 'gradient-xy 15s ease infinite'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(255, 215, 0, 0.3); }
            50% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.6), 0 0 30px rgba(255, 215, 0, 0.4); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
        }
        @keyframes pulse-glow-scale {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 4px 15px rgba(34, 139, 34, 0.4);
            }
            50% { 
                transform: scale(1.08); 
                box-shadow: 0 12px 35px rgba(34, 139, 34, 0.8), 0 0 50px rgba(255, 215, 0, 0.7);
            }
        }
        @keyframes gradient-x {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes gradient-y {
            0%, 100% { background-position: 50% 0%; }
            50% { background-position: 50% 100%; }
        }
        @keyframes gradient-xy {
            0%, 100% { background-position: 0% 0%; }
            25% { background-position: 100% 0%; }
            50% { background-position: 100% 100%; }
            75% { background-position: 0% 100%; }
        }
        .animate-pulse-scale {
            animation: pulse-glow-scale 1s ease-in-out infinite !important;
        }
        .elegant-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(249, 250, 251, 0.95) 100%);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 
                        0 2px 8px rgba(0, 0, 0, 0.04),
                        inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        .elegant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(124, 58, 237, 0.5), 
                rgba(6, 182, 212, 0.5), 
                rgba(34, 139, 34, 0.5), 
                transparent);
            animation: gradient-x 3s ease infinite;
        }
        .floating-shapes::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 5%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
            filter: blur(40px);
        }
        .floating-shapes::after {
            content: '';
            position: absolute;
            bottom: 10%;
            right: 5%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.1) 0%, transparent 70%);
            animation: float 15s ease-in-out infinite reverse;
            filter: blur(40px);
        }
        .connect-button {
            background: linear-gradient(135deg, #10B981 0%, #059669 50%, #047857 100%);
            background-size: 200% 200%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4), 
                        0 1px 3px rgba(0, 0, 0, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: 0.025em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .connect-button:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5), 
                        0 3px 8px rgba(0, 0, 0, 0.15),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            background-position: 100% 100%;
        }
        .connect-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 3s infinite;
        }
        .connect-button::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(255, 215, 0, 0.1), transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .connect-button:hover::after {
            opacity: 1;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        .service-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(249, 250, 251, 0.9));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .service-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1), 
                        0 10px 20px rgba(124, 58, 237, 0.1);
            border-color: rgba(124, 58, 237, 0.3);
        }
        .service-card::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(45deg, #7C3AED, #06B6D4, #10B981);
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
            filter: blur(10px);
        }
        .service-card:hover::before {
            opacity: 0.3;
        }
        .glass-effect {
            background: linear-gradient(135deg, 
                        rgba(255, 255, 255, 0.7), 
                        rgba(255, 255, 255, 0.3));
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06),
                        inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            position: relative;
        }
        .glass-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                        transparent, 
                        rgba(255, 255, 255, 0.8), 
                        transparent);
        }
        
        /* Novos efeitos modernos */
        .modern-glow {
            box-shadow: 0 0 30px rgba(124, 58, 237, 0.2), 
                        0 0 60px rgba(6, 182, 212, 0.1);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #7C3AED, #06B6D4, #10B981);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        
        .neo-brutalist {
            border: 3px solid #000;
            box-shadow: 6px 6px 0 #000;
            transition: all 0.2s ease;
        }
        
        .neo-brutalist:hover {
            transform: translate(-2px, -2px);
            box-shadow: 8px 8px 0 #000;
        }
        
        .aurora-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #ffc0cb 75%, #667eea 100%);
            background-size: 400% 400%;
            animation: gradient-xy 15s ease infinite;
        }
    </style>
</head>
<body class="font-jakarta min-h-screen floating-shapes relative overflow-x-hidden bg-gradient-to-br from-gray-50 via-purple-50/20 to-cyan-50/20">
    <!-- Modern Background Pattern -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-white via-gray-50 to-white"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(124,58,237,0.1)_0%,transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(6,182,212,0.1)_0%,transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(16,185,129,0.05)_0%,transparent_70%)]"></div>
        
        <!-- Mesh gradient overlay -->
        <svg class="absolute inset-0 w-full h-full opacity-30" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center h-full">
            <div class="bg-white rounded-2xl p-8 text-center shadow-2xl border border-gray-100">
                <div class="relative">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-modern-purple mx-auto mb-4"></div>
                    <div class="absolute inset-0 animate-ping rounded-full h-12 w-12 border-2 border-modern-purple opacity-20 mx-auto"></div>
                </div>
                <p class="text-gray-800 font-semibold">Processando pagamento...</p>
                <p class="text-gray-500 text-sm mt-1">Por favor, aguarde</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 py-6 sm:py-8 max-w-6xl">
        
        <!-- Connection Status (Top) -->
        <div id="connection-status" class="bg-white rounded-xl p-4 mb-4 hidden">
            <div class="flex items-center justify-center">
                <div class="flex items-center text-tocantins-green">
                    <div class="w-3 h-3 bg-tocantins-green rounded-full mr-3"></div>
                    <span class="font-medium">Status: <span id="status-text">Verificando...</span></span>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button 
                id="manage-connection"
                class="text-tocantins-green font-medium text-sm hover:text-tocantins-dark-green transition-colors hidden"
            >
                ℹ️ Já conectado? Gerencie sua conexão
            </button>
        </div>

        <!-- Header -->
        <div class="text-center mb-10 animate-fade-in">
            <!-- Logo -->
            <div class="mb-6 flex justify-center">
                <div class="relative group">
                    <div class="absolute -inset-4 bg-gradient-to-r from-modern-purple/20 via-modern-cyan/20 to-tocantins-green/20 rounded-full blur-2xl opacity-70 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tocantins" class="relative mx-auto h-10 sm:h-12 md:h-14 w-auto drop-shadow-xl transform group-hover:scale-105 transition-transform duration-300">
                </div>
            </div>
            <div class="space-y-3">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold font-space">
                    <span class="bg-gradient-to-r from-modern-purple via-modern-cyan to-tocantins-green bg-clip-text text-transparent">
                        CONECTE-SE À INTERNET
                    </span>
                </h1>
                <p class="text-gray-600 font-medium text-base sm:text-lg">WiFi Tocantins Express</p>
                <div class="flex justify-center items-center gap-3 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-md px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <span class="text-yellow-500">⚡</span>
                        <span class="text-sm font-medium text-gray-700">Alta Velocidade</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-md px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <span class="text-green-500">🔒</span>
                        <span class="text-sm font-medium text-gray-700">100% Seguro</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-md px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <span class="text-blue-500">🚌</span>
                        <span class="text-sm font-medium text-gray-700">WiFi a Bordo</span>
                    </span>
                </div>
            </div>
        </div>

                 <!-- Mobile Layout -->
         <div class="lg:hidden space-y-6 mb-8">

             <!-- Payment Section - Mobile (Logo após serviços WiFi) -->
             <div class="space-y-6">
                 <!-- Price & Connect Button -->
                 <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-6 animate-slide-up  relative overflow-hidden">
                    


                     <div class="text-center mb-4 sm:mb-6">
                        <!-- Preço Original Riscado -->
                        <div class="mb-2">
                            <p class="text-gray-500 line-through text-xs sm:text-sm">De R$ 11,99</p>
                            <p class="text-red-600 font-bold text-xs">🎉 50% DE DESCONTO!</p>
                        </div>

                         <div class="bg-gradient-to-br from-tocantins-dark-green via-tocantins-green to-green-600 rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-xl border border-tocantins-gold/50 relative overflow-hidden">
                             
                            
                             <p class="text-white text-xs sm:text-sm font-semibold mb-1 sm:mb-2 relative z-10">🚌 Acesso Completo Durante a Viagem</p>
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <p class="text-white text-xl sm:text-2xl font-bold relative z-10">R$ 5,99</p>
                                <div class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ">
                                    -50%
                                </div>
                            </div>
                            <p class="text-green-100 text-xs relative z-10">✅ Internet ilimitada + Alta velocidade</p>
                         </div>

                        <!-- Benefícios -->
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-3 mb-4 border border-green-200">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-1">✅</span>
                                    <span class="text-gray-700">Conexão instantânea</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-1">✅</span>
                                    <span class="text-gray-700">Sem limite de dados</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-1">✅</span>
                                    <span class="text-gray-700">Velocidade máxima</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-1">✅</span>
                                    <span class="text-gray-700">Pagamento seguro</span>
                                </div>
                            </div>
                        </div>

                         
                         <button 
                             id="connect-btn" 
                             class="connect-button w-full text-white font-bold py-3 sm:py-4 px-4 sm:px-6 rounded-xl shadow-xl relative z-10 mb-3 animate-pulse-scale"
                         >
                             🚀 CONECTAR AGORA
                         </button>

                       
                    </div>
                     
                     <!-- Payment Methods -->
                     <div class="text-center">
                         <p class="text-tocantins-gray-green font-medium mb-4 text-xs sm:text-sm">💳 Pagamento rápido e seguro:</p>
                         <div class="flex justify-center">
                             <div class="flex items-center bg-gradient-to-r from-tocantins-light-cream to-white rounded-xl px-4 sm:px-6 py-2 sm:py-3 border border-tocantins-gold/40 shadow-lg">
                                 <span class="text-lg sm:text-xl mr-2 sm:mr-3 text-tocantins-green">📱</span>
                                 <span class="text-sm sm:text-lg font-bold text-tocantins-green">PIX</span>
                                <span class="text-xs text-gray-500 ml-2">Instantâneo</span>
                    </div>
                    </div>
                        
                        <!-- Confiança -->
                        <div class="mt-4 flex justify-center items-center space-x-4 text-xs text-gray-500">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-1">🔒</span>
                                <span>Pagamento Seguro</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-blue-500 mr-1">⚡</span>
                                <span>Conexão Imediata</span>
                            </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Card - Mobile (Movida para após pagamento) -->
            <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-6 animate-slide-up relative overflow-hidden">
                <!-- Welcome Section -->
                <div class="text-center mb-8">
                    <h2 class="text-base sm:text-lg font-bold text-tocantins-gray-green mb-4 gradient-text">
                        Bem-vindo ao WiFi a bordo! 🚌
                    </h2>
                    
                    <!-- Service Info -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-6 mb-8">
                        <div class="service-card text-center glass-effect rounded-2xl p-3 sm:p-4 shadow-xl border border-tocantins-gold/40 modern-glow floating-animation relative group">
                            <div class="text-2xl sm:text-3xl mb-2 text-tocantins-green transform group-hover:scale-110 transition-transform duration-300">📶</div>
                            <p class="text-xs sm:text-sm text-tocantins-gray-green font-semibold mb-1">Velocidade</p>
                            <p class="text-sm sm:text-base font-bold gradient-text">100+ Mbps</p>
                            <div class="absolute inset-0 bg-gradient-to-r from-tocantins-green/10 to-tocantins-gold/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="service-card text-center glass-effect rounded-2xl p-3 sm:p-4 shadow-xl border border-tocantins-gold/40 modern-glow floating-animation relative group" style="animation-delay: 1s;">
                            <div class="text-2xl sm:text-3xl mb-2 text-tocantins-green transform group-hover:scale-110 transition-transform duration-300">⏱️</div>
                            <p class="text-xs sm:text-sm text-tocantins-gray-green font-semibold mb-1">Durante</p>
                            <p class="text-sm sm:text-base font-bold gradient-text">A Viagem</p>
                            <div class="absolute inset-0 bg-gradient-to-r from-tocantins-green/10 to-tocantins-gold/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="service-card text-center glass-effect rounded-2xl p-3 sm:p-4 shadow-xl border border-tocantins-gold/40 modern-glow floating-animation relative group" style="animation-delay: 2s;">
                            <div class="text-2xl sm:text-3xl mb-2 text-tocantins-green transform group-hover:scale-110 transition-transform duration-300">🔒</div>
                            <p class="text-xs sm:text-sm text-tocantins-gray-green font-semibold mb-1">Conexão</p>
                            <p class="text-sm sm:text-base font-bold gradient-text">Segura</p>
                            <div class="absolute inset-0 bg-gradient-to-r from-tocantins-green/10 to-tocantins-gold/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                    </div>
                </div>
            </div>

             </div>

             <!-- WhatsApp Cards - Mobile (Por último) -->
             <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-5 animate-slide-up relative overflow-hidden">
                 <div class="grid grid-cols-2 gap-2 mb-4">
                     <!-- Card Passagens Compacto -->
                     <button 
                         onclick="openPassagensModal()" 
                         class="glass-effect rounded-xl p-2 shadow-lg border border-blue-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center w-full"
                     >
                         <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 mb-1">
                             <div class="text-lg mb-1">🎫</div>
                             <p class="text-white text-xs font-bold">PASSAGENS</p>
                         </div>
                         <p class="text-xs text-tocantins-gray-green">Compre sua passagem</p>
                     </button>

                     <!-- Card Turismo Compacto -->
                     <button 
                         onclick="openTurismoModal()" 
                         class="glass-effect rounded-xl p-2 shadow-lg border border-orange-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center w-full"
                     >
                         <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-2 mb-1">
                             <div class="text-lg mb-1">🏖️</div>
                             <p class="text-white text-xs font-bold">TURISMO</p>
                         </div>
                         <p class="text-xs text-tocantins-gray-green">Alugue conosco</p>
                     </button>
                 </div>
             </div>
         </div>

         <!-- Desktop Layout -->
         <div class="hidden lg:grid lg:grid-cols-2 gap-6 lg:gap-12 mb-8">
             
             <!-- Left Column - Main Card -->
             <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-6 lg:p-10 animate-slide-up relative overflow-hidden">
                 
                 <!-- Welcome Section -->
            <div class="text-center mb-6">
                     <h2 class="text-sm sm:text-base font-bold text-tocantins-gray-green mb-3">
                         Bem-vindo ao WiFi a bordo! 🚌
                     </h2>
                     
                     <!-- Service Info -->
                     <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">
                         <div class="service-card text-center glass-effect rounded-xl p-2 sm:p-3 shadow-lg border border-tocantins-gold/30">
                             <div class="text-lg sm:text-xl mb-1 text-tocantins-green">📶</div>
                             <p class="text-xs text-tocantins-gray-green font-medium">Velocidade</p>
                             <p class="text-xs sm:text-sm font-bold text-tocantins-green">100+ Mbps</p>
                         </div>
                         <div class="service-card text-center glass-effect rounded-xl p-2 sm:p-3 shadow-lg border border-tocantins-gold/30">
                             <div class="text-lg sm:text-xl mb-1 text-tocantins-green">⏱️</div>
                             <p class="text-xs text-tocantins-gray-green font-medium">Durante</p>
                             <p class="text-xs sm:text-sm font-bold text-tocantins-green">A Viagem</p>
                         </div>
                         <div class="service-card text-center glass-effect rounded-xl p-2 sm:p-3 shadow-lg border border-tocantins-gold/30">
                             <div class="text-lg sm:text-xl mb-1 text-tocantins-green">🔒</div>
                             <p class="text-xs text-tocantins-gray-green font-medium">Conexão</p>
                             <p class="text-xs sm:text-sm font-bold text-tocantins-green">Segura</p>
                         </div>
                     </div>
                 </div>

                 <!-- Serviços WhatsApp - Compactos -->
                 <div class="border-t border-tocantins-gold/30 pt-4 mb-6">
                     <div class="grid grid-cols-2 gap-2 mb-4">
                         
                         <!-- Card Passagens Compacto -->
                         <button 
                             onclick="openPassagensModal()" 
                             class="glass-effect rounded-xl p-2 shadow-lg border border-blue-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center w-full"
                         >
                             <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 mb-1">
                                 <div class="text-lg mb-1">🎫</div>
                                 <p class="text-white text-xs font-bold">PASSAGENS</p>
                             </div>
                             <p class="text-xs text-tocantins-gray-green">Compre sua passagem</p>
                         </button>

                         <!-- Card Turismo Compacto -->
                         <button 
                             onclick="openTurismoModal()" 
                             class="glass-effect rounded-xl p-2 shadow-lg border border-orange-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center w-full"
                         >
                             <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-2 mb-1">
                                 <div class="text-lg mb-1">🏖️</div>
                                 <p class="text-white text-xs font-bold">TURISMO</p>
                             </div>
                             <p class="text-xs text-tocantins-gray-green">Alugue conosco</p>
                         </button>
                </div>
            </div>

                 <!-- Voucher Section - Visível apenas no DESKTOP -->
                 <div class="border-t border-tocantins-gold/30 pt-6 mb-6">
                     <p class="text-center text-tocantins-gray-green font-medium mb-4 text-sm">🎫 Tem um código promocional?</p>
                     <div class="flex space-x-3">
                         <input 
                             type="text" 
                             id="voucher-code"
                             placeholder="Digite seu voucher"
                             class="flex-1 border-2 border-tocantins-green/50 rounded-lg px-3 py-2 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                         >
                         <button 
                             id="voucher-btn"
                             class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white font-bold px-4 py-2 rounded-lg hover:from-tocantins-dark-green hover:to-tocantins-green transition-all shadow-lg text-sm whitespace-nowrap"
                         >
                             OK
                         </button>
                     </div>
                 </div>
                </div>
             
             <!-- Right Column - Payment Options -->
             <div class="space-y-6">
                
                <!-- Price & Connect Button (PRIMEIRO) -->
                <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-6 animate-slide-up  relative overflow-hidden">
                    


                    <div class="text-center mb-4 sm:mb-6">
                        <!-- Preço Original Riscado Desktop -->
                        <div class="mb-2">
                            <p class="text-gray-500 line-through text-sm">De R$ 11,99</p>
                            <p class="text-red-600 font-bold text-xs">🎉 50% DE DESCONTO!</p>
                        </div>

                        <div class="bg-gradient-to-br from-tocantins-dark-green via-tocantins-green to-green-600 rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-xl border border-tocantins-gold/50 relative overflow-hidden">
                            
                            
                            <p class="text-white text-sm font-semibold mb-2 relative z-10">🚌 Acesso Completo Durante a Viagem</p>
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <p class="text-white text-2xl font-bold relative z-10">R$ 5,99</p>
                                <div class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ">
                                    -50%
                                </div>
                            </div>
                            <p class="text-green-100 text-sm relative z-10">✅ Internet ilimitada + Alta velocidade</p>
                        </div>

                        <!-- Benefícios Desktop -->
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-4 mb-4 border border-green-200">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-2">✅</span>
                                    <span class="text-gray-700">Conexão instantânea</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-2">✅</span>
                                    <span class="text-gray-700">Sem limite de dados</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-2">✅</span>
                                    <span class="text-gray-700">Velocidade máxima</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-2">✅</span>
                                    <span class="text-gray-700">Pagamento seguro</span>
                                </div>
                            </div>
                        </div>

                        
                        <button id="connect-btn-desktop" class="connect-button w-full text-white font-bold py-4 px-6 rounded-xl shadow-xl relative z-10 mb-3 animate-pulse-scale">
                            🚀 CONECTAR AGORA!
                </button>
            </div>

            <!-- Payment Methods -->
                    <div class="text-center">
                        <p class="text-tocantins-gray-green font-medium mb-4 text-sm">💳 Pagamento rápido e seguro:</p>
                <div class="flex justify-center">
                            <div class="flex items-center bg-gradient-to-r from-tocantins-light-cream to-white rounded-xl px-6 py-3 border border-tocantins-gold/40 shadow-lg">
                                <span class="text-xl mr-3 text-tocantins-green">📱</span>
                                <span class="text-lg font-bold text-tocantins-green">PIX</span>
                                <span class="text-sm text-gray-500 ml-2">Instantâneo</span>
                            </div>
                        </div>
                        
                        <!-- Confiança Desktop -->
                        <div class="mt-4 flex justify-center items-center space-x-6 text-sm text-gray-500">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">🔒</span>
                                <span>Pagamento Seguro</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-blue-500 mr-2">⚡</span>
                                <span>Conexão Imediata</span>
                            </div>
                        </div>
                    </div>
                </div>

                
                </div>
            </div>

        <!-- Voucher Section - Visível apenas no CELULAR/TABLET -->
        <div class="lg:hidden elegant-card rounded-3xl shadow-2xl p-5 mb-6 animate-slide-up relative overflow-hidden">
            <div class="text-center">
                <p class="text-tocantins-gray-green font-medium mb-4 text-sm">🎫 Tem um código promocional?</p>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <input 
                        type="text" 
                        id="voucher-code-mobile"
                        placeholder="Digite seu voucher"
                        class="flex-1 border-2 border-tocantins-green/50 rounded-lg px-3 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                    >
                    <button 
                        id="voucher-btn-mobile"
                        class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white font-bold px-6 py-3 rounded-lg hover:from-tocantins-dark-green hover:to-tocantins-green transition-all shadow-lg text-sm whitespace-nowrap"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Registration Modal -->
    <div id="registration-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="elegant-card rounded-3xl p-6 sm:p-8 md:p-10 w-full max-w-sm sm:max-w-md animate-slide-up shadow-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-tocantins-gray-green">📋 Cadastro Rápido</h3>
                    <button id="close-registration-modal" class="text-gray-400 hover:text-gray-600 text-xl sm:text-2xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-6">
                    <div class="text-3xl sm:text-4xl mb-2">🚌</div>
                    <p class="text-sm text-gray-600">Preencha seus dados para continuar</p>
                </div>
                
                <form id="registration-form" class="space-y-4">
                    <div id="registration-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
                    
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-tocantins-gray-green mb-2">Nome Completo *</label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="name" 
                            required
                            placeholder="Digite seu nome completo"
                            class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                        >
                    </div>
                    
                    <div>
                        <label for="user_email" class="block text-sm font-medium text-tocantins-gray-green mb-2">E-mail *</label>
                        <input 
                            type="email" 
                            id="user_email" 
                            name="email" 
                            required
                            placeholder="seu@email.com"
                            class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                        >
                    </div>
                    
                    <div>
                        <label for="user_phone" class="block text-sm font-medium text-tocantins-gray-green mb-2">Telefone *</label>
                        <input 
                            type="tel" 
                            id="user_phone" 
                            name="phone" 
                            required
                            placeholder="(XX) X XXXX-XXXX"
                            maxlength="16"
                            class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                        >
                    </div>
                    
                    <div id="password-fields">
                        <div>
                            <label for="user_password" class="block text-sm font-medium text-tocantins-gray-green mb-2">
                                Senha * 
                                <span id="password-helper" class="text-xs text-gray-500 hidden">(deixe em branco para manter a atual)</span>
                            </label>
                            <input 
                                type="password" 
                                id="user_password" 
                                name="password" 
                                placeholder="Mínimo 6 caracteres"
                                minlength="6"
                                class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                            >
                        </div>
                        
                        <div>
                            <label for="user_password_confirmation" class="block text-sm font-medium text-tocantins-gray-green mb-2">Confirmar Senha *</label>
                            <input 
                                type="password" 
                                id="user_password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Repita a senha"
                                minlength="6"
                                class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80"
                            >
                        </div>
                    </div>
                    
                    <button 
                        type="submit" 
                        id="registration-submit-btn"
                        class="connect-button w-full text-white font-bold py-4 rounded-xl shadow-xl transform transition hover:scale-105 active:scale-95 text-sm relative z-10"
                    >
                        ✅ CONTINUAR PARA PAGAMENTO
                    </button>
                </form>
                
                <p class="text-center text-xs text-gray-500 mt-4">
                    🔒 Seus dados estão seguros conosco
                </p>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-60 z-40 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-3xl p-6 sm:p-8 md:p-10 w-full max-w-sm sm:max-w-md md:max-w-lg animate-slide-up shadow-2xl border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base sm:text-lg md:text-xl font-bold text-tocantins-gray-green">💳 Pagamento PIX</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600 text-xl sm:text-2xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-4">
                    <div class="text-3xl sm:text-4xl md:text-5xl mb-2">📱</div>
                    <h4 class="text-base sm:text-lg md:text-xl font-bold text-gray-800 mb-1">PIX Instantâneo</h4>
                    <p class="text-xs sm:text-sm text-gray-600">Aprovação em segundos</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 via-emerald-50 to-green-50 rounded-2xl p-3 sm:p-4 md:p-6 mb-4 border border-green-200 shadow-inner">
                    <p class="text-center text-xl sm:text-2xl md:text-3xl font-bold text-green-700">R$ 0,05</p>
                    <p class="text-center text-xs sm:text-sm text-gray-600">Internet durante toda a viagem</p>
                </div>
                
                <button data-payment="pix" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 sm:py-4 rounded-2xl hover:from-green-600 hover:to-emerald-700 transition-all transform hover:scale-105 shadow-lg hover:shadow-xl text-sm sm:text-base">
                    🚀 PAGAR AGORA
                </button>
                
                <p class="text-center text-xs text-gray-500 mt-3">
                    ✅ Pagamento seguro e instantâneo
                </p>
            </div>
        </div>
    </div>

    <!-- Modal de Passagens -->
    <div id="passagensModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="elegant-card rounded-3xl p-6 sm:p-8 w-full max-w-lg animate-slide-up shadow-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl sm:text-2xl font-bold gradient-text flex items-center">
                        <span class="text-3xl mr-3">🎫</span>
                        Passagens Rodoviárias
                    </h3>
                    <button onclick="closePassagensModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-6">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 mb-6 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 animate-pulse"></div>
                        <h4 class="text-lg sm:text-xl font-bold mb-3 relative z-10">
                            🚌 Compre sua Passagem SEM FILA!
                        </h4>
                        <p class="text-blue-100 text-sm sm:text-base relative z-10">
                            ⚡ Atendimento rápido e direto pelo WhatsApp
                        </p>
                    </div>
                    
                    <div class="space-y-4 text-left">
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <h5 class="font-bold text-green-800 mb-2">✅ Vantagens Exclusivas:</h5>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• ⏰ <strong>Sem enfrentar filas</strong> - Compre direto pelo WhatsApp</li>
                                <li>• 💺 <strong>Escolha seu assento</strong> preferido</li>
                                <li>• 📱 <strong>Pagamento instantâneo</strong> via PIX</li>
                                <li>• 📧 <strong>Bilhete digital</strong> enviado na hora</li>
                                <li>• 🎯 <strong>Atendimento personalizado</strong></li>
                            </ul>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <p class="text-blue-800 text-sm font-semibold text-center">
                                🛡️ <strong>Segurança Garantida:</strong> Empresa licenciada pela ANTT
                            </p>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-4">
                            <p class="text-yellow-800 text-sm font-bold text-center">
                                ⚠️ <strong>Últimas Vagas!</strong> Garante já sua passagem
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="https://wa.me/556384962118?text=Olá!%20Quero%20comprar%20uma%20passagem%20rodoviária.%20Pode%20me%20ajudar?" 
                       target="_blank"
                       class="w-full bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white font-bold py-4 px-6 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 text-base modern-glow relative overflow-hidden group inline-block">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
                        <span class="relative z-10 flex items-center justify-center space-x-2">
                            <span class="text-2xl">📱</span>
                            <span>COMPRAR AGORA VIA WHATSAPP</span>
                        </span>
                    </a>
                    
                    <p class="text-xs text-gray-500 mt-3">
                        🔒 Atendimento seguro e rápido • Disponível 24h
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Turismo -->
    <div id="turismoModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="elegant-card rounded-3xl p-6 sm:p-8 w-full max-w-lg animate-slide-up shadow-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl sm:text-2xl font-bold gradient-text flex items-center">
                        <span class="text-3xl mr-3">🏖️</span>
                        Turismo & Fretamento
                    </h3>
                    <button onclick="closeTurismoModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-6">
                    <div class="bg-gradient-to-r from-orange-500 to-amber-600 rounded-2xl p-6 mb-6 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 animate-pulse"></div>
                        <h4 class="text-lg sm:text-xl font-bold mb-3 relative z-10">
                            🚌 Alugue Ônibus para suas Viagens!
                        </h4>
                        <p class="text-orange-100 text-sm sm:text-base relative z-10">
                            ✨ Transforme sua viagem em uma experiência inesquecível
                        </p>
                    </div>
                    
                    <div class="space-y-4 text-left">
                        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg">
                            <h5 class="font-bold text-amber-800 mb-2">🌟 Serviços Disponíveis:</h5>
                            <ul class="text-sm text-amber-700 space-y-1">
                                <li>• 🎉 <strong>Excursões turísticas</strong> para destinos incríveis</li>
                                <li>• 👰 <strong>Casamentos e eventos</strong> especiais</li>
                                <li>• 🏢 <strong>Viagens corporativas</strong> e empresariais</li>
                                <li>• 🎓 <strong>Formatura e confraternizações</strong></li>
                                <li>• 🏖️ <strong>Feriados e fins de semana</strong> únicos</li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 border border-green-300 rounded-xl p-4">
                            <h5 class="font-bold text-green-800 mb-2">💎 Diferenciais Premium:</h5>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• ❄️ <strong>Ar condicionado</strong> e WiFi gratuito</li>
                                <li>• 🛋️ <strong>Poltronas reclináveis</strong> ultra confortáveis</li>
                                <li>• 🎬 <strong>Entretenimento a bordo</strong></li>
                                <li>• 👨‍✈️ <strong>Motoristas experientes</strong> e certificados</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 border border-red-300 rounded-xl p-4">
                            <p class="text-red-800 text-sm font-bold text-center">
                                🔥 <strong>Oferta Especial!</strong> Consulte condições e descontos especiais
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="https://wa.me/5563984666184?text=Olá!%20Gostaria%20de%20alugar%20um%20ônibus%20para%20turismo.%20Pode%20me%20passar%20mais%20informações?" 
                       target="_blank"
                       class="w-full bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 text-white font-bold py-4 px-6 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 text-base modern-glow relative overflow-hidden group inline-block">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
                        <span class="relative z-10 flex items-center justify-center space-x-2">
                            <span class="text-2xl">🚌</span>
                            <span>SOLICITAR ORÇAMENTO AGORA</span>
                        </span>
                    </a>
                    
                    <p class="text-xs text-gray-500 mt-3">
                        🎯 Orçamento personalizado • Sem compromisso
                    </p>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Funções para controlar os modais de Passagens e Turismo
        function openPassagensModal() {
            document.getElementById('passagensModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePassagensModal() {
            document.getElementById('passagensModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openTurismoModal() {
            document.getElementById('turismoModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTurismoModal() {
            document.getElementById('turismoModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Fechar modais clicando fora deles
        document.getElementById('passagensModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePassagensModal();
            }
        });

        document.getElementById('turismoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTurismoModal();
            }
        });

        // Fechar modais com a tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePassagensModal();
                closeTurismoModal();
            }
        });
    </script>
    <script src="{{ asset('js/mac-detector.js') }}"></script>
    <script src="{{ asset('js/portal.js') }}"></script>
</body>
</html>
