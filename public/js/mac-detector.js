// Sistema de detecção de MAC melhorado
// NOTA: MACs randomizados (02:xx, 06:xx, etc.) são VÁLIDOS e ACEITOS.
// Dispositivos modernos (iOS 14+, Android 10+) usam MACs randomizados por padrão.
// Esses MACs são consistentes por rede (mesmo MAC para mesmo SSID).
class MacDetector {
    constructor() {
        this.realMac = null;
        this.attempts = 0;
        this.maxAttempts = 6; // 30 segundos total
    }

    // Verificar se MAC é válido (formato correto)
    isValidMac(mac) {
        if (!mac) return false;
        const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
        if (!macRegex.test(mac)) return false;
        // Rejeitar apenas MACs claramente inválidos
        const upper = mac.toUpperCase();
        return upper !== '00:00:00:00:00:00' && upper !== 'FF:FF:FF:FF:FF:FF';
    }

    // Detectar MAC com retry
    async detectRealMac() {
        console.log('🔍 Tentando detectar MAC do dispositivo...');
        
        for (let i = 0; i < this.maxAttempts; i++) {
            try {
                const mac = await this.tryDetectMac();
                
                if (mac && this.isValidMac(mac)) {
                    console.log('✅ MAC encontrado:', mac);
                    this.realMac = mac.toUpperCase();
                    this.saveDetectedMac(this.realMac);
                    return this.realMac;
                }
                
                console.log(`⏳ Tentativa ${i + 1}/${this.maxAttempts} - aguardando...`);
                await this.delay(5000);
                
            } catch (error) {
                console.error('Erro na detecção:', error);
            }
        }
        
        console.warn('⚠️ Não foi possível detectar MAC');
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
            if (data.mac_address && this.isValidMac(data.mac_address)) {
                return data.mac_address;
            }
        } catch (error) {
            console.log('Método 1 falhou:', error);
        }

        // Método 2: Via parâmetros URL (se vier do hotspot)
        const urlParams = new URLSearchParams(window.location.search);
        const macFromUrl = urlParams.get('mac');
        if (macFromUrl && this.isValidMac(macFromUrl)) {
            return macFromUrl;
        }

        // Método 3: Via localStorage (cache)
        const cachedMac = localStorage.getItem('real_mac');
        if (cachedMac && this.isValidMac(cachedMac)) {
            return cachedMac;
        }

        return null;
    }

    // Aguardar com callback visual
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Salvar MAC detectado
    saveDetectedMac(mac) {
        if (mac && this.isValidMac(mac)) {
            localStorage.setItem('real_mac', mac.toUpperCase());
            this.realMac = mac.toUpperCase();
            console.log('💾 MAC salvo:', this.realMac);
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
