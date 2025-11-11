/**
 * Portal WiFi Tocantins - TypeScript
 * Sistema de conectividade para ônibus com Starlink
 */

interface ConnectionStatus {
    connected: boolean;
    mac_address: string;
    ip_address?: string;
    expires_at?: string;
    data_used: number;
    status: 'offline' | 'connected' | 'expired';
}

interface UsageStats {
    data_used: number;
    session_duration: number;
    download_speed: number;
    upload_speed: number;
}

interface PaymentData {
    method: 'pix' | 'card' | 'voucher';
    amount: number;
    voucher_code?: string;
}

class WiFiPortal {
    private deviceMac: string = '';
    private connectionCheckInterval: number | null = null;
    private loadingOverlay: HTMLElement | null = null;
    private paymentModal: HTMLElement | null = null;

    constructor() {
        this.init();
    }

    private init(): void {
        this.setupElements();
        this.setupEventListeners();
        this.detectDevice();
        this.checkConnectionStatus();
    }

    private setupElements(): void {
        this.loadingOverlay = document.getElementById('loading-overlay');
        this.paymentModal = document.getElementById('payment-modal');
    }

    private setupEventListeners(): void {
        // Botão principal de conectar
        const connectBtn = document.getElementById('connect-btn');
        connectBtn?.addEventListener('click', () => this.showPaymentModal());

        // Fechar modal
        const closeModal = document.getElementById('close-modal');
        closeModal?.addEventListener('click', () => this.hidePaymentModal());

        // Botão voucher
        const voucherBtn = document.getElementById('voucher-btn');
        voucherBtn?.addEventListener('click', () => this.applyVoucher());

        // Enter no campo voucher
        const voucherInput = document.getElementById('voucher-code') as HTMLInputElement;
        voucherInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.applyVoucher();
            }
        });

        // Botões de pagamento
        document.querySelectorAll('[data-payment-method]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const method = (e.target as HTMLElement).dataset.paymentMethod as 'pix' | 'card';
                this.processPayment({ method, amount: 5.00 });
            });
        });

        // Gerenciar conexão
        const manageBtn = document.getElementById('manage-connection');
        manageBtn?.addEventListener('click', () => this.showConnectionManager());
    }

    /**
     * Detecta o MAC address do dispositivo
     */
    private async detectDevice(): Promise<void> {
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
        }
    }

    /**
     * Verifica status da conexão
     */
    private async checkConnectionStatus(): Promise<void> {
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
    private async getDeviceStatus(macAddress: string): Promise<ConnectionStatus> {
        const response = await fetch(`/api/mikrotik/status/${macAddress}`, {
            headers: {
                'X-CSRF-TOKEN': this.getCSRFToken()
            }
        });

        if (!response.ok) {
            throw new Error('Erro ao obter status do dispositivo');
        }

        return await response.json();
    }

    /**
     * Libera acesso para o dispositivo
     */
    private async allowDevice(macAddress: string): Promise<boolean> {
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
            return false;
        }
    }

    /**
     * Bloqueia dispositivo
     */
    private async blockDevice(macAddress: string): Promise<boolean> {
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
    private async getUsageData(macAddress: string): Promise<UsageStats> {
        const response = await fetch(`/api/mikrotik/usage/${macAddress}`, {
            headers: {
                'X-CSRF-TOKEN': this.getCSRFToken()
            }
        });

        if (!response.ok) {
            throw new Error('Erro ao obter dados de uso');
        }

        return await response.json();
    }

    /**
     * Mostra modal de pagamento
     */
    private showPaymentModal(): void {
        this.paymentModal?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Esconde modal de pagamento
     */
    private hidePaymentModal(): void {
        this.paymentModal?.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    /**
     * Processa pagamento
     */
    private async processPayment(paymentData: PaymentData): Promise<void> {
        this.showLoading();
        this.hidePaymentModal();

        try {
            const response = await fetch('/api/payment/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    ...paymentData,
                    mac_address: this.deviceMac
                })
            });

            const result = await response.json();

            if (result.success) {
                // Pagamento aprovado - liberar acesso
                const allowed = await this.allowDevice(this.deviceMac);
                
                if (allowed) {
                    this.showSuccessMessage('Pagamento aprovado! Conectando...');
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                } else {
                    this.showErrorMessage('Erro ao liberar acesso. Tente novamente.');
                }
            } else {
                this.showErrorMessage(result.message || 'Erro no pagamento. Tente novamente.');
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
    private async applyVoucher(): Promise<void> {
        const voucherInput = document.getElementById('voucher-code') as HTMLInputElement;
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
                // Voucher válido - liberar acesso
                const allowed = await this.allowDevice(this.deviceMac);
                
                if (allowed) {
                    this.showSuccessMessage('Voucher aplicado! Conectando...');
                    voucherInput.value = '';
                    setTimeout(() => {
                        this.checkConnectionStatus();
                    }, 2000);
                } else {
                    this.showErrorMessage('Erro ao liberar acesso. Tente novamente.');
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
    private updateConnectionUI(status: ConnectionStatus): void {
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
    private showManageButton(): void {
        const manageBtn = document.getElementById('manage-connection');
        manageBtn?.classList.remove('hidden');
    }

    /**
     * Inicia monitoramento da conexão
     */
    private startConnectionMonitoring(): void {
        if (this.connectionCheckInterval) {
            clearInterval(this.connectionCheckInterval);
        }

        this.connectionCheckInterval = window.setInterval(() => {
            this.checkConnectionStatus();
        }, 30000); // Verifica a cada 30 segundos
    }

    /**
     * Mostra gerenciador de conexão
     */
    private showConnectionManager(): void {
        // Implementar modal de gerenciamento
        alert('Funcionalidade de gerenciamento em desenvolvimento');
    }

    /**
     * Exibe loading
     */
    private showLoading(): void {
        this.loadingOverlay?.classList.remove('hidden');
    }

    /**
     * Esconde loading
     */
    private hideLoading(): void {
        this.loadingOverlay?.classList.add('hidden');
    }

    /**
     * Exibe mensagem de sucesso
     */
    private showSuccessMessage(message: string): void {
        this.showToast(message, 'success');
    }

    /**
     * Exibe mensagem de erro
     */
    private showErrorMessage(message: string): void {
        this.showToast(message, 'error');
    }

    /**
     * Exibe toast notification
     */
    private showToast(message: string, type: 'success' | 'error'): void {
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
     * Obtém CSRF token
     */
    private getCSRFToken(): string {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return token || '';
    }
}

// Inicializar quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    new WiFiPortal();
});

// Exportar funções para uso global
(window as any).WiFiPortal = {
    allowDevice: async (macAddress: string) => {
        const portal = new WiFiPortal();
        return await (portal as any).allowDevice(macAddress);
    },
    blockDevice: async (macAddress: string) => {
        const portal = new WiFiPortal();
        return await (portal as any).blockDevice(macAddress);
    },
    getDeviceStatus: async (macAddress: string) => {
        const portal = new WiFiPortal();
        return await (portal as any).getDeviceStatus(macAddress);
    },
    getUsageData: async (macAddress: string) => {
        const portal = new WiFiPortal();
        return await (portal as any).getUsageData(macAddress);
    }
};


