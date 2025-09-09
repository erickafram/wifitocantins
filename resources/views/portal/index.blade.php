<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Tocantins - Conecte-se à Internet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        'tocantins-gray-green': '#2F4F2F'
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif']
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-up': 'slideUp 0.6s ease-out'
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
        .animate-pulse-scale {
            animation: pulse-glow-scale 1s ease-in-out infinite !important;
        }
        .elegant-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 248, 220, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 8px 25px rgba(34, 139, 34, 0.1);
            position: relative;
        }
        .elegant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.5), transparent);
        }
        .floating-shapes::before {
            content: '🚌';
            position: absolute;
            top: 15%;
            left: 5%;
            font-size: 2rem;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite;
        }
        .floating-shapes::after {
            content: '📶';
            position: absolute;
            bottom: 15%;
            right: 5%;
            font-size: 1.5rem;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite reverse;
        }
        .connect-button {
            background: linear-gradient(135deg, #228B22 0%, #006400 30%, #228B22 70%, #20B2AA 100%);
            background-size: 300% 300%;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(34, 139, 34, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        .connect-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(34, 139, 34, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            background-position: 100% 50%;
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
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        .service-card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 20px 40px rgba(34, 139, 34, 0.2), 0 8px 20px rgba(255, 215, 0, 0.1);
        }
        .service-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, rgba(255, 215, 0, 0.1), rgba(34, 139, 34, 0.1));
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .service-card:hover::before {
            opacity: 1;
        }
        .glass-effect {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .glass-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
        }
        
        /* Novos efeitos modernos */
        .modern-glow {
            box-shadow: 0 0 20px rgba(34, 139, 34, 0.3), 0 0 40px rgba(255, 215, 0, 0.2);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #228B22, #006400, #FFD700);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="font-inter min-h-screen floating-shapes relative overflow-x-hidden bg-gradient-to-br from-slate-50 via-blue-50/30 to-emerald-50/40">
    <!-- Modern Background Pattern -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-tocantins-light-cream/80 via-white/90 to-tocantins-light-yellow/70"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_25%,rgba(34,139,34,0.05)_0%,transparent_50%)] "></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_75%_75%,rgba(255,215,0,0.05)_0%,transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.1)_25%,rgba(255,255,255,0.1)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.1)_75%)] bg-[length:20px_20px] opacity-30"></div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full">
            <div class="bg-white rounded-lg p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-tocantins-gold mx-auto mb-4"></div>
                <p class="text-tocantins-gray-green font-medium">Processando pagamento...</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 py-6 sm:py-8 max-w-6xl">
        
        <!-- Header -->
        <div class="text-center mb-8 animate-fade-in">
            <!-- Logo -->
            <div class="mb-6 flex justify-center">
                <div class="relative">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tocantins" class="mx-auto h-8 sm:h-10 md:h-12 w-auto drop-shadow-xl">
                    <div class="absolute -inset-2 bg-gradient-to-r from-tocantins-gold/20 to-tocantins-green/20 rounded-full blur-lg -z-10"></div>
                </div>
            </div>
            <div class="space-y-2">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold bg-gradient-to-r from-tocantins-gray-green via-tocantins-dark-green to-tocantins-green bg-clip-text text-transparent mb-3">
                    🌐 CONECTE-SE À INTERNET
                </h1>
                <p class="text-tocantins-green font-semibold text-sm sm:text-base tracking-wide">WiFi Tocantins Express</p>
                <div class="flex justify-center items-center space-x-2 text-xs sm:text-sm text-gray-600">
                    <span class="bg-white/80 backdrop-blur-sm px-3 py-1 rounded-full border border-tocantins-gold/30 shadow-sm">
                        ⚡ Alta Velocidade
                    </span>
                    <span class="bg-white/80 backdrop-blur-sm px-3 py-1 rounded-full border border-tocantins-gold/30 shadow-sm">
                        🔒 Seguro
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
                            <p class="text-gray-500 line-through text-xs sm:text-sm">De R$ 9,99</p>
                            <p class="text-red-600 font-bold text-xs">🎉 50% DE DESCONTO!</p>
                        </div>

                         <div class="bg-gradient-to-br from-tocantins-dark-green via-tocantins-green to-green-600 rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-xl border border-tocantins-gold/50 relative overflow-hidden">
                             
                            
                             <p class="text-white text-xs sm:text-sm font-semibold mb-1 sm:mb-2 relative z-10">🚌 Acesso Completo Durante a Viagem</p>
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <p class="text-white text-xl sm:text-2xl font-bold relative z-10">R$ 4,99</p>
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

                 <!-- OR Divider -->
                 <div class="text-center">
                     <div class="flex items-center">
                         <div class="flex-grow border-t border-tocantins-gold/40"></div>
                         <span class="flex-shrink mx-4 sm:mx-6 text-tocantins-gray-green font-medium text-xs sm:text-sm bg-tocantins-light-cream px-3 sm:px-4 py-1 rounded-full border border-tocantins-gold/30">OU</span>
                         <div class="flex-grow border-t border-tocantins-gold/40"></div>
                     </div>
                 </div>
                 
                 <!-- Free Instagram Option -->
                 <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-5 animate-slide-up relative overflow-hidden">
                     <div class="text-center mb-4">
                         <div class="bg-gradient-to-r from-tocantins-gold to-tocantins-light-yellow rounded-2xl p-3 sm:p-4 mb-4 shadow-lg border border-tocantins-gold/50 relative overflow-hidden">
                             <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -slow"></div>
                             <p class="text-tocantins-gray-green text-xs font-bold mb-1 relative z-10">🎁 GRÁTIS</p>
                             <p class="text-tocantins-gray-green text-xs mb-1 relative z-10">📸 Curta no Instagram e ganhe</p>
                             <p class="text-tocantins-gray-green text-xs sm:text-sm font-bold relative z-10">5 MIN GRÁTIS</p>
                         </div>
                         
                         <button 
                             id="instagram-btn-mobile" 
                             class="w-full bg-gradient-to-r from-tocantins-gold via-amber-400 to-tocantins-light-yellow text-tocantins-gray-green font-bold py-4 px-4 sm:px-5 rounded-2xl shadow-2xl transform transition-all duration-300 hover:scale-105 active:scale-95 hover:from-tocantins-light-yellow hover:to-tocantins-gold hover:shadow-2xl text-sm sm:text-base modern-glow relative overflow-hidden group"
                         >
                         <span class="relative z-10 flex items-center justify-center space-x-2">
                             <span class="text-lg">📸</span>
                             <span>Curtir Instagram</span>
                         </span>
                         <div class="absolute inset-0 bg-gradient-to-r from-pink-300/20 to-purple-300/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                 </button>
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
                            <p class="text-gray-500 line-through text-sm">De R$ 9,99</p>
                            <p class="text-red-600 font-bold text-xs">🎉 50% DE DESCONTO!</p>
                        </div>

                        <div class="bg-gradient-to-br from-tocantins-dark-green via-tocantins-green to-green-600 rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-xl border border-tocantins-gold/50 relative overflow-hidden">
                            
                            
                            <p class="text-white text-sm font-semibold mb-2 relative z-10">🚌 Acesso Completo Durante a Viagem</p>
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <p class="text-white text-2xl font-bold relative z-10">R$ 4,99</p>
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

                <!-- OR Divider -->
                <div class="text-center">
                    <div class="flex items-center">
                        <div class="flex-grow border-t border-tocantins-gold/40"></div>
                        <span class="flex-shrink mx-6 text-tocantins-gray-green font-medium text-sm bg-tocantins-light-cream px-4 py-1 rounded-full border border-tocantins-gold/30">OU</span>
                        <div class="flex-grow border-t border-tocantins-gold/40"></div>
                    </div>
                </div>

                <!-- Free Instagram Option (SEGUNDO) -->
                <div class="elegant-card rounded-3xl shadow-2xl p-5 animate-slide-up relative overflow-hidden">
                    <div class="text-center mb-4">
                        <div class="bg-gradient-to-r from-tocantins-gold to-tocantins-light-yellow rounded-2xl p-4 mb-4 shadow-lg border border-tocantins-gold/50 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -slow"></div>
                            <p class="text-tocantins-gray-green text-xs font-bold mb-1 relative z-10">🎁 GRÁTIS</p>
                            <p class="text-tocantins-gray-green text-xs mb-1 relative z-10">📸 Curta no Instagram e ganhe</p>
                            <p class="text-tocantins-gray-green text-sm font-bold relative z-10">5 MIN GRÁTIS</p>
                        </div>
                        
                        <button id="instagram-btn-desktop" class="w-full bg-gradient-to-r from-tocantins-gold to-tocantins-light-yellow text-tocantins-gray-green font-bold py-3 px-5 rounded-xl shadow-lg transform transition hover:scale-105 active:scale-95 hover:from-tocantins-light-yellow hover:to-tocantins-gold hover:shadow-xl text-sm">
                            📸 Curtir Instagram
                        </button>
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

        <!-- Connection Status -->
        <div id="connection-status" class="bg-white rounded-xl p-4 mb-4 hidden">
            <div class="flex items-center justify-center">
                <div class="flex items-center text-tocantins-green">
                    <div class="w-3 h-3 bg-tocantins-green rounded-full mr-3 "></div>
                    <span class="font-medium">Status: <span id="status-text">Verificando...</span></span>
                </div>
            </div>
        </div>

        <!-- Already Connected -->
        <div class="text-center">
            <button 
                id="manage-connection"
                class="text-tocantins-green font-medium text-sm hover:text-tocantins-dark-green transition-colors hidden"
            >
                ℹ️ Já conectado? Gerencie sua conexão
            </button>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-12 text-sm text-tocantins-gray-green glass-effect rounded-2xl p-6 shadow-xl border border-tocantins-gold/30">
            <p class="mb-3 text-sm">
                <a href="#" class="hover:text-tocantins-green transition-colors font-medium">Termos de Uso</a> • 
                <a href="#" class="hover:text-tocantins-green transition-colors font-medium">Política de Privacidade</a>
            </p>
            <p class="text-tocantins-green font-semibold text-sm">📞 Suporte: (63) 9992410056</p>
            <p class="text-xs text-tocantins-gray-green/70 mt-2">Desenvolvido por Érick Vinicius</p>
        </footer>
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
                    <p class="text-center text-xl sm:text-2xl md:text-3xl font-bold text-green-700">R$ 4,99</p>
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
    <script src="{{ asset('js/portal.js') }}"></script>
</body>
</html>
