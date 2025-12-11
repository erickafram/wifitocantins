<!-- Chat Widget - Popup Flutuante Moderno -->
<div id="chat-widget" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9999]">
    <!-- Botão de Abrir Chat - Design Moderno -->
    <button id="chat-toggle-btn" onclick="toggleChatWidget()" class="chat-fab-button group">
        <!-- Círculo externo com gradiente -->
        <div class="chat-fab-outer"></div>
        
        <!-- Círculo principal -->
        <div class="chat-fab-inner">
            <!-- Ícone de chat -->
            <span id="chat-icon" class="chat-fab-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
            </span>
            <!-- Ícone de fechar -->
            <span id="chat-close-icon" class="chat-fab-icon hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </span>
        </div>
        
        <!-- Badge de notificação -->
        <span id="chat-badge" class="chat-fab-badge hidden">!</span>
    </button>

    <!-- Tooltip elegante -->
    <div id="chat-tooltip" class="chat-tooltip">
        <span>💬</span> Precisa de ajuda?
    </div>

    <!-- Chat Box -->
    <div id="chat-box" class="hidden absolute bottom-20 right-0 w-[360px] sm:w-[400px] bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
        <!-- Header com gradiente -->
        <div class="bg-gradient-to-r from-emerald-500 via-green-500 to-teal-600 p-5 text-white relative overflow-hidden">
            <!-- Padrão decorativo -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative flex items-center space-x-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                    <span class="text-3xl">🚌</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-lg">WiFi Tocantins</h4>
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                        <p class="text-sm text-emerald-100">Atendimento Online</p>
                    </div>
                </div>
                <button onclick="toggleChatWidget()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Formulário Inicial -->
        <div id="chat-form-container" class="p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">👋</span>
                </div>
                <h5 class="font-semibold text-gray-800 text-lg">Olá! Como podemos ajudar?</h5>
                <p class="text-sm text-gray-500 mt-1">Preencha seus dados para iniciar o atendimento</p>
            </div>
            
            <form id="chat-start-form" class="space-y-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text" id="chat-name" placeholder="Seu nome completo" required
                           class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <input type="tel" id="chat-phone" placeholder="(00) 00000-0000" required maxlength="16"
                           class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="email" id="chat-email" placeholder="seu@email.com" required
                           class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                </div>
                <div class="relative">
                    <div class="absolute top-3 left-0 pl-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <textarea id="chat-first-message" placeholder="Descreva como podemos ajudar..." required rows="3"
                              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all resize-none"></textarea>
                </div>
                <button type="submit" id="chat-start-btn"
                        class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-4 rounded-xl font-semibold hover:shadow-lg hover:shadow-emerald-500/30 transform hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                    <span>Iniciar Conversa</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>
            
            <p class="text-xs text-gray-400 text-center mt-4 flex items-center justify-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Seus dados estão protegidos</span>
            </p>
        </div>

        <!-- Área de Mensagens -->
        <div id="chat-messages-container" class="hidden flex flex-col" style="height: 420px;">
            <!-- Info do atendimento -->
            <div id="chat-user-info" class="px-4 py-3 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-emerald-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white text-sm font-bold" id="chat-user-avatar">U</div>
                        <div>
                            <p class="text-sm font-medium text-gray-800" id="chat-user-name-display">Usuário</p>
                            <p class="text-xs text-emerald-600">Conversa ativa</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400" id="chat-status">Online</span>
                </div>
            </div>

            <!-- Mensagens -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-gray-50 to-white">
                <!-- Mensagem de boas-vindas -->
                <div class="flex justify-center">
                    <span class="text-xs text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm">Início da conversa</span>
                </div>
            </div>

            <!-- Indicador de digitação -->
            <div id="typing-indicator" class="hidden px-4 py-2">
                <div class="flex items-center space-x-2 text-gray-500 text-sm">
                    <div class="flex space-x-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span>Atendente digitando...</span>
                </div>
            </div>

            <!-- Input de Mensagem -->
            <div class="p-4 border-t bg-white">
                <form id="chat-send-form" class="flex items-center space-x-3">
                    <div class="flex-1 relative">
                        <input type="text" id="chat-message-input" placeholder="Digite sua mensagem..." autocomplete="off"
                               class="w-full bg-gray-100 border-2 border-transparent rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all pr-12">
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                    <button type="submit" 
                            class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg hover:shadow-emerald-500/30 transform hover:scale-105 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
/* ===== BOTÃO FAB DO CHAT ===== */
.chat-fab-button {
    position: relative;
    width: 60px;
    height: 60px;
    border: none;
    background: transparent;
    cursor: pointer;
    outline: none;
    -webkit-tap-highlight-color: transparent;
}

.chat-fab-outer {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
    opacity: 0.3;
    animation: fabPulse 2s ease-in-out infinite;
}

