@extends('layouts.admin')

@section('title', 'Relatórios')

@section('breadcrumb')
    <span>›</span>
    <span class="text-tocantins-green font-medium">Relatórios</span>
@endsection

@section('page-title', 'Relatórios - WiFi Tocantins')

@push('scripts')
    <script src="{{ asset('js/reports.js') }}"></script>
@endpush

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200/50">
        <h2 class="text-lg font-bold text-tocantins-gray-green mb-4 flex items-center">
            <span class="mr-2">🔍</span>
            Filtros de Relatório
        </h2>
        
        <form method="GET" action="{{ route('admin.reports') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Data Inicial -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                
                <!-- Data Final -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                
                <!-- Status do Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pagamento</label>
                    <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="all" {{ $paymentStatus == 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="pending" {{ $paymentStatus == 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="completed" {{ $paymentStatus == 'completed' ? 'selected' : '' }}>Pago</option>
                        <option value="failed" {{ $paymentStatus == 'failed' ? 'selected' : '' }}>Falhou</option>
                        <option value="cancelled" {{ $paymentStatus == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                
                <!-- Status do Usuário -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Usuário</label>
                    <select name="user_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="all" {{ $userStatus == 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="connected" {{ $userStatus == 'connected' ? 'selected' : '' }}>Conectado</option>
                        <option value="offline" {{ $userStatus == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="active" {{ $userStatus == 'active' ? 'selected' : '' }}>Ativo</option>
                    </select>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-2 rounded-lg text-sm font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center">
                    <span class="mr-2">📊</span>
                    Aplicar Filtros
                </button>
                
                <a href="{{ route('admin.reports') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors flex items-center">
                    <span class="mr-2">🔄</span>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        
        <!-- Receita Total (Pagos) -->
        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-xl shadow-lg hover:shadow-xl p-4 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-green-500/10 to-green-600/10 rounded-full -translate-y-12 translate-x-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm">💵</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Receita (Pagos)</p>
                        <p class="text-lg font-bold text-green-600">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    Ticket médio: R$ {{ number_format($stats['avg_payment'], 2, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Pagamentos Pendentes -->
        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-xl shadow-lg hover:shadow-xl p-4 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-orange-500/10 to-orange-600/10 rounded-full -translate-y-12 translate-x-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm">⏳</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendentes</p>
                        <p class="text-lg font-bold text-orange-600">R$ {{ number_format($stats['pending_payments'], 2, ',', '.') }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    Aguardando pagamento
                </div>
            </div>
        </div>

        <!-- Total de Pagamentos -->
        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-xl shadow-lg hover:shadow-xl p-4 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-full -translate-y-12 translate-x-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm">💳</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamentos</p>
                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['total_payments'] }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-lg bg-green-50 px-2 py-2 text-center">
                        <span class="mb-1 inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                        <p class="font-semibold text-green-700">{{ $stats['completed_payments_count'] }}</p>
                        <p class="text-green-600">Pagos</p>
                    </div>
                    <div class="rounded-lg bg-yellow-50 px-2 py-2 text-center">
                        <span class="mb-1 inline-block w-2 h-2 bg-yellow-500 rounded-full"></span>
                        <p class="font-semibold text-yellow-700">{{ $stats['pending_payments_count'] }}</p>
                        <p class="text-yellow-600">Pendentes</p>
                    </div>
                    <div class="rounded-lg bg-red-50 px-2 py-2 text-center">
                        <span class="mb-1 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                        <p class="font-semibold text-red-700">{{ $stats['failed_payments_count'] }}</p>
                        <p class="text-red-600">Falhos</p>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Total do periodo com distribuicao completa por status.
                </div>
            </div>
        </div>

        <!-- Total de Usuários -->
        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-xl shadow-lg hover:shadow-xl p-4 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-tocantins-green/10 to-tocantins-dark-green/10 rounded-full -translate-y-12 translate-x-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-tocantins-green to-tocantins-dark-green rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm">👥</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</p>
                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    {{ $stats['connected_users'] }} conectados agora
                </div>
            </div>
        </div>

        <!-- Sessões Ativas -->
        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-xl shadow-lg hover:shadow-xl p-4 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-full -translate-y-12 translate-x-12"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm">🌐</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sessões Ativas</p>
                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['active_sessions'] }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    No período selecionado
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Gráfico de Receita por Dia -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200/50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-tocantins-gray-green">Receita por Dia</h3>
                <div class="text-sm text-gray-500">{{ $startDate }} - {{ $endDate }}</div>
            </div>
            <div class="relative h-80">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfico de Pagamentos por Status -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200/50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-tocantins-gray-green">Pagamentos por Status</h3>
            </div>
            <div class="relative h-80">
                <canvas id="paymentsStatusChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Abas de Conteúdo -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200/50 overflow-hidden">
        
        <!-- Navegação das Abas -->
        <div class="flex border-b border-gray-200">
            <button onclick="showTab('payments')" id="tab-payments" class="tab-button flex-1 px-6 py-4 text-sm font-medium text-tocantins-green border-b-2 border-tocantins-green bg-tocantins-green/5">
                💳 Pagamentos ({{ $payments->total() }})
            </button>
            @if($canViewUsersTab)
            <button onclick="showTab('users')" id="tab-users" class="tab-button flex-1 px-6 py-4 text-sm font-medium text-gray-500 hover:text-tocantins-green transition-colors">
                👥 Usuários ({{ $users->total() }})
            </button>
            @endif
        </div>

        <!-- Conteúdo das Abas -->
        
        <!-- Aba de Pagamentos -->
        <div id="content-payments" class="tab-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-tocantins-gray-green">Lista de Pagamentos</h3>
                <div class="flex space-x-2">
                    @if(auth()->user()?->role === 'admin')
                    <form id="bulk-delete-form" method="POST" action="{{ route('admin.reports.payments.bulk-destroy') }}" onsubmit="return confirmBulkDelete();">
                        @csrf
                        @method('DELETE')
                        <button id="bulk-delete-button" type="submit" disabled class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm opacity-50 cursor-not-allowed transition-all">
                            Excluir selecionados (0)
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('admin.reports.export', ['type' => 'payments', 'format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'payment_status' => $paymentStatus]) }}" 
                       class="bg-tocantins-green text-white px-4 py-2 rounded-lg text-sm hover:bg-tocantins-dark-green transition-colors flex items-center">
                        <span class="mr-2">📥</span>
                        Exportar CSV
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            @if(auth()->user()?->role === 'admin')
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4 w-10">
                                <input type="checkbox" id="select-all-payments" class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                            </th>
                            @endif
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">ID</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Usuário</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Valor</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Tipo</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Status</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Data Pagamento</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Criado em</th>
                            @if(auth()->user()?->role === 'admin')
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            @if(auth()->user()?->role === 'admin')
                            <td class="py-3 px-4">
                                <input type="checkbox" class="payment-checkbox rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green" value="{{ $payment->id }}">
                            </td>
                            @endif
                            <td class="py-3 px-4 text-sm text-gray-900">#{{ $payment->id }}</td>
                            <td class="py-3 px-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $payment->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->user->email ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-tocantins-green">
                                R$ {{ number_format($payment->amount, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $payment->payment_type === 'pix' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $payment->payment_type === 'pix' ? '📱 PIX' : '💳 Cartão' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($payment->status === 'completed') ✅ Pago
                                    @elseif($payment->status === 'pending') ⏳ Pendente
                                    @elseif($payment->status === 'failed') ❌ Falhou - API-TEST-RICK
                                    @else 🚫 Cancelado @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900">
                                {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900">
                                {{ $payment->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            @if(auth()->user()?->role === 'admin')
                            <td class="py-3 px-4 text-sm text-gray-900">
                                <form method="POST" action="{{ route('admin.reports.payments.destroy', $payment) }}" onsubmit="return confirm('Tem certeza que deseja excluir este registro? Esta ação remove também o usuário vinculado e os pagamentos dele.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->role === 'admin' ? 9 : 7 }}" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-2">📊</span>
                                    <p class="text-sm">Nenhum pagamento encontrado no período selecionado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($payments->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando {{ $payments->firstItem() }} a {{ $payments->lastItem() }} de {{ $payments->total() }} pagamentos
                </div>
                <div class="flex items-center gap-2">
                    @if ($payments->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-400 text-sm">Anterior</span>
                    @else
                        <a href="{{ $payments->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Anterior</a>
                    @endif

                    <span class="px-3 py-1.5 rounded-lg border border-tocantins-green/30 bg-tocantins-green/10 text-tocantins-gray-green text-sm font-medium">
                        Página {{ $payments->currentPage() }} de {{ $payments->lastPage() }}
                    </span>

                    @if ($payments->hasMorePages())
                        <a href="{{ $payments->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-400 text-sm">Próxima</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($canViewUsersTab)
        <!-- Aba de Usuários -->
        <div id="content-users" class="tab-content p-6 hidden">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-tocantins-gray-green">Lista de Usuários</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reports.export', ['type' => 'users', 'format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'user_status' => $userStatus]) }}" 
                       class="bg-tocantins-green text-white px-4 py-2 rounded-lg text-sm hover:bg-tocantins-dark-green transition-colors flex items-center">
                        <span class="mr-2">📥</span>
                        Exportar CSV
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">ID</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Usuário</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">MAC Address</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Status</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Conectado em</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Expira em</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-4">Cadastro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm text-gray-900">#{{ $user->id }}</td>
                            <td class="py-3 px-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email ?? 'N/A' }}</p>
                                    @if($user->phone)
                                    <p class="text-xs text-gray-500">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->mac_address)
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-tocantins-light-yellow rounded-full flex items-center justify-center mr-2">
                                        <span class="text-tocantins-gray-green text-xs font-bold">
                                            {{ substr($user->mac_address, -2) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-mono">{{ $user->mac_address }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($user->status === 'connected') bg-green-100 text-green-800
                                    @elseif($user->status === 'active') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($user->status === 'connected') 🟢 Conectado
                                    @elseif($user->status === 'active') 🔵 Ativo
                                    @else ⚫ Offline @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900">
                                {{ $user->connected_at ? $user->connected_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900">
                                {{ $user->expires_at ? $user->expires_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900">
                                {{ $user->created_at->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-2">👥</span>
                                    <p class="text-sm">Nenhum usuário encontrado no período selecionado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} usuários
                </div>
                <div class="flex items-center gap-2">
                    @if ($users->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-400 text-sm">Anterior</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Anterior</a>
                    @endif

                    <span class="px-3 py-1.5 rounded-lg border border-tocantins-green/30 bg-tocantins-green/10 text-tocantins-gray-green text-sm font-medium">
                        Página {{ $users->currentPage() }} de {{ $users->lastPage() }}
                    </span>

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-400 text-sm">Próxima</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Scripts específicos da página -->
    <script>
        // Função para mostrar/esconder abas
        function showTab(tabName) {
            // Esconder todos os conteúdos das abas
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remover classe ativa de todos os botões
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('text-tocantins-green', 'border-tocantins-green', 'bg-tocantins-green/5');
                button.classList.add('text-gray-500');
            });
            
            // Mostrar conteúdo da aba selecionada
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Ativar botão da aba selecionada
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.remove('text-gray-500');
            activeButton.classList.add('text-tocantins-green', 'border-tocantins-green', 'bg-tocantins-green/5');
        }

        function updateBulkDeleteState() {
            const checkboxes = Array.from(document.querySelectorAll('.payment-checkbox'));
            const selected = checkboxes.filter(cb => cb.checked);
            const button = document.getElementById('bulk-delete-button');
            const form = document.getElementById('bulk-delete-form');

            if (!button || !form) {
                return;
            }

            form.querySelectorAll('input[name="payment_ids[]"]').forEach(input => input.remove());

            selected.forEach(cb => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'payment_ids[]';
                hidden.value = cb.value;
                form.appendChild(hidden);
            });

            button.textContent = `Excluir selecionados (${selected.length})`;
            button.disabled = selected.length === 0;

            if (button.disabled) {
                button.classList.add('opacity-50', 'cursor-not-allowed');
                button.classList.remove('hover:bg-red-700');
            } else {
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                button.classList.add('hover:bg-red-700');
            }
        }

        function confirmBulkDelete() {
            const selectedCount = document.querySelectorAll('.payment-checkbox:checked').length;
            if (selectedCount === 0) {
                return false;
            }

            return confirm(`Tem certeza que deseja excluir ${selectedCount} registro(s)? Esta ação remove também os usuários vinculados e pagamentos relacionados.`);
        }

        // Função para inicializar gráficos
        function initializeCharts() {
            // Verificar se os elementos existem antes de criar os gráficos
            const revenueCanvas = document.getElementById('revenueChart');
            const statusCanvas = document.getElementById('paymentsStatusChart');

            if (!revenueCanvas || !statusCanvas) {
                console.log('Canvas elements not found, retrying...');
                setTimeout(initializeCharts, 100);
                return;
            }

            try {
                // Gráfico de Receita por Dia
                const revenueCtx = revenueCanvas.getContext('2d');
                
                // Destruir gráfico existente se houver
                if (window.revenueChart instanceof Chart) {
                    window.revenueChart.destroy();
                }

                window.revenueChart = new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($charts['revenue_by_day']->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d/m'); })) !!},
                        datasets: [{
                            label: 'Receita (R$)',
                            data: {!! json_encode($charts['revenue_by_day']->pluck('total')) !!},
                            borderColor: '#FFD700',
                            backgroundColor: 'rgba(255, 215, 0, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#FFD700',
                            pointBorderColor: '#FFD700',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Receita: R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        }
                    }
                });

                // Gráfico de Pagamentos por Status
                const statusCtx = statusCanvas.getContext('2d');
                
                // Destruir gráfico existente se houver
                if (window.statusChart instanceof Chart) {
                    window.statusChart.destroy();
                }

                window.statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($charts['payments_by_status']->pluck('status')->map(function($status) { 
                            return $status === 'completed' ? 'Pago' : ($status === 'pending' ? 'Pendente' : ($status === 'failed' ? 'Falhou' : 'Cancelado')); 
                        })) !!},
                        datasets: [{
                            data: {!! json_encode($charts['payments_by_status']->pluck('count')) !!},
                            backgroundColor: [
                                '#10B981', // green - completed
                                '#F59E0B', // yellow - pending  
                                '#EF4444', // red - failed
                                '#6B7280'  // gray - cancelled
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverBorderWidth: 4,
                            hoverBorderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1000
                        }
                    }
                });

                console.log('Charts initialized successfully');
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        }

        // Inicializar primeira aba como ativa e gráficos
        document.addEventListener('DOMContentLoaded', function() {
            showTab('payments');
            // Aguardar um pouco para garantir que o DOM está completamente carregado
            setTimeout(initializeCharts, 500);

            const selectAll = document.getElementById('select-all-payments');
            const checkboxes = Array.from(document.querySelectorAll('.payment-checkbox'));

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkDeleteState();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (selectAll) {
                        selectAll.checked = checkboxes.length > 0 && checkboxes.every(item => item.checked);
                    }
                    updateBulkDeleteState();
                });
            });

            updateBulkDeleteState();
        });

        // Reinicializar gráficos se a janela for redimensionada
        window.addEventListener('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                if (window.revenueChart instanceof Chart) {
                    window.revenueChart.resize();
                }
                if (window.statusChart instanceof Chart) {
                    window.statusChart.resize();
                }
            }, 100);
        });
    </script>
@endsection