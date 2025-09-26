<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Administrativo') - WiFi Tocantins</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
    
    @stack('styles')
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
                    <a href="{{ route('admin.settings') }}" class="menu-item w-14 h-14 rounded-2xl {{ request()->routeIs('admin.settings*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Configurações">
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
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-tocantins-green transition-colors">Dashboard</a>
                            @yield('breadcrumb')
                        </nav>
                        
                        <h1 class="text-sm sm:text-xl font-bold bg-gradient-to-r from-tocantins-green to-tocantins-dark-green bg-clip-text text-transparent">
                            @yield('page-title', 'WiFi Tocantins Admin')
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <!-- Menu de Ações Rápidas -->
                    <div class="hidden sm:flex items-center space-x-2">
                        @if(!request()->routeIs('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center">
                            <span class="mr-1">🏠</span>
                            Dashboard
                        </a>
                        @endif
                        
                        @if(!request()->routeIs('admin.users*'))
                        <a href="{{ route('admin.users') }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center">
                            <span class="mr-1">👥</span>
                            Usuários
                        </a>
                        @endif
                        
                        @if(!request()->routeIs('admin.reports*'))
                        <a href="{{ route('admin.reports') }}" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center">
                            <span class="mr-1">📈</span>
                            Relatórios
                        </a>
                        @endif
                    </div>
                    
                    <!-- Menu Mobile -->
                    <div class="sm:hidden relative">
                        <button onclick="toggleMobileMenu()" class="bg-tocantins-green text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center">
                            <span class="mr-1">☰</span>
                            Menu
                        </button>
                        
                        <!-- Dropdown Mobile -->
                        <div id="mobileMenu" class="hidden absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                            <div class="py-2">
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.dashboard') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">🏠</span>
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.users*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">👥</span>
                                    Usuários
                                </a>
                                <a href="{{ route('admin.vouchers') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.vouchers*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">🎫</span>
                                    Vouchers
                                </a>
                                <a href="{{ route('admin.reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.reports*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">📈</span>
                                    Relatórios
                                </a>
                                <a href="{{ route('admin.api') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.api*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">🔌</span>
                                    Integrações API
                                </a>
                                <a href="{{ route('admin.devices') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.devices*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">📱</span>
                                    Dispositivos
                                </a>
                                <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.settings*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">⚙️</span>
                                    Configurações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts Base -->
    <script>
        // Função para toggle do dropdown do usuário
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Função para toggle do menu mobile
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        // Fechar dropdowns quando clicar fora
        document.addEventListener('click', function(event) {
            // Fechar dropdown do usuário
            const dropdown = document.getElementById('userDropdown');
            const userButton = event.target.closest('button[onclick="toggleDropdown()"]');
            
            if (!userButton && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
            
            // Fechar menu mobile
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileButton = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!mobileButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

