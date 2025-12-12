@extends('layouts.admin')

@section('title', 'Chat com ' . $conversation->visitor_name)
@section('page-title', '💬 Atendimento')

@section('breadcrumb')
    <span class="mx-2">/</span>
    <a href="{{ route('admin.chat.index') }}" class="hover:text-emerald-600">Chat</a>
    <span class="mx-2">/</span>
    <span class="text-emerald-600 font-medium">{{ $conversation->visitor_name }}</span>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-4 lg:gap-6 h-auto lg:h-[calc(100vh-160px)]">
    <!-- Área Principal do Chat -->
    <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-xl overflow-hidden min-h-[60vh] lg:min-h-0">
        <!-- Header do Chat -->
        <div class="bg-gradient-to-r from-emerald-500 via-green-500 to-teal-600 p-3 lg:p-4 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center space-x-2 lg:space-x-4">
                    <a href="{{ route('admin.chat.index') }}" class="w-8 h-8 lg:w-10 lg:h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                        <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="w-10 h-10 lg:w-14 lg:h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-xl lg:text-2xl font-bold shadow-lg flex-shrink-0">
                        {{ strtoupper(substr($conversation->visitor_name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-sm lg:text-lg truncate">{{ $conversation->visitor_name }}</h3>
                        <div class="flex items-center space-x-2 text-xs lg:text-sm text-emerald-100">
                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $conversation->status === 'active' ? 'bg-green-300 animate-pulse' : ($conversation->status === 'pending' ? 'bg-yellow-300' : 'bg-gray-300') }}"></span>
                            <span>{{ $conversation->status === 'active' ? 'Online' : ($conversation->status === 'pending' ? 'Aguardando' : 'Offline') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-1 lg:space-x-2 flex-shrink-0">
                    <!-- Botão Info Mobile -->
                    <button type="button" id="toggle-sidebar-btn" class="lg:hidden bg-white/20 hover:bg-white/30 p-2 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    @if($conversation->status !== 'closed')
                    <form action="{{ route('admin.chat.close', $conversation->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-white/20 hover:bg-white/30 px-2 lg:px-4 py-2 rounded-xl text-xs lg:text-sm font-medium transition-colors flex items-center space-x-1 lg:space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="hidden sm:inline">Encerrar</span>
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.chat.destroy', $conversation->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta conversa?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500/80 hover:bg-red-500 px-2 lg:px-3 py-2 rounded-xl text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Área de Mensagens -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-3 lg:p-6 space-y-3 lg:space-y-4 bg-gradient-to-b from-gray-50 to-white">
            <!-- Data inicial -->
            <div class="flex justify-center">
                <span class="text-xs text-gray-400 bg-white px-4 py-1.5 rounded-full shadow-sm border">
                    {{ $conversation->created_at->format('d/m/Y') }}
                </span>
            </div>

            @foreach($conversation->messages as $message)
            <div class="flex {{ $message->sender_type === 'admin' ? 'justify-end' : 'justify-start' }} chat-message-enter">
                @if($message->sender_type === 'visitor')
                <div class="flex items-end space-x-2 lg:space-x-3 max-w-[85%] lg:max-w-[70%]">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center text-gray-600 font-bold text-xs lg:text-sm flex-shrink-0">
                        {{ strtoupper(substr($conversation->visitor_name, 0, 1)) }}
                    </div>
                    <div class="bg-white rounded-2xl rounded-bl-md px-3 lg:px-4 py-2 lg:py-3 shadow-md border border-gray-100">
                        <p class="text-sm text-gray-800 leading-relaxed break-words">{{ $message->message }}</p>
                        <p class="text-xs text-gray-400 mt-1 lg:mt-2">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
                @else
                <div class="max-w-[85%] lg:max-w-[70%]">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl rounded-br-md px-3 lg:px-4 py-2 lg:py-3 shadow-md">
                        <p class="text-sm leading-relaxed break-words">{{ $message->message }}</p>
                        <div class="flex items-center justify-end space-x-2 mt-1 lg:mt-2 flex-wrap">
                            @if($message->admin)
                            <span class="text-xs text-emerald-100 hidden sm:inline">{{ $message->admin->name }}</span>
                            <span class="text-emerald-200 hidden sm:inline">•</span>
                            @endif
                            <span class="text-xs text-emerald-100">{{ $message->created_at->format('H:i') }}</span>
                            <svg class="w-4 h-4 text-emerald-200" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Input de Mensagem -->
        @if($conversation->status !== 'closed')
        <div class="p-3 lg:p-4 border-t bg-white">
            <form id="reply-form" class="flex items-center space-x-2 lg:space-x-3">
                @csrf
                <div class="flex-1 relative">
                    <input type="text" 
                           id="message-input"
                           name="message" 
                           placeholder="Digite sua mensagem..." 
                           class="w-full bg-gray-100 border-2 border-transparent rounded-xl px-3 lg:px-5 py-3 lg:py-4 text-sm lg:text-base focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
                           autocomplete="off"
                           required>
                </div>
                <button type="submit" 
                        class="w-11 h-11 lg:w-14 lg:h-14 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-emerald-500/30 transform hover:scale-105 transition-all flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
        @else
        <div class="p-3 lg:p-4 bg-gray-100 border-t">
            <div class="flex items-center justify-center space-x-2 text-gray-500 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Esta conversa foi encerrada</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar com Informações -->
    <div id="sidebar-info" class="hidden lg:block w-full lg:w-80 flex-shrink-0">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden h-full">
            <!-- Header Info com botão fechar no mobile -->
            <div class="p-4 lg:p-6 border-b bg-gradient-to-br from-gray-50 to-white">
                <div class="flex lg:hidden justify-end mb-2">
                    <button type="button" id="close-sidebar-btn" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-2xl lg:text-3xl font-bold mx-auto shadow-lg">
                        {{ strtoupper(substr($conversation->visitor_name, 0, 1)) }}
                    </div>
                    <h4 class="font-bold text-gray-800 mt-3 lg:mt-4 text-base lg:text-lg">{{ $conversation->visitor_name }}</h4>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-2
                        {{ $conversation->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $conversation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $conversation->status === 'closed' ? 'bg-gray-100 text-gray-600' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $conversation->status === 'active' ? 'bg-green-500' : ($conversation->status === 'pending' ? 'bg-yellow-500' : 'bg-gray-400') }}"></span>
                        {{ $conversation->status === 'active' ? 'Conversa Ativa' : ($conversation->status === 'pending' ? 'Aguardando Resposta' : 'Conversa Encerrada') }}
                    </span>
                </div>
            </div>

            <!-- Detalhes -->
            <div class="p-4 lg:p-6 space-y-4 lg:space-y-5">
                <div>
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Telefone</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="tel:{{ $conversation->visitor_phone }}" class="text-gray-800 hover:text-emerald-600 font-medium">
                            {{ $conversation->visitor_phone }}
                        </a>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">E-mail</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:{{ $conversation->visitor_email }}" class="text-gray-800 hover:text-emerald-600 font-medium text-sm truncate">
                            {{ $conversation->visitor_email }}
                        </a>
                    </div>
                </div>

                @if($conversation->visitor_ip)
                <div>
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Endereço IP</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <span class="text-gray-600 text-sm font-mono">{{ $conversation->visitor_ip }}</span>
                    </div>
                </div>
                @endif

                @if($conversation->visitor_mac)
                <div>
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">MAC Address</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-600 text-sm font-mono">{{ $conversation->visitor_mac }}</span>
                    </div>
                </div>
                @endif

                <div class="pt-4 border-t">
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Iniciado em</label>
                    <p class="text-gray-800 font-medium mt-1">{{ $conversation->created_at->format('d/m/Y \à\s H:i') }}</p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total de Mensagens</label>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $conversation->messages->count() }}</p>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="p-4 lg:p-6 border-t bg-gray-50">
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $conversation->visitor_phone) }}" 
                   target="_blank"
                   class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-medium transition-colors flex items-center justify-center space-x-2 text-sm lg:text-base">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>Abrir WhatsApp</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Overlay para mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>


