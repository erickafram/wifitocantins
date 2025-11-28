@extends('portal.layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50/30 to-cyan-50/30 py-10">
    <div class="container mx-auto px-4 max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center shadow-lg">
                <span class="text-4xl">📊</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Status do Voucher</h1>
        </div>

        <!-- Mensagens -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            <script>
                // Notificar app Android quando voucher for ativado
                (function() {
                    console.log('Status: Verificando AndroidApp interface...');
                    
                    if (window.AndroidApp && typeof window.AndroidApp.showConnectionNotification === 'function') {
                        console.log('Status: AndroidApp detectado! Enviando notificação...');
                        
                        var successMessage = "{{ session('success') }}";
                        console.log('Status: Mensagem:', successMessage);
                        
                        var timeMatch = successMessage.match(/(\d+)\s*(hora|horas|minuto|minutos|dia|dias)/i);
                        var timeText = "";
                        
                        if (timeMatch) {
                            var amount = timeMatch[1];
                            var unit = timeMatch[2].toLowerCase();
                            
                            if (unit.includes('hora')) {
                                timeText = amount + (amount == 1 ? " hora" : " horas");
                            } else if (unit.includes('minuto')) {
                                timeText = amount + (amount == 1 ? " minuto" : " minutos");
                            } else if (unit.includes('dia')) {
                                timeText = amount + (amount == 1 ? " dia" : " dias");
                            }
                        }
                        
                        console.log('Status: Tempo extraído:', timeText || 'Nenhum');
                        window.AndroidApp.showConnectionNotification(timeText || "");
                        console.log('Status: Notificação enviada!');
                    }
                })();
            </script>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (!isset($user))
            <!-- Formulário de Consulta -->
            <div class="bg-white rounded-3xl p-8 shadow-2xl">
                <form action="{{ route('voucher.status.check') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="driver_document" class="block text-sm font-semibold text-gray-700 mb-2">
                            🪪 Digite seu CPF
                        </label>
                        <input 
                            type="text" 
                            id="driver_document" 
                            name="driver_document" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none transition text-center text-lg"
                            placeholder="000.000.000-00"
                            value="{{ $document ?? $phone ?? '' }}"
                            required
                            maxlength="14"
                        >
                        <p class="text-xs text-gray-500 mt-1 text-center">Digite o CPF cadastrado no seu voucher</p>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2"
                    >
                        <span class="text-xl">🔍</span>
                        <span>Verificar Status</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('voucher.activate') }}" class="text-sm text-gray-600 hover:text-green-600 transition">
                        ← Voltar para ativar voucher
                    </a>
                </div>
            </div>
        @else
            <!-- Exibir Status do Voucher -->
            <div class="space-y-6">
                <!-- Card de Status Principal -->
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-center mb-6">
                        @if ($isActive)
                            <div class="bg-green-100 text-green-800 px-6 py-3 rounded-full font-bold text-lg flex items-center gap-2">
                                <span class="text-2xl">✅</span>
                                <span>VOUCHER ATIVO</span>
                            </div>
                        @else
                            <div class="bg-red-100 text-red-800 px-6 py-3 rounded-full font-bold text-lg flex items-center gap-2">
                                <span class="text-2xl">❌</span>
                                <span>VOUCHER EXPIRADO</span>
                            </div>
                        @endif
                    </div>

                    <!-- Informações do Voucher -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b">
                            <span class="text-gray-600 font-medium">🪪 CPF</span>
                            <span class="font-bold text-gray-800">{{ $voucher->driver_document ?? $user->driver_phone ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b">
                            <span class="text-gray-600 font-medium">🎫 Código</span>
                            <span class="font-bold text-gray-800">{{ $voucher->code ?? 'N/A' }}</span>
                        </div>

                        @if ($isActive && $timeRemaining)
                            <div class="flex justify-between items-center py-3 border-b">
                                <span class="text-gray-600 font-medium">⏰ Tempo Restante</span>
                                <span class="font-bold text-green-600 text-xl">
                                    {{ $timeRemainingFormatted ?? $timeRemaining['hours'] . 'h ' . $timeRemaining['minutes'] . 'min' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b">
                                <span class="text-gray-600 font-medium">⏳ Expira em</span>
                                <span class="font-semibold text-gray-800">{{ $user->expires_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center py-3 border-b">
                            <span class="text-gray-600 font-medium">📅 Tempo Disponível Hoje</span>
                            <span class="font-bold text-blue-600">{{ $hoursAvailableTodayFormatted ?? $hoursAvailableToday . 'h' }}</span>
                        </div>

                        @if ($voucher)
                            <div class="flex justify-between items-center py-3 border-b">
                                <span class="text-gray-600 font-medium">🔄 Tipo de Voucher</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $voucher->voucher_type === 'unlimited' ? 'Ilimitado' : 'Limitado' }}
                                </span>
                            </div>

                            @if ($voucher->expires_at)
                                <div class="flex justify-between items-center py-3 border-b">
                                    <span class="text-gray-600 font-medium">📆 Voucher Válido Até</span>
                                    <span class="font-semibold text-gray-800">{{ $voucher->expires_at->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        @endif

                        <div class="flex justify-between items-center py-3 border-b">
                            <span class="text-gray-600 font-medium">📡 MAC Address</span>
                            <span class="font-mono text-sm text-gray-800">{{ $user->mac_address ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3">
                            <span class="text-gray-600 font-medium">🌐 IP Address</span>
                            <span class="font-mono text-sm text-gray-800">{{ $user->ip_address ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="mt-6 space-y-3">
                        @if (!$isActive)
                            <a href="{{ route('voucher.activate') }}" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                                <span class="text-xl">🎫</span>
                                <span>Ativar Novamente</span>
                            </a>
                        @endif

                        <button 
                            onclick="location.reload()" 
                            class="w-full border-2 border-gray-300 hover:border-blue-500 text-gray-700 hover:text-blue-600 font-semibold py-3 px-6 rounded-xl transition flex items-center justify-center gap-2"
                        >
                            <span class="text-xl">🔄</span>
                            <span>Atualizar Status</span>
                        </button>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                @if ($isActive)
                    <div class="bg-green-50 rounded-2xl p-6 text-sm text-green-900">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <span class="text-lg">✅</span>
                            Conexão Ativa
                        </h3>
                        <p>Seu voucher está ativo e você pode navegar livremente até {{ $user->expires_at->format('d/m/Y H:i') }}.</p>
                        <p class="mt-2">Tempo restante: <strong>{{ $timeRemainingFormatted ?? $timeRemaining['total_minutes'] . ' minutos' }}</strong></p>
                    </div>
                @else
                    <div class="bg-yellow-50 rounded-2xl p-6 text-sm text-yellow-900">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <span class="text-lg">⚠️</span>
                            Voucher Expirado
                        </h3>
                        <p>Seu voucher expirou. Para continuar navegando, você precisa ativar o voucher novamente.</p>
                        @if ($hoursAvailableToday > 0)
                            <p class="mt-2">Você ainda tem <strong>{{ $hoursAvailableTodayFormatted }}</strong> disponíveis hoje!</p>
                        @else
                            <p class="mt-2 text-red-700">Você atingiu o limite diário. Tente novamente amanhã.</p>
                        @endif
                    </div>
                @endif

                <!-- Botão Voltar -->
                <div class="text-center">
                    <a href="{{ route('voucher.activate') }}" class="text-sm text-gray-600 hover:text-green-600 transition">
                        ← Voltar para página inicial
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatar CPF enquanto digita
    const cpfInput = document.getElementById('driver_document');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }

    // Auto-refresh a cada 30 segundos se o voucher estiver ativo
    @if(isset($isActive) && $isActive)
        setInterval(function() {
            location.reload();
        }, 30000); // 30 segundos
    @endif
});
</script>

<style>
.elegant-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

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
    animation: fadeIn 0.5s ease-out;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.bg-green-100 {
    animation: pulse 2s infinite;
}
</style>
@endsection

