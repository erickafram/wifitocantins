@extends('layouts.admin')

@section('title', 'Controle de Acesso WiFi')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Controle de Acesso WiFi</h1>
            <p class="text-sm text-gray-500 mt-1">Gerencie os MACs liberados. O MikroTik sincroniza a cada 15 segundos via API.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="refreshData()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition" id="btn-refresh">
                <svg class="w-4 h-4" id="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Atualizar
            </button>
            <button onclick="showPreviewAPI()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                Preview API
            </button>
            <button onclick="showLogs()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Logs
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-emerald-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Liberados (L:)</p>
                    <p class="text-2xl font-bold text-emerald-600" id="stat-liberados">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Para Remover (R:)</p>
                    <p class="text-2xl font-bold text-red-600" id="stat-expirados">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Registrados</p>
                    <p class="text-2xl font-bold text-blue-600" id="stat-total">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liberar MAC Manual -->
    <div class="bg-white rounded-xl border shadow-sm mb-6 overflow-hidden">
        <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-200">
            <h2 class="font-semibold text-emerald-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Liberar MAC Manualmente
            </h2>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">MAC Address *</label>
                    <input type="text" id="input-mac" placeholder="XX:XX:XX:XX:XX:XX" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 uppercase" maxlength="17">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Telefone</label>
                    <input type="text" id="input-phone" placeholder="(63) 99999-9999" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Duração</label>
                    <select id="input-hours" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="1">1 hora</option>
                        <option value="2">2 horas</option>
                        <option value="6">6 horas</option>
                        <option value="12">12 horas</option>
                        <option value="24" selected>24 horas</option>
                        <option value="48">48 horas</option>
                        <option value="72">72 horas</option>
                        <option value="168">7 dias</option>
                        <option value="720">30 dias</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="liberateMac()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Liberar
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">O MikroTik vai liberar esse MAC automaticamente na próxima sincronização (~15s).</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="border-b">
            <div class="flex">
                <button onclick="switchTab('liberados')" id="tab-liberados" class="tab-btn flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 border-emerald-500 text-emerald-700 bg-emerald-50 transition">
                    🟢 Liberados <span id="badge-liberados" class="ml-1 bg-emerald-500 text-white text-xs px-1.5 py-0.5 rounded-full">0</span>
                </button>
                <button onclick="switchTab('expirados')" id="tab-expirados" class="tab-btn flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    🔴 Expirados <span id="badge-expirados" class="ml-1 bg-gray-300 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">0</span>
                </button>
                <button onclick="switchTab('todos')" id="tab-todos" class="tab-btn flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    📋 Todos <span id="badge-todos" class="ml-1 bg-gray-300 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">0</span>
                </button>
            </div>
        </div>

        <!-- Busca -->
        <div class="px-5 py-3 bg-gray-50 border-b">
            <input type="text" id="search-input" placeholder="Buscar por MAC, telefone ou nome..." class="w-full sm:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="filterTable()">
        </div>

        <!-- Tabelas -->
        <div id="content-liberados" class="tab-content">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Usuário</th>
                            <th class="px-4 py-3 text-left">Telefone</th>
                            <th class="px-4 py-3 text-left">MAC Address</th>
                            <th class="px-4 py-3 text-left">IP</th>
                            <th class="px-4 py-3 text-left">Expira em</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-liberados" class="divide-y divide-gray-100">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="content-expirados" class="tab-content hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Usuário</th>
                            <th class="px-4 py-3 text-left">Telefone</th>
                            <th class="px-4 py-3 text-left">MAC Address</th>
                            <th class="px-4 py-3 text-left">Expirou em</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-expirados" class="divide-y divide-gray-100">
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="content-todos" class="tab-content hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Usuário</th>
                            <th class="px-4 py-3 text-left">Telefone</th>
                            <th class="px-4 py-3 text-left">MAC Address</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Expira/Expirou</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-todos" class="divide-y divide-gray-100">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview API -->
<div id="modal-api" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] flex flex-col">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Resposta da API para o MikroTik</h3>
            <button onclick="closeModal('modal-api')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 overflow-y-auto flex-1">
            <p class="text-xs text-gray-500 mb-3">Isso é exatamente o que o MikroTik recebe ao consultar <code class="bg-gray-100 px-1 py-0.5 rounded">/api/mikrotik/check-paid-users-lite</code></p>
            <pre id="api-preview-content" class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono whitespace-pre overflow-x-auto"></pre>
            <div id="api-preview-stats" class="mt-3 text-xs text-gray-500"></div>
        </div>
    </div>
</div>

<!-- Modal Logs -->
<div id="modal-logs" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] flex flex-col">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Logs do Sistema (MikroTik)</h3>
            <button onclick="closeModal('modal-logs')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 overflow-y-auto flex-1">
            <pre id="logs-content" class="bg-gray-900 text-gray-300 p-4 rounded-lg text-xs font-mono whitespace-pre-wrap max-h-96 overflow-y-auto"></pre>
        </div>
    </div>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';
