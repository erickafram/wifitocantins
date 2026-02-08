@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Painel Remoto Mikrotik</h2>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Usuários Ativos</h5>
                    <h2 id="activeUsers">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Usuários Pagos</h5>
                    <h2 id="paidUsers">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Dispositivos</h5>
                    <h2 id="totalDevices">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Status</h5>
                    <h2 id="connectionStatus">Conectando...</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary" onclick="syncNow()">
                        <i class="fas fa-sync"></i> Sincronizar Agora
                    </button>
                    <button class="btn btn-info" onclick="refreshStatus()">
                        <i class="fas fa-refresh"></i> Atualizar Status
                    </button>
                    <button class="btn btn-secondary" onclick="showLogs()">
                        <i class="fas fa-file-alt"></i> Ver Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liberar/Bloquear MAC -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Liberar MAC</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="macToLiberate" placeholder="XX:XX:XX:XX:XX:XX">
                        <button class="btn btn-success" onclick="liberateMac()">Liberar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Bloquear MAC</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="macToBlock" placeholder="XX:XX:XX:XX:XX:XX">
                        <button class="btn btn-danger" onclick="blockMac()">Bloquear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#activeTab">Usuários Ativos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#bindingsTab">Usuários Pagos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#leasesTab">DHCP Leases</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="activeTab" class="tab-pane fade show active">
                    <div class="card">
                        <div class="card-body">
                            <div id="activeUsersTable"></div>
                        </div>
                    </div>
                </div>
                <div id="bindingsTab" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <div id="bindingsTable"></div>
                        </div>
                    </div>
                </div>
                <div id="leasesTab" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <div id="leasesTable"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh a cada 10 segundos
setInterval(refreshStatus, 10000);

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    refreshStatus();
});

function refreshStatus() {
    fetch('/admin/mikrotik/remote/status')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('connectionStatus').textContent = 'Offline';
                return;
            }

            document.getElementById('connectionStatus').textContent = 'Online';
            document.getElementById('activeUsers').textContent = data.activeUsers;
            document.getElementById('paidUsers').textContent = data.paidUsers;
            document.getElementById('totalDevices').textContent = data.totalDevices;

            // Atualizar tabelas
            updateActiveUsersTable(data.details.active);
            updateBindingsTable(data.details.bindings);
            updateLeasesTable(data.details.leases);
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('connectionStatus').textContent = 'Erro';
        });
}

function updateActiveUsersTable(users) {
    let html = '<table class="table table-striped"><thead><tr><th>MAC</th><th>IP</th><th>Uptime</th></tr></thead><tbody>';
    users.forEach(user => {
        html += `<tr><td>${user['mac-address']}</td><td>${user.address}</td><td>${user.uptime || '-'}</td></tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('activeUsersTable').innerHTML = html;
}

function updateBindingsTable(bindings) {
    let html = '<table class="table table-striped"><thead><tr><th>MAC</th><th>Tipo</th><th>Comentário</th><th>Ação</th></tr></thead><tbody>';
    bindings.forEach(binding => {
        html += `<tr>
            <td>${binding['mac-address']}</td>
            <td>${binding.type}</td>
            <td>${binding.comment || '-'}</td>
            <td><button class="btn btn-sm btn-danger" onclick="blockMacDirect('${binding['mac-address']}')">Bloquear</button></td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('bindingsTable').innerHTML = html;
}

function updateLeasesTable(leases) {
    let html = '<table class="table table-striped"><thead><tr><th>MAC</th><th>IP</th><th>Hostname</th><th>Status</th></tr></thead><tbody>';
    leases.forEach(lease => {
        html += `<tr><td>${lease['mac-address']}</td><td>${lease.address}</td><td>${lease['host-name'] || '-'}</td><td>${lease.status}</td></tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('leasesTable').innerHTML = html;
}

function syncNow() {
    if (!confirm('Executar sincronização agora?')) return;
    
    fetch('/admin/mikrotik/remote/sync', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Sincronização executada');
            refreshStatus();
        })
        .catch(error => alert('Erro: ' + error));
}

function liberateMac() {
    const mac = document.getElementById('macToLiberate').value;
    if (!mac) return alert('Digite um MAC');
    
    fetch('/admin/mikrotik/remote/liberate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ mac: mac })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'MAC liberado');
        document.getElementById('macToLiberate').value = '';
        refreshStatus();
    })
    .catch(error => alert('Erro: ' + error));
}

function blockMac() {
    const mac = document.getElementById('macToBlock').value;
    if (!mac) return alert('Digite um MAC');
    
    blockMacDirect(mac);
}

function blockMacDirect(mac) {
    if (!confirm(`Bloquear MAC ${mac}?`)) return;
    
    fetch('/admin/mikrotik/remote/block', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ mac: mac })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'MAC bloqueado');
        document.getElementById('macToBlock').value = '';
        refreshStatus();
    })
    .catch(error => alert('Erro: ' + error));
}

function showLogs() {
    fetch('/admin/mikrotik/remote/logs')
        .then(response => response.json())
        .then(data => {
            let logs = data.logs.map(log => `[${log.time}] ${log.message}`).join('\n');
            alert(logs);
        })
        .catch(error => alert('Erro: ' + error));
}
</script>
@endsection
