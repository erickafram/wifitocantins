<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WiFi Tocantins Admin</title>
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
                        'fade-in': 'fadeIn 0.8s ease-in',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'float': 'float 6s ease-in-out infinite'
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
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .floating-shapes::before {
            content: '🚌';
            position: absolute;
            top: 15%;
            left: 10%;
            font-size: 3rem;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite;
        }
        .floating-shapes::after {
            content: '📶';
            position: absolute;
            bottom: 20%;
            right: 10%;
            font-size: 2rem;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite reverse;
        }
    </style>
</head>
<body class="font-inter bg-gradient-to-br from-tocantins-light-cream via-white to-tocantins-light-yellow min-h-screen floating-shapes relative overflow-x-hidden">
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 animate-slide-up">
            
            <!-- Header -->
            <div class="text-center">
                <div class="mb-8">
                    <div class="relative inline-block">
                        <div class="absolute -inset-2 bg-gradient-to-r from-tocantins-green to-tocantins-gold rounded-full blur-lg opacity-30 animate-pulse"></div>
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Tocantins" class="relative mx-auto h-20 w-auto drop-shadow-2xl">
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-tocantins-gray-green mb-3">
                    🔐 Área Administrativa
                </h2>
                <p class="text-sm text-gray-500 mb-2">
                    WiFi Tocantins Express
                </p>
                <div class="inline-flex items-center gap-2 bg-tocantins-green/10 px-4 py-2 rounded-full">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-tocantins-gray-green font-medium">Sistema Online</span>
                </div>
            </div>

            <!-- Alertas -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md animate-fade-in">
                    <div class="flex items-center">
                        <span class="text-lg mr-2">⚠️</span>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md animate-fade-in">
                    <div class="flex items-center">
                        <span class="text-lg mr-2">✅</span>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Formulário de Login -->
            <div class="glass-effect rounded-2xl shadow-2xl p-6 animate-slide-up">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    
                    <div class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                autocomplete="email" 
                                required 
                                value="{{ old('email') }}"
                                placeholder="E-mail de acesso"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-tocantins-gold/50 focus:border-tocantins-gold transition-all text-sm bg-white/90 @error('email') border-red-400 @enderror"
                            >
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                autocomplete="current-password" 
                                required 
                                placeholder="Senha"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-tocantins-gold/50 focus:border-tocantins-gold transition-all text-sm bg-white/90 @error('password') border-red-400 @enderror"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="remember"
                                class="h-4 w-4 text-tocantins-green focus:ring-tocantins-gold border-gray-300 rounded"
                            >
                            <span class="ml-2 text-sm text-gray-600">Lembrar-me</span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white font-semibold py-3 px-6 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] hover:shadow-xl relative overflow-hidden group"
                    >
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                        <span class="relative flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            ENTRAR
                        </span>
                    </button>
                </form>
            </div>


            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-tocantins-gray-green/70">
                    ©2025 WiFi Tocantins Express - Todos os direitos reservados
                </p>
                <p class="text-xs text-tocantins-gray-green/50 mt-1">
                    Desenvolvido por Érick Vinicius
                </p>
            </div>
        </div>
    </div>

    <!-- Script para melhorar UX -->
    <script>
        // Auto-focus no primeiro campo
        document.getElementById('email').focus();

        // Feedback visual no formulário
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="relative z-10">⏳ ENTRANDO...</span>';
            submitBtn.disabled = true;
        });

        // Animação suave nos campos de input
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('transform', 'scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('transform', 'scale-105');
            });
        });
    </script>
</body>
</html>
