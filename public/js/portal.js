/**
 * Portal WiFi Tocantins - JavaScript
 * Sistema de conectividade para ônibus com Starlink
 */

class WiFiPortal {
    constructor() {
        this.deviceMac = '';
        this.deviceIp = '';
        this.connectionCheckInterval = null;
        this.paymentCheckInterval = null;
        this.loadingOverlay = null;
        this.paymentModal = null;
        this.registrationModal = null;
        this.currentUserId = null;
        this.pixTimerInterval = null;
        this.pixCountdownSeconds = 0;
        this.pixPaymentConfirmed = false;
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
        // Botão principal de conectar (mobile)
        const connectBtn = document.getElementById('connect-btn');
        if (connectBtn) {
            connectBtn.addEventListener('click', () => this.showRegistrationModal());
        }

        // Botão principal de conectar (desktop)
        const connectBtnDesktop = document.getElementById('connect-btn-desktop');
        if (connectBtnDesktop) {
            connectBtnDesktop.addEventListener('click', () => this.showRegistrationModal());
        }

        // Botão Instagram (mobile)
        const instagramBtnMobile = document.getElementById('instagram-btn-mobile');
        if (instagramBtnMobile) {
            instagramBtnMobile.addEventListener('click', () => this.processInstagramFree());
        }

        // Botão Instagram (desktop)
        const instagramBtnDesktop = document.getElementById('instagram-btn-desktop');
        if (instagramBtnDesktop) {
            instagramBtnDesktop.addEventListener('click', () => this.processInstagramFree());
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

        // Máscara de telefone e verificação de usuário
        const phoneInput = document.getElementById('user_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => this.applyPhoneMask(e));
            phoneInput.addEventListener('keydown', (e) => this.handlePhoneKeydown(e));
            phoneInput.addEventListener('blur', (e) => this.checkExistingUser('phone', e.target.value));
        }

        // Verificação de usuário por email
        const emailInput = document.getElementById('user_email');
        if (emailInput) {
            emailInput.addEventListener('blur', (e) => this.checkExistingUser('email', e.target.value));
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
            // 🎯 PRIORIDADE: Verificar se MAC está na URL (MikroTik)
            const urlParams = new URLSearchParams(window.location.search);
            const macFromUrl = urlParams.get('mac') || urlParams.get('mikrotik_mac') || urlParams.get('client_mac');
            const ipFromUrl = urlParams.get('ip') || urlParams.get('client_ip');
            
            if (macFromUrl && this.isValidMacAddress(macFromUrl) && !macFromUrl.startsWith('02:')) {
                this.deviceMac = macFromUrl.toUpperCase();
                console.log('🎯 MAC REAL capturado da URL:', this.deviceMac);
                if (ipFromUrl) {
                    this.deviceIp = ipFromUrl;
                    console.log('🌐 IP do dispositivo capturado da URL:', this.deviceIp);
                }
                return;
            }

            if (ipFromUrl) {
                this.deviceIp = ipFromUrl;
                console.log('🌐 IP do dispositivo capturado da URL:', this.deviceIp);
            }

            // Se não tem MAC real na URL, tentar aguardar MAC real
            await this.waitForRealMac();
            
        } catch (error) {
            console.error('❌ Erro ao detectar dispositivo:', error);
            // Usar MAC mock como fallback
            this.deviceMac = this.generateMockMac();
            console.log('⚠️ MAC mock gerado como fallback:', this.deviceMac);
        }
    }

    /**
     * Aguarda MAC real ser detectado (máximo 30 segundos)
     */
    async waitForRealMac() {
        console.log('🔍 Aguardando MAC real...');
        const maxAttempts = 6; // 30 segundos total
        
        for (let i = 0; i < maxAttempts; i++) {
            try {
                // Fazer requisição para detectar MAC
                const response = await fetch('/api/detect-device', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken()
                    }
                });

                const data = await response.json();
                const mac = data.mac_address || '';
                const detectedIp = data.client_ip || data.ip_address;
                
                // Se encontrou MAC real (não começa com 02:)
                if (mac && !mac.startsWith('02:')) {
                    this.deviceMac = mac.toUpperCase();
                    console.log('✅ MAC REAL detectado:', this.deviceMac);
                    if (detectedIp) {
                        this.deviceIp = detectedIp;
                        console.log('🌐 IP do dispositivo detectado:', this.deviceIp);
                    }
                    return;
                }
                
                console.log(`⏳ Tentativa ${i + 1}/${maxAttempts} - MAC mock detectado, aguardando real...`);
                
                // Aguardar 5 segundos antes da próxima tentativa
                await new Promise(resolve => setTimeout(resolve, 5000));
                
            } catch (error) {
                console.error('Erro na tentativa', i + 1, ':', error);
            }
        }
        
