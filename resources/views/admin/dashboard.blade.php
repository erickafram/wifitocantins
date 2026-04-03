@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div id="dashboard-section" class="section-content">
                    
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-green-dark via-green to-green-light rounded-xl px-5 py-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/60 mb-0.5">Starlink · Tocantins Transporte</p>
            <h1 class="text-xl font-bold text-white leading-tight">Dashboard</h1>
            <p class="text-xs text-white/70 mt-0.5">Visão geral do sistema WiFi Tocantins</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.mikrotik.remote.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 border border-white/20 rounded-lg text-xs font-semibold text-white hover:bg-white/25 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                MikroTik
            </a>
            <div class="bg-white/15 border border-white/20 rounded-lg px-3 py-1.5">
                <p class="text-[10px] text-white/60">Atualizado em</p>
                <p class="text-xs font-semibold text-white" id="current-datetime">{{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Status do Sistema - Cards horizontais com STATUS REAL -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statusItems = [
                ['key' => 'mikrotik', 'label' => 'MikroTik', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01', 'color' => 'blue'],
                ['key' => 'database', 'label' => 'Database', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4', 'color' => 'purple'],
                ['key' => 'pagamentos', 'label' => 'Pagamentos', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'amber'],
                ['key' => 'api_sync', 'label' => 'API Sync', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => 'cyan'],
            ];
        @endphp
        @foreach($statusItems as $item)
            @php
                $s = $system_status[$item['key']] ?? ['online' => false, 'detail' => 'Desconhecido'];
                $isWarning = $s['warning'] ?? false;
                $dotColor  = $s['online'] ? ($isWarning ? 'bg-gold' : 'bg-green') : 'bg-red';
                $textColor = $s['online'] ? ($isWarning ? 'text-gold' : 'text-green') : 'text-red';
                $borderColor = $s['online'] ? ($isWarning ? 'border-gold/30' : 'border-green/30') : 'border-red/30';
                $iconBg    = $s['online'] ? ($isWarning ? 'bg-gold-pale' : 'bg-green-pale') : 'bg-red-pale';
                $iconColor = $s['online'] ? ($isWarning ? 'text-gold' : 'text-green') : 'text-red';
            @endphp
            <div class="bg-white rounded-xl border {{ $borderColor }} shadow-card p-3 flex items-center gap-3 hover:shadow-hover transition-all">
                <div class="w-9 h-9 {{ $iconBg }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] text-muted font-medium truncate">{{ $item['label'] }}</p>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} {{ $s['online'] && !$isWarning ? 'animate-pulse' : '' }}"></span>
                        <span class="text-xs font-semibold {{ $textColor }} truncate">{{ $s['detail'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <!-- Usuários Conectados -->
        <div class="bg-white rounded-xl shadow-card border border-border p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 bg-green-pale rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-ink">{{ $stats['connected_users'] }}</p>
                    <p class="text-[11px] text-muted">Conectados agora</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider bg-green/10 text-green px-1.5 py-0.5 rounded">
                    <span class="w-1.5 h-1.5 bg-green rounded-full animate-pulse"></span>Online
                </span>
                @if($stats['temp_bypass_active'] > 0)
                    <span class="text-[11px] font-semibold text-gold">{{ $stats['temp_bypass_active'] }} bypass</span>
                @endif
            </div>
        </div>

        <!-- Receita Hoje -->
        <div class="bg-white rounded-xl shadow-card border border-border p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 bg-gold-pale rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-ink">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
                    <p class="text-[11px] text-muted">Receita hoje</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <span class="text-muted">{{ $stats['today_payments_count'] ?? 0 }} pagamentos</span>
                @php $diff = $stats['daily_revenue'] - $stats['yesterday_revenue']; @endphp
                <span class="{{ $diff >= 0 ? 'text-green font-semibold' : 'text-red font-semibold' }}">
                    {{ $diff >= 0 ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Receita Semana -->
        <div class="bg-white rounded-xl shadow-card border border-border p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 bg-blue-pale rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-ink">R$ {{ number_format($stats['week_revenue'], 2, ',', '.') }}</p>
                    <p class="text-[11px] text-muted">Últimos 7 dias</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <span class="text-muted">Ontem: R$ {{ number_format($stats['yesterday_revenue'], 2, ',', '.') }}</span>
                <span class="text-muted">{{ $stats['yesterday_payments_count'] ?? 0 }} pgtos</span>
            </div>
        </div>

        <!-- Pagamentos Pendentes -->
        <div class="bg-white rounded-xl shadow-card border {{ $stats['pending_payments_count'] > 0 ? 'border-gold/40' : 'border-border' }} p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 {{ $stats['pending_payments_count'] > 0 ? 'bg-gold-pale' : 'bg-surface' }} rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $stats['pending_payments_count'] > 0 ? 'text-gold' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold {{ $stats['pending_payments_count'] > 0 ? 'text-gold' : 'text-ink' }}">{{ $stats['pending_payments_count'] }}</p>
                    <p class="text-[11px] text-muted">Pendentes hoje</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <span class="text-muted">R$ {{ number_format($stats['pending_payments'], 2, ',', '.') }}</span>
                <span class="text-muted">aguardando PIX</span>
            </div>
        </div>
    </div>

    <!-- Info Cards Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-border shadow-card p-4 text-center hover:shadow-hover transition-all">
            <p class="text-xl font-bold text-ink">{{ $stats['total_users'] }}</p>
            <p class="text-[11px] text-muted mt-0.5">Total Usuários</p>
        </div>
        <div class="bg-white rounded-xl border border-border shadow-card p-4 text-center hover:shadow-hover transition-all">
            <p class="text-xl font-bold text-ink">{{ $stats['total_devices'] }}</p>
            <p class="text-[11px] text-muted mt-0.5">Dispositivos</p>
        </div>
        <div class="bg-white rounded-xl border border-border shadow-card p-4 text-center hover:shadow-hover transition-all">
            <p class="text-xl font-bold text-green">{{ $stats['active_vouchers'] }}</p>
            <p class="text-[11px] text-muted mt-0.5">Vouchers Ativos</p>
        </div>
        <div class="bg-white rounded-xl border border-border shadow-card p-4 text-center hover:shadow-hover transition-all">
            <p class="text-xl font-bold text-ink">R$ {{ number_format($stats['month_revenue'], 2, ',', '.') }}</p>
            <p class="text-[11px] text-muted mt-0.5">Receita 30 dias</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-card border border-border p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                <h3 class="text-sm font-bold text-ink">Receita dos Últimos 7 Dias</h3>
                <span class="text-[11px] text-muted">R$ {{ number_format(array_sum($revenue_chart['data']), 2, ',', '.') }} total</span>
            </div>
            <div style="height: 200px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-card border border-border p-5 hover:shadow-hover transition-all">
            <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                <h3 class="text-sm font-bold text-ink">Conexões por Hora</h3>
                <span class="text-[11px] text-muted">Últimas 12h</span>
            </div>
            <div style="height: 200px;">
                <canvas id="connectionsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Content Grid: Connected Users + Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Usuários Online -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-border">
            <div class="flex justify-between items-center border-b border-border px-4 py-3">
                <div>
                    <h3 class="text-sm font-bold text-ink">Usuários Conectados</h3>
                    <p class="text-[11px] text-muted">{{ count($connected_users) }} online agora</p>
                </div>
                <a href="{{ route('admin.mikrotik.remote.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-pale text-blue font-semibold rounded-lg text-xs hover:bg-blue/10 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                    Painel MikroTik
                </a>
            </div>
            <div class="p-4">
            @if(count($connected_users) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left text-[10px] font-bold text-muted uppercase tracking-wider py-2.5">Usuário</th>
                            <th class="text-left text-[10px] font-bold text-muted uppercase tracking-wider py-2.5">Status</th>
                            <th class="text-left text-[10px] font-bold text-muted uppercase tracking-wider py-2.5">Expira</th>
                            <th class="text-right text-[10px] font-bold text-muted uppercase tracking-wider py-2.5">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($connected_users as $user)
                        @php
                            $statusColors = [
                                'connected'   => 'bg-green/10 text-green',
                                'active'      => 'bg-green/10 text-green',
                                'temp_bypass' => 'bg-gold/10 text-gold',
                            ];
                            $statusLabels = [
                                'connected'   => 'Conectado',
                                'active'      => 'Ativo',
                                'temp_bypass' => 'Bypass',
                            ];
                            $stColor  = $statusColors[$user->status] ?? 'bg-gray-100 text-muted';
                            $stLabel  = $statusLabels[$user->status] ?? ucfirst($user->status);
                            $expiresIn = $user->expires_at ? now()->diffInMinutes($user->expires_at, false) : 0;
                            $urgent    = $expiresIn > 0 && $expiresIn < 30;
                        @endphp
                        <tr class="hover:bg-surface transition-colors">
                            <td class="py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-gradient-to-br {{ $user->status === 'temp_bypass' ? 'from-gold to-gold/70' : 'from-green-dark to-green' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-xs font-bold">{{ strtoupper(substr($user->name ?? $user->mac_address, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-ink">{{ $user->name ?? 'Dispositivo' }}</p>
                                        <p class="text-[10px] text-muted font-mono">{{ $user->mac_address }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded {{ $stColor }}">{{ $stLabel }}</span>
                            </td>
                            <td class="py-3">
                                @if($user->expires_at)
                                    @if($expiresIn > 0)
                                        <span class="text-xs font-semibold {{ $urgent ? 'text-red' : ($expiresIn >= 60 ? 'text-green' : 'text-gold') }}">
                                            {{ $expiresIn >= 60 ? floor($expiresIn/60).'h '.($expiresIn%60).'m' : $expiresIn.'m' }}
                                        </span>
                                        <p class="text-[10px] text-muted">{{ $user->expires_at->format('H:i') }}</p>
                                    @else
                                        <span class="text-[10px] text-red font-semibold">Expirado</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <button class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-pale text-red rounded-lg text-xs font-semibold hover:bg-red/10 transition-colors"
                                    onclick="disconnectUser('{{ $user->mac_address }}')">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Desconectar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-10">
                <div class="w-12 h-12 bg-surface rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
                <p class="text-muted text-sm">Nenhum usuário conectado no momento</p>
            </div>
            @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">

            <!-- Últimos Pagamentos -->
            <div class="bg-white rounded-xl shadow-card border border-border">
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <h3 class="text-sm font-bold text-ink">Últimos Pagamentos</h3>
                    <a href="{{ route('admin.reports') }}" class="text-[11px] font-semibold text-green hover:text-green-dark">Ver todos</a>
                </div>
                <div class="p-4">
                @if(count($recent_payments) > 0)
                    <div class="divide-y divide-border">
                        @foreach($recent_payments as $payment)
                        @php
                            $pMap = [
                                'completed' => ['bg' => 'bg-green/10',  'text' => 'text-green',  'icon' => '✅'],
                                'pending'   => ['bg' => 'bg-gold/10',   'text' => 'text-gold',   'icon' => '⏳'],
                                'failed'    => ['bg' => 'bg-red-pale',  'text' => 'text-red',    'icon' => '❌'],
                                'expired'   => ['bg' => 'bg-surface',   'text' => 'text-muted',  'icon' => '⏰'],
                            ];
                            $p = $pMap[$payment->status] ?? ['bg' => 'bg-surface', 'text' => 'text-muted', 'icon' => '?'];
                        @endphp
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-sm flex-shrink-0">{{ $p['icon'] }}</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-ink truncate">{{ $payment->user->phone ?? $payment->user->name ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-muted">{{ $payment->created_at->format('d/m H:i') }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold {{ $p['text'] }} flex-shrink-0 ml-2">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted text-xs py-4">Nenhum pagamento</p>
                @endif
                </div>
            </div>

            <!-- Bypass Activity -->
            <div class="bg-white rounded-xl shadow-card border border-border">
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <h3 class="text-sm font-bold text-ink">Bypass Temporário</h3>
                    <a href="{{ route('admin.mikrotik.remote.index') }}" class="text-[11px] font-semibold text-gold hover:text-gold/80">Ver logs</a>
                </div>
                <div class="grid grid-cols-3 gap-2 p-4">
                    <div class="text-center p-2.5 bg-surface rounded-xl">
                        <p class="text-lg font-bold text-ink">{{ $bypass_stats['total_hoje'] }}</p>
                        <p class="text-[10px] text-muted">Total</p>
                    </div>
                    <div class="text-center p-2.5 bg-green-pale rounded-xl">
                        <p class="text-lg font-bold text-green">{{ $bypass_stats['aprovados_hoje'] }}</p>
                        <p class="text-[10px] text-muted">Aprovados</p>
                    </div>
                    <div class="text-center p-2.5 bg-red-pale rounded-xl">
                        <p class="text-lg font-bold text-red">{{ $bypass_stats['negados_hoje'] }}</p>
                        <p class="text-[10px] text-muted">Negados</p>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="bg-white rounded-xl shadow-card border border-border">
                <div class="border-b border-border px-4 py-3">
                    <h3 class="text-sm font-bold text-ink">Ações Rápidas</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('admin.mikrotik.remote.index') }}" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 bg-blue-pale text-blue font-semibold rounded-lg text-xs hover:bg-blue/10 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                        Controle MikroTik
                    </a>
                    <a href="{{ route('admin.vouchers.index') }}" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 bg-green-pale text-green font-semibold rounded-lg text-xs hover:bg-green/10 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        Gerenciar Vouchers
                    </a>
                    <a href="{{ route('admin.reports') }}" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 bg-gold-pale text-gold font-semibold rounded-lg text-xs hover:bg-gold/10 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Ver Relatórios
                    </a>
                    <a href="{{ route('admin.users') }}" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 bg-surface border border-border text-ink2 font-semibold rounded-lg text-xs hover:bg-border transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Gerenciar Usuários
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Atualizar data/hora
    function updateDateTime() {
        const now = new Date();
        const formatted = now.toLocaleDateString('pt-BR') + ' ' + now.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        const el = document.getElementById('current-datetime');
        if (el) el.textContent = formatted;
    }
    setInterval(updateDateTime, 60000);

    // Gráfico de Receita
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 180);
    revenueGradient.addColorStop(0, 'rgba(0, 163, 53, 0.25)');
    revenueGradient.addColorStop(1, 'rgba(0, 163, 53, 0.01)');
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenue_chart['labels']) !!},
            datasets: [{
                label: 'Receita (R$)',
                data: {!! json_encode($revenue_chart['data']) !!},
                borderColor: '#00A335',
                backgroundColor: revenueGradient,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#00A335',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111111',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) { return 'R$ ' + ctx.parsed.y.toFixed(2).replace('.', ','); }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: v => 'R$ ' + v, font: { size: 11 }, color: '#888888' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#888888' }
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
                backgroundColor: 'rgba(0, 163, 53, 0.75)',
                hoverBackgroundColor: '#00A335',
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 20,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) { return ctx.parsed.y + ' conexões'; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { stepSize: 1, font: { size: 11 }, color: '#6b7280' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#6b7280' }
                }
            }
        }
    });

    // Desconectar usuário
    function disconnectUser(macAddress) {
        if (!confirm('Deseja realmente desconectar este usuário?')) return;
        
        fetch('/admin/mikrotik/remote/block', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ mac: macAddress })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Usuário desconectado!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Erro: ' + (data.error || data.message), 'error');
            }
        })
        .catch(err => showToast('Erro de conexão', 'error'));
    }

    function showToast(message, type = 'info') {
        const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-blue-600' };
        const icons = { success: '✅', error: '❌', info: 'ℹ️' };
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-[100] ${colors[type]} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium transform transition-all duration-300`;
        toast.innerHTML = `<span>${icons[type]}</span> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Auto-refresh a cada 60s
    setTimeout(() => location.reload(), 60000);
</script>
@endsection