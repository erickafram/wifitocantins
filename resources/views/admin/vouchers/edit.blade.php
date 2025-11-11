@extends('layouts.admin')

@section('title', 'Editar Voucher')

@section('breadcrumb')
    <span>›</span>
    <a href="{{ route('admin.vouchers.index') }}" class="text-gray-600 hover:text-tocantins-green">Vouchers</a>
    <span>›</span>
    <span class="text-tocantins-green font-medium">Editar</span>
@endsection

@section('page-title', 'Editar Voucher')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">🎫 Código: {{ $voucher->code }}</h2>
            </div>
            <div class="p-6 bg-gray-50">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Criado em:</span>
                        <span class="font-medium ml-2">{{ $voucher->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($voucher->activated_at)
                        <div>
                            <span class="text-gray-600">Ativado em:</span>
                            <span class="font-medium ml-2">{{ $voucher->activated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green px-6 py-4">
                <h2 class="text-xl font-bold text-white">✏️ Editar Dados</h2>
            </div>

            <form action="{{ route('admin.vouchers.update', $voucher) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Nome do Motorista -->
                <div>
                    <label for="driver_name" class="block text-sm font-bold text-gray-700 mb-2">
                        👤 Nome do Motorista *
                    </label>
                    <input 
                        type="text" 
                        name="driver_name" 
                        id="driver_name" 
                        value="{{ old('driver_name', $voucher->driver_name) }}"
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
                        value="{{ old('driver_document', $voucher->driver_document) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
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
                        <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-tocantins-green {{ old('voucher_type', $voucher->voucher_type) === 'limited' ? 'border-tocantins-green bg-green-50' : 'border-gray-300' }}">
                            <input 
                                type="radio" 
                                name="voucher_type" 
                                value="limited" 
                                {{ old('voucher_type', $voucher->voucher_type) === 'limited' ? 'checked' : '' }}
                                class="mr-3"
                                onchange="toggleDailyHours()"
                            >
                            <div>
                                <p class="font-bold text-gray-800">⏱️ Limitado</p>
                                <p class="text-xs text-gray-600">Controle de horas diárias</p>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ old('voucher_type', $voucher->voucher_type) === 'unlimited' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <input 
                                type="radio" 
                                name="voucher_type" 
                                value="unlimited" 
                                {{ old('voucher_type', $voucher->voucher_type) === 'unlimited' ? 'checked' : '' }}
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
                            value="{{ old('daily_hours', $voucher->daily_hours) }}"
                            min="1"
                            max="24"
                            class="w-32 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                            required
                        >
                        <span class="text-gray-600">horas por dia</span>
                    </div>
                    @if($voucher->voucher_type === 'limited')
                        <p class="mt-2 text-sm text-gray-500">
                            💡 Usado hoje: <strong>{{ $voucher->daily_hours_used }}h</strong> de <strong>{{ $voucher->daily_hours }}h</strong>
                        </p>
                    @endif
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
                        value="{{ old('expires_at', $voucher->expires_at?->format('Y-m-d')) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                    >
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
                    >{{ old('description', $voucher->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1"
                            {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-tocantins-green border-gray-300 rounded focus:ring-tocantins-green"
                        >
                        <span class="ml-3 text-sm font-bold text-gray-700">✅ Voucher Ativo</span>
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button 
                        type="submit" 
                        class="flex-1 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300"
                    >
                        💾 Salvar Alterações
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

        document.addEventListener('DOMContentLoaded', toggleDailyHours);
    </script>
@endsection
