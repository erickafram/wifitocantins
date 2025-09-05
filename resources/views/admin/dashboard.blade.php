<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - WiFi Tocantins</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <div class="flex h-screen bg-gray-100">
        
        <!-- Sidebar - Menu Lateral -->
        <div class="w-16 bg-white shadow-lg border-r border-gray-200 flex flex-col">
            
            <!-- Logo/Brand -->
            <div class="flex items-center justify-center h-16 border-b border-gray-200">
                <div class="w-8 h-8 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green rounded-lg flex items-center justify-center">
                    <span class="text-white text-xs font-bold">W</span>
                </div>
            </div>

            <!-- Menu Items -->
            <nav class="flex-1 flex flex-col pt-4">
                <div class="space-y-2 px-2">
                    <!-- Dashboard -->
                    <button onclick="showSection('dashboard')" class="menu-item w-12 h-12 rounded-lg bg-tocantins-green text-white flex items-center justify-center hover:bg-tocantins-dark-green transition-colors group relative" title="Dashboard">
                        <span class="text-lg">📊</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            Dashboard
                        </div>
                    </button>

                    <!-- Usuários -->
                    <button onclick="showSection('users')" class="menu-item w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-tocantins-green hover:text-white transition-colors group relative" title="Usuários">
                        <span class="text-lg">👥</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            Usuários
                        </div>
                    </button>

                    <!-- Vouchers -->
                    <button onclick="showSection('vouchers')" class="menu-item w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-tocantins-green hover:text-white transition-colors group relative" title="Vouchers">
                        <span class="text-lg">🎫</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            Vouchers
                        </div>
                    </button>

                    <!-- Relatórios -->
                    <button onclick="showSection('reports')" class="menu-item w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-tocantins-green hover:text-white transition-colors group relative" title="Relatórios">
                        <span class="text-lg">📈</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            Relatórios
                        </div>
                    </button>

                    <!-- Configurações -->
                    <button onclick="showSection('settings')" class="menu-item w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-tocantins-green hover:text-white transition-colors group relative" title="Configurações">
                        <span class="text-lg">⚙️</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            Configurações
                        </div>
                    </button>

                    <!-- MikroTik -->
                    <button onclick="showSection('mikrotik')" class="menu-item w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-tocantins-green hover:text-white transition-colors group relative" title="MikroTik">
                        <span class="text-lg">🌐</span>
                        <div class="absolute left-16 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                            MikroTik
                        </div>
                    </button>
                </div>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-2 border-t border-gray-200">
                <div class="relative group">
                    <button onclick="toggleDropdown()" class="w-12 h-12 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green rounded-lg flex items-center justify-center text-white hover:shadow-lg transition-all">
                        <span class="text-xs font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div id="userDropdown" class="hidden absolute bottom-16 left-0 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="py-2">
                            <div class="px-3 py-2 border-b border-gray-100">
                                <p class="text-xs font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                <p class="text-xs text-tocantins-green mt-1">
                                    {{ Auth::user()->role === 'admin' ? '👑 Administrador' : '👤 Gestor' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                    🚪 Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-lg font-semibold text-tocantins-gray-green">WiFi Tocantins Admin</h1>
                        <p class="text-xs text-gray-500">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <!-- Status Indicators -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Sistema Online</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $stats['connected_users'] }} usuários conectados
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4">
                
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="section-content">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        
                        <!-- Usuários Conectados -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-tocantins-green">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-tocantins-green rounded-lg flex items-center justify-center">
                                        <span class="text-white text-sm">👥</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-600">Usuários Conectados</p>
                                    <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['connected_users'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Receita do Dia -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-tocantins-gold">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-tocantins-gold rounded-lg flex items-center justify-center">
                                        <span class="text-white text-sm">💰</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-600">Receita Hoje</p>
                                    <p class="text-lg font-bold text-tocantins-gray-green">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total de Dispositivos -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <span class="text-white text-sm">📱</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-600">Total Dispositivos</p>
                                    <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['total_devices'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Vouchers Ativos -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <span class="text-white text-sm">🎫</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-600">Vouchers Ativos</p>
                                    <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['active_vouchers'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                        
                        <!-- Gráfico de Receita -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">Receita dos Últimos 7 Dias</h3>
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>

                        <!-- Gráfico de Conexões -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">Conexões por Hora</h3>
                            <canvas id="connectionsChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        
                        <!-- Usuários Online -->
                        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-sm font-semibold text-tocantins-gray-green">Usuários Conectados</h3>
                                <button class="bg-tocantins-green text-white px-3 py-1 rounded text-xs hover:bg-tocantins-dark-green transition-colors">
                                    Atualizar
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Dispositivo</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">IP</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Conectado</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Expira</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($connected_users as $user)
                                        <tr>
                                            <td class="py-2">
                                                <div class="flex items-center">
                                                    <div class="w-6 h-6 bg-tocantins-light-yellow rounded-full flex items-center justify-center mr-2">
                                                        <span class="text-tocantins-gray-green text-xs font-bold">
                                                            {{ substr($user->mac_address, -2) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-900">{{ $user->device_name ?? 'Device' }}</p>
                                                        <p class="text-xs text-gray-500">{{ substr($user->mac_address, 0, 12) }}...</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-2 text-xs text-gray-900">{{ $user->ip_address }}</td>
                                            <td class="py-2 text-xs text-gray-900">
                                                {{ $user->connected_at ? $user->connected_at->format('H:i') : '-' }}
                                            </td>
                                            <td class="py-2 text-xs text-gray-900">
                                                {{ $user->expires_at ? $user->expires_at->format('H:i') : '-' }}
                                            </td>
                                            <td class="py-2">
                                                <button class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 transition-colors"
                                                        onclick="disconnectUser('{{ $user->mac_address }}')">
                                                    Desconectar
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Ações Rápidas & Status -->
                        <div class="space-y-4">
                            
                            <!-- Ações Rápidas -->
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">Ações Rápidas</h3>
                                
                                <div class="space-y-2">
                                    <button class="w-full bg-tocantins-gold text-white py-2 px-3 rounded text-xs hover:bg-yellow-600 transition-colors flex items-center justify-center">
                                        <span class="mr-2">🎫</span>
                                        Gerar Vouchers
                                    </button>

                                    <button class="w-full bg-tocantins-green text-white py-2 px-3 rounded text-xs hover:bg-tocantins-dark-green transition-colors flex items-center justify-center">
                                        <span class="mr-2">📊</span>
                                        Ver Relatórios
                                    </button>

                                    <button class="w-full bg-blue-500 text-white py-2 px-3 rounded text-xs hover:bg-blue-600 transition-colors flex items-center justify-center">
                                        <span class="mr-2">⚙️</span>
                                        Config. MikroTik
                                    </button>

                                    <button class="w-full bg-gray-500 text-white py-2 px-3 rounded text-xs hover:bg-gray-600 transition-colors flex items-center justify-center">
                                        <span class="mr-2">💾</span>
                                        Backup Dados
                                    </button>
                                </div>
                            </div>

                            <!-- Status do Sistema -->
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Status do Sistema</h4>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">MikroTik</span>
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Database</span>
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Payment Gateway</span>
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Starlink</span>
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outras Seções (hidden por padrão) -->
                <div id="users-section" class="section-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">Gerenciar Usuários</h2>
                        <p class="text-sm text-gray-600">Seção de gerenciamento de usuários em desenvolvimento...</p>
                    </div>
                </div>

                <div id="vouchers-section" class="section-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">Gerenciar Vouchers</h2>
                        <p class="text-sm text-gray-600">Seção de gerenciamento de vouchers em desenvolvimento...</p>
                    </div>
                </div>

                <div id="reports-section" class="section-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">Relatórios</h2>
                        <p class="text-sm text-gray-600">Seção de relatórios em desenvolvimento...</p>
                    </div>
                </div>

                <div id="settings-section" class="section-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">Configurações</h2>
                        <p class="text-sm text-gray-600">Seção de configurações em desenvolvimento...</p>
                    </div>
                </div>

                <div id="mikrotik-section" class="section-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">MikroTik</h2>
                        <p class="text-sm text-gray-600">Seção de configurações MikroTik em desenvolvimento...</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        // Gráfico de Receita
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue_chart['labels']) !!},
                datasets: [{
                    label: 'Receita (R$)',
                    data: {!! json_encode($revenue_chart['data']) !!},
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Conexões
        const connectionsCtx = document.getElementById('connectionsChart').getContext('2d');
        new Chart(connectionsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($connections_chart['labels']) !!},
                datasets: [{
                    label: 'Conexões',
                    data: {!! json_encode($connections_chart['data']) !!},
                    backgroundColor: '#228B22',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Função para desconectar usuário
        function disconnectUser(macAddress) {
            if (confirm('Deseja realmente desconectar este usuário?')) {
                fetch('/api/mikrotik/block', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ mac_address: macAddress })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Usuário desconectado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao desconectar usuário: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro de conexão: ' + error.message);
                });
            }
        }

        // Função para mostrar seções
        function showSection(sectionName) {
            // Esconder todas as seções
            const sections = document.querySelectorAll('.section-content');
            sections.forEach(section => section.classList.add('hidden'));
            
            // Mostrar seção selecionada
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            // Atualizar menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.classList.remove('bg-tocantins-green', 'text-white');
                item.classList.add('bg-gray-100', 'text-gray-600');
            });
            
            // Destacar item ativo
            const activeButton = document.querySelector(`button[onclick="showSection('${sectionName}')"]`);
            if (activeButton) {
                activeButton.classList.remove('bg-gray-100', 'text-gray-600');
                activeButton.classList.add('bg-tocantins-green', 'text-white');
            }
        }

        // Auto-refresh apenas para dashboard
        let autoRefreshInterval;
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                const dashboardSection = document.getElementById('dashboard-section');
                if (!dashboardSection.classList.contains('hidden')) {
                    location.reload();
                }
            }, 30000);
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }

        // Iniciar auto-refresh
        startAutoRefresh();

        // Função para toggle do dropdown do usuário
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Fechar dropdown quando clicar fora
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const button = event.target.closest('button[onclick="toggleDropdown()"]');
            
            if (!button && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Inicializar dashboard como ativo
        document.addEventListener('DOMContentLoaded', function() {
            showSection('dashboard');
        });
    </script>
</body>
</html>


