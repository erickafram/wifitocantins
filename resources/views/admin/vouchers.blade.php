@extends('layouts.admin')

@section('title', 'Gerenciar Vouchers')

@section('breadcrumb')
    <span>›</span>
    <span class="text-tocantins-green font-medium">Vouchers</span>
@endsection

@section('page-title', 'Gerenciar Vouchers - WiFi Tocantins')

@section('content')
    <!-- Cabeçalho da Página -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-tocantins-gray-green flex items-center">
                    <span class="mr-3 text-3xl">🎫</span>
                    Gerenciar Vouchers
                </h1>
                <p class="text-gray-600 text-sm mt-1">Crie e gerencie vouchers de acesso gratuito ao WiFi</p>
            </div>
            
            <div class="mt-4 sm:mt-0">
                <button onclick="openCreateModal()" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center space-x-2">
                    <span>➕</span>
                    <span>Criar Vouchers</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-200 text-green-800">
            <div class="flex items-center">
                <span class="mr-2">✅</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-200 text-red-800">
            <div class="flex items-center">
                <span class="mr-2">❌</span>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Estatísticas de Vouchers -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total de Vouchers -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total de Vouchers</p>
                    <p class="text-3xl font-bold">{{ $vouchers->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">🎫</span>
                </div>
            </div>
        </div>

        <!-- Vouchers Ativos -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Ativos</p>
                    <p class="text-3xl font-bold">{{ $vouchers->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">✅</span>
                </div>
            </div>
        </div>

        <!-- Vouchers Usados -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Usados</p>
                    <p class="text-3xl font-bold">{{ $vouchers->where('used_count', '>', 0)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">📝</span>
                </div>
            </div>
        </div>

        <!-- Vouchers Expirados -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Expirados</p>
                    <p class="text-3xl font-bold">{{ $vouchers->where('expires_at', '<', now())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">⏰</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <h3 class="text-lg font-semibold text-tocantins-gray-green">Filtros</h3>
            
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <!-- Busca por código -->
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchVouchers" 
                        placeholder="Buscar por código do voucher..."
                        class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                    >
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                </div>
                
                <!-- Filtro por Status -->
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                    <option value="">Todos os Status</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                    <option value="used">Usado</option>
                    <option value="expired">Expirado</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabela de Vouchers -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green">
            <h3 class="text-lg font-semibold text-white">Lista de Vouchers</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="vouchersTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($vouchers as $voucher)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-tocantins-gold to-yellow-500 flex items-center justify-center text-white font-semibold">
                                        🎫
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 font-mono">
                                        {{ $voucher->code }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $voucher->description ?? 'Sem descrição' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $isExpired = $voucher->expires_at && $voucher->expires_at < now();
                                $isMaxUsed = $voucher->used_count >= $voucher->max_uses;
                                
                                if (!$voucher->is_active) {
                                    $statusConfig = ['bg-gray-100', 'text-gray-800', '❌', 'Inativo'];
                                } elseif ($isExpired) {
                                    $statusConfig = ['bg-red-100', 'text-red-800', '⏰', 'Expirado'];
                                } elseif ($isMaxUsed) {
                                    $statusConfig = ['bg-yellow-100', 'text-yellow-800', '📝', 'Esgotado'];
                                } else {
                                    $statusConfig = ['bg-green-100', 'text-green-800', '✅', 'Ativo'];
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig[0] }} {{ $statusConfig[1] }}">
                                {{ $statusConfig[2] }} {{ $statusConfig[3] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $voucher->used_count }} / {{ $voucher->max_uses }}
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                <div class="bg-tocantins-green h-2 rounded-full" style="width: {{ ($voucher->used_count / $voucher->max_uses) * 100 }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $voucher->expires_at ? $voucher->expires_at->format('d/m/Y H:i') : 'Sem validade' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $voucher->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="copyVoucherCode('{{ $voucher->code }}')" class="text-tocantins-green hover:text-tocantins-dark-green transition-colors duration-200" title="Copiar código">
                                    📋
                                </button>
                                @if($voucher->is_active)
                                <button onclick="deactivateVoucher({{ $voucher->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Desativar">
                                    ❌
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <span class="text-6xl mb-4">🎫</span>
                                <h3 class="text-lg font-semibold mb-2">Nenhum voucher encontrado</h3>
                                <p class="text-sm">Clique em "Criar Vouchers" para começar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($vouchers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de Criação de Vouchers -->
    <div id="createVoucherModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-tocantins-gray-green">Criar Vouchers</h3>
                        <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.vouchers.create') }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                            <input type="number" name="quantity" min="1" max="100" value="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prefixo (opcional)</label>
                            <input type="text" name="prefix" maxlength="10" placeholder="WIFI"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição (opcional)</label>
                            <input type="text" name="description" maxlength="255" placeholder="Descrição do voucher"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Máximo de usos</label>
                            <input type="number" name="max_uses" min="1" value="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data de expiração (opcional)</label>
                            <input type="datetime-local" name="expires_at"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        
                        <div class="flex space-x-3 pt-4">
                            <button type="button" onclick="closeCreateModal()" 
                                    class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="flex-1 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                                Criar Vouchers
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openCreateModal() {
            document.getElementById('createVoucherModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createVoucherModal').classList.add('hidden');
        }

        // Fechar modal clicando fora
        document.getElementById('createVoucherModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });

        // Busca e filtros
        document.getElementById('searchVouchers').addEventListener('input', function() {
            filterVouchers();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            filterVouchers();
        });

        function filterVouchers() {
            const searchTerm = document.getElementById('searchVouchers').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#vouchersTable tbody tr');

            rows.forEach(row => {
                if (row.children.length === 1) return; // Skip empty row

                const voucherData = {
                    code: row.children[0].textContent.toLowerCase(),
                    description: row.children[1].textContent.toLowerCase(),
                    status: row.children[2].textContent.toLowerCase()
                };

                const matchesSearch = !searchTerm || 
                    voucherData.code.includes(searchTerm) || 
                    voucherData.description.includes(searchTerm);

                const matchesStatus = !statusFilter || voucherData.status.includes(statusFilter);

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Copiar código do voucher
        function copyVoucherCode(code) {
            navigator.clipboard.writeText(code).then(function() {
                // Criar notificação temporária
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                notification.textContent = `Código ${code} copiado!`;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }).catch(function() {
                alert('Erro ao copiar código: ' + code);
            });
        }

        // Desativar voucher
        function deactivateVoucher(voucherId) {
            if (confirm('Deseja realmente desativar este voucher?')) {
                fetch(`/admin/vouchers/${voucherId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao desativar voucher');
                    }
                })
                .catch(error => {
                    alert('Erro de conexão');
                });
            }
        }
    </script>
@endsection
