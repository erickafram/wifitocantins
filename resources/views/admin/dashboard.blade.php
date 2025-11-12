@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="section-content">
                            <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        
                        <!-- Usuários Conectados -->
                        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-tocantins-green/10 to-tocantins-dark-green/10 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-tocantins-green to-tocantins-dark-green rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                        <span class="text-white text-lg">👥</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conectados</p>
                                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['connected_users'] }}</p>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green h-2 rounded-full" style="width: {{ min(($stats['connected_users'] / 100) * 100, 100) }}%"></div>
                    </div>
                </div>
            </div>

                        <!-- Receita do Dia -->
                        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-tocantins-gold/10 to-yellow-500/10 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-tocantins-gold to-yellow-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                        <span class="text-white text-lg">💰</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Receita Hoje</p>
                                        <p class="text-lg font-bold text-tocantins-gray-green">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-tocantins-gold to-yellow-500 h-2 rounded-full" style="width: 75%"></div>
                    </div>
                </div>
            </div>

                        <!-- Total de Dispositivos -->
                        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                        <span class="text-white text-lg">📱</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivos</p>
                                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['total_devices'] }}</p>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Vouchers Ativos -->
                        <div class="group bg-gradient-to-br from-white to-gray-50/50 rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-200/50 backdrop-blur-sm transform hover:scale-105 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                        <span class="text-white text-lg">🎫</span>
                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Vouchers</p>
                        <p class="text-lg font-bold text-tocantins-gray-green">{{ $stats['active_vouchers'] }}</p>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            
            <!-- Gráfico de Receita -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="text-xs font-semibold text-tocantins-gray-green mb-3">Receita dos Últimos 7 Dias</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Gráfico de Conexões -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="text-xs font-semibold text-tocantins-gray-green mb-3">Conexões por Hora</h3>
                <canvas id="connectionsChart" width="400" height="200"></canvas>
            </div>
        </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Usuários Online -->
                        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xs font-semibold text-tocantins-gray-green">Usuários Conectados</h3>
                                <button class="bg-tocantins-green text-white px-2 py-1 rounded text-xs hover:bg-tocantins-dark-green transition-colors">
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
                                <h3 class="text-xs font-semibold text-tocantins-gray-green mb-3">Ações Rápidas</h3>
                
                                <div class="space-y-2">
                                    <button class="w-full bg-tocantins-gold text-white py-2 px-3 rounded text-xs hover:bg-yellow-600 transition-colors flex items-center justify-center">
                        <span class="mr-2">🎫</span>
                        Gerar Vouchers
                    </button>

                                    <a href="{{ route('admin.reports') }}" class="w-full bg-tocantins-green text-white py-2 px-3 rounded text-xs hover:bg-tocantins-dark-green transition-colors flex items-center justify-center">
                        <span class="mr-2">📊</span>
                        Ver Relatórios
                    </a>

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
                    <h4 class="text-xs font-semibold text-gray-700 mb-3">Status do Sistema</h4>
                    
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

                <!-- Seções removidas - agora cada funcionalidade tem sua própria página -->

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

        // Função para mostrar seções (mantida para compatibilidade, mas não mais usada)
        function showSection(sectionName) {
            // Função removida - agora usamos navegação direta por links
            console.log('Navegação direta implementada - função showSection() obsoleta');
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

        // Monitoramento de Internet e MikroTik
        function updateNetworkStatus() {
            // Simular verificação de internet
            fetch('/api/network/status')
                .then(response => response.json())
                .then(data => {
                    updateInternetStatus(data.internet);
                    updateMikroTikStatus(data.mikrotik);
                })
                .catch(error => {
                    // Se falhar, assumir problemas de conectividade
                    updateInternetStatus({
                        status: 'unstable',
                        speed: '0 Mbps',
                        ping: 'Timeout'
                    });
                    updateMikroTikStatus({
                        status: 'offline',
                        uptime: 'Desconectado'
                    });
                });
        }

        function updateInternetStatus(data) {
            const statusElement = document.getElementById('internet-status');
            const speedElement = document.getElementById('internet-speed');
            const pingElement = document.getElementById('internet-ping');

            if (data.status === 'online') {
                statusElement.className = 'w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-lg';
                speedElement.className = 'text-sm font-bold text-tocantins-green';
                speedElement.textContent = data.speed || '100 Mbps';
                pingElement.textContent = data.ping || 'Ping: 15ms';
            } else if (data.status === 'unstable') {
                statusElement.className = 'w-3 h-3 bg-yellow-500 rounded-full animate-pulse shadow-lg';
                speedElement.className = 'text-sm font-bold text-yellow-600';
                speedElement.textContent = data.speed || 'Instável';
                pingElement.textContent = data.ping || 'Ping: Alto';
            } else {
                statusElement.className = 'w-3 h-3 bg-red-500 rounded-full animate-pulse shadow-lg';
                speedElement.className = 'text-sm font-bold text-red-600';
                speedElement.textContent = 'Offline';
                pingElement.textContent = 'Sem conexão';
            }
        }

        function updateMikroTikStatus(data) {
            const statusElement = document.getElementById('mikrotik-status');
            const uptimeElement = document.getElementById('mikrotik-uptime');

            if (data.status === 'online') {
                statusElement.className = 'w-3 h-3 bg-green-500 rounded-full shadow-lg';
                uptimeElement.className = 'text-xs text-tocantins-green font-semibold';
                uptimeElement.textContent = data.uptime || 'Online';
            } else if (data.status === 'warning') {
                statusElement.className = 'w-3 h-3 bg-yellow-500 rounded-full shadow-lg';
                uptimeElement.className = 'text-xs text-yellow-600 font-semibold';
                uptimeElement.textContent = 'Atenção';
            } else {
                statusElement.className = 'w-3 h-3 bg-red-500 rounded-full shadow-lg';
                uptimeElement.className = 'text-xs text-red-600 font-semibold';
                uptimeElement.textContent = 'Offline';
            }
        }

        // Simular dados de rede (remover quando integrar com MikroTik real)
        function simulateNetworkData() {
            const internetStates = ['online', 'unstable', 'offline'];
            const mikrotikStates = ['online', 'warning', 'offline'];
            
            const internetStatus = internetStates[Math.floor(Math.random() * 3)];
            const mikrotikStatus = mikrotikStates[Math.floor(Math.random() * 3)];

            const data = {
                internet: {
                    status: internetStatus,
                    speed: internetStatus === 'online' ? '100 Mbps' : internetStatus === 'unstable' ? '25 Mbps' : '0 Mbps',
                    ping: internetStatus === 'online' ? 'Ping: 15ms' : internetStatus === 'unstable' ? 'Ping: 150ms' : 'Timeout'
                },
                mikrotik: {
                    status: mikrotikStatus,
                    uptime: mikrotikStatus === 'online' ? 'Online 5h 30m' : mikrotikStatus === 'warning' ? 'CPU Alto' : 'Desconectado'
                }
            };

            updateInternetStatus(data.internet);
            updateMikroTikStatus(data.mikrotik);
        }

        // Atualizar status da rede a cada 10 segundos
        setInterval(() => {
            simulateNetworkData(); // Substituir por updateNetworkStatus() quando integrar
        }, 10000);

        // Inicializar dashboard 
        document.addEventListener('DOMContentLoaded', function() {
            simulateNetworkData(); // Inicializar status
        });
    </script>
@endsection