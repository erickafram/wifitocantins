@extends('layouts.admin')

@section('title', 'Adicionar Novo Usuário')

@section('content')
    <!-- Mensagens de Sucesso/Erro -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
            <span class="mr-2">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center">
            <span class="mr-2">❌</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Formulário de Criação de Usuário -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <span class="mr-2">👤</span>
                Adicionar Novo Usuário
            </h3>
            <p class="text-white/80 text-xs mt-1">Preencha os dados abaixo para criar um novo usuário no sistema</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Digite o nome completo"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="email@exemplo.com"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefone
                    </label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('phone') border-red-500 @enderror"
                        placeholder="(63) 99999-9999"
                    >
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nível de Acesso -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Nível de Acesso <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('role') border-red-500 @enderror"
                    >
                        <option value="">Selecione o nível de acesso</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>👤 Usuário</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>👨‍💼 Gerente</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👑 Administrador</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Usuário:</strong> Acesso básico ao portal | 
                        <strong>Gerente:</strong> Visualiza relatórios | 
                        <strong>Admin:</strong> Acesso total
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status" 
                        name="status" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('status') border-red-500 @enderror"
                    >
                        <option value="">Selecione o status</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🔵 Ativo</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>🟡 Pendente</option>
                        <option value="offline" {{ old('status') == 'offline' ? 'selected' : '' }}>⚫ Offline</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('password') border-red-500 @enderror"
                        placeholder="Mínimo 6 caracteres"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmar Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                        placeholder="Digite a senha novamente"
                    >
                </div>
            </div>

            <!-- Informações do Dispositivo (Opcional) -->
            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">📱 Informações do Dispositivo (Opcional)</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- MAC Address -->
                    <div>
                        <label for="mac_address" class="block text-sm font-medium text-gray-700 mb-2">
                            MAC Address
                        </label>
                        <input 
                            type="text" 
                            id="mac_address" 
                            name="mac_address" 
                            value="{{ old('mac_address') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('mac_address') border-red-500 @enderror"
                            placeholder="00:00:00:00:00:00"
                            pattern="([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})"
                        >
                        @error('mac_address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IP Address -->
                    <div>
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Endereço IP
                        </label>
                        <input 
                            type="text" 
                            id="ip_address" 
                            name="ip_address" 
                            value="{{ old('ip_address') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('ip_address') border-red-500 @enderror"
                            placeholder="192.168.1.1"
                        >
                        @error('ip_address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome do Dispositivo -->
                    <div>
                        <label for="device_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Dispositivo
                        </label>
                        <input 
                            type="text" 
                            id="device_name" 
                            name="device_name" 
                            value="{{ old('device_name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent @error('device_name') border-red-500 @enderror"
                            placeholder="iPhone de João"
                        >
                        @error('device_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="mt-8 flex items-center justify-between border-t pt-6">
                <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors duration-300 flex items-center">
                    <span class="mr-2">←</span>
                    Voltar
                </a>

                <button type="submit" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-8 py-2 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center">
                    <span class="mr-2">✓</span>
                    Criar Usuário
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Máscara para telefone
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            }
            e.target.value = value;
        });

        // Máscara para MAC Address
        document.getElementById('mac_address').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9A-Fa-f]/g, '');
            if (value.length <= 12) {
                value = value.match(/.{1,2}/g)?.join(':') || value;
            }
            e.target.value = value.toUpperCase();
        });

        // Validação de senha
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');

        passwordConfirm.addEventListener('input', function() {
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('As senhas não coincidem');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });
    </script>
@endpush
