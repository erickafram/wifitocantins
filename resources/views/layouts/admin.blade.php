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
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100">
        
        <!-- Sidebar Menu Component -->
        <x-sidebar-menu />

        <!-- Main Content Area -->
        <div class="lg:ml-64 min-h-screen flex flex-col">
            
            <!-- Top Header -->
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200/50 px-4 sm:px-6 py-3">
                <div class="flex justify-between items-center">
                    <!-- Título e Breadcrumb -->
                    <div class="ml-12 lg:ml-0">
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-xs text-gray-500 mb-0.5">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-600 transition-colors flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Início
                            </a>
                            @yield('breadcrumb')
                        </nav>
                        
                        <h1 class="text-lg sm:text-xl font-bold text-slate-800">
                            @yield('page-title', 'WiFi Tocantins Admin')
                        </h1>
                    </div>
                    
                    <!-- Ações Rápidas e Info -->
                    <div class="flex items-center space-x-3">
                        <!-- Data/Hora -->
                        <div class="hidden sm:flex items-center text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ now()->format('d/m/Y H:i') }}
                        </div>
                        
                        <!-- Botões de Ação Rápida (Desktop) -->
                        <div class="hidden lg:flex items-center space-x-2">
                            @if(!request()->routeIs('admin.reports*'))
                            <a href="{{ route('admin.reports') }}" class="flex items-center px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Relatórios
                            </a>
                            @endif
                            
                            @if(!request()->routeIs('admin.vouchers*'))
                            <a href="{{ route('admin.vouchers.create') }}" class="flex items-center px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Novo Voucher
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 p-4 sm:p-6 pb-20">
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="py-3 px-6 text-center text-xs text-gray-400 border-t border-gray-200/50 bg-white/50">
                © {{ date('Y') }} WiFi Tocantins Transporte. Todos os direitos reservados.
            </footer>
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

