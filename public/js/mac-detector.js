// Sistema de detecção de MAC real melhorado
class MacDetector {
    constructor() {
        this.realMac = null;
        this.attempts = 0;
        this.maxAttempts = 6; // 30 segundos total
    }

    // Detectar MAC real com retry
    async detectRealMac() {
        console.log('🔍 Tentando detectar MAC real...');
        
        for (let i = 0; i < this.maxAttempts; i++) {
            try {
                // Tentar diferentes métodos
                const mac = await this.tryDetectMac();
                
                if (mac && !mac.startsWith('02:')) {
                    console.log('✅ MAC real encontrado:', mac);
                    this.realMac = mac;
                    return mac;
                }
                
                console.log(`⏳ Tentativa ${i + 1}/${this.maxAttempts} - aguardando...`);
                await this.delay(5000); // 5 segundos entre tentativas
                
            } catch (error) {
                console.error('Erro na detecção:', error);
            }
        }
        
        console.warn('⚠️ Não foi possível detectar MAC real');
        return null;
    }

    // Tentar detectar MAC via múltiplos métodos
    async tryDetectMac() {
        // Método 1: Via API detect-device
        try {
            const response = await fetch('/api/detect-device', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            if (data.mac_address && !data.mac_address.startsWith('02:')) {
                return data.mac_address;
            }
        } catch (error) {
            console.log('Método 1 falhou:', error);
        }

        // Método 2: Via parâmetros URL (se vier do hotspot)
        const urlParams = new URLSearchParams(window.location.search);
        const macFromUrl = urlParams.get('mac');
        if (macFromUrl && !macFromUrl.startsWith('02:')) {
            return macFromUrl;
        }

        // Método 3: Via localStorage (cache)
        const cachedMac = localStorage.getItem('real_mac');
        if (cachedMac && !cachedMac.startsWith('02:')) {
            return cachedMac;
        }

        return null;
    }

    // Aguardar com callback visual
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Salvar MAC real detectado
    saveRealMac(mac) {
        if (mac && !mac.startsWith('02:')) {
            localStorage.setItem('real_mac', mac);
            this.realMac = mac;
            console.log('💾 MAC real salvo:', mac);
        }
    }

    // Obter MAC para pagamento
    getMacForPayment() {
        return this.realMac || localStorage.getItem('real_mac') || 'unknown';
    }
}

// Instância global
window.macDetector = new MacDetector();

// Auto-detectar ao carregar página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando detecção de MAC...');
    window.macDetector.detectRealMac();
});
