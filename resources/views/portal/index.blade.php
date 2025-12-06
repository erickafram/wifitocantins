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
        $loginUrl = config('wifi.mikrotik.login_url', 'http://10.5.50.1/login');
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
        @keyframes cta-pulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            }
            50% { 
                transform: scale(1.05); 
                box-shadow: 0 8px 30px rgba(16, 185, 129, 0.6), 0 0 40px rgba(20, 184, 166, 0.4);
            }
        }
        .animate-cta-pulse {
            animation: cta-pulse 1.5s ease-in-out infinite;
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
            pointer-events: none;
            z-index: -1;
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
            pointer-events: none;
            z-index: -1;
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

        <!-- Header - Compacto para Mobile -->
        <div class="text-center mb-6 lg:mb-10 animate-fade-in">
            <!-- Logo -->
            <div class="mb-3 lg:mb-6 flex justify-center">
                <div class="relative group">
                    <div class="absolute -inset-2 lg:-inset-4 bg-gradient-to-r from-modern-purple/20 via-modern-cyan/20 to-tocantins-green/20 rounded-full blur-xl lg:blur-2xl opacity-70"></div>
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tocantins" class="relative mx-auto h-8 sm:h-10 md:h-12 lg:h-14 w-auto drop-shadow-xl">
                </div>
            </div>
            <div class="space-y-2 lg:space-y-3">
                <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold font-space">
                    <span class="bg-gradient-to-r from-modern-purple via-modern-cyan to-tocantins-green bg-clip-text text-transparent">
                        CONECTE-SE À INTERNET
                    </span>
                </h1>
                <p class="text-gray-600 font-medium text-sm sm:text-base lg:text-lg">WiFi Tocantins Express</p>
                <!-- Badges - Ocultos em telas muito pequenas -->
                <div class="hidden sm:flex justify-center items-center gap-2 lg:gap-3 flex-wrap">
                    <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-gray-200 shadow-sm text-xs lg:text-sm">
                        <span class="text-yellow-500">⚡</span>
                        <span class="font-medium text-gray-700">Rápido</span>
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-gray-200 shadow-sm text-xs lg:text-sm">
                        <span class="text-green-500">🔒</span>
                        <span class="font-medium text-gray-700">Seguro</span>
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-gray-200 shadow-sm text-xs lg:text-sm">
                        <span class="text-blue-500">🚌</span>
                        <span class="font-medium text-gray-700">WiFi a Bordo</span>
                    </span>
                </div>
            </div>
        </div>

         <!-- Mobile Layout - Otimizado -->
         <div class="lg:hidden space-y-4 mb-6">

             <!-- Card Principal - Pagamento Mobile -->
             <div class="relative rounded-2xl overflow-hidden animate-slide-up group">
                 <!-- Borda gradiente animada -->
                 <div class="absolute -inset-[1px] bg-gradient-to-r from-emerald-500 via-cyan-500 to-emerald-500 rounded-2xl opacity-75 group-hover:opacity-100 blur-[2px] transition-opacity duration-500" style="background-size: 200% 200%; animation: gradient-x 3s ease infinite;"></div>
                 
                 <div class="relative bg-white rounded-2xl overflow-hidden">
                     <!-- Header com Preço - Design Glassmorphism -->
                     <div class="relative p-4 overflow-hidden">
                         <!-- Background com mesh gradient -->
                         <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700"></div>
                         <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.15)_0%,transparent_60%)]"></div>
                         <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.1)_0%,transparent_60%)]"></div>
                         
                         @if($discount_percentage > 0)
                         <!-- Badge de desconto moderno -->
                         <div class="absolute top-2 right-2 flex items-center gap-1">
                             <div class="relative">
                                 <div class="absolute inset-0 bg-red-500 rounded-full blur-md opacity-60 animate-pulse"></div>
                                 <div class="relative bg-gradient-to-r from-red-500 to-rose-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1">
                                     <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
                                     -{{ $discount_percentage }}%
                                 </div>
                             </div>
                         </div>
                         @endif
                         
                         <div class="relative text-center">
                             <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full mb-2">
                                 <span class="text-sm">🚌</span>
                                 <span class="text-white/95 text-xs font-medium">Internet na Viagem</span>
                             </div>
                             <div class="flex items-center justify-center gap-2">
                                 @if($discount_percentage > 0)
                                 <span class="text-white/50 text-sm line-through decoration-red-400">R$ {{ number_format($original_price, 2, ',', '.') }}</span>
                                 @endif
                                 <span class="text-white text-3xl font-bold tracking-tight drop-shadow-lg">R$ {{ number_format($price, 2, ',', '.') }}</span>
                             </div>
                         </div>
                     </div>

                     <!-- Corpo do Card -->
                     <div class="p-4 bg-gradient-to-b from-gray-50/50 to-white">
                         <!-- Ícones de Apps - Design 3D moderno -->
                         <div class="flex justify-center items-center gap-3 mb-3">
                             <!-- Instagram -->
                             <div class="group/icon relative cursor-pointer">
                                 <div class="absolute -inset-1 bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 rounded-2xl blur-lg opacity-50 group-hover/icon:opacity-90 transition-all duration-300 group-hover/icon:scale-110"></div>
                                 <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 p-[2px] shadow-lg transform group-hover/icon:scale-110 group-hover/icon:-rotate-6 transition-all duration-300">
                                     <div class="w-full h-full rounded-[14px] bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 flex items-center justify-center">
                                         <svg class="w-6 h-6 text-white drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                     </div>
                                 </div>
                             </div>
                             <!-- YouTube -->
                             <div class="group/icon relative cursor-pointer">
                                 <div class="absolute -inset-1 bg-red-600 rounded-2xl blur-lg opacity-50 group-hover/icon:opacity-90 transition-all duration-300 group-hover/icon:scale-110"></div>
                                 <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 p-[2px] shadow-lg transform group-hover/icon:scale-110 group-hover/icon:rotate-6 transition-all duration-300">
                                     <div class="w-full h-full rounded-[14px] bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                                         <svg class="w-6 h-6 text-white drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                     </div>
                                 </div>
                             </div>
                             <!-- Netflix -->
                             <div class="group/icon relative cursor-pointer">
                                 <div class="absolute -inset-1 bg-black rounded-2xl blur-lg opacity-40 group-hover/icon:opacity-70 transition-all duration-300 group-hover/icon:scale-110"></div>
                                 <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-gray-900 to-black p-[2px] shadow-lg transform group-hover/icon:scale-110 group-hover/icon:-rotate-6 transition-all duration-300 ring-1 ring-red-600/30">
                                     <div class="w-full h-full rounded-[14px] bg-black flex items-center justify-center">
                                         <span class="text-red-600 font-black text-lg tracking-tighter drop-shadow-md">N</span>
                                     </div>
                                 </div>
                             </div>
                             <!-- WhatsApp -->
                             <div class="group/icon relative cursor-pointer">
                                 <div class="absolute -inset-1 bg-green-500 rounded-2xl blur-lg opacity-50 group-hover/icon:opacity-90 transition-all duration-300 group-hover/icon:scale-110"></div>
                                 <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-green-400 to-green-600 p-[2px] shadow-lg transform group-hover/icon:scale-110 group-hover/icon:rotate-6 transition-all duration-300">
                                     <div class="w-full h-full rounded-[14px] bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                         <svg class="w-6 h-6 text-white drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         
                         <!-- Benefícios em linha - Pills modernas -->
                         <div class="flex justify-center gap-2 mb-4">
                             <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[11px] font-medium border border-emerald-200">
                                 <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                 Ilimitado
                             </span>
                             <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full text-[11px] font-medium border border-amber-200">
                                 <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                 Rápido
                             </span>
                             <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-[11px] font-medium border border-blue-200">
                                 <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                 Seguro
                             </span>
                         </div>

                         <!-- Urgency Badge -->
                         <div class="flex justify-center mb-3">
                             <div class="inline-flex items-center gap-1.5 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                                 <span class="relative flex h-2 w-2">
                                     <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                     <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                 </span>
                                 <span class="text-red-700 text-[11px] font-semibold">PROMOÇÃO LIMITADA</span>
                             </div>
                         </div>

                         <!-- Botão Principal - Pulsando -->
                         <div class="relative">
                             <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 rounded-xl blur-lg opacity-60 animate-pulse"></div>
                             <button 
                                 id="connect-btn" 
                                 class="animate-cta-pulse relative w-full bg-gradient-to-r from-emerald-500 via-green-500 to-teal-600 text-white font-bold py-4 px-4 rounded-xl shadow-xl text-lg flex items-center justify-center gap-2"
                             >
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                 CONECTAR AGORA
                             </button>
                         </div>

                         <!-- Trust indicators -->
                         <div class="mt-3 flex items-center justify-center gap-3 text-[10px] text-gray-500">
                             <span class="flex items-center gap-1">
                                 <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                 Seguro
                             </span>
                             <span class="flex items-center gap-1">
                                 <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                 Instantâneo
                             </span>
                             <span class="flex items-center gap-1">
                                 <div class="w-3 h-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-sm flex items-center justify-center">
                                     <span class="text-white text-[6px] font-bold">P</span>
                                 </div>
                                 PIX
                             </span>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- Botão Voucher Motorista - Compacto -->
             <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-3 border border-green-200 shadow-md">
                 <a 
                     href="{{ route('voucher.activate') }}{{ request()->has('mac') ? '?source=mikrotik&mac=' . request('mac') . '&ip=' . request('ip') : '' }}" 
                     class="flex items-center justify-between"
                 >
                     <div class="flex items-center gap-3">
                         <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow">
                             <span class="text-xl">🎫</span>
                         </div>
                         <div>
                             <p class="text-sm font-bold text-gray-800">Motorista?</p>
                             <p class="text-xs text-gray-500">Ative seu voucher aqui</p>
                         </div>
                     </div>
                     <div class="bg-green-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow">
                         Ativar →
                     </div>
                 </a>
             </div>

             <!-- Serviços - Grid 2x1 Compacto -->
             <div class="grid grid-cols-2 gap-2">
                 <button 
                    type="button"
                    onclick="openPassagensModal()" 
                    class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 text-center shadow-md active:scale-95 transition-transform"
                >
                    <div class="text-lg mb-0.5">🎫</div>
                    <p class="text-white text-[10px] font-bold">PASSAGENS</p>
                </button>
                <button 
                    type="button"
                    onclick="openTurismoModal()" 
                    class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-2 text-center shadow-md active:scale-95 transition-transform"
                >
                    <div class="text-lg mb-0.5">🏖️</div>
                    <p class="text-white text-[10px] font-bold">TURISMO</p>
                </button>
             </div>
         </div>

         <!-- Desktop - 2 Colunas -->
         <div class="hidden lg:grid lg:grid-cols-2 gap-6 mb-8">
             <!-- Left Column - Serviços -->
             <div class="space-y-4">
                 <!-- Serviços WiFi -->
                 <div class="elegant-card rounded-2xl shadow-xl p-5">
                     <h3 class="text-lg font-bold text-tocantins-green mb-3 flex items-center">
                         <span class="text-2xl mr-2">📡</span>
                         Serviços WiFi
                     </h3>
                     <div class="space-y-2 text-sm">
                         <div class="flex items-center p-2 bg-green-50 rounded-lg">
                             <span class="text-green-500 mr-2">✅</span>
                             <span class="text-gray-700">Internet ilimitada</span>
                         </div>
                         <div class="flex items-center p-2 bg-blue-50 rounded-lg">
                             <span class="text-blue-500 mr-2">⚡</span>
                             <span class="text-gray-700">Alta velocidade</span>
                         </div>
                         <div class="flex items-center p-2 bg-purple-50 rounded-lg">
                             <span class="text-purple-500 mr-2">🔒</span>
                             <span class="text-gray-700">Conexão segura</span>
                         </div>
                     </div>
                 </div>

                 <!-- WhatsApp Services Desktop -->
                 <div class="elegant-card rounded-2xl shadow-xl p-4">
                     <div class="grid grid-cols-2 gap-3">
                         <button 
                            type="button"
                            onclick="openPassagensModal()" 
                            class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-center hover:shadow-xl hover:scale-105 hover:-translate-y-1 transition-all duration-300 transform relative overflow-hidden group"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                            <div class="text-2xl mb-1 group-hover:scale-110 transition-transform duration-300">🎫</div>
                            <p class="text-white text-xs font-bold">PASSAGENS</p>
                        </button>
                        <button 
                            type="button"
                            onclick="openTurismoModal()" 
                            class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-3 text-center hover:shadow-xl hover:scale-105 hover:-translate-y-1 transition-all duration-300 transform relative overflow-hidden group"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                            <div class="text-2xl mb-1 group-hover:scale-110 transition-transform duration-300">🏖️</div>
                            <p class="text-white text-xs font-bold">TURISMO</p>
                        </button>
                     </div>
                 </div>

                 <!-- Botão para Motoristas (Desktop) -->
                 <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-2xl shadow-xl p-6 border-2 border-green-300">
                     <div class="flex items-center justify-between">
                         <div class="flex items-center gap-3">
                             <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                                 <span class="text-2xl">🎫</span>
                             </div>
                             <div>
                                 <p class="text-xs text-gray-600">Ative seu voucher aqui</p>
                             </div>
                         </div>
                         <a 
                             href="{{ route('voucher.activate') }}{{ request()->has('mac') ? '?source=mikrotik&mac=' . request('mac') . '&ip=' . request('ip') : '' }}" 
                             class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-2 px-5 rounded-xl shadow-lg transition transform hover:scale-105 flex items-center gap-2 text-sm"
                         >
                             <span>Ativar Voucher</span>
                             <span>→</span>
                         </a>
                     </div>
                 </div>
             </div>
             
             <!-- Right Column - Pagamento -->
             <div class="space-y-4">
                 <div class="elegant-card rounded-2xl shadow-xl p-5 animate-slide-up">
                     <div class="text-center">
                         <!-- Preço Desktop -->
                        <div class="bg-gradient-to-br from-tocantins-dark-green to-green-600 rounded-xl p-5 mb-4 shadow-lg relative overflow-hidden">
                            <!-- Badge de Desconto Dinâmico -->
                            @if($discount_percentage > 0)
                            <div class="absolute top-3 right-3 bg-red-500 text-white text-sm font-bold px-4 py-1.5 rounded-full shadow-lg animate-pulse">
                                -{{ $discount_percentage }}% OFF
                            </div>
                            @endif
                            
                            <p class="text-white text-sm font-medium mb-2">🚌 Acesso Durante a Viagem</p>
                            <div class="flex items-center justify-center gap-3">
                                @if($discount_percentage > 0)
                                <p class="text-white/70 text-lg line-through">R$ {{ number_format($original_price, 2, ',', '.') }}</p>
                                @endif
                                <p class="text-white text-3xl font-bold">R$ {{ number_format($price, 2, ',', '.') }}</p>
                            </div>
                        </div>

                         <!-- Benefícios Desktop -->
                         <div class="grid grid-cols-2 gap-2 mb-4 text-sm">
                             <div class="flex items-center justify-center bg-green-50 rounded-lg py-2">
                                 <span class="text-green-500 mr-1">✅</span>
                                 <span class="text-gray-700">Ilimitado</span>
                             </div>
                             <div class="flex items-center justify-center bg-green-50 rounded-lg py-2">
                                 <span class="text-green-500 mr-1">⚡</span>
                                 <span class="text-gray-700">Alta Velocidade</span>
                             </div>
                         </div>

                         <!-- Botão Desktop -->
                         <button 
                             id="connect-btn-desktop" 
                             class="connect-button w-full text-white font-bold py-4 px-6 rounded-xl shadow-xl mb-3"
                         >
                             🚀 CONECTAR AGORA!
                         </button>

                         <!-- PIX Desktop -->
                        <div class="flex items-center justify-center bg-gradient-to-r from-tocantins-light-cream to-white rounded-lg px-6 py-3 border border-tocantins-gold/40 mb-3">
                            <span class="text-xl mr-3 text-tocantins-green">📱</span>
                            <span class="text-lg font-bold text-tocantins-green">PIX</span>
                            <span class="text-sm text-gray-500 ml-2">Instantâneo</span>
                        </div>
                        
                        <!-- Acesso Imediato Desktop -->
                        <div class="flex items-center justify-center bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg px-6 py-3 border border-green-300/40">
                            <span class="text-xl mr-3">⚡</span>
                            <span class="text-lg font-bold text-green-700">Acesso Imediato</span>
                            <span class="text-sm text-gray-600 ml-2">Após Pagamento</span>
                        </div>
                     </div>
                 </div>
             </div>
         </div>


    </div>

    <!-- Registration Modal -->
    <div id="registration-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="elegant-card rounded-3xl p-6 sm:p-8 md:p-10 w-full max-w-sm sm:max-w-md animate-slide-up shadow-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-tocantins-gray-green">📱 Acesso Rápido</h3>
                    <button id="close-registration-modal" class="text-gray-400 hover:text-gray-600 text-xl sm:text-2xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-6">
                    <div class="text-4xl sm:text-5xl mb-3">🚀</div>
                    <p class="text-base font-semibold text-gray-700">Digite seu telefone para conectar</p>
                    <p class="text-xs text-gray-500 mt-1">Rápido e fácil - apenas 1 campo!</p>
                </div>
                
                <form id="registration-form" class="space-y-4">
                    <div id="registration-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
                    
                    <div>
                            <label for="user_phone" class="block text-sm font-medium text-tocantins-gray-green mb-2">📞 Telefone com DDD</label>
                            <input 
                                type="tel" 
                                id="user_phone" 
                                name="phone" 
                                required
                                placeholder="(63) 9 8101-3050"
                                maxlength="16"
                                autofocus
                                class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-4 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-lg bg-white/80 text-center font-semibold"
                            >
                    </div>
                    
                    <button 
                        type="submit" 
                        id="registration-submit-btn"
                        class="connect-button w-full text-white font-bold py-4 rounded-xl shadow-xl transform transition hover:scale-105 active:scale-95 text-base relative z-10"
                    >
                        📱 GERAR QR CODE PIX
                    </button>
                </form>
                
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-center text-xs text-green-700">
                        ✅ Pague via PIX e conecte instantaneamente!
                    </p>
                </div>
                
                <p class="text-center text-xs text-gray-400 mt-3">
                    🔒 Conexão segura • Liberação automática
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
                    <p class="text-center text-xl sm:text-2xl md:text-3xl font-bold text-green-700">R$ {{ number_format($price, 2, ',', '.') }}</p>
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
        <div class="flex items-center justify-center h-full p-3">
            <div class="elegant-card rounded-2xl p-4 sm:p-6 w-full max-w-sm sm:max-w-md animate-slide-up shadow-2xl relative overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base sm:text-lg font-bold gradient-text flex items-center">
                        <span class="text-xl mr-2">🎫</span>
                        Passagens Rodoviárias
                    </h3>
                    <button onclick="closePassagensModal()" class="text-gray-400 hover:text-gray-600 text-xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-4">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-3 mb-3 text-white shadow-lg relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 animate-pulse"></div>
                        <h4 class="text-sm sm:text-base font-bold mb-1 relative z-10">
                            🚌 Compre SEM FILA!
                        </h4>
                        <p class="text-blue-100 text-xs relative z-10">
                            ⚡ Direto pelo WhatsApp
                        </p>
                    </div>
                    
                    <div class="space-y-2 text-left">
                        <div class="bg-green-50 border-l-3 border-green-500 p-2.5 rounded-lg">
                            <h5 class="font-bold text-green-800 text-xs mb-1">✅ Vantagens:</h5>
                            <ul class="text-[11px] text-green-700 space-y-0.5">
                                <li>• ⏰ <strong>Sem filas</strong></li>
                                <li>• 💺 <strong>Escolha seu assento</strong></li>
                                <li>• 📱 <strong>PIX instantâneo</strong></li>
                                <li>• 📧 <strong>Bilhete digital</strong></li>
                            </ul>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                            <p class="text-blue-800 text-[11px] font-semibold text-center">
                                🛡️ Empresa licenciada ANTT
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="https://wa.me/556384962118?text=Olá!%20Quero%20comprar%20uma%20passagem%20rodoviária.%20Pode%20me%20ajudar?" 
                       target="_blank"
                       class="w-full bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white font-bold py-3 px-4 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 text-sm modern-glow relative overflow-hidden group inline-block">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <span class="text-lg">📱</span>
                            <span>COMPRAR VIA WHATSAPP</span>
                        </span>
                    </a>
                    
                    <p class="text-[10px] text-gray-500 mt-2">
                        🔒 Seguro • Disponível 24h
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Turismo -->
    <div id="turismoModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-3">
            <div class="elegant-card rounded-2xl p-4 sm:p-6 w-full max-w-sm sm:max-w-md animate-slide-up shadow-2xl relative overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base sm:text-lg font-bold gradient-text flex items-center">
                        <span class="text-xl mr-2">🏖️</span>
                        Turismo & Fretamento
                    </h3>
                    <button onclick="closeTurismoModal()" class="text-gray-400 hover:text-gray-600 text-xl transition-colors">×</button>
                </div>
                
                <div class="text-center mb-4">
                    <div class="bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl p-3 mb-3 text-white shadow-lg relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 animate-pulse"></div>
                        <h4 class="text-sm sm:text-base font-bold mb-1 relative z-10">
                            🚌 Alugue Ônibus!
                        </h4>
                        <p class="text-orange-100 text-xs relative z-10">
                            ✨ Viagem inesquecível
                        </p>
                    </div>
                    
                    <div class="space-y-2 text-left">
                        <div class="bg-amber-50 border-l-3 border-amber-500 p-2.5 rounded-lg">
                            <h5 class="font-bold text-amber-800 text-xs mb-1">🌟 Serviços:</h5>
                            <ul class="text-[11px] text-amber-700 space-y-0.5">
                                <li>• 🎉 <strong>Excursões</strong></li>
                                <li>• 👰 <strong>Casamentos</strong></li>
                                <li>• 🏢 <strong>Corporativo</strong></li>
                                <li>• 🎓 <strong>Formaturas</strong></li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 border border-green-300 rounded-lg p-2">
                            <h5 class="font-bold text-green-800 text-xs mb-1">💎 Diferenciais:</h5>
                            <ul class="text-[11px] text-green-700 space-y-0.5">
                                <li>• ❄️ <strong>Ar + WiFi</strong></li>
                                <li>• 🛋️ <strong>Poltronas reclináveis</strong></li>
                                <li>• 👨‍✈️ <strong>Motoristas certificados</strong></li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 border border-red-300 rounded-lg p-2">
                            <p class="text-red-800 text-[11px] font-bold text-center">
                                🔥 Descontos especiais!
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="https://wa.me/5563984666184?text=Olá!%20Gostaria%20de%20alugar%20um%20ônibus%20para%20turismo.%20Pode%20me%20passar%20mais%20informações?" 
                       target="_blank"
                       class="w-full bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 text-white font-bold py-3 px-4 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 text-sm modern-glow relative overflow-hidden group inline-block">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <span class="text-lg">🚌</span>
                            <span>SOLICITAR ORÇAMENTO</span>
                        </span>
                    </a>
                    
                    <p class="text-[10px] text-gray-500 mt-2">
                        🎯 Sem compromisso
                    </p>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Funções para controlar os modais de Passagens e Turismo
        function openPassagensModal() {
            console.log('openPassagensModal chamado');
            const modal = document.getElementById('passagensModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                console.error('Modal passagensModal não encontrado');
            }
        }

        function closePassagensModal() {
            const modal = document.getElementById('passagensModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        function openTurismoModal() {
            console.log('openTurismoModal chamado');
            const modal = document.getElementById('turismoModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                console.error('Modal turismoModal não encontrado');
            }
        }

        function closeTurismoModal() {
            const modal = document.getElementById('turismoModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Aguardar DOM carregar para adicionar event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Fechar modais clicando fora deles
            const passagensModal = document.getElementById('passagensModal');
            if (passagensModal) {
                passagensModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closePassagensModal();
                    }
                });
            }

            const turismoModal = document.getElementById('turismoModal');
            if (turismoModal) {
                turismoModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeTurismoModal();
                    }
                });
            }

            // Fechar modais com a tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closePassagensModal();
                    closeTurismoModal();
                }
            });

            // ===== VOUCHER SYSTEM =====
            function applyVoucher(inputId, buttonId) {
                const input = document.getElementById(inputId);
                const button = document.getElementById(buttonId);
                
                // Verificar se os elementos existem
                if (!input || !button) {
                    console.error('Elementos do voucher não encontrados:', { inputId, buttonId });
                    return;
                }

                const voucherCode = input.value.trim().toUpperCase();

                if (!voucherCode) {
                    alert('❌ Por favor, digite o código do voucher');
                    return;
                }

                // Desabilita botão e mostra loading
                button.disabled = true;
                button.innerHTML = '⏳';

                fetch('/api/voucher/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        voucher_code: voucherCode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Sucesso - mostra mensagem e redireciona
                        alert(`✅ ${data.message}\n\n` +
                              `🎫 Tipo: ${data.voucher_type === 'unlimited' ? 'Ilimitado' : 'Limitado'}\n` +
                              `⏰ Horas concedidas: ${data.hours_granted}h\n` +
                              `📅 Válido até: ${new Date(data.expires_at).toLocaleString('pt-BR')}\n` +
                              (data.voucher_type === 'limited' ? `⏱️ Horas restantes hoje: ${data.remaining_hours_today}h` : ''));
                        
                        // Redireciona para o Google após sucesso
                        setTimeout(() => {
                            window.location.href = 'https://www.google.com';
                        }, 2000);
                    } else {
                        alert(`❌ ${data.message}`);
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = 'OK';
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao validar voucher:', error);
                    alert('❌ Erro ao processar voucher. Tente novamente.');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = 'OK';
                    }
                });
            }

            // Event listeners para vouchers - com verificação de existência
            const applyVoucherMobile = document.getElementById('apply-voucher-mobile');
            if (applyVoucherMobile) {
                applyVoucherMobile.addEventListener('click', function() {
                    applyVoucher('voucher-code-mobile', 'apply-voucher-mobile');
                });
            }

            const applyVoucherDesktop = document.getElementById('apply-voucher-desktop');
            if (applyVoucherDesktop) {
                applyVoucherDesktop.addEventListener('click', function() {
                    applyVoucher('voucher-code-desktop', 'apply-voucher-desktop');
                });
            }

            // Permitir Enter para aplicar voucher
            ['voucher-code-mobile', 'voucher-code-desktop'].forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            const buttonId = inputId.replace('code', 'apply').replace('voucher-', 'apply-voucher-');
                            applyVoucher(inputId, buttonId);
                        }
                    });
                }
            });
        });
    </script>
    
    <!-- Configuração Global do Preço e Duração da Sessão -->
    <script>
        window.WIFI_PRICE = {{ $price }};
        window.SESSION_DURATION = {{ $session_duration ?? 12 }};
    </script>
    
    <script src="{{ asset('js/mac-detector.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/portal.js') }}?v={{ time() }}"></script>
</body>
</html>
