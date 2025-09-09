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
        
        /* App Icons Animations */
        .app-icon {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .app-icon:hover {
            transform: translateY(-5px);
        }
        
        .app-icon:nth-child(1) { animation-delay: 0.1s; }
        .app-icon:nth-child(2) { animation-delay: 0.2s; }
        .app-icon:nth-child(3) { animation-delay: 0.3s; }
        .app-icon:nth-child(4) { animation-delay: 0.4s; }
        .app-icon:nth-child(5) { animation-delay: 0.5s; }
        .app-icon:nth-child(6) { animation-delay: 0.6s; }
        
        @keyframes appPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .app-icon:hover > div {
            animation: appPulse 1s infinite;
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

        <!-- Top Apps Block -->
        <div class="elegant-card rounded-3xl shadow-2xl p-4 sm:p-6 mb-8 animate-slide-up relative overflow-hidden">
            <div class="text-center mb-6">
                <h3 class="text-lg sm:text-xl font-bold gradient-text mb-2 flex items-center justify-center gap-2">
                    <span class="text-2xl">📱</span>
                    Top Apps para usar com WiFi
                </h3>
                <p class="text-sm sm:text-base text-tocantins-gray-green font-medium">
                    Conecte-se e aproveite o melhor da internet!
                </p>
            </div>
            
            <!-- Apps Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-4 sm:gap-6 mb-6">
                <!-- YouTube -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-100">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="#FF0000"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">YouTube</p>
                </div>

                <!-- WhatsApp -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-100">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488" fill="#25D366"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">WhatsApp</p>
                </div>

                <!-- Instagram -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-100">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <radialGradient id="instagram-gradient" cx="0.32" cy="1.08" r="1.5">
                                    <stop offset="0%" stop-color="#ffd53d"/>
                                    <stop offset="25%" stop-color="#ff4d4d"/>
                                    <stop offset="50%" stop-color="#c73ff4"/>
                                    <stop offset="100%" stop-color="#4168f7"/>
                                </radialGradient>
                            </defs>
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill="url(#instagram-gradient)"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">Instagram</p>
                </div>

                <!-- Netflix -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-black rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-800">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M5.398 0v.006c3.028 8.556 5.37 15.175 8.348 23.596 2.344.058 4.85.398 4.854.398-2.8-7.924-5.923-16.747-8.487-24zm8.489 0v9.63L18.6 22.951c-.043-7.86-.004-15.71.002-22.95zM5.398 1.05V24c1.873-.225 2.81-.312 4.715-.398v-9.22z" fill="#E50914"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">Netflix</p>
                </div>

                <!-- Spotify -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-black rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-800">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z" fill="#1DB954"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">Spotify</p>
                </div>

                <!-- TikTok -->
                <div class="app-icon group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-black rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transform group-hover:scale-110 transition-all duration-300 modern-glow border border-gray-800">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" fill="#000"/>
                            <path d="M9.03 12.97c-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36-.23 0-.46.08-.69.13" fill="#ff004f"/>
                            <path d="M16.435 5.99c1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-.69.02-1.37-.06-2.04-.21 1.54-.86 2.59-2.5 2.59-4.37 0-2.76-2.24-5-5-5-.69 0-1.35.14-1.95.39.43-2.34 2.09-4.3 4.25-5.07.66-.23 1.36-.35 2.07-.35 0 1.48-.04 2.96-.04 4.44.23-.05.46-.13.69-.13-.01-1.79.06-3.57-.07-5.36-.01-.39-.22-.73-.41-1.06l.01-4.91" fill="#00f2ea"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-700 font-medium mt-2 group-hover:text-tocantins-green transition-colors duration-300">TikTok</p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center">
                <div class="bg-gradient-to-r from-tocantins-green/10 to-tocantins-gold/10 rounded-2xl p-4 mb-4 border border-tocantins-gold/30">
                    <p class="text-sm sm:text-base text-tocantins-gray-green font-semibold mb-2">
                        🚀 Acesse todos esses apps com velocidade máxima!
                    </p>
                    <div class="flex flex-wrap justify-center gap-2 text-xs sm:text-sm">
                        <span class="bg-white/70 px-3 py-1 rounded-full border border-tocantins-green/30">📺 Streaming</span>
                        <span class="bg-white/70 px-3 py-1 rounded-full border border-tocantins-green/30">🎮 Gaming</span>
                        <span class="bg-white/70 px-3 py-1 rounded-full border border-tocantins-green/30">💬 Social</span>
                        <span class="bg-white/70 px-3 py-1 rounded-full border border-tocantins-green/30">🎵 Música</span>
                    </div>
                </div>
                
                <button onclick="document.getElementById('connect-btn').click()" class="bg-gradient-to-r from-tocantins-green via-tocantins-dark-green to-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 text-sm sm:text-base modern-glow">
                    ⚡ Conectar Agora e Aproveitar
                </button>
            </div>
        </div>

                 <!-- Mobile Layout -->
         <div class="lg:hidden space-y-6 mb-8">
             <!-- Welcome Card - Mobile -->
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
                     <a 
                         href="https://wa.me/556384962118" 
                         target="_blank"
                         class="glass-effect rounded-xl p-2 shadow-lg border border-blue-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center"
                     >
                         <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 mb-1">
                             <div class="text-lg mb-1">🎫</div>
                             <p class="text-white text-xs font-bold">PASSAGENS</p>
                         </div>
                         <p class="text-xs text-tocantins-gray-green">Compre sua passagem</p>
                     </a>

                     <!-- Card Turismo Compacto -->
                     <a 
                         href="https://wa.me/5563984666184" 
                         target="_blank"
                         class="glass-effect rounded-xl p-2 shadow-lg border border-orange-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center"
                     >
                         <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-2 mb-1">
                             <div class="text-lg mb-1">🏖️</div>
                             <p class="text-white text-xs font-bold">TURISMO</p>
                         </div>
                         <p class="text-xs text-tocantins-gray-green">Alugue conosco</p>
                     </a>
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
                         <a 
                             href="https://wa.me/556384962118" 
                             target="_blank"
                             class="glass-effect rounded-xl p-2 shadow-lg border border-blue-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center"
                         >
                             <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 mb-1">
                                 <div class="text-lg mb-1">🎫</div>
                                 <p class="text-white text-xs font-bold">PASSAGENS</p>
                             </div>
                             <p class="text-xs text-tocantins-gray-green">Compre sua passagem</p>
                         </a>

                         <!-- Card Turismo Compacto -->
                         <a 
                             href="https://wa.me/5563984666184"
                             target="_blank"
                             class="glass-effect rounded-xl p-2 shadow-lg border border-orange-300/30 hover:shadow-xl transform transition hover:scale-105 active:scale-95 text-center"
                         >
                             <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-2 mb-1">
                                 <div class="text-lg mb-1">🏖️</div>
                                 <p class="text-white text-xs font-bold">TURISMO</p>
                             </div>
                             <p class="text-xs text-tocantins-gray-green">Alugue conosco</p>
                         </a>
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


    <script src="{{ asset('js/portal.js') }}"></script>
</body>
</html>