.chat-fab-inner {
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 
        0 4px 15px rgba(16, 185, 129, 0.4),
        0 2px 6px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-fab-button:hover .chat-fab-inner {
    transform: scale(1.05);
    box-shadow: 
        0 8px 25px rgba(16, 185, 129, 0.5),
        0 4px 10px rgba(0, 0, 0, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.chat-fab-button:active .chat-fab-inner {
    transform: scale(0.95);
}

.chat-fab-icon {
    color: white;
    width: 26px;
    height: 26px;
    transition: transform 0.3s ease;
}

.chat-fab-icon svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.chat-fab-button:hover .chat-fab-icon {
    transform: scale(1.1);
}

.chat-fab-badge {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
    animation: badgeBounce 1s ease infinite;
    border: 2px solid white;
}

.chat-tooltip {
    position: absolute;
    bottom: 70px;
    right: 0;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    white-space: nowrap;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    pointer-events: none;
    border: 1px solid #e5e7eb;
}

.chat-tooltip::after {
    content: '';
    position: absolute;
    bottom: -6px;
    right: 20px;
    width: 12px;
    height: 12px;
    background: white;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    transform: rotate(45deg);
}

#chat-widget:hover .chat-tooltip {
    opacity: 1;
    transform: translateY(0);
}

@keyframes fabPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.15);
        opacity: 0.15;
    }
}

@keyframes badgeBounce {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* ===== CHAT BOX ===== */
#chat-box {
    animation: chatSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes chatSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

#chat-messages::-webkit-scrollbar {
    width: 5px;
}

#chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

#chat-messages::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

#chat-messages::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

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

/* Tooltip animation */
#chat-widget:hover #chat-tooltip {
    opacity: 1;
}

/* Mobile responsivo */
@media (max-width: 640px) {
    #chat-widget {
        bottom: 16px !important;
        right: 16px !important;
    }
    
    .chat-fab-button {
        width: 54px !important;
        height: 54px !important;
    }
    
    .chat-fab-icon {
        width: 22px !important;
        height: 22px !important;
    }
    
    .chat-tooltip {
        display: none !important;
    }
    
    #chat-box {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        max-height: 85vh !important;
        border-radius: 24px 24px 0 0 !important;
        margin: 0 !important;
    }
    
    #chat-messages-container {
        height: 50vh !important;
    }
    
    #chat-tooltip {
        display: none !important;
    }
}
</style>

