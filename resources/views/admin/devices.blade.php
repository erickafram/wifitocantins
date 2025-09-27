@extends('layouts.admin')

@section('title', 'Gerenciar Dispositivos')

@section('breadcrumb')
    <span>›</span>
    <span class="text-tocantins-green font-medium">Dispositivos</span>
@endsection

@section('page-title', 'Gerenciar Dispositivos - WiFi Tocantins')

@section('content')
    <!-- Cabeçalho da Página -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-tocantins-gray-green flex items-center">
                    <span class="mr-3 text-3xl">📱</span>
                    Gerenciar Dispositivos
                </h1>
                <p class="text-gray-600 text-sm mt-1">Monitore todos os dispositivos conectados à rede WiFi</p>
            </div>
            
            <div class="mt-4 sm:mt-0">
                <button onclick="location.reload()" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all duration-300 flex items-center space-x-2">
                    <span>🔄</span>
                    <span>Atualizar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Estatísticas de Dispositivos -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total de Dispositivos -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total de Dispositivos</p>
                    <p class="text-3xl font-bold">{{ $devices->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">📱</span>
                </div>
            </div>
        </div>

        <!-- Dispositivos Online -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Online Agora</p>
                    <p class="text-3xl font-bold">{{ $devices->where('user.status', 'connected')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">🟢</span>
                </div>
            </div>
        </div>

        <!-- Dispositivos Ativos Hoje -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Ativos Hoje</p>
                    <p class="text-3xl font-bold">{{ $devices->where('last_seen', '>=', now()->startOfDay())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">📊</span>
                </div>
            </div>
        </div>

        <!-- Novos Dispositivos (7 dias) -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-medium">Novos (7 dias)</p>
                    <p class="text-3xl font-bold">{{ $devices->where('created_at', '>=', now()->subDays(7))->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-400 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">🆕</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <h3 class="text-lg font-semibold text-tocantins-gray-green">Filtros</h3>
            
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <!-- Busca por MAC ou Nome -->
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchDevices" 
                        placeholder="Buscar por MAC, nome ou IP..."
                        class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"
                    >
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                </div>
                
                <!-- Filtro por Status -->
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                    <option value="">Todos os Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="blocked">Bloqueado</option>
                </select>
                
                <!-- Filtro por Período -->
                <select id="periodFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                    <option value="">Todos os Períodos</option>
                    <option value="today">Hoje</option>
                    <option value="week">Última Semana</option>
                    <option value="month">Último Mês</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabela de Dispositivos -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green">
            <h3 class="text-lg font-semibold text-white">Lista de Dispositivos</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="devicesTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereços</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tráfego</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($devices as $device)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                                        @php
                                            $deviceIcon = '📱';
                                            if ($device->device_type) {
                                                switch(strtolower($device->device_type)) {
                                                    case 'mobile': case 'smartphone': case 'android': case 'iphone': $deviceIcon = '📱'; break;
                                                    case 'laptop': case 'computer': case 'pc': $deviceIcon = '💻'; break;
                                                    case 'tablet': case 'ipad': $deviceIcon = '📱'; break;
                                                    case 'tv': case 'smart tv': $deviceIcon = '📺'; break;
                                                    default: $deviceIcon = '🖥️'; break;
                                                }
                                            }
                                        @endphp
                                        {{ $deviceIcon }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $device->device_name ?? 'Dispositivo Desconhecido' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $device->mac_address }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($device->user)
                                <div class="text-sm text-gray-900">{{ $device->user->name ?? 'Sem nome' }}</div>
                                <div class="text-xs text-gray-500">{{ $device->user->email ?? 'Sem email' }}</div>
                            @else
                                <span class="text-xs text-gray-400">Não associado</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $device->ip_address ?? 'N/A' }}</div>
                            @if($device->hostname)
                                <div class="text-xs text-gray-500">{{ $device->hostname }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $isOnline = $device->user && $device->user->status === 'connected';
                                $lastSeen = $device->last_seen ? $device->last_seen->diffInMinutes(now()) : null;
                                
                                if ($isOnline) {
                                    $statusConfig = ['bg-green-100', 'text-green-800', '🟢', 'Online'];
                                } elseif ($lastSeen !== null && $lastSeen < 5) {
                                    $statusConfig = ['bg-yellow-100', 'text-yellow-800', '🟡', 'Recente'];
                                } elseif ($lastSeen !== null && $lastSeen < 60) {
                                    $statusConfig = ['bg-blue-100', 'text-blue-800', '🔵', 'Ativo'];
                                } else {
                                    $statusConfig = ['bg-gray-100', 'text-gray-800', '⚫', 'Offline'];
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig[0] }} {{ $statusConfig[1] }}">
                                {{ $statusConfig[2] }} {{ $statusConfig[3] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($device->last_seen)
                                <div>{{ $device->last_seen->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $device->last_seen->diffForHumans() }}</div>
                            @else
                                <span class="text-xs text-gray-400">Nunca visto</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col">
                                @if($device->bytes_downloaded || $device->bytes_uploaded)
                                    <span class="text-xs">⬇️ {{ formatBytes($device->bytes_downloaded ?? 0) }}</span>
                                    <span class="text-xs">⬆️ {{ formatBytes($device->bytes_uploaded ?? 0) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">Sem dados</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="viewDevice('{{ $device->mac_address }}')" class="text-tocantins-green hover:text-tocantins-dark-green transition-colors duration-200" title="Visualizar detalhes">
                                    👁️
                                </button>
                                @if($isOnline)
                                <button onclick="disconnectDevice('{{ $device->mac_address }}')" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Desconectar">
                                    🔌
                                </button>
                                @endif
                                <button onclick="blockDevice('{{ $device->mac_address }}')" class="text-orange-600 hover:text-orange-900 transition-colors duration-200" title="Bloquear dispositivo">
                                    🚫
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <span class="text-6xl mb-4">📱</span>
                                <h3 class="text-lg font-semibold mb-2">Nenhum dispositivo encontrado</h3>
                                <p class="text-sm">Aguarde dispositivos se conectarem à rede.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($devices->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $devices->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de Detalhes do Dispositivo -->
    <div id="deviceModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-tocantins-gray-green">Detalhes do Dispositivo</h3>
                        <button onclick="closeDeviceModal()" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div id="deviceModalContent">
                        <!-- Conteúdo será carregado dinamicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filtros
        document.getElementById('searchDevices').addEventListener('input', function() {
            filterDevices();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            filterDevices();
        });

        document.getElementById('periodFilter').addEventListener('change', function() {
            filterDevices();
        });

        function filterDevices() {
            const searchTerm = document.getElementById('searchDevices').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const periodFilter = document.getElementById('periodFilter').value;
            const rows = document.querySelectorAll('#devicesTable tbody tr');

            rows.forEach(row => {
                if (row.children.length === 1) return; // Skip empty row

                const deviceData = {
                    device: row.children[0].textContent.toLowerCase(),
                    user: row.children[1].textContent.toLowerCase(),
                    addresses: row.children[2].textContent.toLowerCase(),
                    status: row.children[3].textContent.toLowerCase()
                };

                const matchesSearch = !searchTerm || 
                    deviceData.device.includes(searchTerm) || 
                    deviceData.user.includes(searchTerm) || 
                    deviceData.addresses.includes(searchTerm);

                const matchesStatus = !statusFilter || deviceData.status.includes(statusFilter);

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Ações dos dispositivos
        function viewDevice(macAddress) {
            // Implementar visualização de dispositivo
            const modal = document.getElementById('deviceModal');
            const content = document.getElementById('deviceModalContent');
            
            content.innerHTML = `
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            `;
            
            modal.classList.remove('hidden');
            
            // Simular carregamento de dados
            setTimeout(() => {
                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">MAC Address</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">${macAddress}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1 text-sm text-gray-900">Em desenvolvimento...</p>
                            </div>
                        </div>
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Funcionalidade em desenvolvimento:</strong><br>
                                Em breve você poderá ver histórico completo, estatísticas de tráfego e configurações avançadas do dispositivo.
                            </p>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        function disconnectDevice(macAddress) {
            if (confirm('Deseja realmente desconectar este dispositivo?')) {
                // Implementar desconexão
                alert('Funcionalidade em desenvolvimento - Dispositivo: ' + macAddress);
            }
        }

        function blockDevice(macAddress) {
            if (confirm('Deseja realmente bloquear este dispositivo?')) {
                // Implementar bloqueio
                alert('Funcionalidade em desenvolvimento - Bloquear: ' + macAddress);
            }
        }

        function closeDeviceModal() {
            document.getElementById('deviceModal').classList.add('hidden');
        }

        // Fechar modal clicando fora
        document.getElementById('deviceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeviceModal();
            }
        });

        // Auto-refresh a cada 30 segundos
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
@endsection

@php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
@endphp
