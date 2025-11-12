@extends('layouts.admin')

@section('title', 'Gerenciar Usuários')

@section('content')
                <!-- Mensagens de Sucesso/Erro -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                        <span class="mr-2">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                        <span class="mr-2">❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                
                <!-- Estatísticas Resumidas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Total de Usuários -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-xs font-medium">Total de Usuários</p>
                                <p class="text-2xl font-bold">{{ $stats['total_users'] }}</p>
                            </div>
                            <div class="w-10 h-10 bg-blue-400 rounded-lg flex items-center justify-center">
                                <span class="text-xl">👥</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários Conectados -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-xs font-medium">Conectados</p>
                                <p class="text-2xl font-bold">{{ $stats['connected_users'] }}</p>
                            </div>
                            <div class="w-10 h-10 bg-green-400 rounded-lg flex items-center justify-center">
                                <span class="text-xl">🟢</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários Cadastrados Hoje -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-xs font-medium">Cadastros Hoje</p>
                                <p class="text-2xl font-bold">{{ $stats['today_registrations'] }}</p>
                            </div>
                            <div class="w-10 h-10 bg-purple-400 rounded-lg flex items-center justify-center">
                                <span class="text-xl">🆕</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários com Pagamentos -->
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-xs font-medium">Com Pagamentos</p>
                                <p class="text-2xl font-bold">{{ $stats['users_with_payments'] }}</p>
                            </div>
                            <div class="w-10 h-10 bg-amber-400 rounded-lg flex items-center justify-center">
                                <span class="text-xl">💰</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Busca -->
                <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-tocantins-gray-green">Filtros e Busca</h3>
                            <a href="{{ route('admin.users.create') }}" class="lg:hidden bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center text-xs">
                                <span class="mr-1">➕</span>
                                Adicionar
                            </a>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <!-- Busca -->
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="searchUsers" 
                                    placeholder="Buscar por nome, email ou telefone..."
                                    class="w-full sm:w-72 pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-xs"
                                >
                                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                            </div>
                            
                            <!-- Filtro por Status -->
                            <select id="statusFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-xs">
                                <option value="">Todos os Status</option>
                                <option value="connected">Conectados</option>
                                <option value="offline">Offline</option>
                                <option value="pending">Pendente</option>
                                <option value="active">Ativo</option>
                            </select>
                            
                            <!-- Filtro por Tipo -->
                            <select id="roleFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-xs">
                                <option value="">Todos os Tipos</option>
                                <option value="user">Usuário</option>
                                <option value="manager">Gerente</option>
                                <option value="admin">Administrador</option>
                            </select>
                            
                            <!-- Botão Adicionar Usuário (Desktop) -->
                            <a href="{{ route('admin.users.create') }}" class="hidden lg:flex bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-4 py-1.5 rounded-lg hover:shadow-lg transition-all duration-300 items-center text-xs font-medium">
                                <span class="mr-1">➕</span>
                                Adicionar Usuário
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Usuários -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green">
                        <h3 class="text-sm font-semibold text-white">Lista de Usuários</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full" id="usersTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-tocantins-green to-tocantins-dark-green flex items-center justify-center text-white text-xs font-semibold">
                                                    {{ $user->name ? strtoupper(substr($user->name, 0, 1)) : '?' }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-xs font-medium text-gray-900">
                                                    {{ $user->name ?? 'Nome não informado' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: {{ $user->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">{{ $user->email ?? 'Email não informado' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->phone ?? 'Telefone não informado' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">{{ $user->mac_address ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->ip_address ?? 'IP não registrado' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $statusConfig = [
                                                'connected' => ['bg-green-100', 'text-green-800', '🟢', 'Conectado'],
                                                'offline' => ['bg-gray-100', 'text-gray-800', '⚫', 'Offline'],
                                                'pending' => ['bg-yellow-100', 'text-yellow-800', '🟡', 'Pendente'],
                                                'active' => ['bg-blue-100', 'text-blue-800', '🔵', 'Ativo'],
                                            ];
                                            $config = $statusConfig[$user->status] ?? ['bg-gray-100', 'text-gray-800', '❓', 'Desconhecido'];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-4 font-semibold rounded-full {{ $config[0] }} {{ $config[1] }}">
                                            {{ $config[2] }} {{ $config[3] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $roleConfig = [
                                                'admin' => ['bg-red-100', 'text-red-800', '👑', 'Admin'],
                                                'manager' => ['bg-purple-100', 'text-purple-800', '👨‍💼', 'Gerente'],
                                                'user' => ['bg-blue-100', 'text-blue-800', '👤', 'Usuário'],
                                            ];
                                            $roleData = $roleConfig[$user->role] ?? ['bg-gray-100', 'text-gray-800', '❓', 'Indefinido'];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-4 font-semibold rounded-full {{ $roleData[0] }} {{ $roleData[1] }}">
                                            {{ $roleData[2] }} {{ $roleData[3] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                        {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewUser({{ $user->id }})" class="text-tocantins-green hover:text-tocantins-dark-green transition-colors duration-200" title="Visualizar">
                                                👁️
                                            </button>
                                            <button onclick="editUser({{ $user->id }})" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Editar">
                                                ✏️
                                            </button>
                                            @if($user->status === 'connected')
                                            <button onclick="disconnectUser({{ $user->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Desconectar">
                                                🔌
                                            </button>
                                            @endif
                                            <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Excluir">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <span class="text-4xl mb-2">👥</span>
                                            <h3 class="text-sm font-semibold mb-1">Nenhum usuário encontrado</h3>
                                            <p class="text-xs">Não há usuários cadastrados no sistema ainda.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    @if($users->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>
@endsection

@push('modals')
    <!-- Modal de Visualização de Usuário -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-tocantins-gray-green">Detalhes do Usuário</h3>
                        <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div id="userModalContent">
                        <!-- Conteúdo será carregado dinamicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        // Busca e filtros
        document.getElementById('searchUsers').addEventListener('input', function() {
            filterUsers();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            filterUsers();
        });

        document.getElementById('roleFilter').addEventListener('change', function() {
            filterUsers();
        });

        function filterUsers() {
            const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                if (row.children.length === 1) return; // Skip empty row

                const userData = {
                    name: row.children[0].textContent.toLowerCase(),
                    email: row.children[1].textContent.toLowerCase(),
                    status: row.children[3].textContent.toLowerCase(),
                    role: row.children[4].textContent.toLowerCase()
                };

                const matchesSearch = !searchTerm || 
                    userData.name.includes(searchTerm) || 
                    userData.email.includes(searchTerm);

                const matchesStatus = !statusFilter || userData.status.includes(statusFilter);
                const matchesRole = !roleFilter || userData.role.includes(roleFilter);

                if (matchesSearch && matchesStatus && matchesRole) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Funções para ações dos usuários
        function viewUser(userId) {
            // Implementar visualização de usuário
            fetch(`/admin/users/${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userModalContent').innerHTML = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.name || 'Não informado'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.email || 'Não informado'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.phone || 'Não informado'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.status}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">MAC Address</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.mac_address || 'Não registrado'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                    <p class="mt-1 text-sm text-gray-900">${data.ip_address || 'Não registrado'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('userModal').classList.remove('hidden');
                })
                .catch(error => {
                    alert('Erro ao carregar dados do usuário');
                });
        }

        function editUser(userId) {
            // Redirecionar para página de edição
            window.location.href = `/admin/users/${userId}/edit`;
        }

        function disconnectUser(userId) {
            if (confirm('Deseja realmente desconectar este usuário?')) {
                // Implementar desconexão
                fetch(`/admin/users/${userId}/disconnect`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao desconectar usuário');
                    }
                });
            }
        }

        function deleteUser(userId) {
            if (confirm('Deseja realmente excluir este usuário? Esta ação não pode ser desfeita.')) {
                // Implementar exclusão
                fetch(`/admin/users/${userId}`, {
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
                        alert('Erro ao excluir usuário');
                    }
                });
            }
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        // Fechar modal clicando fora
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
    </script>
@endpush
