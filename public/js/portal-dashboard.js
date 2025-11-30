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
                <!-- Aviso Importante - Passo 1 -->
                <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 rounded-2xl p-6 mb-6">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-red-800 mb-2">ATENÇÃO - IMPORTANTE!</h4>
                            <p class="text-sm text-red-700 font-semibold">
                                NÃO FECHE ESTA TELA até concluir os 5 passos para liberar seu acesso à internet!
                            </p>
                        </div>
                    </div>
                    <div class="bg-white/80 rounded-xl p-3">
                        <p class="text-xs text-gray-700 font-medium">
                            ✓ Mantenha esta janela aberta<br>
                            ✓ Complete todos os passos<br>
                            ✓ Aguarde a confirmação final
                        </p>
                    </div>
                </div>

                <!-- Indicador de Passos -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-500">PROGRESSO</span>
                        <span class="text-xs font-bold text-blue-600">PASSO 1 DE 5</span>
                    </div>
                    <div class="flex gap-1">
                        <div class="flex-1 h-2 bg-blue-500 rounded-full"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Passo 2: QR Code PIX</h3>
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
                    ✅ Já Paguei - Verificar Pagamento (Passo 3)
                </button>
            </div>
        `;

        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.hasAttribute('data-close')) {
                if (confirm('⚠️ Tem certeza? Você ainda não concluiu todos os passos. Fechar agora pode impedir a liberação do acesso.')) {
                    modal.remove();
                }
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
                <!-- Indicador de Passos -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-500">PROGRESSO</span>
                        <span class="text-xs font-bold text-blue-600" id="step-indicator">PASSO 3 DE 5</span>
                    </div>
                    <div class="flex gap-1" id="progress-bars">
                        <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                        <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                        <div class="flex-1 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
                    </div>
                </div>

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
                    <p class="text-sm font-semibold text-blue-800" id="processing-status">Passo 3: Verificando pagamento...</p>
                    <p class="text-xs text-blue-600 mt-2" id="processing-timer">Verificando automaticamente...</p>
                </div>
                
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-red-800 font-bold">
                        ⚠️ NÃO FECHE ESTA TELA!
                    </p>
                    <p class="text-xs text-red-700 mt-1">
                        O processo está em andamento. Fechar agora pode impedir a liberação do acesso.
                    </p>
                </div>
                
                <div class="space-y-2 text-left text-sm text-gray-600" id="steps-list">
                    <div class="flex items-start gap-2" id="step-3">
                        <span class="text-blue-500 animate-pulse">⏳</span>
                        <span>Passo 3: Verificando confirmação do pagamento</span>
                    </div>
                    <div class="flex items-start gap-2" id="step-4">
                        <span class="text-gray-400">○</span>
                        <span>Passo 4: Configurando liberação de internet</span>
                    </div>
                    <div class="flex items-start gap-2" id="step-5">
                        <span class="text-gray-400">○</span>
                        <span>Passo 5: Ativando conexão Starlink</span>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.processingModal = modal;
        
        // Iniciar verificação automática do pagamento
        this.startPaymentVerification();
    }

    async startPaymentVerification() {
        const statusElement = document.getElementById('processing-status');
        const timerElement = document.getElementById('processing-timer');
        const stepIndicator = document.getElementById('step-indicator');
        const progressBars = document.getElementById('progress-bars');
        
        let attempts = 0;
        const maxAttempts = 60; // 60 tentativas = ~2 minutos
        
        const checkPayment = async () => {
            attempts++;
            
            if (timerElement) {
                timerElement.textContent = `Tentativa ${attempts}/${maxAttempts} - Verificando...`;
            }
            
            try {
                // Buscar o último pagamento do usuário
                const response = await fetch('/api/payment/check-status');
                const result = await response.json();
                
                if (result.success && result.payment && result.payment.status === 'completed') {
                    // Pagamento confirmado! Avançar para passo 4
                    this.updateStep4();
                    
                    setTimeout(() => {
                        // Avançar para passo 5
                        this.updateStep5();
                        
                        setTimeout(() => {
                            // Concluir processo
                            this.showSuccessModal();
                        }, 3000);
                    }, 3000);
                    
                    return; // Parar verificação
                }
                
                // Se não confirmou e ainda tem tentativas, tentar novamente
                if (attempts < maxAttempts) {
                    setTimeout(checkPayment, 2000); // Verificar a cada 2 segundos
                } else {
                    // Timeout - mostrar mensagem de erro
                    if (statusElement) {
                        statusElement.textContent = 'Pagamento não confirmado ainda. Tente novamente em alguns minutos.';
                    }
                    if (timerElement) {
                        timerElement.textContent = 'Você pode fechar esta tela e verificar depois.';
                    }
                }
            } catch (error) {
                console.error('Erro ao verificar pagamento:', error);
                
                // Tentar novamente se ainda houver tentativas
                if (attempts < maxAttempts) {
                    setTimeout(checkPayment, 2000);
                }
            }
        };
        
        // Iniciar verificação
        checkPayment();
    }
    
    updateStep4() {
        const statusElement = document.getElementById('processing-status');
        const stepIndicator = document.getElementById('step-indicator');
        const progressBars = document.getElementById('progress-bars');
        const step3 = document.getElementById('step-3');
        const step4 = document.getElementById('step-4');
        
        if (statusElement) {
            statusElement.textContent = 'Passo 4: Configurando liberação de internet...';
        }
        
        if (stepIndicator) {
            stepIndicator.textContent = 'PASSO 4 DE 5';
        }
        
        if (progressBars) {
            progressBars.innerHTML = `
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <div class="flex-1 h-2 bg-gray-200 rounded-full"></div>
            `;
        }
        
        if (step3) {
            step3.innerHTML = `
                <span class="text-green-500">✓</span>
                <span>Passo 3: Pagamento confirmado!</span>
            `;
        }
        
        if (step4) {
            step4.innerHTML = `
                <span class="text-blue-500 animate-pulse">⏳</span>
                <span>Passo 4: Configurando liberação de internet</span>
            `;
        }
    }
    
    updateStep5() {
        const statusElement = document.getElementById('processing-status');
        const stepIndicator = document.getElementById('step-indicator');
        const progressBars = document.getElementById('progress-bars');
        const step4 = document.getElementById('step-4');
        const step5 = document.getElementById('step-5');
        
        if (statusElement) {
            statusElement.textContent = 'Passo 5: Ativando conexão Starlink...';
        }
        
        if (stepIndicator) {
            stepIndicator.textContent = 'PASSO 5 DE 5';
        }
        
        if (progressBars) {
            progressBars.innerHTML = `
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1 h-2 bg-blue-500 rounded-full animate-pulse"></div>
            `;
        }
        
        if (step4) {
            step4.innerHTML = `
                <span class="text-green-500">✓</span>
                <span>Passo 4: Internet configurada!</span>
            `;
        }
        
        if (step5) {
            step5.innerHTML = `
                <span class="text-blue-500 animate-pulse">⏳</span>
                <span>Passo 5: Ativando conexão Starlink</span>
            `;
        }
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
        
        // 1. Procurar em elementos com data attributes
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
        
        // 2. Se não encontrou, procurar em todo o body
        if (!timeText) {
            var bodyText = document.body.textContent || '';
            var match = bodyText.match(/(\d+)\s*(hora|horas|minuto|minutos|dia|dias)/i);
            
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
        
        console.log('Tempo detectado:', timeText || 'Nenhum');
        
        // Se não encontrou, usar padrão
        return timeText || "";
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

