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
        this.currentPaymentId = null;
        this.pixTimerInterval = null;
        this.pixCountdownSeconds = 0;
        this.pixPaymentConfirmed = false;
        this.releaseCountdownInterval = null;
        this.releaseCountdownSeconds = 0;
        this.sessionDurationHours = window.SESSION_DURATION || 12; // Duração da sessão em horas
        this.init();
    }
    
    /**
     * Calcula o horário de expiração do acesso
     */
    calculateExpirationTime() {
        const now = new Date();
        const expiresAt = new Date(now.getTime() + (this.sessionDurationHours * 60 * 60 * 1000));
        
        const hours = expiresAt.getHours().toString().padStart(2, '0');
        const minutes = expiresAt.getMinutes().toString().padStart(2, '0');
        
        // Formatar data se for outro dia
        const today = new Date();
        if (expiresAt.getDate() !== today.getDate()) {
            const day = expiresAt.getDate().toString().padStart(2, '0');
            const month = (expiresAt.getMonth() + 1).toString().padStart(2, '0');
            return `${day}/${month} às ${hours}:${minutes}`;
        }
        
        return `${hours}:${minutes} horas`;
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
            connectBtn.addEventListener('click', () => this.handleConnectClick());
        }

        // Botão principal de conectar (desktop)
        const connectBtnDesktop = document.getElementById('connect-btn-desktop');
        if (connectBtnDesktop) {
            connectBtnDesktop.addEventListener('click', () => this.handleConnectClick());
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

        // Máscara de telefone (SIMPLIFICADO - sem verificação de usuário existente)
        const phoneInput = document.getElementById('user_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => this.applyPhoneMask(e));
            phoneInput.addEventListener('keydown', (e) => this.handlePhoneKeydown(e));
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
            
            if (macFromUrl && this.isValidMacAddress(macFromUrl)) {
                this.deviceMac = macFromUrl.toUpperCase();
                console.log('🎯 MAC capturado da URL:', this.deviceMac);
                if (this.isRandomizedMac(this.deviceMac)) {
                    console.log('ℹ️ MAC é randomizado (normal em dispositivos modernos)');
                }
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
                
                // Se encontrou MAC válido (aceitar todos, incluindo randomizados)
                if (mac && this.isValidMacAddress(mac)) {
                    this.deviceMac = mac.toUpperCase();
                    console.log('✅ MAC detectado:', this.deviceMac);
                    if (this.isRandomizedMac(this.deviceMac)) {
                        console.log('ℹ️ MAC randomizado (normal em dispositivos modernos)');
                    }
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
     * Verifica se MAC é randomizado (localmente administrado).
     * MACs randomizados são perfeitamente válidos - dispositivos modernos
     * (iOS 14+, Android 10+) usam MACs randomizados por padrão.
     * O MAC randomizado é consistente por rede (mesmo MAC para mesmo SSID).
     */
    isRandomizedMac(mac) {
        if (!mac || mac.length < 2) return false;
        const firstByte = parseInt(mac.substring(0, 2), 16);
        return (firstByte & 0x02) !== 0;
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
     * Reseta o formulário de registro (SIMPLIFICADO)
     */
    resetRegistrationForm() {
        this.currentUserId = null;
        
        const phoneInput = document.getElementById('user_phone');
        const submitBtn = document.getElementById('registration-submit-btn');
        const errorDiv = document.getElementById('registration-errors');

        if (phoneInput) phoneInput.value = '';
        
        if (submitBtn) {
            submitBtn.innerHTML = '📱 GERAR QR CODE PIX';
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
     * Processa submissão do formulário de registro (SIMPLIFICADO - apenas telefone)
     * 🚀 OTIMIZADO: Mostra loading imediato e faz registro + QR Code em paralelo
     */
    async handleRegistrationSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const phone = formData.get('phone').replace(/\D/g, '');

        // Validar telefone brasileiro (10 ou 11 dígitos)
        if (phone.length < 10 || phone.length > 11) {
            this.showRegistrationError('Por favor, insira um telefone válido com DDD (10 ou 11 dígitos).');
            return;
        }

        // 🚀 MOSTRAR LOADING IMEDIATAMENTE
        const submitBtn = document.getElementById('registration-submit-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="animate-pulse">⏳ GERANDO QR CODE...</span>';
        submitBtn.disabled = true;
        
        // Esconder modal e mostrar loading global
        this.hideRegistrationModal();
        this.showLoading();

        try {
            // 🚀 VERIFICAR MAC EM PARALELO (não bloqueia)
            if (!this.deviceMac || this.deviceMac === 'DETECTING...') {
                await this.ensureRealIdentifiers();
            }

            const data = {
                phone: phone,
                user_id: this.currentUserId,
                mac_address: this.deviceMac,
                ip_address: this.deviceIp
            };

            console.log('📤 ENVIANDO PARA BACKEND:', { phone, mac: this.deviceMac, ip: this.deviceIp });

            // 🚀 FAZER REGISTRO
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
                
                // 🚀 GERAR QR CODE IMEDIATAMENTE (já está com loading)
                console.log('✅ Cadastro OK, gerando QR Code PIX...');
                await this.processPixPaymentFast();
            } else {
                this.hideLoading();
                this.showRegistrationModal();
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat();
                    this.showRegistrationError(errorMessages.join('<br>'));
                } else {
                    this.showRegistrationError(result.message || 'Erro no cadastro.');
                }
            }
        } catch (error) {
            console.error('Erro no registro:', error);
            this.hideLoading();
            this.showRegistrationModal();
            this.showRegistrationError('Erro de conexão. Tente novamente.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    /**
     * 🚀 Versão otimizada do processPixPayment (sem validações redundantes)
     */
    async processPixPaymentFast() {
        try {
            const response = await fetch('/api/payment/pix/generate-qr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    amount: window.WIFI_PRICE || 5.99,
                    mac_address: this.deviceMac,
                    user_id: this.currentUserId,
                    ip_address: this.deviceIp
                })
            });

            const result = await response.json();

            if (result.success && result.qr_code) {
                this.hideLoading();
                this.showPixQRCode(result);
                console.log('💳 QR Code gerado:', { payment_id: result.payment_id, gateway: result.gateway });
            } else {
                this.hideLoading();
                this.showErrorMessage(result.message || 'Erro ao gerar QR Code PIX.');
            }
        } catch (error) {
            console.error('Erro no pagamento PIX:', error);
            this.hideLoading();
            this.showErrorMessage('Erro de conexão. Verifique sua internet.');
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
     * Preenche os dados do usuário no formulário (SIMPLIFICADO)
     */
    fillUserData(userData) {
        this.currentUserId = userData.id;

        const phoneInput = document.getElementById('user_phone');

        if (phoneInput && userData.phone) {
            // Aplicar formatação ao telefone
            const formattedPhone = this.formatPhoneNumber(userData.phone);
            phoneInput.value = formattedPhone;
        }

        // ✅ RECUPERAR MAC E IP DO BANCO (para usuários expirados que tentam reconectar)
        if (userData.mac_address && !this.deviceMac) {
            this.deviceMac = userData.mac_address;
            console.log('✅ MAC recuperado do banco:', this.deviceMac);
        }

        if (userData.ip_address && !this.deviceIp) {
            this.deviceIp = userData.ip_address;
            console.log('✅ IP recuperado do banco:', this.deviceIp);
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
     * Verifica se usuário existe e decide se mostra cadastro ou pagamento
     */
    async handleConnectClick() {
        this.showLoading();

        try {
            // Verificar se já temos o MAC
            if (!this.deviceMac) {
                await this.ensureRealIdentifiers();
            }

            if (!this.deviceMac) {
                this.hideLoading();
                this.showErrorMessage('Não foi possível identificar seu dispositivo. Reconecte ao WiFi.');
                return;
            }

            // Verificar se usuário já existe
            const response = await fetch(`/api/user/check-mac/${this.deviceMac}`);
            const data = await response.json();

            this.hideLoading();

            if (data.exists && data.user_id) {
                // Usuário já existe - ir direto para QR Code PIX
                this.currentUserId = data.user_id;
                console.log('✅ Usuário já cadastrado, gerando QR Code PIX direto...');
                this.processPixPayment();
            } else {
                // Usuário novo - mostrar cadastro simplificado (apenas telefone)
                console.log('📝 Novo usuário, mostrando cadastro simplificado');
                this.showRegistrationModal();
            }

        } catch (error) {
            this.hideLoading();
            console.error('Erro ao verificar usuário:', error);
            // Em caso de erro, mostrar cadastro por segurança
            this.showRegistrationModal();
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
                    amount: window.WIFI_PRICE || 5.99, // 🎯 VALOR DINÂMICO DO BANCO
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
        this.pixCountdownSeconds = 180; // 3 minutos
        this.pixPaymentConfirmed = false;
        this.updatePixTimerDisplay();
        this.updatePixStatusHint('Finalize o pagamento em até 3 minutos.');

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
     * Exibe modal com QR Code PIX - Interface com 5 passos
     */
    showPixQRCode(data) {
        const modal = document.createElement('div');
        modal.id = 'pix-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-80 z-50 backdrop-blur-sm';
        
        // Detectar se é dispositivo móvel
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen p-2 overflow-y-auto">
                <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl my-2 max-h-[98vh] flex flex-col">
                    
                    <!-- Header Compacto -->
                    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-xl px-3 py-2 text-white">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📱</span>
                                <span class="text-sm font-bold">Pagamento PIX</span>
                            </div>
                            <span class="text-lg font-bold">R$ ${data.qr_code.amount}</span>
                        </div>
                    </div>
                    
                    <!-- Timeline 5 Passos - Compacta -->
                    <div class="bg-gray-50 px-2 py-2 border-b">
                        <div class="flex items-center justify-between text-center">
                            <div class="flex flex-col items-center flex-1">
                                <div id="step-1" class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-bold">✓</div>
                                <span class="text-[10px] mt-0.5 text-green-600 font-medium">Aviso</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-gray-300 -mt-3"><div id="line-1-2" class="h-full bg-green-500 transition-all" style="width:100%"></div></div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step-2" class="w-6 h-6 rounded-full bg-yellow-500 flex items-center justify-center text-white text-xs font-bold animate-pulse">2</div>
                                <span id="step-2-text" class="text-[10px] mt-0.5 text-yellow-600 font-medium">Code</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-gray-300 -mt-3"><div id="line-2-3" class="h-full bg-gray-300 transition-all" style="width:0%"></div></div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step-3" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-xs font-bold">3</div>
                                <span id="step-3-text" class="text-[10px] mt-0.5 text-gray-400 font-medium">Pagar</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-gray-300 -mt-3"><div id="line-3-4" class="h-full bg-gray-300 transition-all" style="width:0%"></div></div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step-4" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-xs font-bold">4</div>
                                <span id="step-4-text" class="text-[10px] mt-0.5 text-gray-400 font-medium">Liberar</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-gray-300 -mt-3"><div id="line-4-5" class="h-full bg-gray-300 transition-all" style="width:0%"></div></div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step-5" class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-xs font-bold">5</div>
                                <span id="step-5-text" class="text-[10px] mt-0.5 text-gray-400 font-medium">Pronto</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Área de Conteúdo Dinâmico -->
                    <div id="dynamic-content" class="p-3 flex-1 overflow-y-auto">
                        
                        <!-- PASSO 1: Aviso Importante (visível inicialmente) -->
                        <div id="step-1-content" class="hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">⚠️</span>
                                    <span class="text-red-700 font-bold text-sm">IMPORTANTE!</span>
                                </div>
                                <p class="text-red-600 text-xs leading-relaxed">
                                    <strong>Não feche esta tela!</strong> Complete os 5 passos para liberar seu acesso à internet.
                                </p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                <p class="text-blue-700 text-xs leading-relaxed">
                                    <strong>📋 O que vai acontecer:</strong><br>
                                    1. Você verá o QR Code PIX<br>
                                    2. Pague pelo app do banco<br>
                                    3. O status atualiza automaticamente<br>
                                    4. Aguarde a liberação (1 min)<br>
                                    5. Pronto! Navegue à vontade
                                </p>
                            </div>
                            <button id="btn-start-payment" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg text-sm transition-all">
                                ✅ ENTENDI, CONTINUAR
                            </button>
                        </div>
                        
                        <!-- PASSO 2: QR Code -->
                        <div id="step-2-content" class="hidden">
                            <div class="text-center">
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 mb-3">
                                    <p class="text-amber-700 text-xs font-semibold">📱 Abra seu banco e efetue o pagamento</p>
                                </div>
                                ${!isMobile ? `
                                <div class="bg-white p-2 rounded-lg border-2 border-dashed border-green-300 mb-2 inline-block">
                                    <img src="${data.qr_code.image_url}" alt="QR Code" class="w-32 h-32 mx-auto">
                                </div>
                                ` : ''}
                                <div class="bg-blue-50 rounded-lg p-2 mb-2 border border-blue-200">
                                    <p class="text-[10px] font-bold text-blue-900 mb-1">📋 Código Copia e Cola ${isMobile ? '(USE ESTE!)' : ''}</p>
                                    <div class="bg-white border rounded p-1.5 mb-2 max-h-12 overflow-y-auto">
                                        <p class="text-[10px] text-gray-700 break-all font-mono" id="pix-code">${data.qr_code.emv_string}</p>
                                    </div>
                                    <button id="copy-pix-code" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded text-xs">
                                        📋 COPIAR CÓDIGO
                                    </button>
                                </div>
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 mb-2">
                                    <p class="text-amber-700 text-[10px]"><strong>⏱️ Expira em:</strong> <span id="pix-timer-text">03:00</span></p>
                                </div>
                                <button id="btn-paid" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-lg text-sm">
                                    ✅ JÁ PAGUEI - VERIFICAR
                                </button>
                                <p class="text-gray-500 text-[9px] mt-1">O status atualiza automaticamente a cada 5s</p>
                            </div>
                        </div>
                        
                        <!-- PASSO 3: Verificando Pagamento -->
                        <div id="step-3-content" class="hidden text-center">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                                <div class="animate-spin w-10 h-10 border-4 border-yellow-500 border-t-transparent rounded-full mx-auto mb-3"></div>
                                <p class="text-yellow-700 font-bold text-sm mb-1">Verificando pagamento...</p>
                                <p class="text-yellow-600 text-xs">Aguarde, estamos confirmando</p>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-2">
                                <p class="text-gray-600 text-[10px]">🔄 Verificação automática a cada 5s</p>
                            </div>
                        </div>
                        
                        <!-- PASSO 4: Pagamento Confirmado + Liberando -->
                        <div id="step-4-content" class="hidden text-center">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                                <span class="text-3xl">✅</span>
                                <p class="text-green-700 font-bold text-sm mt-1">Seu Pagamento foi Confirmado!</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="animate-spin w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div>
                                <p class="text-blue-700 font-bold text-xs mb-1">Aguarde - Liberando acesso...</p>
                                <p class="text-blue-600 text-[10px] mb-2">Configurando no MikroTik</p>
                                <div class="bg-white rounded p-2 border">
                                    <p class="text-lg font-bold text-blue-600" id="release-countdown">01:00</p>
                                    <div class="w-full bg-blue-100 rounded-full h-1.5 mt-1">
                                        <div id="release-progress" class="bg-blue-500 h-1.5 rounded-full transition-all" style="width:0%"></div>
                                    </div>
                                </div>
                                <p class="text-red-500 text-[10px] mt-2 font-bold">⚠️ Não feche esta tela!</p>
                            </div>
                        </div>
                        
                        <!-- PASSO 5: Conectado -->
                        <div id="step-5-content" class="hidden text-center">
                            <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-lg p-4 text-white">
                                <span class="text-4xl">🎉</span>
                                <p class="font-bold text-lg mt-2">CONECTADO!</p>
                                <p class="text-green-100 text-xs mt-1">Aproveite a internet</p>
                                <div class="bg-white/20 rounded p-2 mt-2">
                                    <p class="text-[10px]">⏰ Você tem acesso até:</p>
                                    <p class="text-sm font-bold" id="access-expires-at">${this.calculateExpirationTime()}</p>
                                </div>
                                <div class="bg-white/10 rounded p-2 mt-2">
                                    <p class="text-[10px]">Redirecionando para Google...</p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Mostrar passo 1 (aviso) primeiro
        document.getElementById('step-1-content').classList.remove('hidden');
        
        // Event: Botão "Entendi, Continuar" - vai para passo 2
        document.getElementById('btn-start-payment').addEventListener('click', () => {
            this.goToStep2();
        });
        
        // Event: Copiar código PIX
        document.getElementById('copy-pix-code')?.addEventListener('click', () => {
            this.copyPixCode(data.qr_code.emv_string);
        });
        
        // Event: Botão "Já Paguei" - vai para passo 3
        document.getElementById('btn-paid')?.addEventListener('click', () => {
            this.goToStep3(data.payment_id);
        });
        
        // Event: Cancelar (se existir o botão)
        document.getElementById('cancel-payment')?.addEventListener('click', () => {
            this.closePixModal();
        });
        
        // Salvar payment_id para uso posterior
        this.currentPaymentId = data.payment_id;
    }
    
    /**
     * Vai para o Passo 2 - QR Code
     */
    goToStep2() {
        // Atualizar timeline
        document.getElementById('step-1').classList.remove('bg-green-500');
        document.getElementById('step-1').classList.add('bg-green-500');
        document.getElementById('step-1').innerHTML = '✓';
        
        document.getElementById('step-2').classList.remove('bg-yellow-500', 'animate-pulse');
        document.getElementById('step-2').classList.add('bg-yellow-500', 'animate-pulse');
        document.getElementById('line-1-2').style.width = '100%';
        document.getElementById('line-1-2').classList.remove('bg-gray-300');
        document.getElementById('line-1-2').classList.add('bg-green-500');
        
        // Trocar conteúdo
        document.getElementById('step-1-content').classList.add('hidden');
        document.getElementById('step-2-content').classList.remove('hidden');
        
        // Iniciar timer
        this.startPixCountdown();
        
        // Iniciar verificação automática
        this.paymentCheckInterval = setInterval(() => {
            this.checkPaymentStatus(this.currentPaymentId);
        }, 5000);
    }
    
    /**
     * Vai para o Passo 3 - Verificando
     */
    goToStep3(paymentId) {
        // Atualizar timeline
        document.getElementById('step-2').classList.remove('bg-yellow-500', 'animate-pulse');
        document.getElementById('step-2').classList.add('bg-green-500');
        document.getElementById('step-2').innerHTML = '✓';
        document.getElementById('step-2-text').classList.remove('text-yellow-600');
        document.getElementById('step-2-text').classList.add('text-green-600');
        
        document.getElementById('step-3').classList.remove('bg-gray-300', 'text-gray-500');
        document.getElementById('step-3').classList.add('bg-yellow-500', 'text-white', 'animate-pulse');
        document.getElementById('step-3-text').classList.remove('text-gray-400');
        document.getElementById('step-3-text').classList.add('text-yellow-600');
        document.getElementById('line-2-3').style.width = '100%';
        document.getElementById('line-2-3').classList.remove('bg-gray-300');
        document.getElementById('line-2-3').classList.add('bg-green-500');
        
        // Trocar conteúdo
        document.getElementById('step-2-content').classList.add('hidden');
        document.getElementById('step-3-content').classList.remove('hidden');
        
        // Verificar pagamento imediatamente
        this.checkPaymentStatus(paymentId);
    }
    
    /**
     * Vai para o Passo 4 - Liberando
     */
    goToStep4() {
        this.pixPaymentConfirmed = true;
        this.stopPixCountdown();
        
        // Atualizar timeline - Passo 2 concluído (verde)
        document.getElementById('step-2').classList.remove('bg-yellow-500', 'animate-pulse');
        document.getElementById('step-2').classList.add('bg-green-500');
        document.getElementById('step-2').innerHTML = '✓';
        document.getElementById('step-2-text').classList.remove('text-yellow-600');
        document.getElementById('step-2-text').classList.add('text-green-600');
        document.getElementById('line-2-3').style.width = '100%';
        document.getElementById('line-2-3').classList.remove('bg-gray-300');
        document.getElementById('line-2-3').classList.add('bg-green-500');
        
        // Atualizar timeline - Passo 3 concluído (verde)
        document.getElementById('step-3').classList.remove('bg-yellow-500', 'bg-gray-300', 'animate-pulse', 'text-gray-500');
        document.getElementById('step-3').classList.add('bg-green-500', 'text-white');
        document.getElementById('step-3').innerHTML = '✓';
        document.getElementById('step-3-text').classList.remove('text-yellow-600', 'text-gray-400');
        document.getElementById('step-3-text').classList.add('text-green-600');
        document.getElementById('step-3-text').textContent = 'Pago!';
        document.getElementById('line-3-4').style.width = '100%';
        document.getElementById('line-3-4').classList.remove('bg-gray-300');
        document.getElementById('line-3-4').classList.add('bg-green-500');
        
        // Atualizar timeline - Passo 4 ativo (azul pulsando)
        document.getElementById('step-4').classList.remove('bg-gray-300', 'text-gray-500');
        document.getElementById('step-4').classList.add('bg-blue-500', 'text-white', 'animate-pulse');
        document.getElementById('step-4-text').classList.remove('text-gray-400');
        document.getElementById('step-4-text').classList.add('text-blue-600');
        
        // Trocar conteúdo
        document.getElementById('step-2-content')?.classList.add('hidden');
        document.getElementById('step-3-content')?.classList.add('hidden');
        document.getElementById('step-4-content').classList.remove('hidden');
        
        // Esconder botões (pagamento já confirmado)
        document.getElementById('cancel-payment')?.classList.add('hidden');
        document.getElementById('btn-paid')?.classList.add('hidden');
        
        // Iniciar contador de 60 segundos
        this.startReleaseCountdown(60);
    }
    
    /**
     * Vai para o Passo 5 - Conectado
     */
    goToStep5() {
        this.stopReleaseCountdown();
        
        // Atualizar timeline
        document.getElementById('step-4').classList.remove('bg-blue-500', 'animate-pulse');
        document.getElementById('step-4').classList.add('bg-green-500');
        document.getElementById('step-4').innerHTML = '✓';
        document.getElementById('step-4-text').classList.remove('text-blue-600');
        document.getElementById('step-4-text').classList.add('text-green-600');
        document.getElementById('step-4-text').textContent = 'OK!';
        
        document.getElementById('step-5').classList.remove('bg-gray-300', 'text-gray-500');
        document.getElementById('step-5').classList.add('bg-green-500', 'text-white');
        document.getElementById('step-5').innerHTML = '✓';
        document.getElementById('step-5-text').classList.remove('text-gray-400');
        document.getElementById('step-5-text').classList.add('text-green-600');
        document.getElementById('line-4-5').style.width = '100%';
        document.getElementById('line-4-5').classList.remove('bg-gray-300');
        document.getElementById('line-4-5').classList.add('bg-green-500');
        
        // Trocar conteúdo
        document.getElementById('step-4-content').classList.add('hidden');
        document.getElementById('step-5-content').classList.remove('hidden');
        
        // Redirecionar após 3 segundos
        setTimeout(() => {
            window.location.href = 'https://www.google.com';
        }, 3000);
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
     * Verifica status do pagamento - Usa o novo fluxo de 5 passos
     */
    async checkPaymentStatus(paymentId) {
        console.log('🔄 Verificando status do pagamento:', paymentId);
        
        try {
            const response = await fetch(`/api/payment/pix/status?payment_id=${paymentId}`);
            const result = await response.json();
            
            console.log('📊 Resultado da verificação:', result);
            
            if (result.success && result.payment.status === 'completed') {
                console.log('✅ Pagamento confirmado!');
                
                // Parar verificação automática
                if (this.paymentCheckInterval) {
                    clearInterval(this.paymentCheckInterval);
                    this.paymentCheckInterval = null;
                }

                // Ir para passo 4 (Liberando)
                this.goToStep4();
                
                // Liberar dispositivo no MikroTik em background
                this.allowDevice(this.deviceMac);
            } else {
                console.log('⏱️ Pagamento ainda pendente');
            }
        } catch (error) {
            console.error('❌ Erro ao verificar status do pagamento:', error);
        }
    }
    
    /**
     * Inicia contador de liberação - Vai para passo 5 quando terminar
     */
    startReleaseCountdown(seconds) {
        this.releaseCountdownSeconds = seconds;
        this.releaseCountdownInterval = setInterval(() => {
            this.releaseCountdownSeconds--;
            
            const countdownEl = document.getElementById('release-countdown');
            const progressEl = document.getElementById('release-progress');
            
            if (countdownEl) {
                const mins = Math.floor(this.releaseCountdownSeconds / 60).toString().padStart(2, '0');
                const secs = (this.releaseCountdownSeconds % 60).toString().padStart(2, '0');
                countdownEl.textContent = `${mins}:${secs}`;
            }
            
            if (progressEl) {
                const progress = ((seconds - this.releaseCountdownSeconds) / seconds) * 100;
                progressEl.style.width = `${progress}%`;
            }
            
            // Quando o contador chegar a 0, ir para passo 5
            if (this.releaseCountdownSeconds <= 0) {
                this.goToStep5();
            }
        }, 1000);
    }
    
    /**
     * Para contador de liberação
     */
    stopReleaseCountdown() {
        if (this.releaseCountdownInterval) {
            clearInterval(this.releaseCountdownInterval);
            this.releaseCountdownInterval = null;
        }
    }

    /**
     * Fecha modal do PIX
     */
    closePixModal() {
        const modal = document.getElementById('pix-modal');
        if (modal) {
            if (this.paymentCheckInterval) {
                clearInterval(this.paymentCheckInterval);
                this.paymentCheckInterval = null;
            }
            this.stopReleaseCountdown();
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
                this.showSuccessMessage('✅ Pagamento aprovado! Conectando à Starlink...');
                const allowed = await this.allowDevice(this.deviceMac);
                
                if (allowed) {
                    setTimeout(() => {
                        this.showSuccessMessage('🛰️ Conectado à Starlink! Navegue à vontade com a melhor internet do Brasil...');
                        
                        // Redirecionar para o Google após 2 segundos
                        setTimeout(() => {
                            window.location.href = 'https://www.google.com';
                        }, 2000);
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
                this.showSuccessMessage('✅ Voucher aplicado! Conectando à Starlink...');
                if (voucherInput) voucherInput.value = '';
                
                const allowed = await this.allowDevice(this.deviceMac);
                if (allowed) {
                    setTimeout(() => {
                        this.showSuccessMessage('🛰️ Conectado à Starlink! Navegue à vontade com a melhor internet do Brasil...');
                        
                        // Redirecionar para o Google após 2 segundos
                        setTimeout(() => {
                            window.location.href = 'https://www.google.com';
                        }, 2000);
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
            this.isValidMacAddress(this.deviceMac) &&
            this.deviceMac !== '00:00:00:00:00:00' &&
            this.deviceIp
        );
    }

    redirectToCaptivePortal() {
        const captiveUrl = 'http://10.5.50.1';
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
