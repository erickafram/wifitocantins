@extends('portal.layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50/30 to-cyan-50/30 py-10">
    <div class="container mx-auto px-4 max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center shadow-lg">
                <span class="text-4xl">🎫</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Ativar Voucher</h1>
            <p class="text-gray-600 mt-2">Motorista Tocantins Transporte</p>
        </div>

        <!-- Mensagens -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">❌</span>
                    <span style="white-space: pre-line;">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="text-xl mr-2">⚠️</span>
                    <span style="white-space: pre-line;">{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card de Ativação -->
        <div class="bg-white rounded-3xl p-8 shadow-2xl">
            <form action="{{ route('voucher.activate.submit') }}" method="POST" id="voucherForm">
                @csrf

                <!-- Código do Voucher -->
                <div class="mb-6">
                    <label for="voucher_code" class="block text-sm font-semibold text-gray-700 mb-2">
                        🎫 Código do Voucher
                    </label>
                    <input 
                        type="text" 
                        id="voucher_code" 
                        name="voucher_code" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none transition uppercase"
                        placeholder="Ex: ABC123"
                        value="{{ old('voucher_code') }}"
                        required
                        maxlength="20"
                        style="text-transform: uppercase;"
                    >
                    <p class="text-xs text-gray-500 mt-1">Digite o código fornecido pela empresa</p>
                </div>

                <!-- Telefone do Motorista -->
                <div class="mb-6">
                    <label for="driver_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                        📱 Seu Telefone
                    </label>
                    <input 
                        type="tel" 
                        id="driver_phone" 
                        name="driver_phone" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none transition"
                        placeholder="(00) 00000-0000"
                        value="{{ old('driver_phone') }}"
                        required
                        maxlength="20"
                    >
                    <p class="text-xs text-gray-500 mt-1">Necessário para verificar o status do voucher</p>
                </div>

                <!-- Campos ocultos para MAC e IP -->
                <input type="hidden" id="mac_address" name="mac_address" value="{{ $mac_address ?? '' }}">
                <input type="hidden" id="ip_address" name="ip_address" value="{{ $ip_address ?? '' }}">

                <!-- Informações do Dispositivo -->
                <div class="mb-6 bg-gray-50 rounded-xl p-4 text-xs text-gray-600">
                    <p class="font-semibold mb-2">📡 Informações da Conexão:</p>
                    <p><strong>IP:</strong> <span id="display_ip">{{ $ip_address ?? 'Detectando...' }}</span></p>
                    <p><strong>MAC:</strong> <span id="display_mac">{{ $mac_address ?? 'Detectando...' }}</span></p>
                </div>

                <!-- Botão de Ativar -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2"
                >
                    <span class="text-xl">🚀</span>
                    <span>Ativar Voucher</span>
                </button>
            </form>

            <!-- Link para Verificar Status -->
            <div class="mt-6 text-center">
                <a href="{{ route('voucher.status') }}" class="text-sm text-gray-600 hover:text-green-600 transition">
                    Já ativou? Verificar status do voucher →
                </a>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="mt-6 bg-blue-50 rounded-2xl p-6 text-sm text-blue-900">
            <h3 class="font-bold mb-2 flex items-center gap-2">
                <span class="text-lg">ℹ️</span>
                Como funciona?
            </h3>
            <ul class="space-y-2 list-disc list-inside">
                <li>Cada voucher tem um limite de horas diárias</li>
                <li>O acesso expira automaticamente ao fim do período</li>
                <li>Você pode verificar o tempo restante a qualquer momento</li>
                <li>Use o mesmo telefone para acessar o voucher em outros dias</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-detectar MAC e IP do dispositivo
    const detectDevice = async () => {
        try {
            const response = await fetch('/api/detect-device', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            const data = await response.json();
            
            if (data.mac_address) {
                document.getElementById('mac_address').value = data.mac_address;
                document.getElementById('display_mac').textContent = data.mac_address;
            }
            
            if (data.ip_address) {
                document.getElementById('ip_address').value = data.ip_address;
                document.getElementById('display_ip').textContent = data.ip_address;
            }
        } catch (error) {
            console.error('Erro ao detectar dispositivo:', error);
        }
    };

    // Detectar na inicialização se não tiver MAC/IP
    if (!document.getElementById('mac_address').value || !document.getElementById('ip_address').value) {
        detectDevice();
    }

    // Formatar telefone enquanto digita
    const phoneInput = document.getElementById('driver_phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        }
        
        e.target.value = value;
    });

    // Converter código do voucher para maiúsculas
    const voucherInput = document.getElementById('voucher_code');
    voucherInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
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
</style>
@endsection

