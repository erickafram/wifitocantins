<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gerenciar Usuários - WiFi Tocantins</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tocantins-gold': '#FFD700',
                        'tocantins-green': '#228B22',
                        'tocantins-light-cream': '#FFF8DC',
                        'tocantins-dark-green': '#006400',
                        'tocantins-light-yellow': '#FFE55C',
                        'tocantins-gray-green': '#2F4F2F'
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="font-inter bg-gray-100 text-sm">

    <!-- Layout Principal -->
    <div class="flex h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200">
        
        <!-- Sidebar - Menu Lateral -->
        <div class="w-20 bg-gradient-to-b from-white via-gray-50 to-white shadow-2xl border-r border-gray-200 flex flex-col relative">
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-tocantins-green/5 via-transparent to-tocantins-gold/5 pointer-events-none"></div>
            
            <!-- Logo/Brand -->
            <div class="flex items-center justify-center h-20 border-b border-gray-200 relative z-10">
                <div class="w-12 h-12 bg-gradient-to-br from-tocantins-green via-tocantins-dark-green to-green-800 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-all duration-300">
                    <span class="text-white text-lg font-bold">W</span>
                    <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-20 blur"></div>
                </div>
            </div>

            <!-- Menu Items -->
            <nav class="flex-1 flex flex-col pt-6 relative z-10">
                <div class="space-y-3 px-3">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Dashboard">
                        <span class="text-xl">📊</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Dashboard
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Usuários (Ativo) -->
                    <a href="{{ route('admin.users') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 group relative" title="Usuários">
                        <span class="text-xl">👥</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-30 blur group-hover:opacity-50"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Usuários
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Vouchers -->
                    <a href="{{ route('admin.vouchers') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Vouchers">
                        <span class="text-xl">🎫</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Vouchers
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Relatórios -->
                    <a href="{{ route('admin.reports') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Relatórios">
                        <span class="text-xl">📈</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Relatórios
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Integrações API -->
                    <a href="{{ route('admin.api') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Integrações API">
                        <span class="text-xl">🔌</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Integrações API
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Configurações -->
                    <a href="{{ route('admin.settings.index') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Configurações">
                        <span class="text-xl">⚙️</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Configurações
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Dispositivos -->
                    <a href="{{ route('admin.devices') }}" class="menu-item w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center shadow-md hover:shadow-lg hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white transform hover:scale-110 transition-all duration-300 group relative" title="Dispositivos">
                        <span class="text-xl">📱</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Dispositivos
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>
                </div>
            </nav>

            <!-- User Avatar & Logout (fixo no bottom) -->
            <div class="p-3 border-t border-gray-200 relative z-10">
                <div class="w-14 h-14 bg-gradient-to-br from-gray-600 to-gray-800 rounded-2xl flex items-center justify-center text-white shadow-lg group relative">
                    <span class="text-lg">👤</span>
                    <div class="absolute left-20 bottom-0 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl mb-2">
                        {{ auth()->user()->name ?? 'Admin' }}
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-tocantins-gray-green flex items-center">
                            <span class="mr-3 text-3xl">👥</span>
                            Gerenciar Usuários
                        </h1>
                        <p class="text-gray-600 text-sm mt-1">Visualize e gerencie todos os usuários cadastrados no sistema</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Botão Atualizar -->
                        <button onclick="location.reload()" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center space-x-2">
                            <span>🔄</span>
                            <span>Atualizar</span>
                        </button>
                        
                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center space-x-2">
                                <span>🚪</span>
                                <span>Sair</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                
                <!-- Estatísticas Resumidas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total de Usuários -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total de Usuários</p>
                                <p class="text-3xl font-bold">{{ $stats['total_users'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-400 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">👥</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários Conectados -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Conectados</p>
                                <p class="text-3xl font-bold">{{ $stats['connected_users'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-400 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">🟢</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários Cadastrados Hoje -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Cadastros Hoje</p>
                                <p class="text-3xl font-bold">{{ $stats['today_registrations'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-400 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">🆕</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usuários com Pagamentos -->
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-sm font-medium">Com Pagamentos</p>
                                <p class="text-3xl font-bold">{{ $stats['users_with_payments'] }}</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-400 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">💰</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Busca -->
                <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <h3 class="text-lg font-semibold text-tocantins-gray-green">Filtros e Busca</h3>
                        
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <!-- Busca -->
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="searchUsers" 
                                    placeholder="Buscar por nome, email ou telefone..."
                                    class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                                >
                                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                            </div>
                            
                            <!-- Filtro por Status -->
                            <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                                <option value="">Todos os Status</option>
                                <option value="connected">Conectados</option>
                                <option value="offline">Offline</option>
                                <option value="pending">Pendente</option>
                                <option value="active">Ativo</option>
                            </select>
                            
                            <!-- Filtro por Tipo -->
                            <select id="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                                <option value="">Todos os Tipos</option>
                                <option value="user">Usuário</option>
                                <option value="manager">Gerente</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Usuários -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green">
                        <h3 class="text-lg font-semibold text-white">Lista de Usuários</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full" id="usersTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-tocantins-green to-tocantins-dark-green flex items-center justify-center text-white font-semibold">
                                                    {{ $user->name ? strtoupper(substr($user->name, 0, 1)) : '?' }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $user->name ?? 'Nome não informado' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: {{ $user->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email ?? 'Email não informado' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->phone ?? 'Telefone não informado' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->mac_address ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->ip_address ?? 'IP não registrado' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusConfig = [
                                                'connected' => ['bg-green-100', 'text-green-800', '🟢', 'Conectado'],
                                                'offline' => ['bg-gray-100', 'text-gray-800', '⚫', 'Offline'],
                                                'pending' => ['bg-yellow-100', 'text-yellow-800', '🟡', 'Pendente'],
                                                'active' => ['bg-blue-100', 'text-blue-800', '🔵', 'Ativo'],
                                            ];
                                            $config = $statusConfig[$user->status] ?? ['bg-gray-100', 'text-gray-800', '❓', 'Desconhecido'];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config[0] }} {{ $config[1] }}">
                                            {{ $config[2] }} {{ $config[3] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $roleConfig = [
                                                'admin' => ['bg-red-100', 'text-red-800', '👑', 'Admin'],
                                                'manager' => ['bg-purple-100', 'text-purple-800', '👨‍💼', 'Gerente'],
                                                'user' => ['bg-blue-100', 'text-blue-800', '👤', 'Usuário'],
                                            ];
                                            $roleData = $roleConfig[$user->role] ?? ['bg-gray-100', 'text-gray-800', '❓', 'Indefinido'];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleData[0] }} {{ $roleData[1] }}">
                                            {{ $roleData[2] }} {{ $roleData[3] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <span class="text-6xl mb-4">👥</span>
                                            <h3 class="text-lg font-semibold mb-2">Nenhum usuário encontrado</h3>
                                            <p class="text-sm">Não há usuários cadastrados no sistema ainda.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>

            </main>
        </div>
    </div>

    <!-- Modal de Visualização de Usuário -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-tocantins-gray-green">Detalhes do Usuário</h3>
                        <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div id="userModalContent">
                        <!-- Conteúdo será carregado dinamicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>

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
            // Implementar edição de usuário
            alert('Funcionalidade de edição em desenvolvimento');
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
</body>
</html>
