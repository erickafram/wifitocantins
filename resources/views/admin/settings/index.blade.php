@extends('layouts.admin')

@section('title', 'Configurações do Sistema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">⚙️ Configurações do Sistema</h1>
            <p class="text-gray-600">Gerencie as configurações gerais do WiFi Tocantins</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card: Preços -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Preços e Valores
                    </h2>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <label for="wifi_price" class="block text-sm font-bold text-gray-700 mb-2">
                            💰 Preço do WiFi (R$)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-bold">R$</span>
                            <input 
                                type="number" 
                                name="wifi_price" 
                                id="wifi_price" 
                                step="0.01" 
                                min="0.01" 
                                max="999.99"
                                value="{{ old('wifi_price', $settings['wifi_price']) }}"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg font-bold"
                                required
                            >
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Este valor será cobrado dos usuários para acesso ao WiFi durante toda a viagem.
                        </p>
                        @error('wifi_price')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card: Gateway PIX -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Gateway de Pagamento
                    </h2>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <label for="pix_gateway" class="block text-sm font-bold text-gray-700 mb-3">
                            🔌 Gateway PIX Ativo
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ $settings['pix_gateway'] == 'woovi' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                                <input 
                                    type="radio" 
                                    name="pix_gateway" 
                                    value="woovi" 
                                    {{ $settings['pix_gateway'] == 'woovi' ? 'checked' : '' }}
                                    class="mr-3"
                                >
                                <div>
                                    <p class="font-bold text-gray-800">Woovi</p>
                                    <p class="text-xs text-gray-600">OpenPix</p>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ $settings['pix_gateway'] == 'pagbank' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                                <input 
                                    type="radio" 
                                    name="pix_gateway" 
                                    value="pagbank" 
                                    {{ $settings['pix_gateway'] == 'pagbank' ? 'checked' : '' }}
                                    class="mr-3"
                                >
                                <div>
                                    <p class="font-bold text-gray-800">PagBank</p>
                                    <p class="text-xs text-gray-600">PagSeguro</p>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ $settings['pix_gateway'] == 'santander' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                                <input 
                                    type="radio" 
                                    name="pix_gateway" 
                                    value="santander" 
                                    {{ $settings['pix_gateway'] == 'santander' ? 'checked' : '' }}
                                    class="mr-3"
                                >
                                <div>
                                    <p class="font-bold text-gray-800">Santander</p>
                                    <p class="text-xs text-gray-600">Banco</p>
                                </div>
                            </label>
                        </div>
                        @error('pix_gateway')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror>
                    </div>
                </div>
            </div>

            <!-- Card: Sessão -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Duração da Sessão
                    </h2>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <label for="session_duration" class="block text-sm font-bold text-gray-700 mb-2">
                            ⏱️ Duração da Sessão WiFi (horas)
                        </label>
                        <input 
                            type="number" 
                            name="session_duration" 
                            id="session_duration" 
                            min="1" 
                            max="168"
                            value="{{ old('session_duration', $settings['session_duration']) }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-lg font-bold"
                            required
                        >
                        <p class="mt-2 text-sm text-gray-600">
                            Tempo que o usuário terá acesso ao WiFi após o pagamento (1-168 horas).
                        </p>
                        @error('session_duration')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-bold rounded-xl hover:from-green-600 hover:to-green-700 transition-all transform hover:scale-105 shadow-lg">
                    💾 Salvar Configurações
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
