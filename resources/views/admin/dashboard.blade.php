@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Dashboard Section -->
<div id="dashboard-section" class="section-content">
                    
                    <!-- Header com Data e Hora -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                            <p class="text-sm text-gray-500">Visão geral do sistema WiFi Tocantins</p>
                        </div>
                        <div class="bg-white rounded-xl px-4 py-2 shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500">Atualizado em</p>
                            <p class="text-sm font-semibold text-gray-700" id="current-datetime">{{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Stats Cards - Grid 2x2 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        
                        <!-- Usuários Conectados -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-800">{{ $stats['connected_users'] }}</p>
                                    <p class="text-xs text-gray-500">Conectados agora</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1 animate-pulse"></span>
                                    Online
                                </span>
                            </div>
                        </div>

                        <!-- Receita Hoje -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-800">R$ {{ number_format($stats['daily_revenue'], 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">Receita hoje</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-gray-500">{{ $stats['today_payments_count'] ?? 0 }} pagamentos</span>
                                @php
                                    $diff = $stats['daily_revenue'] - $stats['yesterday_revenue'];
                                    $isPositive = $diff >= 0;
                                @endphp
                                <span class="{{ $isPositive ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $isPositive ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Receita Ontem -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-800">R$ {{ number_format($stats['yesterday_revenue'], 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">Receita ontem</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-gray-500">{{ $stats['yesterday_payments_count'] ?? 0 }} pagamentos</span>
                                <span class="text-gray-400">{{ now()->subDay()->format('d/m') }}</span>
                            </div>
                        </div>

                        <!-- Vouchers Ativos -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active_vouchers'] }}</p>
                                    <p class="text-xs text-gray-500">Vouchers ativos</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <a href="{{ route('admin.vouchers.index') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                                    Gerenciar vouchers →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                        <!-- Gráfico de Receita -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-800">Receita dos Últimos 7 Dias</h3>
                                <span class="text-xs text-gray-500">R$ {{ number_format(array_sum($revenue_chart['data']), 2, ',', '.') }} total</span>
                            </div>
                            <div style="height: 200px;">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>

                        <!-- Gráfico de Conexões -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-800">Conexões por Hora</h3>
                                <span class="text-xs text-gray-500">Últimas 12h</span>
                            </div>
                            <div style="height: 200px;">
                                <canvas id="connectionsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        
                        <!-- Usuários Online -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">Usuários Conectados</h3>
                                    <p class="text-xs text-gray-500">{{ count($connected_users) }} usuários online</p>
                                </div>
                                <button onclick="location.reload()" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Atualizar
                                </button>
                            </div>
                            
                            @if(count($connected_users) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3">Usuário</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3">IP</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3">Conectado</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3">Expira</th>
                                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach($connected_users as $user)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-3">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                                        <span class="text-white text-xs font-bold">
                                                            {{ strtoupper(substr($user->name ?? $user->mac_address, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $user->name ?? 'Dispositivo' }}</p>
                                                        <p class="text-xs text-gray-500 font-mono">{{ $user->mac_address }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <span class="text-sm text-gray-700 font-mono">{{ $user->ip_address }}</span>
                                            </td>
                                            <td class="py-3">
                                                <span class="text-sm text-gray-700">{{ $user->connected_at ? $user->connected_at->format('H:i') : '-' }}</span>
                                            </td>
                                            <td class="py-3">
                                                @if($user->expires_at)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->expires_at->diffInMinutes(now()) < 30 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                        {{ $user->expires_at->format('H:i') }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-right">
                                                <button class="inline-flex items-center px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors"
                                                    onclick="disconnectUser('{{ $user->mac_address }}')">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
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
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">Nenhum usuário conectado no momento</p>
                            </div>
                            @endif
                        </div>

                        <!-- Sidebar - Ações Rápidas & Status -->
                        <div class="space-y-4">

                            <!-- Ações Rápidas -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                                <h3 class="text-sm font-semibold text-gray-800 mb-4">Ações Rápidas</h3>
                                
                                <div class="space-y-2">
                                    <a href="{{ route('admin.vouchers.index') }}" class="w-full flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg text-sm font-medium hover:from-purple-600 hover:to-purple-700 transition-all">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        Gerenciar Vouchers
                                    </a>

                                    <a href="{{ route('admin.reports') }}" class="w-full flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg text-sm font-medium hover:from-emerald-600 hover:to-emerald-700 transition-all">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Ver Relatórios
                                    </a>

                                    <a href="{{ route('admin.users') }}" class="w-full flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-medium hover:from-blue-600 hover:to-blue-700 transition-all">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        Gerenciar Usuários
                                    </a>

                                    <a href="{{ route('admin.settings.index') }}" class="w-full flex items-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-all">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Configurações
                                    </a>
                                </div>
                            </div>

                            <!-- Status do Sistema -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                                <h4 class="text-sm font-semibold text-gray-800 mb-4">Status do Sistema</h4>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-700">MikroTik</span>
                                        </div>
                                        <span class="flex items-center text-xs text-emerald-600 font-medium">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                            Online
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-700">Database</span>
                                        </div>
                                        <span class="flex items-center text-xs text-emerald-600 font-medium">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1.5"></span>
                                            Online
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-700">Pagamentos</span>
                                        </div>
                                        <span class="flex items-center text-xs text-emerald-600 font-medium">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1.5"></span>
                                            Ativo
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-700">Starlink</span>
                                        </div>
                                        <span class="flex items-center text-xs text-emerald-600 font-medium">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1.5"></span>
                                            Online
                                        </span>
                                    </div>
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
        revenueGradient.addColorStop(0, 'rgba(251, 191, 36, 0.3)');
        revenueGradient.addColorStop(1, 'rgba(251, 191, 36, 0.01)');
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue_chart['labels']) !!},
                datasets: [{
                    label: 'Receita (R$)',
                    data: {!! json_encode($revenue_chart['data']) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: revenueGradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f59e0b',
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value;
                            },
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#6b7280'
                        }
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
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' conexões';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 10 },
                            color: '#6b7280'
                        }
                    }
                }
            }
        });

        // Função para desconectar usuário
        function disconnectUser(macAddress) {
            if (confirm('Deseja realmente desconectar este usuário?')) {
                fetch('/api/mikrotik/block', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ mac_address: macAddress })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Usuário desconectado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao desconectar usuário: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro de conexão: ' + error.message);
                });
            }
        }

        // Auto-refresh do dashboard a cada 60 segundos
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
@endsection