@extends('portal.layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-purple-50/30 to-cyan-50/30 py-10">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Olá, {{ $user->name ?? 'Passageiro' }} 👋</h1>
                <p class="text-gray-500 mt-1 text-sm">Gerencie suas cobranças PIX e conexão a bordo.</p>
            </div>
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-semibold text-tocantins-green hover:text-tocantins-dark-green transition-colors">Sair</button>
            </form>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            @if ($isVoucherUser && $activeVoucher)
                <!-- Seção de Voucher para Motoristas - MELHORADA -->
                <div class="elegant-card rounded-3xl p-6 shadow-2xl bg-gradient-to-br from-white to-blue-50" id="voucher-section">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                                🎫 Voucher Motorista
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">Acesso WiFi Gratuito</p>
                        </div>
                        <span class="px-4 py-2 text-sm font-bold rounded-full voucher-status-badge shadow-md">
                            <span id="voucher-status-text">⏳</span>
                        </span>
                    </div>

                    <div class="space-y-5">
                        <!-- Código do Voucher - Destaque -->
                        <div class="bg-white rounded-2xl p-4 shadow-sm border-2 border-blue-100">
                            <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">Seu Código</p>
                            <p class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 font-mono tracking-wider">
                                {{ $activeVoucher->code }}
                            </p>
                        </div>

                        <!-- Status Dinâmico - Card Melhorado -->
                        <div id="voucher-dynamic-status" class="p-5 rounded-2xl border-2 shadow-inner transition-all duration-300">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="relative">
                                    <div class="w-4 h-4 rounded-full status-indicator"></div>
                                    <div class="w-4 h-4 rounded-full status-indicator absolute top-0 left-0 animate-ping opacity-75"></div>
                                </div>
                                <p class="font-bold text-lg status-message">Verificando...</p>
                            </div>
                            
                            <!-- Grid de Informações -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-white/50 rounded-xl p-3 text-center">
                                    <p class="text-xs text-gray-500 mb-1">Horas Usadas</p>
                                    <p class="text-2xl font-black text-gray-800">
                                        <span id="hours-used">{{ $voucherStatus['hours_used_today'] ?? 0 }}</span>
                                        <span class="text-sm text-gray-400">/ {{ $voucherStatus['total_daily_hours'] ?? 0 }}h</span>
                                    </p>
                                </div>
                                <div class="bg-white/50 rounded-xl p-3 text-center">
                                    <p class="text-xs text-gray-500 mb-1">Tempo Restante</p>
                                    <p class="text-2xl font-black text-blue-600" id="session-time-left">
                                        @if($voucherStatus['session_time_left_minutes'] && $voucherStatus['session_time_left_minutes'] > 0)
                                            {{ floor($voucherStatus['session_time_left_minutes'] / 60) }}h {{ $voucherStatus['session_time_left_minutes'] % 60 }}m
                                        @else
                                            --
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Barra de Progresso Melhorada -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs font-semibold text-gray-600">
                                    <span>Progresso Diário</span>
                                    <span id="progress-percentage" class="text-blue-600">0%</span>
                                </div>
                                <div class="relative w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 h-3 rounded-full transition-all duration-700 ease-out" 
                                         id="progress-bar" style="width: 0%">
                                        <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mensagem de Próximo Reset - Melhorada -->
                            <div id="next-reset-info" class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-400 rounded-lg" style="display: none;">
                                <p class="text-sm font-semibold text-amber-800 mb-1">
                                    ⏰ Próximo reset: <span id="next-reset-time" class="font-mono"></span>
                                </p>
                                <p class="text-xs text-amber-700">
                                    ⏳ Aguarde <span id="time-to-reset" class="font-bold"></span> para usar novamente
                                </p>
                            </div>
                        </div>

                        <!-- Botão de Ação - Melhorado -->
                        <div id="voucher-action-section">
                            <button type="button" id="refresh-voucher-status" 
                                    class="w-full bg-gradient-to-r from-blue-500 via-blue-600 to-purple-600 text-white font-bold py-4 rounded-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 animate-spin hidden" id="refresh-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="refresh-button-text">🔄 Atualizar Status</span>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- Seção de Pagamento para Usuários Regulares -->
                <div class="elegant-card rounded-3xl p-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Pagamento atual</h2>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ ($latestPayment && $latestPayment->status === 'completed') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $latestPayment?->status ?? 'Nenhum' }}
                        </span>
                    </div>

                    @if ($latestPayment)
                        <p class="text-sm text-gray-500 mb-2">Valor</p>
                        <p class="text-3xl font-bold text-gray-900 mb-4">R$ {{ number_format($latestPayment->amount, 2, ',', '.') }}</p>

                        <p class="text-sm text-gray-500 mb-2">Última atualização</p>
                        <p class="text-sm text-gray-700 mb-4">{{ $latestPayment->updated_at->format('d/m/Y H:i') }}</p>

                        <div class="space-y-3">
                            <form action="{{ route('portal.dashboard.payments.regenerate') }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{ $latestPayment->status === 'pending' ? $latestPayment->id : '' }}">
                                <button type="submit" class="w-full connect-button flex items-center justify-center gap-2 py-3 text-white font-semibold rounded-xl">
                                    {{ $latestPayment->status === 'pending' ? '🔄 Gerar Novo QR Code' : '🚀 Comprar Novamente' }}
                                </button>
                            </form>

                            <button type="button" class="w-full border border-tocantins-green rounded-xl py-3 text-tocantins-green font-semibold hover:bg-tocantins-green hover:text-white transition" data-action="show-qrcode" data-payment="{{ $latestPayment->id }}">
                                📱 Ver QR Code Atual
                            </button>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Nenhum pagamento localizado. Clique abaixo para gerar sua primeira cobrança.</p>
                        <form action="{{ route('portal.dashboard.payments.regenerate') }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full connect-button flex items-center justify-center gap-2 py-3 text-white font-semibold rounded-xl">
                                🚀 Gerar QR Code
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="elegant-card rounded-3xl p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Seus dados</h2>
                <div class="space-y-4 text-sm text-gray-600">
                    <div>
                        <p class="text-gray-500 uppercase text-xs">Telefone</p>
                        <p class="font-semibold text-gray-800">{{ $user->phone ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 uppercase text-xs">E-mail</p>
                        <p class="font-semibold text-gray-800">{{ $user->email ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 uppercase text-xs">MAC address</p>
                        <p class="font-mono text-gray-800">{{ $user->mac_address ?? 'Aguardando conexão' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 uppercase text-xs">IP interno</p>
                        <p class="font-mono text-gray-800">{{ $user->ip_address ?? 'Aguardando' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 uppercase text-xs">Status</p>
                        <p class="font-semibold {{ $user->status === 'connected' ? 'text-green-600' : 'text-gray-600' }}">{{ ucfirst($user->status ?? 'offline') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Histórico de pagamentos</h2>
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-100 text-sm uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-left">Valor</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-4 py-3">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @class([
                                            'bg-green-100 text-green-700' => $payment->status === 'completed',
                                            'bg-yellow-100 text-yellow-700' => $payment->status === 'pending',
                                            'bg-red-100 text-red-700' => $payment->status === 'failed' || $payment->status === 'cancelled',
                                            'bg-gray-100 text-gray-600' => $payment->status === 'offline',
                                        ])">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($payment->payment_type === 'pix')
                                        <button type="button" class="text-tocantins-green text-sm font-semibold hover:underline" data-action="show-qrcode" data-payment="{{ $payment->id }}">
                                            Ver QR Code
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400">Cartão</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">Nenhum pagamento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if (session('qr_code'))
    <script>
        window.__portalQrCode = @json(session('qr_code'));
        window.__portalGateway = @json(session('gateway'));
    </script>
@endif

@if ($isVoucherUser && $activeVoucher)
<script>
// Dados iniciais do voucher
window.voucherData = @json($voucherStatus);
window.voucherCode = @json($activeVoucher->code);

// Função para atualizar status do voucher
function updateVoucherStatus(data = null) {
    if (!data) data = window.voucherData;
    
    const statusBadge = document.querySelector('.voucher-status-badge');
    const statusText = document.getElementById('voucher-status-text');
    const statusIndicator = document.querySelector('.status-indicator');
    const statusMessage = document.querySelector('.status-message');
    const dynamicStatus = document.getElementById('voucher-dynamic-status');
    const hoursUsed = document.getElementById('hours-used');
    const totalHours = document.getElementById('total-hours');
    const sessionTimeLeft = document.getElementById('session-time-left');
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const nextResetInfo = document.getElementById('next-reset-info');
    const nextResetTime = document.getElementById('next-reset-time');
    const timeToReset = document.getElementById('time-to-reset');
    
    // Calcular progresso
    const usagePercent = data.total_daily_hours > 0 ? 
        Math.round((data.hours_used_today / data.total_daily_hours) * 100) : 0;
    
    // Atualizar elementos
    hoursUsed.textContent = data.hours_used_today;
    totalHours.textContent = data.total_daily_hours;
    progressBar.style.width = usagePercent + '%';
    progressPercentage.textContent = usagePercent + '%';
    
    // Determinar status e cores
    let statusClass, borderClass, indicatorClass, message, badgeClass;
    
    if (!data.is_valid || !data.can_use_today) {
        // Voucher esgotado ou inválido
        statusClass = 'bg-red-50 text-red-800';
        borderClass = 'border-red-200';
        indicatorClass = 'bg-red-500';
        badgeClass = 'bg-red-100 text-red-700';
        
        if (!data.can_use_today) {
            message = `⏰ Limite de ${data.total_daily_hours}h atingido hoje`;
            statusText.textContent = 'Esgotado';
            
            // Mostrar informações de reset
            if (data.next_reset_time) {
                nextResetInfo.style.display = 'block';
                const resetDate = new Date(data.next_reset_time);
                nextResetTime.textContent = resetDate.toLocaleString('pt-BR');
                
                // Calcular tempo para reset
                const now = new Date();
                const timeUntilReset = resetDate - now;
                const hoursUntilReset = Math.floor(timeUntilReset / (1000 * 60 * 60));
                const minutesUntilReset = Math.floor((timeUntilReset % (1000 * 60 * 60)) / (1000 * 60));
                timeToReset.textContent = `${hoursUntilReset}h ${minutesUntilReset}min`;
            }
        } else {
            message = '❌ Voucher inválido ou expirado';
            statusText.textContent = 'Inválido';
        }
        
        // Alterar cor da barra de progresso para vermelho
        progressBar.className = 'bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full transition-all duration-500';
        
    } else if (data.session_time_left_minutes && data.session_time_left_minutes > 0) {
        // Sessão ativa
        statusClass = 'bg-green-50 text-green-800';
        borderClass = 'border-green-200';
        indicatorClass = 'bg-green-500 animate-pulse';
        badgeClass = 'bg-green-100 text-green-700';
        message = '🟢 Conectado e navegando';
        statusText.textContent = 'Ativo';
        
        // Atualizar tempo restante da sessão
        const hours = Math.floor(data.session_time_left_minutes / 60);
        const minutes = data.session_time_left_minutes % 60;
        sessionTimeLeft.textContent = `${hours}h ${minutes}min`;
        
        nextResetInfo.style.display = 'none';
        
    } else {
        // Voucher válido mas não em uso
        statusClass = 'bg-blue-50 text-blue-800';
        borderClass = 'border-blue-200';
        indicatorClass = 'bg-blue-500';
        badgeClass = 'bg-blue-100 text-blue-700';
        message = `✅ Disponível (${data.remaining_hours_today}h restantes)`;
        statusText.textContent = 'Disponível';
        sessionTimeLeft.textContent = '--';
        nextResetInfo.style.display = 'none';
    }
    
    // Aplicar classes
    statusBadge.className = `px-3 py-1 text-xs font-semibold rounded-full ${badgeClass}`;
    dynamicStatus.className = `p-4 rounded-xl border-2 ${statusClass} ${borderClass}`;
    statusIndicator.className = `w-3 h-3 rounded-full ${indicatorClass}`;
    statusMessage.textContent = message;
}

// Função para buscar status atualizado do servidor
async function refreshVoucherStatus() {
    const button = document.getElementById('refresh-voucher-status');
    const buttonText = document.getElementById('refresh-button-text');
    const spinner = document.getElementById('refresh-spinner');
    const originalText = buttonText.textContent;
    
    try {
        buttonText.textContent = 'Atualizando...';
        spinner.classList.remove('hidden');
        button.disabled = true;
        
        const response = await fetch('/api/voucher/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                voucher_code: window.voucherCode
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                window.voucherData = data.voucher_status;
                updateVoucherStatus(data.voucher_status);
                
                // Mostrar feedback de sucesso
                spinner.classList.add('hidden');
                buttonText.textContent = '✅ Atualizado!';
                setTimeout(() => {
                    buttonText.textContent = originalText;
                }, 2000);
            } else {
                throw new Error(data.message || 'Erro ao atualizar status');
            }
        } else {
            throw new Error('Erro na requisição');
        }
        
    } catch (error) {
        console.error('Erro ao atualizar status do voucher:', error);
        spinner.classList.add('hidden');
        buttonText.textContent = '❌ Erro ao atualizar';
        setTimeout(() => {
            buttonText.textContent = originalText;
        }, 3000);
    } finally {
        button.disabled = false;
    }
}

// Event listeners
document.getElementById('refresh-voucher-status').addEventListener('click', refreshVoucherStatus);

// Atualizar status inicial
updateVoucherStatus();

// Auto-refresh a cada 30 segundos
setInterval(refreshVoucherStatus, 30000);

// Atualizar contador de tempo em tempo real
setInterval(() => {
    if (window.voucherData.session_time_left_minutes > 0) {
        window.voucherData.session_time_left_minutes--;
        
        const sessionTimeLeft = document.getElementById('session-time-left');
        const hours = Math.floor(window.voucherData.session_time_left_minutes / 60);
        const minutes = window.voucherData.session_time_left_minutes % 60;
        sessionTimeLeft.textContent = `${hours}h ${minutes}min`;
        
        // Se chegou a zero, atualizar status
        if (window.voucherData.session_time_left_minutes <= 0) {
            refreshVoucherStatus();
        }
    }
}, 60000); // A cada minuto
</script>
@endif

@endsection