<script>
(function() {
    let chatSessionId = localStorage.getItem('chat_session_id');
    let chatUserName = localStorage.getItem('chat_user_name');
    let chatOpen = false;
    let lastMessageId = 0;
    let pollingInterval = null;

    // Máscara de telefone
    const phoneInput = document.getElementById('chat-phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 0) {
                value = '(' + value;
                if (value.length > 3) value = value.slice(0, 3) + ') ' + value.slice(3);
                if (value.length > 10) value = value.slice(0, 10) + '-' + value.slice(10);
            }
            e.target.value = value;
        });
    }

    // Toggle Chat Widget
    window.toggleChatWidget = function() {
        const chatBox = document.getElementById('chat-box');
        const chatIcon = document.getElementById('chat-icon');
        const closeIcon = document.getElementById('chat-close-icon');
        const tooltip = document.getElementById('chat-tooltip');
        const badge = document.getElementById('chat-badge');
        
        chatOpen = !chatOpen;
        
        if (chatOpen) {
            chatBox.classList.remove('hidden');
            chatIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            tooltip.style.display = 'none';
            badge.classList.add('hidden');
            
            // Se já tem sessão, mostrar mensagens
            if (chatSessionId) {
                showMessagesContainer();
                loadMessages();
                startPolling();
            }
        } else {
            chatBox.classList.add('hidden');
            chatIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            tooltip.style.display = '';
            stopPolling();
        }
    };

    // Mostrar container de mensagens
    function showMessagesContainer() {
        document.getElementById('chat-form-container').classList.add('hidden');
        document.getElementById('chat-messages-container').classList.remove('hidden');
        
        // Atualizar info do usuário
        if (chatUserName) {
            document.getElementById('chat-user-name-display').textContent = chatUserName;
            document.getElementById('chat-user-avatar').textContent = chatUserName.charAt(0).toUpperCase();
        }
    }

    // Adicionar mensagem na tela
    function addMessage(message, isAdmin = false, adminName = null, time = null) {
        const messagesDiv = document.getElementById('chat-messages');
        const msgDiv = document.createElement('div');
        msgDiv.className = `flex ${isAdmin ? 'justify-start' : 'justify-end'} chat-message-enter`;
        
        const displayTime = time || new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        if (isAdmin) {
            msgDiv.innerHTML = `
                <div class="flex items-end space-x-2 max-w-[85%]">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-md">
                        ${adminName ? adminName.charAt(0).toUpperCase() : 'A'}
                    </div>
                    <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm border border-gray-100">
                        ${adminName ? `<p class="text-xs font-medium text-emerald-600 mb-1">${adminName}</p>` : ''}
                        <p class="text-sm text-gray-800 leading-relaxed">${message}</p>
                        <p class="text-xs text-gray-400 mt-2">${displayTime}</p>
                    </div>
                </div>
            `;
        } else {
            msgDiv.innerHTML = `
                <div class="max-w-[85%]">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl rounded-br-md px-4 py-3 shadow-md">
                        <p class="text-sm leading-relaxed">${message}</p>
                        <p class="text-xs text-emerald-100 mt-2 text-right">${displayTime}</p>
                    </div>
                </div>
            `;
        }
        
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Iniciar conversa
    const startForm = document.getElementById('chat-start-form');
    if (startForm) {
        startForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('chat-name').value.trim();
            const phone = document.getElementById('chat-phone').value.trim();
            const email = document.getElementById('chat-email').value.trim();
            const message = document.getElementById('chat-first-message').value.trim();
            
            if (!name || !phone || !email || !message) {
                alert('Preencha todos os campos');
                return;
            }

            // Desabilitar botão
            const btn = document.getElementById('chat-start-btn');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Conectando...</span>
            `;

            fetch('/api/chat/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    email: email,
                    message: message,
                    mac: window.CLIENT_MAC || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chatSessionId = data.session_id;
                    chatUserName = name;
                    localStorage.setItem('chat_session_id', chatSessionId);
                    localStorage.setItem('chat_user_name', chatUserName);
                    
                    showMessagesContainer();
                    addMessage(message, false);
                    
                    // Mensagem automática do sistema
                    setTimeout(() => {
                        addMessage('Obrigado por entrar em contato, ' + name.split(' ')[0] + '! 😊 Nossa equipe foi notificada e responderá em breve. Enquanto isso, fique à vontade para enviar mais detalhes.', true, 'Assistente Virtual');
                    }, 800);
                    
                    startPolling();
                } else {
                    alert(data.message || 'Erro ao iniciar conversa. Tente novamente.');
                    resetStartButton();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão. Verifique sua internet e tente novamente.');
                resetStartButton();
            });
        });
    }

    function resetStartButton() {
        const btn = document.getElementById('chat-start-btn');
        btn.disabled = false;
        btn.innerHTML = `
            <span>Iniciar Conversa</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        `;
    }

    // Enviar mensagem
    const sendForm = document.getElementById('chat-send-form');
    if (sendForm) {
        sendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const input = document.getElementById('chat-message-input');
            const message = input.value.trim();
            
            if (!message || !chatSessionId) return;

            // Adicionar mensagem imediatamente (otimista)
            addMessage(message, false);
            input.value = '';

            fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: chatSessionId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.message && data.message.id) {
                    lastMessageId = Math.max(lastMessageId, data.message.id);
                }
            })
            .catch(error => {
                console.error('Erro ao enviar:', error);
            });
        });
    }

    // Carregar mensagens
    function loadMessages() {
        if (!chatSessionId) return;

        fetch(`/api/chat/messages?session_id=${chatSessionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages) {
                    const messagesDiv = document.getElementById('chat-messages');
                    // Manter apenas o indicador de início
                    messagesDiv.innerHTML = `
                        <div class="flex justify-center">
                            <span class="text-xs text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm">Início da conversa</span>
                        </div>
                    `;
                    
                    data.messages.forEach(msg => {
                        const isAdmin = msg.sender_type === 'admin';
                        const adminName = isAdmin && msg.admin ? msg.admin.name : (isAdmin ? 'Atendente' : null);
                        const time = new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                        
                        addMessage(msg.message, isAdmin, adminName, time);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                }
            });
    }

    // Polling para novas mensagens
    function startPolling() {
        if (pollingInterval) return;
        
        pollingInterval = setInterval(() => {
            if (!chatSessionId) return;

            fetch(`/api/chat/check?session_id=${chatSessionId}&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_new && data.messages) {
                        data.messages.forEach(msg => {
                            const adminName = msg.admin ? msg.admin.name : 'Atendente';
                            addMessage(msg.message, true, adminName);
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        
                        // Mostrar badge se chat fechado
                        if (!chatOpen) {
                            document.getElementById('chat-badge').classList.remove('hidden');
                        }
                    }
                });
        }, 4000);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // Se já tem sessão, iniciar polling em background
    if (chatSessionId) {
        startPolling();
    }

    // Mostrar tooltip após 3 segundos
    setTimeout(() => {
        const tooltip = document.getElementById('chat-tooltip');
        if (tooltip && !chatOpen) {
            tooltip.style.opacity = '1';
            setTimeout(() => {
                tooltip.style.opacity = '0';
            }, 5000);
        }
    }, 3000);
})();
</script>
