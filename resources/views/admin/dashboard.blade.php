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
<body class="font-inter bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-tocantins-gold">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-tocantins-gray-green">
                        🌐 WiFi Tocantins Admin
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ now()->format('d/m/Y H:i') }}</span>
                    
                    <!-- Informações do Usuário -->
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-tocantins-gray-green">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ Auth::user()->role === 'admin' ? '👑 Administrador' : '👤 Gestor' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </div>
                        
                        <!-- Menu Dropdown -->
                        <div class="relative">
                            <button onclick="toggleDropdown()" class="text-gray-600 hover:text-tocantins-green transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            🚪 Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            
            <!-- Usuários Conectados -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-tocantins-green">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-tocantins-green rounded-full flex items-center justify-center">
                            <span class="text-white text-sm">👥</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Usuários Conectados</p>
                        <p class="text-lg sm:text-xl md:text-2xl font-bold text-tocantins-gray-green">{{ $stats['connected_users'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Receita do Dia -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-tocantins-gold">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-tocantins-gold rounded-full flex items-center justify-center">
                            <span class="text-white text-sm">💰</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Receita Hoje</p>
                        <p class="text-lg sm:text-xl md:text-2xl font-bold text-tocantins-gray-green">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Total de Dispositivos -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm">📱</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Dispositivos</p>
                        <p class="text-lg sm:text-xl md:text-2xl font-bold text-tocantins-gray-green">{{ $stats['total_devices'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Vouchers Ativos -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm">🎫</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Vouchers Ativos</p>
                        <p class="text-lg sm:text-xl md:text-2xl font-bold text-tocantins-gray-green">{{ $stats['active_vouchers'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Gráfico de Receita -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-base sm:text-lg font-semibold text-tocantins-gray-green mb-4">Receita dos Últimos 7 Dias</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Gráfico de Conexões -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-base sm:text-lg font-semibold text-tocantins-gray-green mb-4">Conexões por Hora</h3>
                <canvas id="connectionsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Usuários Online -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-tocantins-gray-green">Usuários Conectados</h3>
                    <button class="bg-tocantins-green text-white px-3 py-1 sm:px-4 sm:py-2 rounded-lg text-xs sm:text-sm hover:bg-tocantins-dark-green transition-colors">
                        Atualizar
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Dispositivo</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">IP</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Conectado em</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Expira em</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($connected_users as $user)
                            <tr>
                                <td class="py-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-tocantins-light-yellow rounded-full flex items-center justify-center mr-3">
                                            <span class="text-tocantins-gray-green text-xs font-bold">
                                                {{ substr($user->mac_address, -2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->device_name ?? 'Device' }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->mac_address }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-sm text-gray-900">{{ $user->ip_address }}</td>
                                <td class="py-3 text-sm text-gray-900">
                                    {{ $user->connected_at ? $user->connected_at->format('H:i') : '-' }}
                                </td>
                                <td class="py-3 text-sm text-gray-900">
                                    {{ $user->expires_at ? $user->expires_at->format('H:i') : '-' }}
                                </td>
                                <td class="py-3">
                                    <button class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors"
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

            <!-- Ações Rápidas -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-base sm:text-lg font-semibold text-tocantins-gray-green mb-4">Ações Rápidas</h3>
                
                <div class="space-y-3">
                    <!-- Gerar Vouchers -->
                    <button class="w-full bg-tocantins-gold text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-yellow-600 transition-colors flex items-center justify-center text-sm">
                        <span class="mr-2">🎫</span>
                        Gerar Vouchers
                    </button>

                    <!-- Ver Relatórios -->
                    <button class="w-full bg-tocantins-green text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-tocantins-dark-green transition-colors flex items-center justify-center text-sm">
                        <span class="mr-2">📊</span>
                        Ver Relatórios
                    </button>

                    <!-- Configurações MikroTik -->
                    <button class="w-full bg-blue-500 text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center text-sm">
                        <span class="mr-2">⚙️</span>
                        Config. MikroTik
                    </button>

                    <!-- Backup Dados -->
                    <button class="w-full bg-gray-500 text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-gray-600 transition-colors flex items-center justify-center text-sm">
                        <span class="mr-2">💾</span>
                        Backup Dados
                    </button>
                </div>

                <!-- Status do Sistema -->
                <div class="mt-6 pt-4 border-t border-gray-200">
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

        // Auto-refresh a cada 30 segundos
        setInterval(() => {
            location.reload();
        }, 30000);

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
    </script>
</body>
</html>


