<!-- Sidebar - Menu Lateral Moderno -->
<aside class="fixed inset-y-0 left-0 z-40 w-72 lg:w-64 transform transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0" id="sidebar">
    <!-- Background com gradiente -->
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/10 via-transparent to-amber-500/10"></div>
    
    <!-- Conteúdo da Sidebar -->
    <div class="relative h-full flex flex-col">
        
        <!-- Logo/Brand -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <span class="text-white text-lg font-bold">W</span>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-amber-400 rounded-full border-2 border-slate-900"></div>
                </div>
                <div>
                    <h1 class="text-white font-bold text-sm">WiFi Tocantins</h1>
                    <p class="text-emerald-400 text-xs font-medium">Painel Admin</p>
                </div>
            </div>
            <!-- Botão fechar (mobile) -->
            <button onclick="toggleSidebar()" class="lg:hidden text-white/60 hover:text-white p-1.5 hover:bg-white/10 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Menu Principal -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin scrollbar-thumb-white/10">
            
            <!-- Seção: Principal -->
            <div class="mb-4">
                <p class="px-3 mb-2 text-xs font-semibold text-white/40 uppercase tracking-wider">Principal</p>
                
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Dashboard</span>
                </a>

                <!-- Relatórios -->
                <a href="{{ route('admin.reports') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.reports*') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.reports*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Relatórios</span>
                </a>
            </div>

            <!-- Seção: Gestão -->
            <div class="mb-4">
                <p class="px-3 mb-2 text-xs font-semibold text-white/40 uppercase tracking-wider">Gestão</p>
                
                @if(Auth::user()->role === 'admin')
                <!-- Usuários -->
                <a href="{{ route('admin.users') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Usuários</span>
                </a>
                @endif

                <!-- Vouchers -->
                <a href="{{ route('admin.vouchers.index') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.vouchers*') ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.vouchers*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Vouchers</span>
                </a>

                @if(Auth::user()->role === 'admin')
                <!-- Dispositivos -->
                <a href="{{ route('admin.devices') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.devices*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.devices*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Dispositivos</span>
                </a>
                @endif
            </div>

            <!-- Seção: Comunicação -->
            <div class="mb-4">
                <p class="px-3 mb-2 text-xs font-semibold text-white/40 uppercase tracking-wider">Comunicação</p>
                
                <!-- Chat Atendimento -->
                <a href="{{ route('admin.chat.index') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 relative {{ request()->routeIs('admin.chat*') ? 'bg-gradient-to-r from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.chat*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Chat</span>
                    <!-- Badge de mensagens não lidas -->
                    <span id="chat-unread-badge" class="hidden absolute right-3 min-w-5 h-5 px-1.5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-pulse"></span>
                </a>

                @if(Auth::user()->role === 'admin')
                <!-- WhatsApp -->
                <a href="{{ route('admin.whatsapp.index') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.whatsapp*') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.whatsapp*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">WhatsApp</span>
                </a>
                @endif
            </div>

            <!-- Seção: Sistema (Apenas Admin) -->
            @if(Auth::user()->role === 'admin')
            <div class="mb-4">
                <p class="px-3 mb-2 text-xs font-semibold text-white/40 uppercase tracking-wider">Sistema</p>
                
                <!-- Mikrotik Remoto -->
                <a href="{{ route('admin.mikrotik.remote.index') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.mikrotik.remote*') ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.mikrotik.remote*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Mikrotik Remoto</span>
                </a>
                
                <!-- Configurações -->
                <a href="{{ route('admin.settings.index') }}" onclick="closeSidebarOnMobile()" 
                   class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.settings*') ? 'bg-gradient-to-r from-slate-500 to-slate-600 text-white shadow-lg shadow-slate-500/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.settings*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <span class="ml-3 font-medium text-sm">Configurações</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- User Info & Logout -->
        <div class="p-3 border-t border-white/10">
            <div class="flex items-center p-3 rounded-xl bg-white/5 hover:bg-white/10 transition-colors cursor-pointer" onclick="toggleDropdown()">
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-sm font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-slate-900"></div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-white/50">
                        {{ Auth::user()->role === 'admin' ? 'Administrador' : 'Gestor' }}
                    </p>
                </div>
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                </svg>
            </div>
            
            <!-- Dropdown Menu -->
            <div id="userDropdown" class="hidden mt-2 p-2 rounded-xl bg-slate-700/50 backdrop-blur-sm">
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Meu Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sair do Sistema
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Menu Toggle Button -->
<button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-slate-900 text-white rounded-xl shadow-lg flex items-center justify-center hover:bg-slate-800 transition-colors" id="menuToggleBtn">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- Overlay for mobile sidebar -->
<div id="sidebarOverlay" class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) dropdown.classList.toggle('hidden');
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuBtn = document.getElementById('menuToggleBtn');
        
        if (sidebar) {
            sidebar.classList.toggle('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.toggle('hidden');
        }
    }

    function closeSidebarOnMobile() {
        if (window.innerWidth < 1024) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar) sidebar.classList.add('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    }

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const userArea = event.target.closest('[onclick="toggleDropdown()"]');
        
        if (dropdown && !userArea && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Verificar mensagens não lidas do chat
    function checkUnreadMessages() {
        fetch('{{ route("admin.chat.unread") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('chat-unread-badge');
                if (badge && data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('hidden');
                } else if (badge) {
                    badge.classList.add('hidden');
                }
            })
            .catch(() => {});
    }

    // Verificar a cada 30 segundos
    setInterval(checkUnreadMessages, 30000);
    checkUnreadMessages();
</script>
