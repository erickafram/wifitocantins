@extends('layouts.admin')

@section('title', 'Configurações WhatsApp')

@section('breadcrumb')
    <span class="mx-2">/</span>
    <a href="{{ route('admin.whatsapp.index') }}" class="hover:text-tocantins-green transition-colors">WhatsApp</a>
    <span class="mx-2">/</span>
    <span class="text-tocantins-green font-medium">Configurações</span>
@endsection

@section('page-title', 'Configurações do WhatsApp')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.whatsapp.settings.update') }}">
        @csrf
        @method('PUT')

        <!-- Configurações Gerais -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-6">
                    <span class="text-2xl">⚙️</span>
                    Configurações Gerais
                </h2>

                <div class="space-y-6">
                    <!-- Envio Automático -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="font-medium text-gray-800">Envio Automático</p>
                            <p class="text-sm text-gray-500">Enviar mensagens automaticamente para pagamentos pendentes</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_send_enabled" value="1" class="sr-only peer" {{ $settings['auto_send_enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>

                    <!-- Tempo de Pendência -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tempo de Pendência (minutos)
                        </label>
                        <p class="text-xs text-gray-500 mb-2">
                            Tempo mínimo que um pagamento deve estar pendente antes de enviar a mensagem
                        </p>
                        <input type="number" name="pending_minutes" value="{{ $settings['pending_minutes'] }}" min="1" max="1440" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent" required>
                        @error('pending_minutes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Template da Mensagem -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-6">
                    <span class="text-2xl">💬</span>
                    Template da Mensagem
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mensagem Padrão
                        </label>
                        <p class="text-xs text-gray-500 mb-2">
                            Use as variáveis: <code class="bg-gray-100 px-1 rounded">{nome}</code>, <code class="bg-gray-100 px-1 rounded">{valor}</code>, <code class="bg-gray-100 px-1 rounded">{telefone}</code>
                        </p>
                        <textarea name="message_template" rows="8" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent font-mono text-sm" required>{{ $settings['message_template'] }}</textarea>
                        @error('message_template')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                        <p class="text-sm font-medium text-green-700 mb-2">📱 Preview da Mensagem:</p>
                        <div id="message-preview" class="bg-white rounded-lg p-3 shadow-sm text-sm whitespace-pre-wrap">
                            {{ $settings['message_template'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex gap-4">
            <a href="{{ route('admin.whatsapp.index') }}" class="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium hover:bg-gray-300 transition-colors text-center">
                Cancelar
            </a>
            <button type="submit" class="flex-1 bg-gradient-to-r from-tocantins-green to-green-600 text-white py-3 px-4 rounded-xl font-medium hover:from-green-600 hover:to-green-700 transition-all shadow-lg">
                Salvar Configurações
            </button>
        </div>
    </form>

    <!-- Informações -->
    <div class="bg-blue-50 rounded-2xl p-6 border border-blue-200">
        <h3 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
            <span class="text-xl">ℹ️</span>
            Informações Importantes
        </h3>
        <ul class="text-sm text-blue-700 space-y-2">
            <li>• O envio automático verifica pagamentos pendentes a cada 5 minutos</li>
            <li>• Cada pagamento recebe apenas uma mensagem (não há reenvio automático)</li>
            <li>• Mensagens com falha podem ser reenviadas manualmente</li>
            <li>• O WhatsApp pode bloquear números que enviam muitas mensagens em pouco tempo</li>
            <li>• Recomendamos manter um intervalo de pelo menos 15 minutos</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Atualizar preview em tempo real
document.querySelector('textarea[name="message_template"]').addEventListener('input', function() {
    document.getElementById('message-preview').textContent = this.value;
});
</script>
@endpush
