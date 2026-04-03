<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WiFi Tocantins Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: '#F8F9FA',
                        ink:     '#111111',
                        ink2:    '#333333',
                        muted:   '#888888',
                        border:  '#E5E5E5',
                        green: {
                            DEFAULT: '#00A335',
                            light:   '#00C040',
                            dark:    '#007A28',
                            pale:    '#E8F5E9',
                        },
                        red: {
                            DEFAULT: '#D32F2F',
                            pale:    '#FFEBEE',
                        },
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        card:  '0 1px 3px rgba(0,0,0,0.08)',
                        hover: '0 4px 12px rgba(0,0,0,0.10)',
                        modal: '0 20px 60px rgba(0,0,0,0.20)',
                    },
                    keyframes: {
                        fadeUp: {
                            '0%':   { opacity: '0', transform: 'translateY(24px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        pulse2: {
                            '0%,100%': { opacity: '1' },
                            '50%':     { opacity: '.4' },
                        },
                    },
                    animation: {
                        'fade-up': 'fadeUp .5s cubic-bezier(.22,1,.36,1) both',
                        'pulse2':  'pulse2 2s ease-in-out infinite',
                    },
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-surface min-h-screen flex items-center justify-center px-4 py-12">

    <!-- Background decorativo -->
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <!-- blob verde top-left -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-green/8 blur-3xl"></div>
        <!-- blob verde bottom-right -->
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-green-light/8 blur-3xl"></div>
        <!-- grid sutil -->
        <div class="absolute inset-0 opacity-[0.025]"
             style="background-image:linear-gradient(#00A335 1px,transparent 1px),linear-gradient(90deg,#00A335 1px,transparent 1px);background-size:40px 40px"></div>
    </div>

    <div class="relative w-full max-w-sm animate-fade-up">

        <!-- ── TÍTULO ── -->
        <div class="text-center mb-8">
            <!-- ícone satélite -->
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-green-dark to-green shadow-hover mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
            </div>

            <p class="text-[10px] font-bold uppercase tracking-widest text-green mb-1">Starlink</p>
            <h1 class="text-2xl font-bold text-ink leading-tight">Tocantins Transporte</h1>
            <p class="text-sm text-muted mt-0.5">WiFi Tocantins · Painel Administrativo</p>

            <!-- badge online -->
            <div class="inline-flex items-center gap-1.5 mt-3 bg-green-pale border border-green/20 px-3 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-green animate-pulse2"></span>
                <span class="text-[10px] font-semibold text-green uppercase tracking-wider">Sistema Online</span>
            </div>
        </div>

        <!-- ── CARD PRINCIPAL ── -->
        <div class="bg-white border border-border rounded-2xl shadow-modal p-6">

            <!-- Hero strip -->
            <div class="bg-gradient-to-r from-green-dark via-green to-green-light rounded-xl px-4 py-3 mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm leading-none">Área Restrita</p>
                    <p class="text-white/70 text-[10px] mt-0.5">Acesso somente para administradores</p>
                </div>
            </div>

            <!-- Alertas -->
            @if ($errors->any())
                <div class="flex items-start gap-2.5 bg-red-pale border border-red/20 rounded-xl px-3.5 py-3 mb-5">
                    <svg class="w-4 h-4 text-red flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="text-xs text-red font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="flex items-center gap-2.5 bg-green-pale border border-green/20 rounded-xl px-3.5 py-3 mb-5">
                    <svg class="w-4 h-4 text-green flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-xs text-green font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Formulário -->
            <form method="POST" action="{{ route('login') }}" id="login-form" class="space-y-4">
                @csrf

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">
                        E-mail
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input
                            id="email" name="email" type="email"
                            autocomplete="email" required
                            value="{{ old('email') }}"
                            placeholder="seu@email.com"
                            class="w-full pl-9 pr-4 py-2.5 text-sm text-ink bg-surface border border-border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green
                                   transition-all placeholder:text-muted
                                   @error('email') border-red focus:ring-red/30 @enderror"
                        >
                    </div>
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">
                        Senha
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password" name="password" type="password"
                            autocomplete="current-password" required
                            placeholder="••••••••"
                            class="w-full pl-9 pr-10 py-2.5 text-sm text-ink bg-surface border border-border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green
                                   transition-all placeholder:text-muted
                                   @error('password') border-red focus:ring-red/30 @enderror"
                        >
                        <!-- toggle senha -->
                        <button type="button" id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted hover:text-ink2 transition-colors">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Lembrar-me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                               class="w-3.5 h-3.5 rounded border-border text-green focus:ring-green/30 accent-green">
                        <span class="text-xs text-muted">Lembrar-me</span>
                    </label>
                </div>

                <!-- Botão submit -->
                <button type="submit" id="submit-btn"
                        class="w-full bg-green hover:bg-green-light active:bg-green-dark text-white font-semibold text-sm
                               py-2.5 px-4 rounded-xl shadow-card hover:shadow-hover
                               transition-all duration-200 flex items-center justify-center gap-2 mt-2">
                    <svg id="btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span id="btn-text">Entrar</span>
                </button>
            </form>
        </div>

        <!-- ── FOOTER ── -->
        <p class="text-center text-[10px] text-muted mt-6">
            © {{ date('Y') }} WiFi Tocantins Express · Desenvolvido por Érick Vinicius v2.0
        </p>
    </div>

    <script>
        // Auto-focus
        document.getElementById('email').focus();

        // Toggle senha
        const toggleBtn = document.getElementById('toggle-password');
        const pwdInput  = document.getElementById('password');
        const eyeIcon   = document.getElementById('eye-icon');
        toggleBtn.addEventListener('click', () => {
            const isText = pwdInput.type === 'text';
            pwdInput.type = isText ? 'password' : 'text';
            eyeIcon.innerHTML = isText
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
        });

        // Loading no submit
        document.getElementById('login-form').addEventListener('submit', function () {
            const btn  = document.getElementById('submit-btn');
            const text = document.getElementById('btn-text');
            const icon = document.getElementById('btn-icon');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            icon.innerHTML = `<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                              <path fill="currentColor" class="opacity-75"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>`;
            icon.classList.add('animate-spin');
            text.textContent = 'Entrando...';
        });
    </script>
</body>
</html>
