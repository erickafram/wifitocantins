<!-- Sidebar - Menu Lateral -->
<div class="w-16 lg:w-20 bg-gradient-to-b from-white via-gray-50 to-white shadow-2xl border-r border-gray-200 flex flex-col relative fixed lg:relative h-full z-40 transform transition-transform duration-300 -translate-x-full lg:translate-x-0" id="sidebar">
    <!-- Gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-b from-tocantins-green/5 via-transparent to-tocantins-gold/5 pointer-events-none"></div>
    
    <!-- Logo/Brand -->
    <div class="flex items-center justify-center h-16 lg:h-20 border-b border-gray-200 relative z-10">
        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-tocantins-green via-tocantins-dark-green to-green-800 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-all duration-300">
            <span class="text-white text-sm lg:text-lg font-bold">W</span>
            <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl opacity-20 blur"></div>
        </div>
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 flex flex-col pt-4 lg:pt-6 relative z-10">
        <div class="space-y-2 lg:space-y-3 px-2 lg:px-3">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Dashboard">
                <span class="text-xs lg:text-sm">📊</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Dashboard
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>

            <!-- Usuários (Apenas Admin) -->
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.users*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Usuários">
                <span class="text-xs lg:text-sm">👥</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.users*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Usuários
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>
            @endif

            <!-- Vouchers -->
            <a href="{{ route('admin.vouchers.index') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.vouchers*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Vouchers">
                <span class="text-xs lg:text-sm">🎫</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.vouchers*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Vouchers
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>

            <!-- Relatórios -->
            <a href="{{ route('admin.reports') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.reports*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Relatórios">
                <span class="text-xs lg:text-sm">📈</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.reports*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Relatórios
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>

            <!-- WhatsApp -->
            <a href="{{ route('admin.whatsapp.index') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.whatsapp*') ? 'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-green-500 hover:to-green-600 hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="WhatsApp">
                <span class="text-xs lg:text-sm">💬</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-green-400 to-green-600 rounded-2xl {{ request()->routeIs('admin.whatsapp*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    WhatsApp
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>

            <!-- Configurações (Apenas Admin) -->
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.settings.index') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.settings*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Configurações">
                <span class="text-xs lg:text-sm">⚙️</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.settings*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Configurações
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>
            @endif

            <!-- Dispositivos -->
            <a href="{{ route('admin.devices') }}" class="menu-item w-12 h-12 lg:w-14 lg:h-14 rounded-2xl {{ request()->routeIs('admin.devices*') ? 'bg-gradient-to-br from-tocantins-green to-tocantins-dark-green text-white shadow-lg' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 hover:from-tocantins-green hover:to-tocantins-dark-green hover:text-white' }} flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-300 group relative" title="Dispositivos">
                <span class="text-xs lg:text-sm">📱</span>
                <div class="absolute -inset-1 bg-gradient-to-r from-tocantins-gold to-tocantins-green rounded-2xl {{ request()->routeIs('admin.devices*') ? 'opacity-30' : 'opacity-0 group-hover:opacity-30' }} blur transition-opacity duration-300"></div>
                <div class="absolute left-16 lg:left-20 bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-xl">
                    Dispositivos
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
            </a>
        </div>
    </nav>

    <!-- User Info & Logout -->
    <div class="p-2 lg:p-3 border-t border-gray-200/50 relative z-10">
        <div class="relative group">
            <button onclick="toggleDropdown()" class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-tocantins-green via-tocantins-dark-green to-green-800 rounded-2xl flex items-center justify-center text-white shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300">
                <span class="text-xs lg:text-sm font-bold">
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

<!-- Mobile Menu Toggle -->
<button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 bg-tocantins-green text-white p-2 rounded-lg shadow-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- Overlay for mobile sidebar -->
<div id="sidebarOverlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden" onclick="toggleSidebar()"></div>

<script>
    // Função para toggle do dropdown do usuário
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Função para toggle da sidebar no mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Fechar dropdown quando clicar fora
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const button = event.target.closest('button[onclick="toggleDropdown()"]');
        
        if (!button && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
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
