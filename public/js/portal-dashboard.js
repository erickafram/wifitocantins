class PortalDashboard {
    constructor() {
        this.paymentData = window.__portalQrCode || null;
        this.checkingPayment = false;
        this.processingModal = null;
        this.init();
    }

    init() {
        document.querySelectorAll('[data-action="show-qrcode"]').forEach(button => {
            button.addEventListener('click', () => {
                const paymentId = button.getAttribute('data-payment');
                this.fetchQrCode(paymentId);
            });
        });

        if (this.paymentData) {
            this.renderQrModal(this.paymentData);
            this.paymentData = null;
        }
    }

    async fetchQrCode(paymentId) {
        try {
            const response = await fetch(`/api/payment/pix/status?payment_id=${paymentId}`);
            const result = await response.json();

            if (!result.success) {
                this.showToast(result.message || 'Não foi possível obter o status.', 'error');
                return;
            }

            this.renderQrModal({
                emv_string: result.payment.pix_emv_string || 'QR code disponível no aplicativo.',
                amount: result.payment.amount,
                image_url: result.payment.qr_image_url || null,
                status: result.payment.status,
            });
        } catch (error) {
            console.error(error);
            this.showToast('Erro ao consultar status. Tente novamente.', 'error');
        }
    }

    renderQrModal(data) {
        const existing = document.getElementById('qr-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'qr-modal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4';

        modal.innerHTML = `
            <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">QR Code PIX</h3>
                    <button class="text-gray-400 hover:text-gray-600 text-2xl" data-close>×</button>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 text-center mb-4">
                    <p class="text-sm text-gray-500 mb-2">Valor</p>
                    <p class="text-3xl font-bold text-gray-900 mb-4">R$ ${Number(data.amount).toFixed(2)}</p>
                    <div class="flex justify-center mb-4">
                        ${data.image_url ? `<img src="${data.image_url}" alt="QR Code" class="w-48 h-48">` : '<span class="text-gray-500 text-sm">QR Code não disponível.</span>'}
                    </div>
                    <button class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-600 transition" data-copy="${data.emv_string}">
                        📋 Copiar código PIX
                    </button>
                </div>
                <p class="text-xs text-gray-500 text-center mb-3">Use o código copia-e-cola se preferir pagar pelo app.</p>
                <button class="w-full px-4 py-3 bg-blue-500 text-white rounded-xl text-sm font-semibold hover:bg-blue-600 transition" data-check-payment>
                    ✅ Já Paguei - Verificar Pagamento
                </button>
            </div>
        `;

        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.hasAttribute('data-close')) {
                modal.remove();
            }
        });

        modal.querySelector('[data-copy]')?.addEventListener('click', (event) => {
            const code = event.currentTarget.getAttribute('data-copy');
            navigator.clipboard.writeText(code).then(() => {
                this.showToast('Código PIX copiado! ✅', 'success');
            });
        });

        modal.querySelector('[data-check-payment]')?.addEventListener('click', () => {
            this.showProcessingModal();
            modal.remove();
        });

        document.body.appendChild(modal);
    }

    showProcessingModal() {
        const existing = document.getElementById('processing-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'processing-modal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4';

        modal.innerHTML = `
            <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                        <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">🚀 Processando Pagamento</h3>
                    <p class="text-gray-600 mb-4">Verificando confirmação e liberando acesso...</p>
                </div>
                
                <div class="bg-blue-50 rounded-2xl p-6 mb-6">
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse"></div>
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                    </div>
                    <p class="text-sm font-semibold text-blue-800" id="processing-status">Verificando pagamento...</p>
                    <p class="text-xs text-blue-600 mt-2" id="processing-timer">Tempo estimado: <span class="font-bold" id="timer-seconds">60</span>s</p>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-yellow-800">
                        <span class="font-bold">⚠️ Importante:</span> Mantenha esta tela aberta durante o processo.
                    </p>
                </div>
                
                <div class="space-y-2 text-left text-sm text-gray-600">
                    <div class="flex items-start gap-2">
                        <span class="text-green-500">✓</span>
                        <span>Verificando confirmação do pagamento</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-blue-500">⏳</span>
                        <span>Configurando liberação de internet</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400">○</span>
                        <span>Ativando conexão Starlink</span>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.processingModal = modal;
        
        // Iniciar timer de 60 segundos
        this.startProcessingTimer();
    }

    startProcessingTimer() {
        let seconds = 60;
        const timerElement = document.getElementById('timer-seconds');
        const statusElement = document.getElementById('processing-status');
        
        const interval = setInterval(() => {
            seconds--;
            if (timerElement) {
                timerElement.textContent = seconds;
            }
            
            // Atualizar mensagens conforme o tempo
            if (seconds === 45 && statusElement) {
                statusElement.textContent = 'Pagamento confirmado! Configurando acesso...';
            } else if (seconds === 30 && statusElement) {
                statusElement.textContent = 'Liberando internet na rede Starlink...';
            } else if (seconds === 15 && statusElement) {
                statusElement.textContent = 'Finalizando configuração...';
            } else if (seconds <= 0) {
                clearInterval(interval);
                this.showSuccessModal();
            }
        }, 1000);
    }

    showSuccessModal() {
        if (this.processingModal) {
            this.processingModal.remove();
        }
        
        // Notificar app Android se disponível
        if (window.AndroidApp && typeof window.AndroidApp.showConnectionNotification === 'function') {
            // Tentar obter tempo do plano comprado
            var timeText = this.getTimeFromPlan();
            window.AndroidApp.showConnectionNotification(timeText);
        }

        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4';

        modal.innerHTML = `
            <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <span class="text-5xl">✅</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Internet Liberada!</h3>
                    <p class="text-gray-600">Sua conexão está ativa e pronta para uso.</p>
                </div>
                
                <div class="bg-green-50 rounded-2xl p-6 mb-6">
                    <p class="text-sm font-semibold text-green-800 mb-2">🌐 Você já pode navegar!</p>
                    <p class="text-xs text-green-600">Aproveite sua viagem com internet de alta velocidade.</p>
                </div>
                
                <button class="w-full px-4 py-3 bg-green-500 text-white rounded-xl text-sm font-semibold hover:bg-green-600 transition" onclick="location.reload()">
                    🎉 Começar a Navegar
                </button>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Auto-fechar e recarregar após 5 segundos
        setTimeout(() => {
            location.reload();
        }, 5000);
    }

    getTimeFromPlan() {
        // Tentar obter tempo do plano da página
        var timeText = "";
        
        // Procurar por elementos que possam conter informação de tempo
        var planElements = document.querySelectorAll('[data-plan-duration], .plan-duration, [data-duration]');
        
        if (planElements.length > 0) {
            var durationText = planElements[0].textContent || planElements[0].getAttribute('data-plan-duration') || planElements[0].getAttribute('data-duration');
            var match = durationText.match(/(\d+)\s*(hora|horas|minuto|minutos|dia|dias)/i);
            
            if (match) {
                var amount = match[1];
                var unit = match[2].toLowerCase();
                
                if (unit.includes('hora')) {
                    timeText = amount + (amount == 1 ? " hora" : " horas");
                } else if (unit.includes('minuto')) {
                    timeText = amount + (amount == 1 ? " minuto" : " minutos");
                } else if (unit.includes('dia')) {
                    timeText = amount + (amount == 1 ? " dia" : " dias");
                }
            }
        }
        
        // Se não encontrou, usar padrão
        return timeText || "Tempo disponível";
    }

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 z-[60] px-4 py-3 rounded-xl text-white shadow-lg transition ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => new PortalDashboard());

