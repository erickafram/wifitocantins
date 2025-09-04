/**
 * Portal WiFi Tocantins - JavaScript
 * Sistema de conectividade para ônibus com Starlink
 */

class WiFiPortal {
    constructor() {
        this.deviceMac = '';
        this.connectionCheckInterval = null;
        this.loadingOverlay = null;
        this.paymentModal = null;
        this.registrationModal = null;
        this.currentUserId = null;
        this.init();
    }

    init() {
        this.setupElements();
        this.setupEventListeners();
        this.detectDevice();
        this.checkConnectionStatus();
    }

    setupElements() {
        this.loadingOverlay = document.getElementById('loading-overlay');
        this.paymentModal = document.getElementById('payment-modal');
        this.registrationModal = document.getElementById('registration-modal');
    }

    setupEventListeners() {
        // Botão principal de conectar
        const connectBtn = document.getElementById('connect-btn');
        if (connectBtn) {
            connectBtn.addEventListener('click', () => this.showRegistrationModal());
        }

        // Botão Instagram
        const instagramBtn = document.getElementById('instagram-btn');
        if (instagramBtn) {
            instagramBtn.addEventListener('click', () => this.processInstagramFree());
        }

        // Fechar modais
        const closeModal = document.getElementById('close-modal');
        if (closeModal) {
            closeModal.addEventListener('click', () => this.hidePaymentModal());
        }

        const closeRegistrationModal = document.getElementById('close-registration-modal');
        if (closeRegistrationModal) {
            closeRegistrationModal.addEventListener('click', () => this.hideRegistrationModal());
        }

        // Formulário de registro
        const registrationForm = document.getElementById('registration-form');
        if (registrationForm) {
            registrationForm.addEventListener('submit', (e) => this.handleRegistrationSubmit(e));
        }

        // Botões voucher (desktop e mobile)
        const voucherBtn = document.getElementById('voucher-btn');
        if (voucherBtn) {
            voucherBtn.addEventListener('click', () => this.applyVoucher('voucher-code'));
        }

        const voucherBtnMobile = document.getElementById('voucher-btn-mobile');
        if (voucherBtnMobile) {
            voucherBtnMobile.addEventListener('click', () => this.applyVoucher('voucher-code-mobile'));
        }

        // Enter nos campos voucher
        const voucherInput = document.getElementById('voucher-code');
        if (voucherInput) {
            voucherInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.applyVoucher('voucher-code');
                }
            });
        }

        const voucherInputMobile = document.getElementById('voucher-code-mobile');
        if (voucherInputMobile) {
            voucherInputMobile.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.applyVoucher('voucher-code-mobile');
                }
            });
        }

        // Gerenciar conexão
        const manageBtn = document.getElementById('manage-connection');
        if (manageBtn) {
            manageBtn.addEventListener('click', () => this.showConnectionManager());
        }

        // Fechar modais clicando fora
        if (this.paymentModal) {
            this.paymentModal.addEventListener('click', (e) => {
                if (e.target === this.paymentModal) {
                    this.hidePaymentModal();
                }
            });
        }

        if (this.registrationModal) {
            this.registrationModal.addEventListener('click', (e) => {
                if (e.target === this.registrationModal) {
                    this.hideRegistrationModal();
                }
            });
        }
    }

    /**
     * Detecta o MAC address do dispositivo
     */
    async detectDevice() {
        try {
            const response = await fetch('/api/detect-device', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            });

            const data = await response.json();
            this.deviceMac = data.mac_address || '';
            
            console.log('Dispositivo detectado:', this.deviceMac);
        } catch (error) {
            console.error('Erro ao detectar dispositivo:', error);
            // Simular MAC para desenvolvimento
            this.deviceMac = this.generateMockMac();
        }
    }

    /**
     * Gera MAC fictício para desenvolvimento
     */
    generateMockMac() {
        const hex = '0123456789ABCDEF';
        let mac = '';
        for (let i = 0; i < 6; i++) {
            if (i > 0) mac += ':';
            mac += hex[Math.floor(Math.random() * 16)];
            mac += hex[Math.floor(Math.random() * 16)];
        }
        return mac;
    }

    /**
     * Verifica status da conexão
     */
    async checkConnectionStatus() {
        if (!this.deviceMac) return;

        try {
            const status = await this.getDeviceStatus(this.deviceMac);
            this.updateConnectionUI(status);

            if (status.connected) {
                this.showManageButton();
                this.startConnectionMonitoring();
            }
        } catch (error) {
            console.error('Erro ao verificar status:', error);
        }
    }

    /**
     * Obtém status do dispositivo via API MikroTik
     */
    async getDeviceStatus(macAddress) {
        try {
            const response = await fetch(`/api/mikrotik/status/${macAddress}`, {
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            });

            if (!response.ok) {
                throw new Error('Erro ao obter status do dispositivo');
            }

            return await response.json();
        } catch (error) {
            // Retornar status mock para desenvolvimento
            return {
                connected: false,
                mac_address: macAddress,
                ip_address: null,
                expires_at: null,
                data_used: 0,
                status: 'offline'
            };
        }
    }

    /**
     * Libera acesso para o dispositivo
     */
    async allowDevice(macAddress) {
        try {
            const response = await fetch('/api/mikrotik/allow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({ mac_address: macAddress })
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Erro ao liberar dispositivo:', error);
            // Simular sucesso para desenvolvimento
            return true;
        }
    }

    /**
     * Bloqueia dispositivo
     */
    async blockDevice(macAddress) {
        try {
            const response = await fetch('/api/mikrotik/block', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({ mac_address: macAddress })
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Erro ao bloquear dispositivo:', error);
            return false;
        }
    }

    /**
     * Obtém dados de uso
     */
    async getUsageData(macAddress) {
        try {
            const response = await fetch(`/api/mikrotik/usage/${macAddress}`, {
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            });

            if (!response.ok) {
                throw new Error('Erro ao obter dados de uso');
            }

            return await response.json();
        } catch (error) {
            // Retornar dados mock
            return {
                data_used: 0,
                session_duration: 0,
                download_speed: 0,
                upload_speed: 0
            };
        }
    }

    /**
     * Mostra modal de registro
     */
    showRegistrationModal() {
        if (this.registrationModal) {
            this.registrationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Esconde modal de registro
     */
    hideRegistrationModal() {
        if (this.registrationModal) {
            this.registrationModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    /**
     * Mostra modal de pagamento
     */
    showPaymentModal() {
        if (this.paymentModal) {
            this.paymentModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Esconde modal de pagamento
     */
    hidePaymentModal() {
        if (this.paymentModal) {
            this.paymentModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    /**
     * Processa submissão do formulário de registro
     */
    async handleRegistrationSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone')
        };

        // Validação básica
        if (!data.name || !data.email || !data.phone) {
            this.showRegistrationError('Todos os campos são obrigatórios.');
            return;
        }

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            this.showRegistrationError('Por favor, insira um e-mail válido.');
            return;
        }

        // Mostrar loading no botão
        const submitBtn = document.getElementById('registration-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'CADASTRANDO...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.currentUserId = result.user_id;
                this.hideRegistrationModal();
                this.showPaymentModal();
                this.showSuccessMessage('Cadastro realizado com sucesso!');
            } else {
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat();
                    this.showRegistrationError(errorMessages.join('<br>'));
                } else {
                    this.showRegistrationError(result.message || 'Erro no cadastro.');
                }
            }
        } catch (error) {
            console.error('Erro no registro:', error);
            this.showRegistrationError('Erro de conexão. Tente novamente.');
        } finally {
            // Restaurar botão
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }

    /**
     * Mostra erro no formulário de registro
     */
    showRegistrationError(message) {
        const errorDiv = document.getElementById('registration-errors');
        if (errorDiv) {
            errorDiv.innerHTML = message;
            errorDiv.classList.remove('hidden');
            
            // Esconder após 5 segundos
            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }
    }

    /**
     * Processa pagamento PIX
     */
    async processPixPayment() {
        this.showLoading();
        this.hidePaymentModal();

        try {
            const response = await fetch('/api/payment/pix', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    amount: 4.99,
                    mac_address: this.deviceMac,
                    user_id: this.currentUserId
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage('Pagamento PIX aprovado! Conectando...');
                const allowed = await this.allowDevice(this.deviceMac);
                
                if (allowed) {
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                }
            } else {
                this.showErrorMessage(result.message || 'Erro no pagamento PIX.');
            }
        } catch (error) {
            console.error('Erro no pagamento PIX:', error);
            this.showErrorMessage('Erro de conexão. Verifique sua internet.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Processa pagamento com cartão
     */
    async processCardPayment() {
        this.showLoading();
        this.hidePaymentModal();

        try {
            const response = await fetch('/api/payment/card', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    amount: 4.99,
                    mac_address: this.deviceMac,
                    user_id: this.currentUserId
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage('Pagamento aprovado! Conectando...');
                const allowed = await this.allowDevice(this.deviceMac);
                
                if (allowed) {
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                }
            } else {
                this.showErrorMessage(result.message || 'Erro no pagamento.');
            }
        } catch (error) {
            console.error('Erro no pagamento:', error);
            this.showErrorMessage('Erro de conexão. Verifique sua internet.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Processa acesso grátis via Instagram
     */
    async processInstagramFree() {
        // Verificar se já usou recentemente
        const lastUsed = localStorage.getItem('instagram_free_used');
        if (lastUsed && (Date.now() - parseInt(lastUsed)) < 6 * 60 * 60 * 1000) { // 6 horas
            this.showErrorMessage('Você já usou o acesso grátis recentemente. Aguarde algumas horas.');
            return;
        }

        // Mostrar instruções detalhadas
        if (!confirm(`📸 INSTRUÇÕES PARA GANHAR INTERNET GRÁTIS:

1. Clique em "OK" para abrir nosso Instagram
2. CURTA nossa última postagem
3. SIGA nossa página (opcional, mas ajuda!)
4. Retorne a esta aba em 15-30 segundos
5. Confirme que completou as ações

⚠️ Importante: Mantenha esta aba aberta!

Continuar?`)) {
            return;
        }

        // Salvar dados de controle
        const startTime = Date.now();
        localStorage.setItem('instagram_start', startTime.toString());
        localStorage.setItem('instagram_tab_focus', 'true');

        // Abrir Instagram com parâmetros de tracking
        const instagramUrl = 'https://www.instagram.com/tocantinstransporteturismo?igsh=MXRpeGc5Z2x3ZWZpbg==';
        const newTab = window.open(instagramUrl, '_blank');

        // Detectar quando usuário volta à nossa aba
        this.startFocusDetection(startTime);

        // Mostrar contador visual
        this.showInstagramTimer();
    }

    /**
     * Detecta foco na aba para saber quando usuário retorna
     */
    startFocusDetection(startTime) {
        let hasReturnedFromInstagram = false;
        
        const handleFocus = () => {
            if (!hasReturnedFromInstagram) {
                const timeAway = Date.now() - startTime;
                
                // Usuário ficou pelo menos 15 segundos fora (tempo mínimo para curtir)
                if (timeAway >= 15000) {
                    hasReturnedFromInstagram = true;
                    window.removeEventListener('focus', handleFocus);
                    this.handleInstagramReturn(timeAway);
                }
            }
        };

        // Detectar quando usuário volta para nossa aba
        window.addEventListener('focus', handleFocus);

        // Timeout de segurança (2 minutos)
        setTimeout(() => {
            if (!hasReturnedFromInstagram) {
                window.removeEventListener('focus', handleFocus);
                this.hideInstagramTimer();
            }
        }, 120000);
    }

    /**
     * Lida com o retorno do usuário do Instagram
     */
    async handleInstagramReturn(timeAway) {
        this.hideInstagramTimer();
        
        const seconds = Math.floor(timeAway / 1000);
        
        // Verificação baseada no tempo gasto
        if (timeAway >= 15000 && timeAway <= 180000) { // Entre 15 segundos e 3 minutos
            if (confirm(`✅ Detectamos que você visitou nosso Instagram por ${seconds} segundos!

Você completou as seguintes ações?
• ❤️ Curtiu nossa última postagem
• 👥 Seguiu nossa página (opcional)

Se sim, clique OK para ganhar 5 minutos grátis!`)) {
                
                // Perguntas adicionais para validar engajamento
                const questions = await this.askEngagementQuestions();
                if (questions.score >= 2) {
                    await this.activateInstagramFree();
                    localStorage.setItem('instagram_free_used', Date.now().toString());
                } else {
                    this.showErrorMessage('Por favor, complete as ações no Instagram primeiro. Tente novamente!');
                }
            }
        } else if (timeAway < 15000) {
            this.showErrorMessage('Tempo muito curto! Curta nossa postagem e tente novamente.');
        } else {
            this.showErrorMessage('Tempo expirado. Tente novamente mais rapidamente.');
        }
    }

    /**
     * Faz perguntas para validar engajamento
     */
    async askEngagementQuestions() {
        let score = 0;
        
        // Pergunta 1: Cor do logo
        const question1 = prompt(`🎯 VERIFICAÇÃO RÁPIDA:

Qual é a cor principal do nosso logo no Instagram?
a) Azul
b) Verde
c) Vermelho
d) Roxo

Digite apenas a letra (a, b, c ou d):`);
        
        if (question1 && question1.toLowerCase() === 'b') {
            score++;
        }

        // Pergunta 2: Última postagem
        const question2 = confirm(`✅ Você curtiu nossa ÚLTIMA postagem (a mais recente no topo)?

Clique OK se SIM, Cancelar se NÃO.`);
        
        if (question2) {
            score++;
        }

        // Pergunta 3: Seguindo
        const question3 = confirm(`👥 Você começou a seguir nossa página?

Clique OK se SIM, Cancelar se NÃO.`);
        
        if (question3) {
            score++;
        }

        return { score, total: 3 };
    }

    /**
     * Ativa acesso grátis do Instagram
     */
    async activateInstagramFree() {
        this.showLoading();

        try {
            const response = await fetch('/api/instagram/free-access', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    mac_address: this.deviceMac,
                    source: 'instagram'
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage('🎉 Acesso liberado! Você tem 5 minutos grátis. Aproveite!');
                
                const allowed = await this.allowDevice(this.deviceMac);
                if (allowed) {
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                }
            } else {
                this.showErrorMessage(result.message || 'Erro ao ativar acesso grátis.');
            }
        } catch (error) {
            console.error('Erro no acesso grátis:', error);
            this.showErrorMessage('Erro de conexão. Tente novamente.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Aplica voucher
     */
    async applyVoucher(inputId = 'voucher-code') {
        const voucherInput = document.getElementById(inputId);
        const code = voucherInput?.value.trim();

        if (!code) {
            this.showErrorMessage('Digite um código de voucher válido.');
            return;
        }

        this.showLoading();

        try {
            const response = await fetch('/api/voucher/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    code: code,
                    mac_address: this.deviceMac
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage('Voucher aplicado! Conectando...');
                if (voucherInput) voucherInput.value = '';
                
                const allowed = await this.allowDevice(this.deviceMac);
                if (allowed) {
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                }
            } else {
                this.showErrorMessage(result.message || 'Voucher inválido ou expirado.');
            }
        } catch (error) {
            console.error('Erro ao aplicar voucher:', error);
            this.showErrorMessage('Erro de conexão. Tente novamente.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Atualiza interface com status da conexão
     */
    updateConnectionUI(status) {
        const statusElement = document.getElementById('connection-status');
        const statusText = document.getElementById('status-text');

        if (statusElement && statusText) {
            statusElement.classList.remove('hidden');
            
            if (status.connected) {
                statusText.textContent = 'Conectado';
                statusElement.className = 'bg-green-100 border border-green-200 rounded-xl p-4 mb-4';
            } else {
                statusText.textContent = 'Desconectado';
                statusElement.className = 'bg-red-100 border border-red-200 rounded-xl p-4 mb-4';
            }
        }
    }

    /**
     * Mostra botão de gerenciar conexão
     */
    showManageButton() {
        const manageBtn = document.getElementById('manage-connection');
        if (manageBtn) {
            manageBtn.classList.remove('hidden');
        }
    }

    /**
     * Inicia monitoramento da conexão
     */
    startConnectionMonitoring() {
        if (this.connectionCheckInterval) {
            clearInterval(this.connectionCheckInterval);
        }

        this.connectionCheckInterval = setInterval(() => {
            this.checkConnectionStatus();
        }, 30000); // Verifica a cada 30 segundos
    }

    /**
     * Mostra gerenciador de conexão
     */
    showConnectionManager() {
        alert('Funcionalidade de gerenciamento em desenvolvimento');
    }

    /**
     * Exibe loading
     */
    showLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.remove('hidden');
        }
    }

    /**
     * Esconde loading
     */
    hideLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.add('hidden');
        }
    }

    /**
     * Exibe mensagem de sucesso
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    /**
     * Exibe mensagem de erro
     */
    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    /**
     * Exibe toast notification
     */
    showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slide-up ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    /**
     * Mostra timer visual do Instagram
     */
    showInstagramTimer() {
        // Remover timer existente se houver
        this.hideInstagramTimer();

        // Criar elemento do timer
        const timer = document.createElement('div');
        timer.id = 'instagram-timer';
        timer.className = 'fixed top-4 left-4 z-50 bg-purple-600 text-white p-4 rounded-xl shadow-lg';
        timer.innerHTML = `
            <div class="text-center">
                <div class="text-2xl mb-2">📸</div>
                <div class="text-sm font-bold">Visite nosso Instagram</div>
                <div class="text-xs">Curta e volte aqui!</div>
                <div class="mt-2 text-xs text-purple-200">Aguardando retorno...</div>
            </div>
        `;

        document.body.appendChild(timer);
    }

    /**
     * Esconde timer do Instagram
     */
    hideInstagramTimer() {
        const timer = document.getElementById('instagram-timer');
        if (timer) {
            timer.remove();
        }
    }

    /**
     * Obtém CSRF token
     */
    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return token || '';
    }
}

// Inicializar quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    const portal = new WiFiPortal();
    
    // Adicionar event listeners para botões de pagamento
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-payment="pix"]')) {
            portal.processPixPayment();
        }
        if (e.target.closest('[data-payment="card"]')) {
            portal.processCardPayment();
        }
    });
});

// Exportar para uso global
window.WiFiPortal = WiFiPortal;
