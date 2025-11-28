@extends('portal.layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50/30 to-cyan-50/30 py-8">
    <div class="container mx-auto px-4 max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <div class="bg-white rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center shadow-lg">
                <span class="text-3xl">🎫</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Voucher de Motorista</h1>
            <p class="text-gray-500 text-sm mt-1">Digite seu CPF ou código do voucher</p>
        </div>

        <!-- Mensagens -->
        @if (session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-start">
                    <span class="text-xl mr-2">❌</span>
                    <span class="text-sm" style="white-space: pre-line;">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">⚠️</span>
                    <span style="white-space: pre-line;">{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!isset($voucher))
            <!-- ETAPA 1: Formulário de Busca -->
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <form action="{{ route('voucher.search') }}" method="POST" id="searchForm">
                    @csrf
                    
                    <!-- Campo de Busca -->
                    <div class="mb-5">
                        <label for="search_term" class="block text-sm font-semibold text-gray-700 mb-2">
                            🔍 CPF ou Código do Voucher
                        </label>
                        <input 
                            type="text" 
                            id="search_term" 
                            name="search_term" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none transition text-center text-lg font-medium"
                            placeholder="000.000.000-00 ou WIFI-XXXX-XXXX"
                            value="{{ old('search_term') }}"
                            required
                            autofocus
                        >
                        <p class="text-xs text-gray-500 mt-2 text-center">
                            Digite o CPF cadastrado ou o código do voucher
                        </p>
                    </div>

                    <!-- Campos ocultos para MAC e IP -->
                    <input type="hidden" name="mac_address" value="{{ $mac_address ?? '' }}">
                    <input type="hidden" name="ip_address" value="{{ $ip_address ?? '' }}">

                    <!-- Botão de Buscar -->
                    <button 
                        type="submit" 
                        id="searchBtn"
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition transform hover:scale-[1.02] flex items-center justify-center gap-2"
                    >
                        <span class="text-lg">🔍</span>
                        <span>Buscar Voucher</span>
                    </button>
                </form>

                <!-- Dica -->
                <div class="mt-5 bg-blue-50 rounded-xl p-4 text-xs text-blue-700">
                    <p class="font-semibold mb-1">💡 Dica:</p>
                    <p>Você pode digitar seu CPF com ou sem pontos e traços. Exemplo: 12345678900 ou 123.456.789-00</p>
                </div>
            </div>

            <!-- Tutorial Como Ativar -->
            <div class="mt-6 bg-white rounded-2xl p-5 shadow-lg">
                <h3 class="text-center text-gray-800 font-bold text-lg mb-4">📖 Como Ativar seu Voucher</h3>
                
                <div class="space-y-4">
                    <!-- Passo 1 -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">1</div>
                        <div>
                            <p class="font-semibold text-gray-800">Digite seu CPF</p>
                            <p class="text-xs text-gray-500">Use o CPF cadastrado pela empresa ou o código do voucher (WIFI-XXXX-XXXX)</p>
                        </div>
                    </div>

                    <!-- Passo 2 -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">2</div>
                        <div>
                            <p class="font-semibold text-gray-800">Clique em "Buscar Voucher"</p>
                            <p class="text-xs text-gray-500">O sistema vai encontrar seu voucher e mostrar as informações</p>
                        </div>
                    </div>

                    <!-- Passo 3 -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">3</div>
                        <div>
                            <p class="font-semibold text-gray-800">Clique em "Ativar Voucher"</p>
                            <p class="text-xs text-gray-500">Aguarde a ativação e pronto! Sua internet será liberada</p>
                        </div>
                    </div>
                </div>

                <!-- Aviso -->
                <div class="mt-4 bg-yellow-50 rounded-xl p-3 text-xs text-yellow-700 border border-yellow-200">
                    <p class="flex items-center gap-2">
                        <span class="text-base">⚠️</span>
                        <span><strong>Importante:</strong> Você precisa estar conectado ao Wi-Fi "Tocantins Transporte" para ativar o voucher.</span>
                    </p>
                </div>
            </div>
        @else
            <!-- ETAPA 2: Voucher Encontrado - Mostrar Informações -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header do Voucher -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-green-100 uppercase tracking-wider">Voucher</p>
                            <p class="text-xl font-bold font-mono">{{ $voucher->code }}</p>
                        </div>
                        <div class="text-right">
                            @if($voucher->voucher_type === 'unlimited')
                                <span class="inline-flex items-center gap-1 bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                                    ♾️ Ilimitado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                                    ⏱️ {{ $voucher->daily_hours }}h/dia
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informações do Motorista -->
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">👤</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $voucher->driver_name }}</p>
                            @if($voucher->driver_document)
                                <p class="text-sm text-gray-500">CPF: {{ $voucher->driver_document }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Status do Voucher -->
                    <div class="mb-5 p-4 rounded-xl 
                        @if($voucherStatus['type'] === 'success') bg-green-50 border border-green-200
                        @elseif($voucherStatus['type'] === 'info') bg-blue-50 border border-blue-200
                        @elseif($voucherStatus['type'] === 'warning') bg-yellow-50 border border-yellow-200
                        @else bg-red-50 border border-red-200
                        @endif
                    ">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">
                                @if($voucherStatus['type'] === 'success') ✅
                                @elseif($voucherStatus['type'] === 'info') ℹ️
                                @elseif($voucherStatus['type'] === 'warning') ⚠️
                                @else ❌
                                @endif
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-medium 
                                    @if($voucherStatus['type'] === 'success') text-green-800
                                    @elseif($voucherStatus['type'] === 'info') text-blue-800
                                    @elseif($voucherStatus['type'] === 'warning') text-yellow-800
                                    @else text-red-800
                                    @endif
                                " style="white-space: pre-line;">{{ $voucherStatus['message'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        @if($voucherStatus['is_active_session'] && $voucherStatus['time_remaining'])
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-blue-600 font-medium">Tempo Restante</p>
                                <p class="text-lg font-bold text-blue-800">
                                    {{ $voucherStatus['time_remaining']['hours'] }}h {{ $voucherStatus['time_remaining']['minutes'] }}min
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-blue-600 font-medium">Expira em</p>
                                <p class="text-sm font-bold text-blue-800">
                                    {{ $voucherStatus['time_remaining']['expires_at']->format('H:i') }}
                                </p>
                            </div>
                        @elseif($voucherStatus['next_activation'])
                            <div class="col-span-2 bg-yellow-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-yellow-600 font-medium">Próxima Ativação Disponível</p>
                                <p class="text-lg font-bold text-yellow-800">
                                    {{ $voucherStatus['next_activation']->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @elseif($voucherStatus['hours_available_today'] && $voucher->voucher_type === 'limited')
                            <div class="col-span-2 bg-green-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-green-600 font-medium">Tempo Disponível Hoje</p>
                                <p class="text-lg font-bold text-green-800">
                                    {{ $voucher->formatHours($voucherStatus['hours_available_today']) }}
                                </p>
                            </div>
                        @endif

                        @if($voucher->expires_at)
                            <div class="col-span-2 bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 font-medium">Validade do Voucher</p>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $voucher->expires_at->format('d/m/Y') }}
                                    @if($voucher->expires_at->isPast())
                                        <span class="text-red-600">(Expirado)</span>
                                    @else
                                        <span class="text-green-600">({{ $voucher->expires_at->diffForHumans() }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Botão de Ativar ou Voltar -->
                    @if($voucherStatus['can_activate'])
                        <form action="{{ route('voucher.activate.submit') }}" method="POST" id="activateForm">
                            @csrf
                            <input type="hidden" name="voucher_code" value="{{ $voucher->code }}">
                            <input type="hidden" name="mac_address" value="{{ $mac_address ?? '' }}">
                            <input type="hidden" name="ip_address" value="{{ $ip_address ?? '' }}">
                            
                            <!-- Botão Normal -->
                            <button 
                                type="submit" 
                                id="activateBtn"
                                class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-[1.02] flex items-center justify-center gap-2"
                            >
                                <span class="text-xl" id="btnIcon">🚀</span>
                                <span id="btnText">Ativar Voucher Agora</span>
                            </button>
                        </form>

                        <!-- Card de Loading (oculto por padrão) -->
                        <div id="loadingCard" class="hidden">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                <!-- Spinner animado -->
                                <div class="flex justify-center mb-4">
                                    <div class="relative">
                                        <div class="w-16 h-16 border-4 border-blue-200 rounded-full"></div>
                                        <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full absolute top-0 left-0 animate-spin"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-2xl" id="loadingEmoji">📡</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensagem de status -->
                                <p class="text-center text-blue-800 font-semibold text-lg mb-2" id="loadingMessage">
                                    Conectando ao servidor...
                                </p>
                                
                                <!-- Barra de progresso -->
                                <div class="w-full bg-blue-200 rounded-full h-2 mb-3">
                                    <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-1000" style="width: 5%"></div>
                                </div>

                                <!-- Temporizador -->
                                <p class="text-center text-blue-600 text-sm">
                                    <span id="timerText">Aguarde até 60 segundos...</span>
                                </p>

                                <!-- Dica -->
                                <div class="mt-4 bg-white/50 rounded-lg p-3 text-xs text-blue-700">
                                    <p class="flex items-center gap-2">
                                        <span>💡</span>
                                        <span id="tipText">Estamos configurando sua conexão com a internet.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('voucher.activate') }}" 
                           class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                            <span class="text-xl">←</span>
                            <span>Voltar e Buscar Outro</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Botão para buscar outro voucher -->
            @if($voucherStatus['can_activate'])
                <div class="mt-4 text-center">
                    <a href="{{ route('voucher.activate') }}" class="text-sm text-gray-600 hover:text-green-600 transition">
                        ← Buscar outro voucher
                    </a>
                </div>
            @endif
        @endif

        <!-- Link para Verificar Status -->
        <div class="mt-6 text-center">
            <a href="{{ route('voucher.status') }}" class="text-sm text-gray-600 hover:text-green-600 transition">
                Já ativou? Verificar status do voucher →
            </a>
        </div>

        <!-- Info do dispositivo (pequeno) -->
        <div class="mt-4 text-center text-xs text-gray-400">
            <p>IP: {{ $ip_address ?? 'N/A' }} | MAC: {{ $mac_address ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF
    const searchInput = document.getElementById('search_term');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Se parece com código de voucher (começa com WIFI ou tem hífen), converter para maiúsculas
            if (value.toUpperCase().startsWith('WIFI') || value.includes('-')) {
                e.target.value = value.toUpperCase();
            }
        });
    }

    // Loading no formulário de busca
    const searchForm = document.getElementById('searchForm');
    const searchBtn = document.getElementById('searchBtn');
    if (searchForm && searchBtn) {
        searchForm.addEventListener('submit', function() {
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<span class="text-lg">⏳</span><span>Buscando...</span>';
        });
    }

    // Loading no formulário de ativação com temporizador
    const activateForm = document.getElementById('activateForm');
    const activateBtn = document.getElementById('activateBtn');
    const loadingCard = document.getElementById('loadingCard');
    
    if (activateForm && activateBtn && loadingCard) {
        activateForm.addEventListener('submit', function() {
            // Esconder botão e mostrar card de loading
            activateBtn.classList.add('hidden');
            loadingCard.classList.remove('hidden');
            
            // Elementos do loading
            const loadingMessage = document.getElementById('loadingMessage');
            const loadingEmoji = document.getElementById('loadingEmoji');
            const progressBar = document.getElementById('progressBar');
            const timerText = document.getElementById('timerText');
            const tipText = document.getElementById('tipText');
            
            // Mensagens e emojis para cada etapa
            const stages = [
                { time: 0, msg: 'Conectando ao servidor...', emoji: '📡', tip: 'Estamos configurando sua conexão com a internet.', progress: 10 },
                { time: 5, msg: 'Validando voucher...', emoji: '🔐', tip: 'Verificando se o voucher está disponível.', progress: 25 },
                { time: 10, msg: 'Registrando dispositivo...', emoji: '📱', tip: 'Associando seu dispositivo ao voucher.', progress: 40 },
                { time: 15, msg: 'Configurando acesso...', emoji: '⚙️', tip: 'Preparando sua conexão com a rede.', progress: 55 },
                { time: 25, msg: 'Liberando internet...', emoji: '🌐', tip: 'Quase lá! Liberando acesso à internet.', progress: 70 },
                { time: 35, msg: 'Finalizando...', emoji: '✨', tip: 'Últimos ajustes na sua conexão.', progress: 85 },
                { time: 50, msg: 'Aguarde mais um pouco...', emoji: '⏳', tip: 'O servidor está processando sua solicitação.', progress: 95 },
            ];
            
            let secondsElapsed = 0;
            let currentStage = 0;
            
            // Atualizar temporizador a cada segundo
            const timerInterval = setInterval(function() {
                secondsElapsed++;
                const remaining = 60 - secondsElapsed;
                
                if (remaining > 0) {
                    timerText.textContent = `Tempo estimado: ${remaining} segundos...`;
                } else {
                    timerText.textContent = 'Processando... aguarde...';
                }
                
                // Verificar se deve mudar de etapa
                for (let i = stages.length - 1; i >= 0; i--) {
                    if (secondsElapsed >= stages[i].time && currentStage < i) {
                        currentStage = i;
                        loadingMessage.textContent = stages[i].msg;
                        loadingEmoji.textContent = stages[i].emoji;
                        tipText.textContent = stages[i].tip;
                        progressBar.style.width = stages[i].progress + '%';
                        break;
                    }
                }
                
                // Se passou de 60 segundos, mostrar mensagem especial
                if (secondsElapsed >= 60) {
                    loadingMessage.textContent = 'Ainda processando...';
                    loadingEmoji.textContent = '🔄';
                    tipText.textContent = 'O servidor está demorando mais que o normal. Por favor, aguarde.';
                    progressBar.style.width = '100%';
                }
            }, 1000);
            
            // Iniciar primeira etapa
            loadingMessage.textContent = stages[0].msg;
            loadingEmoji.textContent = stages[0].emoji;
            tipText.textContent = stages[0].tip;
            progressBar.style.width = stages[0].progress + '%';
        });
    }
});
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.container > div {
    animation: fadeIn 0.4s ease-out;
}
</style>
@endsection

