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
        <div class="flex-1 flex flex-col overflow-hidden ml-0 lg:ml-20">
            
            <!-- Top Header -->
            <header class="bg-gradient-to-r from-white via-gray-50 to-white shadow-lg border-b border-gray-200/50 px-4 sm:px-6 py-3 sm:py-4 backdrop-blur-sm">
                <div class="flex justify-between items-center">
                    <!-- Título e Breadcrumb -->
                    <div class="ml-12 lg:ml-0">
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-tocantins-green transition-colors">Dashboard</a>
                            @yield('breadcrumb')
                        </nav>
                        
                        <h1 class="text-sm sm:text-lg font-bold bg-gradient-to-r from-tocantins-green to-tocantins-dark-green bg-clip-text text-transparent">
                            @yield('page-title', 'WiFi Tocantins Admin')
                        </h1>
                        <p class="text-xs text-gray-500 font-medium hidden sm:block">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <!-- Ações Rápidas (Desktop) -->
                    <div class="hidden lg:flex items-center space-x-2">
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
                    
                    <!-- Data/Hora (Mobile) -->
                    <div class="lg:hidden text-right">
                        <p class="text-xs text-gray-500 font-medium">{{ now()->format('d/m H:i') }}</p>
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
        // Funções são definidas no componente sidebar-menu.blade.php
    </script>

    @stack('modals')
    @stack('scripts')
</body>
</html>

