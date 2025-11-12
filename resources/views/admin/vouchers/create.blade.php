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
    <div class="max-w-8xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green px-6 py-4">
                <h2 class="text-xl font-bold text-white">📝 Dados do Voucher</h2>
            </div>

            <form action="{{ route('admin.vouchers.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Nome do Motorista -->
                <div>
                    <label for="driver_name" class="block text-sm font-bold text-gray-700 mb-2">
                        👤 Nome do Motorista *
                    </label>
                    <input 
                        type="text" 
                        name="driver_name" 
                        id="driver_name" 
                        value="{{ old('driver_name') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                        required
                    >
                    @error('driver_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Documento -->
                <div>
                    <label for="driver_document" class="block text-sm font-bold text-gray-700 mb-2">
                        📄 CPF/CNH (Opcional)
                    </label>
                    <input 
                        type="text" 
                        name="driver_document" 
                        id="driver_document" 
                        value="{{ old('driver_document') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                        placeholder="000.000.000-00"
                    >
                    @error('driver_document')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Voucher -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        🎫 Tipo de Voucher *
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-tocantins-green {{ old('voucher_type', 'limited') === 'limited' ? 'border-tocantins-green bg-green-50' : 'border-gray-300' }}">
                            <input 
                                type="radio" 
                                name="voucher_type" 
                                value="limited" 
                                {{ old('voucher_type', 'limited') === 'limited' ? 'checked' : '' }}
                                class="mr-3"
                                onchange="toggleDailyHours()"
                            >
                            <div>
                                <p class="font-bold text-gray-800">⏱️ Limitado</p>
                                <p class="text-xs text-gray-600">Controle de horas diárias</p>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ old('voucher_type') === 'unlimited' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <input 
                                type="radio" 
                                name="voucher_type" 
                                value="unlimited" 
                                {{ old('voucher_type') === 'unlimited' ? 'checked' : '' }}
                                class="mr-3"
                                onchange="toggleDailyHours()"
                            >
                            <div>
                                <p class="font-bold text-gray-800">♾️ Ilimitado</p>
                                <p class="text-xs text-gray-600">Sem limite de horas</p>
                            </div>
                        </label>
                    </div>
                    @error('voucher_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Horas Diárias -->
                <div id="daily_hours_container">
                    <label for="daily_hours" class="block text-sm font-bold text-gray-700 mb-2">
                        ⏰ Horas Diárias Permitidas *
                    </label>
                    <div class="flex items-center gap-4">
                        <input 
                            type="number" 
                            name="daily_hours" 
                            id="daily_hours" 
                            value="{{ old('daily_hours', 24) }}"
                            min="1"
                            max="24"
                            class="w-32 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                            required
                        >
                        <span class="text-gray-600">horas por dia</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        💡 Define quantas horas o motorista pode usar por dia (1-24 horas)
                    </p>
                    @error('daily_hours')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data de Expiração -->
                <div>
                    <label for="expires_at" class="block text-sm font-bold text-gray-700 mb-2">
                        📅 Data de Expiração (Opcional)
                    </label>
                    <input 
                        type="date" 
                        name="expires_at" 
                        id="expires_at" 
                        value="{{ old('expires_at') }}"
                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        💡 Deixe em branco para voucher sem data de expiração
                    </p>
                    @error('expires_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                        📝 Descrição/Observações (Opcional)
                    </label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                        placeholder="Ex: Motorista da rota Palmas-Araguaína"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button 
                        type="submit" 
                        class="flex-1 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300"
                    >
                        ✅ Criar Voucher
                    </button>
                    <a 
                        href="{{ route('admin.vouchers.index') }}" 
                        class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition-all duration-300 text-center"
                    >
                        ❌ Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Informações -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 mb-2">ℹ️ Informações Importantes</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• O código do voucher será gerado automaticamente</li>
                <li>• Vouchers <strong>Limitados</strong>: o contador de horas é resetado diariamente</li>
                <li>• Vouchers <strong>Ilimitados</strong>: acesso sem restrição de horas</li>
                <li>• O voucher pode ser ativado/desativado a qualquer momento</li>
                <li>• Motoristas usarão o código no portal para acessar a internet</li>
            </ul>
        </div>
    </div>

    <script>
        function toggleDailyHours() {
            const voucherType = document.querySelector('input[name="voucher_type"]:checked').value;
            const container = document.getElementById('daily_hours_container');
            const input = document.getElementById('daily_hours');
            
            if (voucherType === 'unlimited') {
                container.style.opacity = '0.5';
                input.disabled = true;
                input.required = false;
            } else {
                container.style.opacity = '1';
                input.disabled = false;
                input.required = true;
            }
        }

        // Inicializa o estado correto ao carregar a página
        document.addEventListener('DOMContentLoaded', toggleDailyHours);
    </script>
@endsection
