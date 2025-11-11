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
                    <a href="{{ route('admin.dashboard') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Dashboard">
                        <span class="text-xl">📊</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Dashboard
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Usuários -->
                    <a href="{{ route('admin.users') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.users*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Usuários">
                        <span class="text-xl">👥</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.users*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Usuários
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Vouchers -->
                    <a href="{{ route('admin.vouchers') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.vouchers*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Vouchers">
                        <span class="text-xl">🎫</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.vouchers*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Vouchers
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Relatórios -->
                    <a href="{{ route('admin.reports') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.reports*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Relatórios">
                        <span class="text-xl">📈</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.reports*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Relatórios
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Integrações API -->
                    <a href="{{ route('admin.api') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.api*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Integrações API">
                        <span class="text-xl">🔌</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.api*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Integrações API
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Configurações -->
                    <a href="{{ route('admin.settings.index') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.settings*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Configurações">
                        <span class="text-xl">⚙️</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.settings*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Configurações
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>

                    <!-- Dispositivos -->
                    <a href="{{ route('admin.devices') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.devices*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Dispositivos">
                        <span class="text-xl">📱</span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.devices*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                        <div class="absolute left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                            Dispositivos
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>
                    </a>
                </div>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-3 border-t border-gray-200/50 relative z-10">
                <div class="relative group">
                    <button onclick="toggleDropdown()" class="w-14 h-14 bg-gradient-to-br from-tocantins-green via-tocantins-dark-green to-green-800 rounded-2xl flex items-center justify-center text-white shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300">
                        <span class="text-sm font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-30 blur"></div>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div id="userDropdown" class="hidden absolute bottom-20 left-0 w-64 bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-2xl border border-gray-200/50 z-50 backdrop-blur-sm">
                        <div class="p-4">
                            <div class="flex items-center space-x-3 pb-3 border-b border-gray-100">
                                <div class="w-10 h-10 bg-gradient-to-br from-tocantins-green to-tocantins-dark-green rounded-xl flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                    <p class="text-xs text-tocantins-green mt-1 font-medium">
                                        {{ Auth::user()->role === 'admin' ? '👑 Administrador' : '👤 Gestor' }}
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-2 px-3 rounded-xl text-xs font-medium hover:from-red-600 hover:to-red-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                                    🚪 Sair do Sistema
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
            <header class="bg-gradient-to-r from-white via-gray-50 to-white shadow-lg border-b border-gray-200/50 px-3 sm:px-6 py-2 sm:py-4 backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                    <div>
                        <h1 class="text-sm sm:text-xl font-bold bg-gradient-to-r from-tocantins-green to-tocantins-dark-green bg-clip-text text-transparent">
                            WiFi Tocantins Admin
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <!-- Status Indicators -->
                    <div class="flex items-center space-x-2 sm:space-x-6 overflow-x-auto">
                        
                        <!-- Internet Status -->
                        <div class="bg-white/70 rounded-xl sm:rounded-2xl px-2 sm:px-4 py-1 sm:py-2 shadow-lg backdrop-blur-sm border border-gray-200/50 flex-shrink-0">
                            <div class="flex items-center space-x-1 sm:space-x-3">
                                <div class="flex flex-col items-center">
                                    <div id="internet-status" class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full animate-pulse shadow-lg"></div>
                                    <span class="text-xs text-gray-600 font-medium mt-0.5 hidden sm:block">Internet</span>
                                </div>
                                <div class="border-l border-gray-300 h-4 sm:h-8 hidden sm:block"></div>
                                <div class="flex flex-col">
                                    <span id="internet-speed" class="text-xs sm:text-sm font-bold text-tocantins-green">100 Mbps</span>
                                    <span id="internet-ping" class="text-xs text-gray-500 hidden sm:block">Ping: 15ms</span>
                                </div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="bg-white/70 rounded-xl sm:rounded-2xl px-2 sm:px-4 py-1 sm:py-2 shadow-lg backdrop-blur-sm border border-gray-200/50 flex-shrink-0">
                            <div class="flex items-center space-x-1 sm:space-x-3">
                                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full shadow-lg"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-600 font-medium hidden sm:block">Sistema Online</span>
                                    <span class="text-xs text-tocantins-green font-semibold">{{ $stats['connected_users'] }} usuários</span>
                                </div>
                            </div>
                        </div>

                        <!-- MikroTik Status -->
                        <div class="bg-white/70 rounded-xl sm:rounded-2xl px-2 sm:px-4 py-1 sm:py-2 shadow-lg backdrop-blur-sm border border-gray-200/50 flex-shrink-0">
                            <div class="flex items-center space-x-1 sm:space-x-3">
                                <div id="mikrotik-status" class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full shadow-lg"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-600 font-medium hidden sm:block">MikroTik</span>
                                    <span id="mikrotik-uptime" class="text-xs text-tocantins-green font-semibold">Online</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4">
        
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
                                        <p class="text-2xl font-bold text-tocantins-gray-green">{{ $stats['connected_users'] }}</p>
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
                                        <p class="text-2xl font-bold text-tocantins-gray-green">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
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
                                        <p class="text-2xl font-bold text-tocantins-gray-green">{{ $stats['total_devices'] }}</p>
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
                        <p class="text-2xl font-bold text-tocantins-gray-green">{{ $stats['active_vouchers'] }}</p>
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
</body>
</html>


