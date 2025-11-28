@extends('portal.layout')

@section('content')
<style>
.tab-active {
    background-color: white;
    color: #2563eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
.tab-inactive {
    color: #6b7280;
}
.tab-inactive:hover {
    color: #374151;
}
</style>
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50/30 to-cyan-50/30 py-10">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">🎫 Voucher de Motorista</h1>
                <p class="text-gray-500 mt-1 text-sm">Ative seu voucher para acesso gratuito ao WiFi</p>
            </div>
            <a href="{{ route('portal.dashboard') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-800 transition-colors">← Voltar</a>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <span class="mr-2">✅</span>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <span class="mr-2">❌</span>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Formulário de Ativação de Voucher -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-8">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl text-white">🎫</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Ativar Voucher</h2>
                <p class="text-gray-500 mt-2">Digite o código do seu voucher para liberar o acesso</p>
            </div>

            <form id="voucherForm" class="space-y-6">
                @csrf
                <div>
                    <label for="voucher_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Código do Voucher
                    </label>
                    <input 
                        type="text" 
                        id="voucher_code" 
                        name="voucher_code" 
                        placeholder="Ex: WIFI-A3B7-K9M2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center font-mono text-lg uppercase"
                        required
                        maxlength="20"
                    >
                </div>

                <button 
                    type="submit" 
                    id="activateBtn"
                    class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white py-4 px-6 rounded-xl font-semibold text-lg hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
                >
                    <span id="btnText">🚀 Ativar Voucher</span>
                    <div id="btnSpinner" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                </button>
            </form>
        </div>

        <!-- Status do Voucher (se ativo) -->
        <div id="voucherStatus" class="bg-white rounded-3xl shadow-2xl p-8 hidden">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl text-green-600">✅</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Voucher Ativo</h3>
                <div id="statusContent" class="text-gray-600 space-y-2">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Verificar Status por Telefone ou Código -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl text-white">🔍</span>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Consultar Voucher</h2>
                <p class="text-gray-500 mt-2">Verifique o status do seu voucher por telefone ou código</p>
            </div>

            <!-- Abas de consulta -->
            <div class="flex mb-6 bg-gray-100 rounded-xl p-1">
                <button id="tabPhone" class="flex-1 py-2 px-4 rounded-lg font-medium transition-all tab-active">
                    📱 Por Telefone
                </button>
                <button id="tabCode" class="flex-1 py-2 px-4 rounded-lg font-medium transition-all tab-inactive">
                    🎫 Por Código
                </button>
            </div>

            <!-- Formulário por Telefone -->
            <form id="checkByPhoneForm" class="space-y-4">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefone
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="(63) 99999-9999"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    id="checkPhoneBtn"
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white py-3 px-6 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
                >
                    <span id="checkPhoneBtnText">🔍 Verificar por Telefone</span>
                    <div id="checkPhoneBtnSpinner" class="hidden animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </button>
            </form>

            <!-- Formulário por Código -->
            <form id="checkByCodeForm" class="space-y-4 hidden">
                @csrf
                <div>
                    <label for="check_voucher_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Código do Voucher
                    </label>
                    <input 
                        type="text" 
                        id="check_voucher_code" 
                        name="voucher_code" 
                        placeholder="Ex: WIFI-A3B7-K9M2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center font-mono text-lg uppercase"
                        maxlength="20"
                    >
                </div>

                <button 
                    type="submit" 
                    id="checkCodeBtn"
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white py-3 px-6 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
                >
                    <span id="checkCodeBtnText">🔍 Verificar por Código</span>
                    <div id="checkCodeBtnSpinner" class="hidden animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </button>
            </form>

            <!-- Resultado da verificação -->
            <div id="statusResult" class="mt-6 hidden">
                <div id="statusResultContent" class="p-4 rounded-xl">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voucherForm = document.getElementById('voucherForm');
    const checkByPhoneForm = document.getElementById('checkByPhoneForm');
    const checkByCodeForm = document.getElementById('checkByCodeForm');
    const phoneInput = document.getElementById('phone');
    
    // Elementos das abas
    const tabPhone = document.getElementById('tabPhone');
    const tabCode = document.getElementById('tabCode');

    // Funcionalidade das abas
    tabPhone.addEventListener('click', function() {
        switchTab('phone');
    });
    
    tabCode.addEventListener('click', function() {
        switchTab('code');
    });
    
    function switchTab(tab) {
        if (tab === 'phone') {
            tabPhone.className = 'flex-1 py-2 px-4 rounded-lg font-medium transition-all bg-white text-blue-600 shadow-sm';
            tabCode.className = 'flex-1 py-2 px-4 rounded-lg font-medium transition-all text-gray-600 hover:text-gray-800';
            checkByPhoneForm.classList.remove('hidden');
            checkByCodeForm.classList.add('hidden');
        } else {
            tabCode.className = 'flex-1 py-2 px-4 rounded-lg font-medium transition-all bg-white text-blue-600 shadow-sm';
            tabPhone.className = 'flex-1 py-2 px-4 rounded-lg font-medium transition-all text-gray-600 hover:text-gray-800';
            checkByCodeForm.classList.remove('hidden');
            checkByPhoneForm.classList.add('hidden');
        }
    }

    // Máscara para telefone
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            if (value.length < 14) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        e.target.value = value;
    });

    // Ativação de voucher
    voucherForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const activateBtn = document.getElementById('activateBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const voucherCode = document.getElementById('voucher_code').value.trim().toUpperCase();

        if (!voucherCode) {
            showAlert('Por favor, digite o código do voucher.', 'error');
            return;
        }

        // Mostrar loading
        activateBtn.disabled = true;
        btnText.textContent = 'Ativando...';
        btnSpinner.classList.remove('hidden');

        // Fazer requisição
        fetch('/api/voucher/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                voucher_code: voucherCode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showVoucherStatus(data);
                showAlert(data.message, 'success');
                voucherForm.reset();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro de conexão. Tente novamente.', 'error');
        })
        .finally(() => {
            // Restaurar botão
            activateBtn.disabled = false;
            btnText.textContent = '🚀 Ativar Voucher';
            btnSpinner.classList.add('hidden');
        });
    });

    // Verificação de status por telefone
    checkByPhoneForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const checkBtn = document.getElementById('checkPhoneBtn');
        const checkBtnText = document.getElementById('checkPhoneBtnText');
        const checkBtnSpinner = document.getElementById('checkPhoneBtnSpinner');
        const phone = phoneInput.value.replace(/\D/g, '');

        if (phone.length < 10) {
            showAlert('Por favor, digite um telefone válido.', 'error');
            return;
        }

        // Mostrar loading
        checkBtn.disabled = true;
        checkBtnText.textContent = 'Verificando...';
        checkBtnSpinner.classList.remove('hidden');

        // Fazer requisição
        fetch('/portal/voucher/check-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                phone: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            showStatusResult(data);
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro de conexão. Tente novamente.', 'error');
        })
        .finally(() => {
            // Restaurar botão
            checkBtn.disabled = false;
            checkBtnText.textContent = '🔍 Verificar por Telefone';
            checkBtnSpinner.classList.add('hidden');
        });
    });

    // Verificação de status por código
    checkByCodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const checkBtn = document.getElementById('checkCodeBtn');
        const checkBtnText = document.getElementById('checkCodeBtnText');
        const checkBtnSpinner = document.getElementById('checkCodeBtnSpinner');
        const voucherCode = document.getElementById('check_voucher_code').value.trim().toUpperCase();

        if (!voucherCode) {
            showAlert('Por favor, digite o código do voucher.', 'error');
            return;
        }

        // Mostrar loading
        checkBtn.disabled = true;
        checkBtnText.textContent = 'Verificando...';
        checkBtnSpinner.classList.remove('hidden');

        // Fazer requisição
        fetch('/portal/voucher/check-by-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                voucher_code: voucherCode
            })
        })
        .then(response => response.json())
        .then(data => {
            showStatusResult(data);
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro de conexão. Tente novamente.', 'error');
        })
        .finally(() => {
            // Restaurar botão
            checkBtn.disabled = false;
            checkBtnText.textContent = '🔍 Verificar por Código';
            checkBtnSpinner.classList.add('hidden');
        });
    });

    function showVoucherStatus(data) {
        const statusDiv = document.getElementById('voucherStatus');
        const statusContent = document.getElementById('statusContent');
        
        statusContent.innerHTML = `
            <p><strong>Motorista:</strong> ${data.driver_name}</p>
            <p><strong>Tipo:</strong> ${data.voucher_type === 'unlimited' ? 'Ilimitado' : 'Limitado'}</p>
            <p><strong>Horas concedidas:</strong> ${data.hours_granted}h</p>
            <p><strong>Expira em:</strong> ${new Date(data.expires_at).toLocaleString('pt-BR')}</p>
            ${data.voucher_type === 'limited' ? `<p><strong>Horas restantes hoje:</strong> ${data.remaining_hours_today}h</p>` : ''}
        `;
        
        statusDiv.classList.remove('hidden');
        statusDiv.scrollIntoView({ behavior: 'smooth' });
    }

    function showStatusResult(data) {
        const resultDiv = document.getElementById('statusResult');
        const resultContent = document.getElementById('statusResultContent');
        
        if (data.success && data.voucher) {
            const voucher = data.voucher;
            const user = data.user;
            const session = data.session;
            
            let statusIcon = '✅';
            let statusText = 'Voucher Encontrado';
            let statusClass = 'bg-green-50 border-green-200 text-green-800';
            
            if (!voucher.is_active) {
                statusIcon = '❌';
                statusText = 'Voucher Inativo';
                statusClass = 'bg-red-50 border-red-200 text-red-800';
            } else if (voucher.expires_at && new Date(voucher.expires_at) < new Date()) {
                statusIcon = '⏰';
                statusText = 'Voucher Expirado';
                statusClass = 'bg-red-50 border-red-200 text-red-800';
            } else if (voucher.is_connected) {
                statusIcon = '🟢';
                statusText = 'Conectado Agora';
                statusClass = 'bg-blue-50 border-blue-200 text-blue-800';
            }
            
            resultContent.className = `p-4 rounded-xl ${statusClass}`;
            
            let html = `
                <div class="flex items-center mb-3">
                    <span class="mr-2 text-lg">${statusIcon}</span>
                    <strong class="text-lg">${statusText}</strong>
                </div>
                <div class="text-sm space-y-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p><strong>Motorista:</strong> ${voucher.driver_name}</p>
                            <p><strong>Código:</strong> ${voucher.code}</p>
                            <p><strong>Tipo:</strong> ${voucher.voucher_type === 'unlimited' ? 'Ilimitado' : 'Limitado'}</p>
                        </div>
                        <div>
                            <p><strong>Status:</strong> ${voucher.is_active ? 'Ativo' : 'Inativo'}</p>
                            ${voucher.activated_at ? `<p><strong>Ativado em:</strong> ${new Date(voucher.activated_at).toLocaleString('pt-BR')}</p>` : '<p><strong>Status:</strong> Nunca ativado</p>'}
                            ${voucher.expires_at ? `<p><strong>Expira em:</strong> ${new Date(voucher.expires_at).toLocaleString('pt-BR')}</p>` : '<p><strong>Validade:</strong> Sem expiração</p>'}
                        </div>
                    </div>
            `;
            
            if (voucher.voucher_type === 'limited') {
                html += `
                    <div class="mt-3 p-3 bg-white bg-opacity-50 rounded-lg">
                        <p><strong>Horas por dia:</strong> ${voucher.daily_hours}h</p>
                        <p><strong>Horas restantes hoje:</strong> ${voucher.remaining_hours_today}h</p>
                        ${voucher.last_used_date ? `<p><strong>Último uso:</strong> ${new Date(voucher.last_used_date).toLocaleDateString('pt-BR')}</p>` : ''}
                    </div>
                `;
            }
            
            if (session && voucher.is_connected) {
                const minutesUsed = session.minutes_used;
                const hoursUsed = Math.floor(minutesUsed / 60);
                const remainingMinutes = minutesUsed % 60;
                
                html += `
                    <div class="mt-3 p-3 bg-white bg-opacity-50 rounded-lg">
                        <p class="font-medium text-blue-700 mb-2">📡 Sessão Ativa</p>
                        <p><strong>Conectado desde:</strong> ${new Date(session.started_at).toLocaleString('pt-BR')}</p>
                        <p><strong>Tempo usado:</strong> ${hoursUsed}h ${remainingMinutes}min</p>
                        <p><strong>Tempo restante:</strong> ${Math.floor(voucher.time_remaining_minutes / 60)}h ${voucher.time_remaining_minutes % 60}min</p>
                    </div>
                `;
            }
            
            if (user) {
                html += `
                    <div class="mt-3 p-3 bg-white bg-opacity-50 rounded-lg">
                        <p class="font-medium text-gray-700 mb-2">🔗 Informações de Conexão</p>
                        ${user.mac_address ? `<p><strong>MAC:</strong> ${user.mac_address}</p>` : ''}
                        ${user.ip_address ? `<p><strong>IP:</strong> ${user.ip_address}</p>` : ''}
                        ${user.phone ? `<p><strong>Telefone:</strong> ${user.phone}</p>` : ''}
                    </div>
                `;
            }
            
            html += '</div>';
            resultContent.innerHTML = html;
        } else {
            resultContent.className = 'p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800';
            resultContent.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-2">ℹ️</span>
                    <span>${data.message || 'Nenhum voucher encontrado.'}</span>
                </div>
            `;
        }
        
        resultDiv.classList.remove('hidden');
        resultDiv.scrollIntoView({ behavior: 'smooth' });
    }

    function showAlert(message, type) {
        // Remove alertas existentes
        const existingAlerts = document.querySelectorAll('.alert-notification');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert-notification fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg max-w-sm ${
            type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'
        }`;
        
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${type === 'success' ? '✅' : '❌'}</span>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endsection
