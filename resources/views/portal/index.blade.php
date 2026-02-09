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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                        'modern-pink': '#EC4899',
                        'brand': {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse-btn {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            50% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        .animate-slide-up { animation: slideUp 0.5s ease-out; }
        .btn-pulse { animation: pulse-btn 2s ease-in-out infinite; }
        .shimmer-effect { position: relative; overflow: hidden; }
        .shimmer-effect::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2.5s infinite;
        }
        .connect-button {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .connect-button:hover { transform: translateY(-1px); filter: brightness(1.05); }
        .connect-button:active { transform: scale(0.98); }
        .gradient-text {
            background: linear-gradient(135deg, #059669, #10B981);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .elegant-card {
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }
    </style>
</head>
<body class="font-sans min-h-screen bg-gray-50">
    <!-- No-WiFi Warning Overlay (aparece APENAS quando acessa pelo navegador sem estar no WiFi) -->
    <div id="no-wifi-warning" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm animate-slide-up shadow-2xl text-center">
                <!-- Ícone WiFi desconectado -->
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M6 18L18 6"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">Conecte-se ao WiFi primeiro</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Você está acessando pelo <strong>navegador</strong> sem estar conectado ao <strong>WiFi do ônibus</strong>. Para pagar e usar a internet, siga os passos abaixo:
                </p>

                <!-- Instruções -->
                <div class="bg-gray-50 rounded-xl p-4 mb-5 text-left">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Como conectar:</p>
                    <div class="space-y-2.5">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <p class="text-sm text-gray-700"><strong>Desative os Dados Móveis</strong> (4G/5G) do celular</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <p class="text-sm text-gray-700">Conecte ao WiFi <strong>"TocantinsTransporteWiFi"</strong></p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <p class="text-sm text-gray-700">Aguarde a <strong>tela de login</strong> aparecer automaticamente</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <p class="text-sm text-gray-700">Clique em <strong>"Conectar Agora"</strong> e faça o pagamento PIX</p>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5">
                    <p class="text-xs text-amber-800">
                        <strong>Importante:</strong> Se você pagar sem estar no WiFi do ônibus, o acesso <strong>não será liberado</strong> porque não conseguimos identificar seu dispositivo.
                    </p>
                </div>

                <button 
                    id="no-wifi-retry-btn"
                    onclick="retryWifiCheck()"
                    class="connect-button w-full text-white font-bold py-3.5 rounded-xl shadow-md text-sm mb-3"
                >
                    JÁ CONECTEI NO WIFI, VERIFICAR
                </button>

                <p class="text-[11px] text-gray-400 mt-2">A tela de pagamento só aparece quando você estiver no WiFi do ônibus</p>
            </div>
        </div>
    </div>

    <script>
    /**
     * Detecção inteligente:
     * - Se tem parâmetros do MikroTik (mac, from_mikrotik, captive, etc.) = veio pelo captive portal = OK
     * - Se NÃO tem parâmetros = acessou direto pelo navegador = precisa verificar se está no WiFi
     */
    function hasMikrotikContext() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.has('mac') || urlParams.has('mikrotik_mac') || urlParams.has('client_mac') ||
               urlParams.has('from_mikrotik') || urlParams.has('from_router') || 
               urlParams.has('captive') || urlParams.has('from_login');
    }

    function showNoWifiWarning() {
        document.getElementById('no-wifi-warning').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Marcar globalmente que o acesso sem WiFi está bloqueado
        window._noWifiBlocked = true;
    }

    function hideNoWifiWarning() {
        document.getElementById('no-wifi-warning').classList.add('hidden');
        document.body.style.overflow = 'auto';
        window._noWifiBlocked = false;
    }

    function retryWifiCheck() {
        const btn = document.getElementById('no-wifi-retry-btn');
        btn.innerHTML = '<span class="animate-pulse">Verificando...</span>';
        btn.disabled = true;

        // Testar se consegue alcançar o gateway do MikroTik
        const img = new Image();
        let responded = false;
        
        img.onload = function() {
            responded = true;
            // Está no WiFi! Recarregar a página
            hideNoWifiWarning();
            window.location.href = 'http://10.5.50.1';
        };
        
        img.onerror = function() {
            // onerror também pode significar que alcançou mas não tem a imagem
            // Tentar via fetch como fallback
            fetch('http://10.5.50.1', { mode: 'no-cors', cache: 'no-cache' })
                .then(() => {
                    responded = true;
                    hideNoWifiWarning();
                    window.location.href = 'http://10.5.50.1';
                })
                .catch(() => {
                    btn.innerHTML = 'WIFI NÃO DETECTADO!';
                    btn.classList.remove('connect-button');
                    btn.classList.add('bg-red-500');
                    setTimeout(() => {
                        btn.innerHTML = 'JÁ CONECTEI NO WIFI, VERIFICAR';
                        btn.classList.add('connect-button');
                        btn.classList.remove('bg-red-500');
                        btn.disabled = false;
                    }, 2500);
                });
        };
        
        img.src = 'http://10.5.50.1/favicon.ico?t=' + Date.now();
        
        // Timeout de 4 segundos
        setTimeout(function() {
            if (!responded) {
                btn.innerHTML = 'WIFI NÃO DETECTADO!';
                btn.classList.remove('connect-button');
                btn.classList.add('bg-red-500');
                setTimeout(() => {
                    btn.innerHTML = 'JÁ CONECTEI NO WIFI, VERIFICAR';
                    btn.classList.add('connect-button');
                    btn.classList.remove('bg-red-500');
                    btn.disabled = false;
                }, 2500);
            }
        }, 4000);
    }

    // Executar detecção ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        // Se veio pelo captive portal (tem parâmetros do MikroTik), não precisa verificar nada
        if (hasMikrotikContext()) {
            window._noWifiBlocked = false;
            return;
        }

        // Acessou direto pelo navegador - verificar se está na rede WiFi
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn && conn.type === 'cellular') {
            // API confirma que está em dados móveis
            showNoWifiWarning();
            return;
        }

        // Testar conectividade com o gateway para confirmar
        let gatewayReached = false;
        
        fetch('http://10.5.50.1', { mode: 'no-cors', cache: 'no-cache' })
            .then(() => {
                gatewayReached = true;
                // Está no WiFi sem parâmetros - redirecionar para o captive portal
                // para que pegue MAC e IP corretamente
                window.location.href = 'http://10.5.50.1';
            })
            .catch(() => {
                // Não alcançou o gateway = não está no WiFi do ônibus
                if (!gatewayReached) {
                    showNoWifiWarning();
                }
            });
        
        // Timeout: se não responder em 4s, mostrar aviso
        setTimeout(function() {
            if (!gatewayReached) {
                showNoWifiWarning();
            }
        }, 4000);
    });
    </script>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center h-full">
            <div class="bg-white rounded-2xl p-8 text-center shadow-xl">
                <div class="animate-spin rounded-full h-10 w-10 border-3 border-gray-200 border-t-brand-600 mx-auto mb-4"></div>
                <p class="text-gray-800 font-semibold text-sm">Processando pagamento...</p>
                <p class="text-gray-400 text-xs mt-1">Por favor, aguarde</p>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Header compacto -->
        <header class="bg-white border-b border-gray-100 px-4 py-3 sm:py-4">
            <div class="max-w-lg mx-auto flex items-center justify-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="WiFi Tocantins" class="h-8 sm:h-10 w-auto">
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">WiFi Tocantins</h1>
                    <p class="text-[11px] sm:text-xs text-gray-500">Internet a bordo</p>
                </div>
            </div>
        </header>

        <!-- Conteudo Principal -->
        <main class="flex-1 px-4 py-5 sm:py-8">
            <div class="max-w-lg mx-auto space-y-4 sm:space-y-5">

                <!-- SEÇÃO 1: Card de Conexão (CTA principal) -->
                <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden animate-slide-up">
                    <!-- Faixa de preço -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-4 text-center relative">
                        @if($discount_percentage > 0)
                        <span class="absolute top-2 right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            -{{ $discount_percentage }}%
                        </span>
                        @endif
                        <p class="text-emerald-100 text-xs font-medium mb-1">Internet durante toda a viagem</p>
                        <div class="flex items-center justify-center gap-2">
                            @if($discount_percentage > 0)
                            <span class="text-white/50 text-sm line-through">R$ {{ number_format($original_price, 2, ',', '.') }}</span>
                            @endif
                            <span class="text-white text-3xl sm:text-4xl font-extrabold">R$ {{ number_format($price, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <!-- O que está incluso -->
                        <div class="grid grid-cols-3 gap-2 mb-5">
                            <div class="text-center p-2 bg-gray-50 rounded-xl">
                                <div class="text-lg mb-0.5">&#x267B;&#xFE0F;</div>
                                <span class="text-[11px] font-medium text-gray-700">Ilimitado</span>
                            </div>
                            <div class="text-center p-2 bg-gray-50 rounded-xl">
                                <div class="text-lg mb-0.5">&#x26A1;</div>
                                <span class="text-[11px] font-medium text-gray-700">Alta velocidade</span>
                            </div>
                            <div class="text-center p-2 bg-gray-50 rounded-xl">
                                <div class="text-lg mb-0.5">&#x1F512;</div>
                                <span class="text-[11px] font-medium text-gray-700">Seguro</span>
                            </div>
                        </div>

                        <!-- Apps compatíveis -->
                        <div class="flex justify-center items-center gap-2.5 mb-5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-red-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-black flex items-center justify-center shadow-sm">
                                <span class="text-red-600 font-black text-sm">N</span>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-green-500 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </div>
                        </div>

                        <!-- Botão CTA Principal - Mobile -->
                        <button 
                            id="connect-btn" 
                            class="connect-button btn-pulse w-full text-white font-bold py-4 rounded-xl text-base flex items-center justify-center gap-2 shadow-lg lg:hidden"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/></svg>
                            CONECTAR AGORA
                        </button>

                        <!-- Botão CTA Principal - Desktop -->
                        <button 
                            id="connect-btn-desktop" 
                            class="connect-button btn-pulse w-full text-white font-bold py-4 rounded-xl text-base items-center justify-center gap-2 shadow-lg hidden lg:flex"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/></svg>
                            CONECTAR AGORA
                        </button>

                        <!-- Indicadores de confiança -->
                        <div class="mt-3 flex items-center justify-center gap-4 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                Pagamento seguro
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                PIX instantâneo
                            </span>
                        </div>
                    </div>
                </section>

                <!-- SEÇÃO 2: Voucher do motorista -->
                <section class="bg-white rounded-xl border border-gray-200 shadow-sm animate-fade-in">
                    <a 
                        href="{{ route('voucher.activate') }}{{ request()->has('mac') ? '?source=mikrotik&mac=' . request('mac') . '&ip=' . request('ip') : '' }}" 
                        class="flex items-center justify-between p-4 group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center border border-amber-200">
                                <span class="text-lg">&#x1F3AB;</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Motorista? Ative seu voucher</p>
                                <p class="text-xs text-gray-400">Acesso gratuito com código</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </section>

                <!-- SEÇÃO 3: Serviços extras -->
                <section class="animate-fade-in">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2.5 px-1">Outros serviços</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            type="button"
                            onclick="openPassagensModal()" 
                            class="bg-white rounded-xl border border-gray-200 p-4 text-left shadow-sm hover:shadow-md hover:border-blue-200 transition-all group"
                        >
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-2 group-hover:bg-blue-100 transition-colors">
                                <span class="text-xl">&#x1F3AB;</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">Passagens</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Compre sem fila</p>
                        </button>
                        <button 
                            type="button"
                            onclick="openTurismoModal()" 
                            class="bg-white rounded-xl border border-gray-200 p-4 text-left shadow-sm hover:shadow-md hover:border-orange-200 transition-all group"
                        >
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mb-2 group-hover:bg-orange-100 transition-colors">
                                <span class="text-xl">&#x1F3D6;&#xFE0F;</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">Turismo</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Alugue um ônibus</p>
                        </button>
                    </div>
                </section>

            </div>
        </main>

        <!-- Footer simples -->
        <footer class="text-center py-4 px-4">
            <p class="text-[11px] text-gray-300">WiFi Tocantins Express &bull; Internet via Starlink</p>
        </footer>
    </div>

    <!-- Registration Modal -->
    <div id="registration-modal" class="fixed inset-0 bg-black/50 z-50 hidden backdrop-blur-sm">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-md animate-slide-up shadow-xl">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Acesso rápido</h3>
                    <button id="close-registration-modal" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">&times;</button>
                </div>
                
                <p class="text-sm text-gray-500 mb-5">Digite seu telefone para gerar o QR Code PIX e se conectar.</p>
                
                <form id="registration-form" class="space-y-4">
                    <div id="registration-errors" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm"></div>
                    
                    <div>
                        <label for="user_phone" class="block text-sm font-medium text-gray-700 mb-1.5">Telefone com DDD</label>
                        <input 
                            type="tel" 
                            id="user_phone" 
                            name="phone" 
                            required
                            placeholder="(63) 9 8101-3050"
                            maxlength="16"
                            autofocus
                            class="w-full border border-gray-300 rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-base text-center font-medium"
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        id="registration-submit-btn"
                        class="connect-button w-full text-white font-bold py-3.5 rounded-xl shadow-md text-sm"
                    >
                        GERAR QR CODE PIX
                    </button>
                </form>
                
                <p class="text-center text-xs text-gray-400 mt-4">
                    Pagamento seguro &bull; Liberação automática
                </p>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="payment-modal" class="fixed inset-0 bg-black/50 z-40 hidden backdrop-blur-sm">
        <div class="flex items-end sm:items-center justify-center h-full p-0 sm:p-4">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl p-6 sm:p-8 w-full max-w-md animate-slide-up shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Pagamento PIX</h3>
                    <button id="close-modal" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">&times;</button>
                </div>
                
                <div class="bg-emerald-50 rounded-xl p-5 mb-5 text-center border border-emerald-100">
                    <p class="text-3xl font-extrabold text-emerald-700">R$ {{ number_format($price, 2, ',', '.') }}</p>
                    <p class="text-sm text-emerald-600 mt-1">Internet durante toda a viagem</p>
                </div>
                
                <button data-payment="pix" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition-colors shadow-md text-sm">
                    PAGAR AGORA
                </button>
                
                <p class="text-center text-xs text-gray-400 mt-3">
                    Pagamento seguro e instantâneo
                </p>
            </div>
        </div>
    </div>

    <!-- Modal de Passagens -->
    <div id="passagensModal" class="fixed inset-0 bg-black/50 z-50 hidden backdrop-blur-sm">
        <div class="flex items-end sm:items-center justify-center h-full p-0 sm:p-4">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl p-5 sm:p-6 w-full max-w-md animate-slide-up shadow-xl max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-lg">&#x1F3AB;</span>
                        Passagens Rodoviárias
                    </h3>
                    <button onclick="closePassagensModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">&times;</button>
                </div>
                
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                    <h4 class="text-sm font-bold text-blue-800 mb-2">Compre sem fila pelo WhatsApp</h4>
                    <ul class="text-xs text-blue-700 space-y-1.5">
                        <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Escolha seu assento</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Pagamento via PIX instantâneo</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Bilhete digital no celular</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Empresa licenciada ANTT</li>
                    </ul>
                </div>
                
                <a href="https://wa.me/556384962118?text=Olá!%20Quero%20comprar%20uma%20passagem%20rodoviária.%20Pode%20me%20ajudar?" 
                   target="_blank"
                   class="flex items-center justify-center gap-2 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl transition-colors shadow-md text-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    COMPRAR VIA WHATSAPP
                </a>
                
                <p class="text-[11px] text-gray-400 mt-3 text-center">Disponível 24h</p>
            </div>
        </div>
    </div>

    <!-- Modal de Turismo -->
    <div id="turismoModal" class="fixed inset-0 bg-black/50 z-50 hidden backdrop-blur-sm">
        <div class="flex items-end sm:items-center justify-center h-full p-0 sm:p-4">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl p-5 sm:p-6 w-full max-w-md animate-slide-up shadow-xl max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-lg">&#x1F3D6;&#xFE0F;</span>
                        Turismo & Fretamento
                    </h3>
                    <button onclick="closeTurismoModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">&times;</button>
                </div>
                
                <div class="space-y-3 mb-4">
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                        <h5 class="font-bold text-orange-800 text-xs mb-2">Tipos de serviço</h5>
                        <div class="grid grid-cols-2 gap-2 text-xs text-orange-700">
                            <span class="flex items-center gap-1.5">&#x1F389; Excursões</span>
                            <span class="flex items-center gap-1.5">&#x1F470; Casamentos</span>
                            <span class="flex items-center gap-1.5">&#x1F3E2; Corporativo</span>
                            <span class="flex items-center gap-1.5">&#x1F393; Formaturas</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                        <h5 class="font-bold text-gray-700 text-xs mb-2">Diferenciais</h5>
                        <ul class="text-xs text-gray-600 space-y-1.5">
                            <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Ar condicionado + WiFi</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Poltronas reclináveis</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500">&#x2713;</span> Motoristas certificados</li>
                        </ul>
                    </div>
                </div>
                
                <a href="https://wa.me/5563984666184?text=Olá!%20Gostaria%20de%20alugar%20um%20ônibus%20para%20turismo.%20Pode%20me%20passar%20mais%20informações?" 
                   target="_blank"
                   class="flex items-center justify-center gap-2 w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3.5 rounded-xl transition-colors shadow-md text-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    SOLICITAR ORÇAMENTO
                </a>
                
                <p class="text-[11px] text-gray-400 mt-3 text-center">Sem compromisso</p>
            </div>
        </div>
    </div>


    <script>
        function openPassagensModal() {
            document.getElementById('passagensModal')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closePassagensModal() {
            document.getElementById('passagensModal')?.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        function openTurismoModal() {
            document.getElementById('turismoModal')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeTurismoModal() {
            document.getElementById('turismoModal')?.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Fechar modais clicando no backdrop
            ['passagensModal', 'turismoModal'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('click', e => { if (e.target === el) { el.classList.add('hidden'); document.body.style.overflow = 'auto'; } });
            });

            // ESC para fechar
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') { closePassagensModal(); closeTurismoModal(); }
            });

            // ===== VOUCHER SYSTEM =====
            function applyVoucher(inputId, buttonId) {
                const input = document.getElementById(inputId);
                const button = document.getElementById(buttonId);
                if (!input || !button) return;

                const voucherCode = input.value.trim().toUpperCase();
                if (!voucherCode) { alert('Por favor, digite o código do voucher'); return; }

                button.disabled = true;
                button.textContent = '...';

                fetch('/api/voucher/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ voucher_code: voucherCode })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(`${data.message}\n\nTipo: ${data.voucher_type === 'unlimited' ? 'Ilimitado' : 'Limitado'}\nHoras: ${data.hours_granted}h\nVálido até: ${new Date(data.expires_at).toLocaleString('pt-BR')}` +
                              (data.voucher_type === 'limited' ? `\nHoras restantes hoje: ${data.remaining_hours_today}h` : ''));
                        setTimeout(() => { window.location.href = 'https://www.google.com'; }, 2000);
                    } else {
                        alert(data.message);
                        button.disabled = false;
                        button.textContent = 'OK';
                    }
                })
                .catch(() => {
                    alert('Erro ao processar voucher. Tente novamente.');
                    button.disabled = false;
                    button.textContent = 'OK';
                });
            }

            // Event listeners voucher
            ['mobile', 'desktop'].forEach(suffix => {
                const btn = document.getElementById(`apply-voucher-${suffix}`);
                if (btn) btn.addEventListener('click', () => applyVoucher(`voucher-code-${suffix}`, `apply-voucher-${suffix}`));
                const input = document.getElementById(`voucher-code-${suffix}`);
                if (input) input.addEventListener('keypress', e => { if (e.key === 'Enter') applyVoucher(`voucher-code-${suffix}`, `apply-voucher-${suffix}`); });
            });
        });
    </script>
    
    <script>
        window.WIFI_PRICE = {{ $price }};
        window.SESSION_DURATION = {{ $session_duration ?? 12 }};
    </script>
    
    <script src="{{ asset('js/mac-detector.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/portal.js') }}?v={{ time() }}"></script>
    
    @include('components.chat-widget')
</body>
</html>