        // Se não encontrou MAC real, usar o último obtido
        console.warn('⚠️ Timeout: Usando último MAC obtido');
        const response = await fetch('/api/detect-device', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken()
            }
        });
        const data = await response.json();
        this.deviceMac = (data.mac_address || this.generateMockMac()).toUpperCase();
        if (data.client_ip || data.ip_address) {
            this.deviceIp = data.client_ip || data.ip_address;
        }
        console.log('📱 MAC final:', this.deviceMac);
    }

    /**
     * Valida formato do MAC address
     */
    isValidMacAddress(mac) {
        const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
        return macRegex.test(mac);
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
                if (status.ip_address && status.ip_address !== this.deviceIp) {
                    this.deviceIp = status.ip_address;
                    console.log('🌐 IP atualizado pelo status do dispositivo:', this.deviceIp);
                }
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
            this.resetRegistrationForm();
            this.registrationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Reseta o formulário de registro
     */
    resetRegistrationForm() {
        this.currentUserId = null;
        
        const nameInput = document.getElementById('full_name');
        const emailInput = document.getElementById('user_email');
        const phoneInput = document.getElementById('user_phone');
        const submitBtn = document.getElementById('registration-submit-btn');
        const errorDiv = document.getElementById('registration-errors');

        if (nameInput) nameInput.value = '';
        if (emailInput) emailInput.value = '';
        if (phoneInput) phoneInput.value = '';
        
        if (submitBtn) {
            submitBtn.innerHTML = '✅ CONTINUAR PARA PAGAMENTO';
            submitBtn.disabled = false;
        }

        if (errorDiv) {
            errorDiv.classList.add('hidden');
            errorDiv.className = 'hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm';
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
            phone: formData.get('phone').replace(/\D/g, ''), // Remove formatação para enviar apenas números
            user_id: this.currentUserId, // Incluir ID se for usuário existente
            mac_address: this.deviceMac, // 🎯 ADICIONAR MAC ADDRESS
            ip_address: this.deviceIp
        };

        // Validação básica
        if (!data.name || !data.email || !data.phone) {
            this.showRegistrationError('Todos os campos são obrigatórios.');
            return;
        }

        // 🚀 VALIDAR SE MAC FOI DETECTADO
        const identifiersOk = await this.ensureRealIdentifiers();
        if (!identifiersOk) {
            this.showRegistrationError('Não foi possível detectar o dispositivo. Siga as instruções e tente novamente.');
            return;
        }

        data.mac_address = this.deviceMac;
        data.ip_address = this.deviceIp;

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            this.showRegistrationError('Por favor, insira um e-mail válido.');
            return;
        }

        // Validar telefone brasileiro (10 ou 11 dígitos)
        if (data.phone.length < 10 || data.phone.length > 11) {
            this.showRegistrationError('Por favor, insira um telefone válido com DDD.');
            return;
        }

        // Mostrar loading no botão
        const submitBtn = document.getElementById('registration-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = this.currentUserId ? 'ATUALIZANDO...' : 'CADASTRANDO...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('/api/register-for-payment', {
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
                const message = result.existing_user ? 
                    'Dados atualizados com sucesso!' : 
                    'Cadastro realizado com sucesso!';
                this.showSuccessMessage(message);
            } else {
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat();
                    this.showRegistrationError(errorMessages.join('<br>'));
                } else if (result.existing_user_data) {
                    // Usuário já existe, preencher dados automaticamente
                    this.fillUserData(result.existing_user_data);
                    this.showRegistrationError('Usuário já cadastrado! Dados preenchidos automaticamente. Verifique e prossiga para o pagamento.');
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
     * Verifica se usuário já existe por email ou telefone
     */
    async checkExistingUser(field, value) {
        if (!value || value.length < 3) return;

        // Limpar valor dependendo do campo
        let cleanValue = value;
        if (field === 'phone') {
            cleanValue = value.replace(/\D/g, '');
            if (cleanValue.length < 10) return;
        }

        if (field === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(cleanValue)) return;
        }

        try {
            const payload = {};
            payload[field] = cleanValue;

            const response = await fetch('/api/check-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.exists && result.user) {
                this.fillUserData(result.user);
                this.showUserFoundMessage(result.user.name);
            }
        } catch (error) {
            console.error('Erro ao verificar usuário:', error);
        }
    }

    /**
     * Preenche os dados do usuário no formulário
     */
    fillUserData(userData) {
        this.currentUserId = userData.id;

        const nameInput = document.getElementById('full_name');
        const emailInput = document.getElementById('user_email');
        const phoneInput = document.getElementById('user_phone');

        if (nameInput && userData.name) {
            nameInput.value = userData.name;
        }

        if (emailInput && userData.email) {
            emailInput.value = userData.email;
        }

        if (phoneInput && userData.phone) {
            // Aplicar formatação ao telefone
            const formattedPhone = this.formatPhoneNumber(userData.phone);
            phoneInput.value = formattedPhone;
        }

        // Atualizar botão para indicar atualização
        const submitBtn = document.getElementById('registration-submit-btn');
        if (submitBtn) {
            submitBtn.innerHTML = '✅ ATUALIZAR E PAGAR';
        }

        if (!this.hasRealIdentifiers()) {
            this.ensureRealIdentifiers();
        }
    }

    /**
     * Mostra mensagem de usuário encontrado
     */
    showUserFoundMessage(name) {
        const errorDiv = document.getElementById('registration-errors');
        if (errorDiv) {
            errorDiv.innerHTML = `👋 Olá ${name}! Seus dados foram preenchidos automaticamente. Você pode editar se necessário.`;
            errorDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm';
            errorDiv.classList.remove('hidden');
            
            // Esconder após 5 segundos
            setTimeout(() => {
                errorDiv.classList.add('hidden');
                errorDiv.className = 'hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm';
            }, 5000);
        }
    }

    /**
     * Formata número de telefone brasileiro
     */
    formatPhoneNumber(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        
        if (cleanPhone.length === 11) {
            return `(${cleanPhone.substring(0, 2)}) ${cleanPhone.substring(2, 3)} ${cleanPhone.substring(3, 7)}-${cleanPhone.substring(7)}`;
        } else if (cleanPhone.length === 10) {
            return `(${cleanPhone.substring(0, 2)}) ${cleanPhone.substring(2, 6)}-${cleanPhone.substring(6)}`;
        }
        
        return phone;
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
            // 🎯 VALIDAR SE TEMOS MAC E USER_ID
            const identifiersOk = await this.ensureRealIdentifiers();
            if (!identifiersOk) {
                return;
            }

            if (!this.currentUserId) {
                this.showErrorMessage('Erro: Dados do usuário não encontrados. Faça o registro novamente.');
                return;
            }

            const response = await fetch('/api/payment/pix/generate-qr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    amount: 0.10, // 🎯 VALOR ATUALIZADO
                    mac_address: this.deviceMac,
                    user_id: this.currentUserId,
                    ip_address: this.deviceIp
                })
            });

            const result = await response.json();

            if (result.success && result.qr_code) {
                this.hideLoading();
                this.showPixQRCode(result);
                
                // 🎯 LOG PARA DEBUG
                console.log('💳 Pagamento PIX criado:', {
                    payment_id: result.payment_id,
                    mac_address: this.deviceMac,
                    user_id: this.currentUserId,
                    gateway: result.gateway
                });
            } else {
                this.showErrorMessage(result.message || 'Erro ao gerar QR Code PIX.');
            }
        } catch (error) {
            console.error('Erro no pagamento PIX:', error);
            this.showErrorMessage('Erro de conexão. Verifique sua internet.');
        } finally {
            this.hideLoading();
        }
    }

    formatCountdown(seconds) {
        const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        return `${mins}:${secs}`;
    }

    updatePixTimerDisplay(message) {
        const timerText = document.getElementById('pix-timer-text');
        if (timerText && message) {
            timerText.textContent = message;
        } else if (timerText) {
            timerText.textContent = `⏱️ Tempo restante: ${this.formatCountdown(this.pixCountdownSeconds)}`;
        }
    }

    updatePixStatusHint(message) {
        const statusHint = document.getElementById('pix-status-hint');
        if (statusHint) {
            statusHint.textContent = message;
        }
    }

    startPixCountdown() {
        this.stopPixCountdown();
        this.pixCountdownSeconds = 120;
        this.pixPaymentConfirmed = false;
        this.updatePixTimerDisplay();
        this.updatePixStatusHint('Finalize o pagamento em até 2 minutos.');

        this.pixTimerInterval = setInterval(() => {
            if (this.pixPaymentConfirmed) {
                this.stopPixCountdown();
                return;
            }

            this.pixCountdownSeconds -= 1;
            if (this.pixCountdownSeconds <= 0) {
                this.pixCountdownSeconds = 0;
                this.updatePixTimerDisplay();
                this.handlePixTimeout();
                return;
            }

            this.updatePixTimerDisplay();
        }, 1000);
    }

    stopPixCountdown() {
        if (this.pixTimerInterval) {
            clearInterval(this.pixTimerInterval);
            this.pixTimerInterval = null;
        }
    }

    handlePixTimeout() {
        this.stopPixCountdown();
        if (this.paymentCheckInterval) {
            clearInterval(this.paymentCheckInterval);
            this.paymentCheckInterval = null;
        }

        this.updatePixTimerDisplay('⏱️ Tempo esgotado');
        this.updatePixStatusHint('O QR Code expirou. Gere um novo pagamento.');

        const checkButton = document.getElementById('check-payment-status');
        if (checkButton) {
            checkButton.innerHTML = 'Tempo esgotado';
            checkButton.disabled = true;
            checkButton.classList.add('cursor-not-allowed');
        }

        this.showErrorMessage('Tempo para pagamento expirou. Gere um novo QR Code.');
    }

    /**
     * Exibe modal com QR Code PIX
     */
    showPixQRCode(data) {
        const modal = document.createElement('div');
        modal.id = 'pix-qr-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-60 z-50 backdrop-blur-sm';
        
        modal.innerHTML = `
            <div class="flex items-center justify-center h-full p-4">
                <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-sm sm:max-w-md animate-slide-up shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">💳 PIX QR Code</h3>
                        <button id="close-pix-modal" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div class="text-center mb-4">
                        <div class="bg-white p-4 rounded-xl border-2 border-gray-200 mb-4">
                            <img src="${data.qr_code.image_url}" alt="QR Code PIX" class="w-48 h-48 mx-auto">
                        </div>
                        
                        <div class="bg-green-50 rounded-xl p-4 mb-4">
                            <p class="text-green-800 font-bold text-xl">R$ ${data.qr_code.amount}</p>
                            <p class="text-green-600 text-sm">Escaneie o código ou use copia e cola</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-3 mb-4">
                            <p class="text-xs text-gray-600 mb-2">Código PIX:</p>
                            <div class="bg-white border rounded-lg p-2 text-xs break-all" id="pix-code">
                                ${data.qr_code.emv_string}
                            </div>
                            <button id="copy-pix-code" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-lg text-xs hover:bg-blue-600 transition-colors">
                                📋 Copiar Código
                            </button>
                        </div>
                        
                        <div class="bg-yellow-50 rounded-xl p-3 mb-4">
                            <p id="pix-timer-text" class="text-sm font-semibold text-yellow-700">⏱️ Tempo restante: 02:00</p>
                            <p id="pix-status-hint" class="text-xs text-yellow-600 mt-1">Finalize o pagamento em até 2 minutos.</p>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button id="check-payment-status" class="flex-1 bg-green-500 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-600 transition-colors">
                                🔄 Verificar Status
                            </button>
                            <button id="cancel-payment" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg text-sm hover:bg-gray-600 transition-colors">
                                ❌ Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event listeners
        document.getElementById('close-pix-modal').addEventListener('click', () => {
            this.closePixModal();
        });
        
        document.getElementById('copy-pix-code').addEventListener('click', () => {
            this.copyPixCode(data.qr_code.emv_string);
        });
        
        document.getElementById('check-payment-status').addEventListener('click', () => {
            this.checkPaymentStatus(data.payment_id);
        });
        
        document.getElementById('cancel-payment').addEventListener('click', () => {
            this.closePixModal();
        });
        
        // Auto-verificar status a cada 5 segundos
        this.startPixCountdown();
        this.paymentCheckInterval = setInterval(() => {
            this.checkPaymentStatus(data.payment_id);
        }, 5000);
    }

    /**
     * Copia código PIX para a área de transferência
     */
    async copyPixCode(code) {
        try {
            await navigator.clipboard.writeText(code);
            this.showSuccessMessage('Código PIX copiado! Cole no seu app de pagamento.');
        } catch (error) {
            // Fallback para navegadores mais antigos
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showSuccessMessage('Código PIX copiado!');
        }
    }

    /**
     * Verifica status do pagamento
     */
    async checkPaymentStatus(paymentId) {
        console.log('🔄 Verificando status do pagamento:', paymentId);
        
        // Feedback visual
        const checkButton = document.getElementById('check-payment-status');
        if (checkButton) {
            checkButton.innerHTML = '⏳ Verificando...';
            checkButton.disabled = true;
        }
        
        try {
            const response = await fetch(`/api/payment/pix/status?payment_id=${paymentId}`);
            const result = await response.json();
            
            console.log('📊 Resultado da verificação:', result);
            
            if (result.success && result.payment.status === 'completed') {
                console.log('✅ Pagamento confirmado!');
                if (this.paymentCheckInterval) {
                    clearInterval(this.paymentCheckInterval);
                    this.paymentCheckInterval = null;
                }

                this.pixPaymentConfirmed = true;
                this.stopPixCountdown();

                this.updatePixTimerDisplay('✅ Pagamento confirmado!');
                this.updatePixStatusHint('Aguarde até 2 minutos para liberação automática do acesso.');

                const checkButton = document.getElementById('check-payment-status');
                if (checkButton) {
                    checkButton.innerHTML = 'Pagamento confirmado';
                    checkButton.disabled = true;
                }

                const cancelButton = document.getElementById('cancel-payment');
                if (cancelButton) {
                    cancelButton.innerHTML = 'Fechar';
                    cancelButton.classList.remove('bg-gray-500');
                    cancelButton.classList.add('bg-green-500', 'hover:bg-green-600');
                    cancelButton.addEventListener('click', () => {
                        this.closePixModal();
                    });
                }

                this.showSuccessMessage('Pagamento confirmado! Aguarde enquanto liberamos seu acesso.');

                const allowed = await this.allowDevice(this.deviceMac);
                if (allowed) {
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                }
            } else {
                console.log('⏱️ Pagamento ainda pendente');
                this.updatePixStatusHint('Ainda aguardando confirmação do pagamento...');

                // Restaurar botão
                if (checkButton) {
                    setTimeout(() => {
                        checkButton.innerHTML = '🔄 Verificar Status';
                        checkButton.disabled = false;
                    }, 2000);
                }
            }
        } catch (error) {
            console.error('❌ Erro ao verificar status do pagamento:', error);
            this.showErrorMessage('Erro ao verificar status. Tente novamente.');
            
            // Restaurar botão
            if (checkButton) {
                checkButton.innerHTML = '🔄 Verificar Status';
                checkButton.disabled = false;
            }
        }
    }

    /**
     * Fecha modal do PIX
     */
    closePixModal() {
        const modal = document.getElementById('pix-qr-modal');
        if (modal) {
            if (this.paymentCheckInterval) {
                clearInterval(this.paymentCheckInterval);
                this.paymentCheckInterval = null;
            }
            this.stopPixCountdown();
            this.pixPaymentConfirmed = false;
            this.pixCountdownSeconds = 0;
            modal.remove();
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
                    amount: 0.05,
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
            const identifiersOk = await this.ensureRealIdentifiers();
            if (!identifiersOk) {
                this.hideLoading();
                return;
            }

            const response = await fetch('/api/instagram/free-access', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    mac_address: this.deviceMac,
                    ip_address: this.deviceIp,
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
            const identifiersOk = await this.ensureRealIdentifiers();
            if (!identifiersOk) {
                this.hideLoading();
                return;
            }

            const response = await fetch('/api/voucher/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    code: code,
                    mac_address: this.deviceMac,
                    ip_address: this.deviceIp
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
     * Exibe mensagem informativa
     */
    showInfoMessage(message) {
        this.showToast(message, 'info');
    }

    /**
     * Exibe toast notification
     */
    showToast(message, type) {
        const toast = document.createElement('div');
        let bgColor = 'bg-red-500';
        if (type === 'success') bgColor = 'bg-green-500';
        if (type === 'info') bgColor = 'bg-blue-500';
        
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slide-up ${bgColor}`;
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
     * Aplica máscara de telefone brasileiro (XX) X XXXX-XXXX
     */
    applyPhoneMask(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        
        // Limita a 11 dígitos
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        
        // Aplica a máscara
        if (value.length <= 2) {
            value = value.replace(/(\d{0,2})/, '($1');
        } else if (value.length <= 3) {
            value = value.replace(/(\d{2})(\d{0,1})/, '($1) $2');
        } else if (value.length <= 7) {
            value = value.replace(/(\d{2})(\d{1})(\d{0,4})/, '($1) $2 $3');
        } else {
            value = value.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2 $3-$4');
        }
        
        e.target.value = value;
    }

    /**
     * Lida com teclas especiais no campo de telefone
     */
    handlePhoneKeydown(e) {
        // Permite: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true) ||
            // Permite: setas
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        
        // Bloqueia se não for número
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    }

    /**
     * Obtém CSRF token
     */
    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return token || '';
    }

    hasRealIdentifiers() {
        return (
            this.deviceMac &&
            this.deviceMac.length === 17 &&
            !this.deviceMac.startsWith('02:') &&
            this.deviceIp
        );
    }

    redirectToCaptivePortal() {
        const captiveUrl = 'http://login.tocantinswifi.local';
        const returnParam = encodeURIComponent(window.location.href);
        window.location.replace(`${captiveUrl}?return_url=${returnParam}`);
    }

    async ensureRealIdentifiers() {
        if (this.hasRealIdentifiers()) {
            return true;
        }

        try {
            await this.detectDevice();
        } catch (error) {
            console.warn('detecção falhou', error);
        }

        if (this.hasRealIdentifiers()) {
            return true;
        }

        this.showErrorMessage('Não conseguimos identificar seu dispositivo. Você será redirecionado para a tela de login.');
        setTimeout(() => this.redirectToCaptivePortal(), 1500);
        return false;
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
