<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Administrativo') - WiFi Tocantins</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin Custom Styles -->
    <link href="{{ asset('css/admin-styles.css') }}" rel="stylesheet">
    
    <!-- Tailwind Config -->
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
        
        <!-- Sidebar Menu Component -->
        <x-sidebar-menu />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden ml-0 lg:ml-0">
            
            <!-- Top Header -->
            <header class="bg-gradient-to-r from-white via-gray-50 to-white shadow-lg border-b border-gray-200/50 px-3 sm:px-6 py-2 sm:py-4 backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                    <div>
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-tocantins-green transition-colors">Dashboard</a>
                            @yield('breadcrumb')
                        </nav>
                        
                        <h1 class="text-base sm:text-lg font-bold bg-gradient-to-r from-tocantins-green to-tocantins-dark-green bg-clip-text text-transparent">
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
                                <a href="{{ route('admin.vouchers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.vouchers*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">🎫</span>
                                    Vouchers
                                </a>
                                <a href="{{ route('admin.reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.reports*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">📈</span>
                                    Relatórios
                                </a>
                                <a href="{{ route('admin.whatsapp.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.whatsapp*') ? 'bg-green-100 text-green-700 font-medium' : '' }}">
                                    <span class="mr-2">💬</span>
                                    WhatsApp
                                </a>
                                <a href="{{ route('admin.devices') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.devices*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">📱</span>
                                    Dispositivos
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center {{ request()->routeIs('admin.settings*') ? 'bg-tocantins-green/10 text-tocantins-green font-medium' : '' }}">
                                    <span class="mr-2">⚙️</span>
                                    Configurações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 pb-16">
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

        // Função para toggle da sidebar no mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
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
            
            // Fechar sidebar no mobile quando clicar fora
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const sidebarButton = event.target.closest('button[onclick="toggleSidebar()"]');
            
            if (window.innerWidth < 1024 && !sidebarButton && !sidebar.contains(event.target) && !overlay.contains(event.target)) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    </script>

    @stack('modals')
    @stack('scripts')
</body>
</html>

