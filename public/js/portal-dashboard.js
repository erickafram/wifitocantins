class PortalDashboard {
    constructor() {
        this.paymentData = window.__portalQrCode || null;
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
                <p class="text-xs text-gray-500 text-center">Use o código copia-e-cola se preferir pagar pelo app.</p>
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

        document.body.appendChild(modal);
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