let cachedData = null;
let currentTab = 'liberados';

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', () => {
    refreshData();
    // Auto-refresh a cada 15s (mesmo intervalo do MikroTik)
    setInterval(refreshData, 15000);
});

async function refreshData() {
    const icon = document.getElementById('refresh-icon');
    icon?.classList.add('animate-spin');

    try {
        const response = await fetch('/admin/mikrotik/remote/status');
        const data = await response.json();

        if (data.error) {
            showToast('Erro: ' + data.error, 'error');
            return;
        }

        cachedData = data;

        // Atualizar stats
        document.getElementById('stat-liberados').textContent = data.stats.total_liberados;
        document.getElementById('stat-expirados').textContent = data.stats.total_expirados;
        document.getElementById('stat-total').textContent = data.stats.total_registrados;

        // Atualizar badges
        document.getElementById('badge-liberados').textContent = data.stats.total_liberados;
        document.getElementById('badge-expirados').textContent = data.stats.total_expirados;
        document.getElementById('badge-todos').textContent = data.stats.total_registrados;

        // Atualizar tabelas
        renderLiberados(data.liberados);
        renderExpirados(data.expirados);
        renderTodos(data.todos);

    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    } finally {
        icon?.classList.remove('animate-spin');
    }
}

function renderLiberados(users) {
    const tbody = document.getElementById('table-liberados');
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum MAC liberado no momento</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        const expiresAt = new Date(user.expires_at);
        const now = new Date();
        const diffMs = expiresAt - now;
        const diffH = Math.floor(diffMs / 3600000);
        const diffM = Math.floor((diffMs % 3600000) / 60000);
        const timeLeft = diffH > 0 ? `${diffH}h ${diffM}m` : `${diffM}m`;

        return `<tr class="hover:bg-gray-50 user-row" data-search="${(user.mac_address + ' ' + (user.phone || '') + ' ' + (user.name || '')).toLowerCase()}">
            <td class="px-4 py-3">
                <p class="font-medium text-gray-800 text-sm">${user.name || '-'}</p>
                <p class="text-xs text-gray-400">${user.device_name || ''}</p>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm ${user.phone ? 'text-gray-700 font-medium' : 'text-gray-400'}">${user.phone || 'Não informado'}</span>
            </td>
            <td class="px-4 py-3">
                <code class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-xs font-mono">${user.mac_address}</code>
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">${user.ip_address || '-'}</td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-emerald-600 font-medium">${timeLeft}</span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">${formatDate(user.expires_at)}</p>
            </td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="editExpiration(${user.id}, '${user.mac_address}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar tempo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <button onclick="blockMac('${user.mac_address}')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Bloquear">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderExpirados(users) {
    const tbody = document.getElementById('table-expirados');
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhum MAC expirado recente</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        return `<tr class="hover:bg-gray-50 user-row" data-search="${(user.mac_address + ' ' + (user.phone || '') + ' ' + (user.name || '')).toLowerCase()}">
            <td class="px-4 py-3">
                <p class="font-medium text-gray-800 text-sm">${user.name || '-'}</p>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm ${user.phone ? 'text-gray-700 font-medium' : 'text-gray-400'}">${user.phone || 'Não informado'}</span>
            </td>
            <td class="px-4 py-3">
                <code class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs font-mono">${user.mac_address}</code>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-red-500">${formatDate(user.expires_at)}</span>
            </td>
            <td class="px-4 py-3 text-center">
                <button onclick="reLiberate(${user.id}, '${user.mac_address}')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-medium hover:bg-emerald-100 transition" title="Re-liberar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Re-liberar
                </button>
            </td>
        </tr>`;
    }).join('');
}

function renderTodos(users) {
    const tbody = document.getElementById('table-todos');
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum registro</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        const statusMap = {
            'connected': { label: 'Liberado', color: 'bg-emerald-100 text-emerald-700' },
            'active': { label: 'Ativo', color: 'bg-emerald-100 text-emerald-700' },
            'expired': { label: 'Expirado', color: 'bg-red-100 text-red-700' },
            'cleaned': { label: 'Limpo', color: 'bg-gray-100 text-gray-500' },
            'pending': { label: 'Pendente', color: 'bg-amber-100 text-amber-700' },
        };
        const st = statusMap[user.status] || { label: user.status, color: 'bg-gray-100 text-gray-600' };
        const isActive = ['connected', 'active'].includes(user.status) && user.expires_at && new Date(user.expires_at) > new Date();

        return `<tr class="hover:bg-gray-50 user-row" data-search="${(user.mac_address + ' ' + (user.phone || '') + ' ' + (user.name || '')).toLowerCase()}">
            <td class="px-4 py-3">
                <p class="font-medium text-gray-800 text-sm">${user.name || '-'}</p>
                <p class="text-xs text-gray-400">${user.device_name || ''}</p>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm ${user.phone ? 'text-gray-700 font-medium' : 'text-gray-400'}">${user.phone || '-'}</span>
            </td>
            <td class="px-4 py-3">
                <code class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-mono">${user.mac_address}</code>
            </td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${st.color}">${st.label}</span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">${user.expires_at ? formatDate(user.expires_at) : '-'}</td>
            <td class="px-4 py-3 text-center">
                ${isActive ? `
                    <button onclick="blockMac('${user.mac_address}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-medium hover:bg-red-100 transition">Bloquear</button>
                ` : `
                    <button onclick="reLiberate(${user.id}, '${user.mac_address}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-medium hover:bg-emerald-100 transition">Liberar</button>
                `}
            </td>
        </tr>`;
    }).join('');
}

// ========== Ações ==========

async function liberateMac() {
    const mac = document.getElementById('input-mac').value.trim();
    const phone = document.getElementById('input-phone').value.trim();
    const hours = document.getElementById('input-hours').value;

    if (!mac) return showToast('Digite um MAC address', 'error');
    if (!/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/.test(mac)) return showToast('MAC inválido. Use formato XX:XX:XX:XX:XX:XX', 'error');

    try {
        const res = await fetch('/admin/mikrotik/remote/liberate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ mac, phone, hours: parseInt(hours) })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('input-mac').value = '';
            document.getElementById('input-phone').value = '';
            refreshData();
        } else {
            showToast(data.error || 'Erro ao liberar', 'error');
        }
    } catch (e) {
        showToast('Erro de conexão', 'error');
    }
}

async function blockMac(mac) {
    if (!confirm(`Bloquear MAC ${mac}?\n\nO MikroTik vai remover o acesso na próxima sincronização (~15s).`)) return;

    try {
        const res = await fetch('/admin/mikrotik/remote/block', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ mac })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            refreshData();
        } else {
            showToast(data.error || 'Erro ao bloquear', 'error');
        }
    } catch (e) {
        showToast('Erro de conexão', 'error');
    }
}

async function reLiberate(userId, mac) {
    const hours = prompt('Liberar por quantas horas?', '24');
    if (!hours) return;

    try {
        const res = await fetch('/admin/mikrotik/remote/liberate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ mac, hours: parseInt(hours) })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            refreshData();
        } else {
            showToast(data.error || 'Erro', 'error');
        }
    } catch (e) {
        showToast('Erro de conexão', 'error');
    }
}

async function editExpiration(userId, mac) {
    const hours = prompt(`Editar tempo de ${mac}.\nQuantas horas a partir de agora?`, '24');
    if (!hours) return;

    try {
        const res = await fetch('/admin/mikrotik/remote/edit-expiration', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ user_id: userId, hours: parseInt(hours) })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            refreshData();
        } else {
            showToast(data.error || 'Erro', 'error');
        }
    } catch (e) {
        showToast('Erro de conexão', 'error');
    }
}

// ========== Preview API ==========

async function showPreviewAPI() {
    document.getElementById('modal-api').classList.remove('hidden');

    try {
        const res = await fetch('/admin/mikrotik/remote/sync', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();

        document.getElementById('api-preview-content').textContent = data.api_response || 'Erro';
        document.getElementById('api-preview-stats').innerHTML = data.stats
            ? `<span class="text-emerald-600 font-medium">L: ${data.stats.liberar} para liberar</span> &bull; <span class="text-red-600 font-medium">R: ${data.stats.remover} para remover</span>`
            : '';
    } catch (e) {
        document.getElementById('api-preview-content').textContent = 'Erro ao consultar API';
    }
}

async function showLogs() {
    document.getElementById('modal-logs').classList.remove('hidden');

    try {
        const res = await fetch('/admin/mikrotik/remote/logs');
        const data = await res.json();

        if (data.logs && data.logs.length > 0) {
            document.getElementById('logs-content').textContent = data.logs.join('\n');
        } else {
            document.getElementById('logs-content').textContent = 'Nenhum log do MikroTik encontrado.';
        }
    } catch (e) {
        document.getElementById('logs-content').textContent = 'Erro ao carregar logs.';
    }
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Fechar modais ao clicar fora
document.addEventListener('click', (e) => {
    ['modal-api', 'modal-logs'].forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) modal.classList.add('hidden');
    });
});

// ========== Tabs ==========

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-emerald-500', 'text-emerald-700', 'bg-emerald-50');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('tab-' + tab).classList.add('border-emerald-500', 'text-emerald-700', 'bg-emerald-50');
    document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');

    document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
    document.getElementById('content-' + tab).classList.remove('hidden');
}

function filterTable() {
    const query = document.getElementById('search-input').value.toLowerCase();
    const activeContent = document.getElementById('content-' + currentTab);
    activeContent.querySelectorAll('.user-row').forEach(row => {
        const match = row.dataset.search.includes(query);
        row.style.display = match ? '' : 'none';
    });
}

// ========== Utils ==========

function formatDate(dateString) {
    if (!dateString) return '-';
    const d = new Date(dateString);
    return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };
    toast.className = `fixed top-4 right-4 z-[100] ${colors[type] || colors.info} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 transform transition-all duration-300`;

    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    toast.innerHTML = `<span>${icon}</span> ${message}`;

    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
