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
                <div class="mb-6">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tocantins" class="mx-auto h-24 w-auto drop-shadow-lg">
                </div>
                <h2 class="text-3xl font-bold text-tocantins-gray-green mb-2">
                    🔐 Área Administrativa
                </h2>
                <p class="text-sm text-gray-600">
                    WiFi Tocantins Express - Painel de Controle
                </p>
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
            <div class="glass-effect rounded-3xl shadow-2xl p-8 animate-slide-up">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-tocantins-gray-green mb-2">
                            📧 E-mail
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            value="{{ old('email') }}"
                            placeholder="seu@email.com"
                            class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80 @error('email') border-red-500 @enderror"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-tocantins-gray-green mb-2">
                            🔒 Senha
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            placeholder="Sua senha"
                            class="w-full border-2 border-tocantins-green/50 rounded-lg px-4 py-3 focus:outline-none focus:border-tocantins-gold focus:ring-2 focus:ring-tocantins-gold/20 transition-all text-sm bg-white/80 @error('password') border-red-500 @enderror"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="h-4 w-4 text-tocantins-green focus:ring-tocantins-gold border-tocantins-green/50 rounded"
                            >
                            <label for="remember" class="ml-2 block text-sm text-tocantins-gray-green">
                                Lembrar-me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white font-bold py-4 px-6 rounded-xl shadow-xl transform transition hover:scale-105 active:scale-95 hover:shadow-2xl text-sm relative overflow-hidden"
                        >
                            <span class="relative z-10">🚀 ENTRAR NO PAINEL</span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform skew-x-12 -translate-x-full hover:translate-x-full transition-transform duration-700"></div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informações de Acesso -->
            <div class="glass-effect rounded-2xl p-6 text-center animate-fade-in">
                <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">ℹ️ Informações de Acesso</h3>
                <div class="space-y-2 text-xs text-gray-600">
                    <p><strong>👑 Administrador:</strong> Acesso completo ao sistema</p>
                    <p><strong>👤 Gestor:</strong> Relatórios e monitoramento</p>
                </div>
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
