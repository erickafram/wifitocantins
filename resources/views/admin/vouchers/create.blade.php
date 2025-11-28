@extends('layouts.admin')

@section('title', 'Criar Voucher')

@section('breadcrumb')
    <span>›</span>
    <a href="{{ route('admin.vouchers.index') }}" class="text-gray-600 hover:text-tocantins-green">Vouchers</a>
    <span>›</span>
    <span class="text-tocantins-green font-medium">Novo Voucher</span>
@endsection

@section('page-title', 'Criar Novo Voucher')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulário Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-xl">📝</span> Dados do Voucher
                        </h2>
                    </div>

                    <form action="{{ route('admin.vouchers.store') }}" method="POST" class="p-6">
                        @csrf

                        <!-- Seção: Dados do Motorista -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-tocantins-green text-white rounded-full flex items-center justify-center text-xs">1</span>
                                Dados do Motorista
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nome do Motorista -->
                                <div class="md:col-span-2">
                                    <label for="driver_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nome do Motorista <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="driver_name" 
                                        id="driver_name" 
                                        value="{{ old('driver_name') }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                        placeholder="Nome completo do motorista"
                                        required
                                    >
                                    @error('driver_name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Telefone do Motorista -->
                                <div>
                                    <label for="driver_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Telefone <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        name="driver_phone" 
                                        id="driver_phone" 
                                        value="{{ old('driver_phone') }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                        placeholder="(00) 00000-0000"
                                        required
                                    >
                                    @error('driver_phone')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Documento -->
                                <div>
                                    <label for="driver_document" class="block text-sm font-medium text-gray-700 mb-1">
                                        CPF/CNH <span class="text-gray-400 text-xs">(opcional)</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="driver_document" 
                                        id="driver_document" 
                                        value="{{ old('driver_document') }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                        placeholder="000.000.000-00"
                                    >
                                    @error('driver_document')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <!-- Seção: Configuração do Voucher -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-tocantins-green text-white rounded-full flex items-center justify-center text-xs">2</span>
                                Configuração do Voucher
                            </h3>

                            <!-- Tipo de Voucher -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Voucher <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label id="label_limited" class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all hover:border-tocantins-green {{ old('voucher_type', 'limited') === 'limited' ? 'border-tocantins-green bg-green-50 ring-2 ring-green-200' : 'border-gray-200' }}">
                                        <input 
                                            type="radio" 
                                            name="voucher_type" 
                                            value="limited" 
                                            {{ old('voucher_type', 'limited') === 'limited' ? 'checked' : '' }}
                                            class="sr-only"
                                            onchange="toggleDailyHours()"
                                        >
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-lg">⏱️</div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Limitado</p>
                                                <p class="text-xs text-gray-500">Horas diárias</p>
                                            </div>
                                        </div>
                                    </label>

                                    <label id="label_unlimited" class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ old('voucher_type') === 'unlimited' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200' }}">
                                        <input 
                                            type="radio" 
                                            name="voucher_type" 
                                            value="unlimited" 
                                            {{ old('voucher_type') === 'unlimited' ? 'checked' : '' }}
                                            class="sr-only"
                                            onchange="toggleDailyHours()"
                                        >
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-lg">♾️</div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Ilimitado</p>
                                                <p class="text-xs text-gray-500">Sem limite</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @error('voucher_type')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tempo Diário e Intervalo -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Tempo Diário Permitido -->
                                <div id="daily_hours_container" class="transition-all duration-300">
                                    <label for="daily_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tempo Diário <span class="text-red-500" id="daily_hours_required">*</span>
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="number" 
                                            name="daily_hours" 
                                            id="daily_hours" 
                                            value="{{ old('daily_hours', 2) }}"
                                            min="0.01"
                                            max="24"
                                            step="0.01"
                                            class="w-full px-4 py-2.5 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">horas</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Ex: 2.5 = 2h30min</p>
                                    @error('daily_hours')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Intervalo entre Ativações -->
                                <div>
                                    <label for="activation_interval_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                        Intervalo entre Ativações <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="number" 
                                            name="activation_interval_hours" 
                                            id="activation_interval_hours" 
                                            value="{{ old('activation_interval_hours', 24) }}"
                                            min="0.01"
                                            max="168"
                                            step="0.01"
                                            class="w-full px-4 py-2.5 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                            required
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">horas</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Ex: 24 = 1x por dia</p>
                                    @error('activation_interval_hours')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <!-- Seção: Opções Adicionais -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs">3</span>
                                Opções Adicionais
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Data de Expiração -->
                                <div>
                                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
                                        Data de Expiração <span class="text-gray-400 text-xs">(opcional)</span>
                                    </label>
                                    <input 
                                        type="date" 
                                        name="expires_at" 
                                        id="expires_at" 
                                        value="{{ old('expires_at') }}"
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Deixe vazio para sem expiração</p>
                                    @error('expires_at')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Descrição -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Observações <span class="text-gray-400 text-xs">(opcional)</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="description" 
                                        id="description" 
                                        value="{{ old('description') }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent transition-all"
                                        placeholder="Ex: Rota Palmas-Araguaína"
                                    >
                                    @error('description')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button 
                                type="submit" 
                                class="flex-1 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Criar Voucher
                            </button>
                            <a 
                                href="{{ route('admin.vouchers.index') }}" 
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-300 flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar de Informações -->
            <div class="lg:col-span-1">
                <!-- Card de Dicas -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5 mb-4">
                    <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                        <span class="text-lg">💡</span> Dicas Rápidas
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">•</span>
                            <span>O código será gerado automaticamente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">•</span>
                            <span>Telefone é usado para vincular o voucher</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">•</span>
                            <span>Voucher pode ser desativado a qualquer momento</span>
                        </li>
                    </ul>
                </div>

                <!-- Card Tipo Limitado -->
                <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="text-lg">⏱️</span> Voucher Limitado
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        O motorista tem um limite de horas por dia. O contador reseta à meia-noite.
                    </p>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-700">
                            <strong>Exemplo:</strong> 2h/dia = motorista pode usar até 2 horas de internet por dia.
                        </p>
                    </div>
                </div>

                <!-- Card Tipo Ilimitado -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="text-lg">♾️</span> Voucher Ilimitado
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Sem limite de horas. O motorista pode usar a internet sem restrições de tempo.
                    </p>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-700">
                            <strong>Ideal para:</strong> Motoristas VIP ou parceiros especiais.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDailyHours() {
            const voucherType = document.querySelector('input[name="voucher_type"]:checked')?.value || 'limited';
            const container = document.getElementById('daily_hours_container');
            const input = document.getElementById('daily_hours');
            const requiredMark = document.getElementById('daily_hours_required');
            const labelLimited = document.getElementById('label_limited');
            const labelUnlimited = document.getElementById('label_unlimited');
            
            if (voucherType === 'unlimited') {
                container.classList.add('opacity-50', 'pointer-events-none');
                input.removeAttribute('required');
                input.value = '';
                requiredMark.classList.add('hidden');
                
                // Atualizar estilos dos labels
                labelLimited.classList.remove('border-tocantins-green', 'bg-green-50', 'ring-2', 'ring-green-200');
                labelLimited.classList.add('border-gray-200');
                labelUnlimited.classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                labelUnlimited.classList.remove('border-gray-200');
            } else {
                container.classList.remove('opacity-50', 'pointer-events-none');
                input.setAttribute('required', 'required');
                if (!input.value) input.value = '2';
                requiredMark.classList.remove('hidden');
                
                // Atualizar estilos dos labels
                labelUnlimited.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                labelUnlimited.classList.add('border-gray-200');
                labelLimited.classList.add('border-tocantins-green', 'bg-green-50', 'ring-2', 'ring-green-200');
                labelLimited.classList.remove('border-gray-200');
            }
        }

        // Formatar telefone enquanto digita
        document.addEventListener('DOMContentLoaded', function() {
            toggleDailyHours();
            
            const phoneInput = document.getElementById('driver_phone');
            if (phoneInput) {
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
            }
        });
    </script>
@endsection