<style>
.chat-message-enter {
    animation: messageEnter 0.3s ease-out;
}

@keyframes messageEnter {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#messages-container::-webkit-scrollbar {
    width: 6px;
}

#messages-container::-webkit-scrollbar-track {
    background: transparent;
}

#messages-container::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

#messages-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Mobile sidebar styles */
@media (max-width: 1023px) {
    #sidebar-info.sidebar-open {
        display: block;
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 85%;
        max-width: 320px;
        z-index: 50;
        overflow-y: auto;
        animation: slideIn 0.3s ease-out;
    }
    
    #sidebar-overlay.overlay-visible {
        display: block;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    const form = document.getElementById('reply-form');
    const input = document.getElementById('message-input');
    
    // Mobile sidebar toggle
    const sidebar = document.getElementById('sidebar-info');
    const overlay = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('toggle-sidebar-btn');
    const closeBtn = document.getElementById('close-sidebar-btn');
    
    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        overlay.classList.add('overlay-visible');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('overlay-visible');
        document.body.style.overflow = '';
    }
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', openSidebar);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Scroll para o final
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    scrollToBottom();

    // Enviar mensagem
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = input.value.trim();
            if (!message) return;

            // Desabilitar input temporariamente
            input.disabled = true;

            fetch('{{ route("admin.chat.reply", $conversation->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Adicionar mensagem na tela
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'flex justify-end chat-message-enter';
                    msgDiv.innerHTML = `
                        <div class="max-w-[70%]">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl rounded-br-md px-4 py-3 shadow-md">
                                <p class="text-sm leading-relaxed">${message}</p>
                                <div class="flex items-center justify-end space-x-2 mt-2">
                                    <span class="text-xs text-emerald-100">{{ Auth::user()->name }}</span>
                                    <span class="text-emerald-200">•</span>
                                    <span class="text-xs text-emerald-100">${new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})}</span>
                                    <svg class="w-4 h-4 text-emerald-200" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    `;
                    messagesContainer.appendChild(msgDiv);
                    input.value = '';
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem');
            })
            .finally(() => {
                input.disabled = false;
                input.focus();
            });
        });
    }

    // Polling para novas mensagens
    let lastMessageId = {{ $conversation->messages->last()->id ?? 0 }};
    
    setInterval(function() {
        fetch('{{ route("admin.chat.messages", $conversation->id) }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    const newMessages = data.messages.filter(m => m.id > lastMessageId && m.sender_type === 'visitor');
                    
                    newMessages.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'flex justify-start chat-message-enter';
                        msgDiv.innerHTML = `
                            <div class="flex items-end space-x-3 max-w-[70%]">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($conversation->visitor_name, 0, 1)) }}
                                </div>
                                <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-md border border-gray-100">
                                    <p class="text-sm text-gray-800 leading-relaxed">${msg.message}</p>
                                    <p class="text-xs text-gray-400 mt-2">${new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})}</p>
                                </div>
                            </div>
                        `;
                        messagesContainer.appendChild(msgDiv);
                        lastMessageId = msg.id;
                    });
                    
                    if (newMessages.length > 0) {
                        scrollToBottom();
                        // Tocar som de notificação (opcional)
                        // new Audio('/sounds/notification.mp3').play();
                    }
                }
            });
    }, 4000);

    // Focus no input
    if (input) {
        input.focus();
    }
});
</script>
@endpush
@endsection
