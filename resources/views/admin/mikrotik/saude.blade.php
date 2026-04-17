@extends('layouts.admin')

@section('title', 'Saúde dos MikroTiks')

@section('breadcrumb')
    <span class="text-muted">›</span>
    <span class="text-green font-semibold">Saúde dos MikroTiks</span>
@endsection

@section('page-title', 'Saúde dos MikroTiks')

@section('content')
<div class="max-w-8xl mx-auto">

    {{-- Hero --}}
    <div class="bg-gradient-to-r from-green-dark via-green to-green-light rounded-xl px-5 py-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/60 mb-0.5">Starlink · Monitoramento</p>
            <h1 class="text-xl font-bold text-white">Saúde dos {{ $summary['total'] }} MikroTiks</h1>
            <p class="text-xs text-white/70 mt-0.5">
                Cada ônibus sincroniza a cada 15s. Se parou de sincronizar, ninguém paga nem conecta naquele ônibus.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span id="auto-refresh-indicator" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/15 border border-white/20 rounded-lg text-[10px] font-semibold text-white">
                <span class="w-1.5 h-1.5 rounded-full bg-green-light animate-pulse"></span>
                Atualizando a cada 15s
            </span>
            <button onclick="refreshNow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-green font-bold text-xs rounded-lg hover:bg-green-pale transition shadow-card">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Atualizar agora
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-green/30 shadow-card p-4">
            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Online</p>
            <p class="text-3xl font-bold text-green mt-1" id="sum-online">{{ $summary['online'] }}</p>
            <p class="text-[10px] text-muted mt-0.5">sync ≤ 30s</p>
        </div>
        <div class="bg-white rounded-xl border border-gold/30 shadow-card p-4">
            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Atrasado</p>
            <p class="text-3xl font-bold text-gold mt-1" id="sum-lagging">{{ $summary['lagging'] }}</p>
            <p class="text-[10px] text-muted mt-0.5">30s a 5min</p>
        </div>
        <div class="bg-white rounded-xl border border-red/30 shadow-card p-4">
            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Offline</p>
            <p class="text-3xl font-bold text-red mt-1" id="sum-offline">{{ $summary['offline'] }}</p>
            <p class="text-[10px] text-muted mt-0.5">&gt; 5min sem sync</p>
        </div>
        <div class="bg-white rounded-xl border border-blue/30 shadow-card p-4">
            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Usuários ativos</p>
            <p class="text-3xl font-bold text-blue mt-1" id="sum-users">{{ $summary['total_users'] }}</p>
            <p class="text-[10px] text-muted mt-0.5">conectados agora</p>
        </div>
    </div>

    {{-- Lista de ônibus --}}
    <div class="bg-white rounded-xl border border-border shadow-card overflow-hidden">
        <div class="px-5 py-3 border-b border-border flex items-center justify-between">
            <h2 class="text-sm font-bold text-ink">Ônibus / MikroTiks</h2>
            <span class="text-[10px] text-muted" id="last-check-label">
                Última verificação: <span id="last-check-time">agora</span>
            </span>
        </div>

        @if($data->isEmpty())
            <div class="p-8 text-center text-muted text-sm">
                Nenhum MikroTik registrado ainda. Quando qualquer ônibus fizer o primeiro sync,
                ele aparece aqui automaticamente.
            </div>
        @else
            <div class="divide-y divide-border" id="bus-list">
                @foreach($data as $item)
                    @php
                        $bus = $item['bus'];
                        $status = $item['status'];
                        $secs = $item['seconds_since_sync'];
                        $colorMap = [
                            'online'  => ['dot' => 'bg-green', 'text' => 'text-green', 'label' => 'ONLINE', 'badge' => 'bg-green-pale text-green border-green/30'],
                            'lagging' => ['dot' => 'bg-gold', 'text' => 'text-gold', 'label' => 'ATRASADO', 'badge' => 'bg-gold-pale text-gold border-gold/30'],
                            'offline' => ['dot' => 'bg-red', 'text' => 'text-red', 'label' => 'OFFLINE', 'badge' => 'bg-red-pale text-red border-red/30'],
                            'unknown' => ['dot' => 'bg-muted', 'text' => 'text-muted', 'label' => 'NUNCA SINCRONIZOU', 'badge' => 'bg-surface text-muted border-border'],
                        ];
                        $c = $colorMap[$status];

                        if ($secs === null) {
                            $syncText = 'nunca sincronizou';
                        } elseif ($secs < 60) {
                            $syncText = $secs . 's atrás';
                        } elseif ($secs < 3600) {
                            $syncText = floor($secs / 60) . 'min atrás';
                        } else {
                            $syncText = floor($secs / 3600) . 'h atrás';
                        }
                    @endphp
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-surface/30 transition"
                         data-serial="{{ $bus->mikrotik_serial }}">
                        <div class="w-3 h-3 rounded-full {{ $c['dot'] }} flex-shrink-0" data-dot></div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-ink text-sm truncate">{{ $bus->name }}</p>
                                @if($bus->plate)
                                    <span class="text-[10px] text-muted font-mono">{{ $bus->plate }}</span>
                                @endif
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded border {{ $c['badge'] }}" data-badge>
                                    {{ $c['label'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-muted flex-wrap">
                                <span class="font-mono">{{ $bus->mikrotik_serial }}</span>
                                @if($bus->last_public_ip)
                                    <span>· IP {{ $bus->last_public_ip }}</span>
                                @endif
                                @if($bus->last_city || $bus->last_state)
                                    <span>· {{ trim(($bus->last_city ?? '') . ' ' . ($bus->last_state ?? '')) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Último sync</p>
                            <p class="text-sm font-bold {{ $c['text'] }}" data-sync-text>{{ $syncText }}</p>
                        </div>

                        <div class="text-right flex-shrink-0 w-20">
                            <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Usuários</p>
                            <p class="text-sm font-bold text-ink" data-users>{{ $item['active_users'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Legenda --}}
    <div class="mt-4 bg-surface/50 rounded-xl border border-border p-4 text-xs text-muted">
        <p class="font-bold text-ink mb-2">O que esses status significam:</p>
        <ul class="space-y-1">
            <li><span class="inline-block w-2 h-2 rounded-full bg-green align-middle mr-1"></span> <strong class="text-green">Online</strong> — sincronizou nos últimos 30s. Tudo normal.</li>
            <li><span class="inline-block w-2 h-2 rounded-full bg-gold align-middle mr-1"></span> <strong class="text-gold">Atrasado</strong> — sem sync entre 30s e 5min. Pode ser Starlink instável.</li>
            <li><span class="inline-block w-2 h-2 rounded-full bg-red align-middle mr-1"></span> <strong class="text-red">Offline</strong> — sem sync há mais de 5min. Ônibus desligado, Starlink caiu ou MikroTik travou. Usuários não conseguem pagar/conectar nesse ônibus.</li>
        </ul>
    </div>

</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let refreshTimer = null;

    function secondsToText(secs) {
        if (secs === null || secs === undefined) return 'nunca sincronizou';
        if (secs < 60) return secs + 's atrás';
        if (secs < 3600) return Math.floor(secs / 60) + 'min atrás';
        return Math.floor(secs / 3600) + 'h atrás';
    }

    function statusClasses(status) {
        const map = {
            online:  { dot: 'bg-green', text: 'text-green', label: 'ONLINE',   badge: 'bg-green-pale text-green border-green/30' },
            lagging: { dot: 'bg-gold',  text: 'text-gold',  label: 'ATRASADO', badge: 'bg-gold-pale text-gold border-gold/30' },
            offline: { dot: 'bg-red',   text: 'text-red',   label: 'OFFLINE',  badge: 'bg-red-pale text-red border-red/30' },
            unknown: { dot: 'bg-muted', text: 'text-muted', label: 'NUNCA SINCRONIZOU', badge: 'bg-surface text-muted border-border' },
        };
        return map[status] || map.unknown;
    }

    async function refreshNow() {
        try {
            const res = await fetch('{{ route('admin.mikrotik.health.json') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const json = await res.json();

            let online = 0, lagging = 0, offline = 0, totalUsers = 0;

            json.data.forEach(item => {
                if (item.status === 'online') online++;
                else if (item.status === 'lagging') lagging++;
                else offline++;
                totalUsers += item.active_users;

                const row = document.querySelector(`[data-serial="${item.serial}"]`);
                if (!row) return;
                const c = statusClasses(item.status);

                const dot = row.querySelector('[data-dot]');
                dot.className = 'w-3 h-3 rounded-full ' + c.dot + ' flex-shrink-0';

                const badge = row.querySelector('[data-badge]');
                badge.className = 'px-2 py-0.5 text-[9px] font-bold rounded border ' + c.badge;
                badge.textContent = c.label;

                const syncText = row.querySelector('[data-sync-text]');
                syncText.className = 'text-sm font-bold ' + c.text;
                syncText.textContent = secondsToText(item.seconds_since_sync);

                row.querySelector('[data-users]').textContent = item.active_users;
            });

            document.getElementById('sum-online').textContent = online;
            document.getElementById('sum-lagging').textContent = lagging;
            document.getElementById('sum-offline').textContent = offline;
            document.getElementById('sum-users').textContent = totalUsers;
            document.getElementById('last-check-time').textContent = new Date().toLocaleTimeString('pt-BR');
        } catch (e) {
            console.error('Erro ao atualizar saúde dos MikroTiks:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('last-check-time').textContent = new Date().toLocaleTimeString('pt-BR');
        refreshTimer = setInterval(refreshNow, 15000);
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(refreshTimer);
        } else {
            refreshNow();
            refreshTimer = setInterval(refreshNow, 15000);
        }
    });
</script>
@endsection
