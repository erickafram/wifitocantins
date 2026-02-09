@extends('layouts.admin')

@section('title', 'Gerenciar Vouchers')

@section('breadcrumb')
    <span>›</span>
    <a href="{{ route('admin.vouchers.index') }}" class="text-tocantins-green font-medium">Vouchers</a>
@endsection

@section('page-title', 'Gerenciamento de Vouchers')

@section('content')
    <!-- Header com botão -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Vouchers de Motoristas</h1>
            <p class="text-gray-500 text-sm">Gerencie os vouchers de acesso para motoristas</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:shadow-lg transform hover:scale-[1.02] transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Novo Voucher
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 flex items-center gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Estatísticas -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border border-emerald-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Ativos</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Inativos</p>
                    <p class="text-2xl font-bold text-gray-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border border-red-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Expirados</p>
                    <p class="text-2xl font-bold text-red-500">{{ $stats['expired'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border border-indigo-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Ilimitados</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $stats['unlimited'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.vouchers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, código ou telefone..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
            </div>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                <option value="">Todos os Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativos</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativos</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirados</option>
            </select>
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                <option value="">Todos os Tipos</option>
                <option value="limited" {{ request('type') == 'limited' ? 'selected' : '' }}>Limitado</option>
                <option value="unlimited" {{ request('type') == 'unlimited' ? 'selected' : '' }}>Ilimitado</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-tocantins-green text-white rounded-lg text-sm font-medium hover:bg-tocantins-dark-green transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Vouchers -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header da tabela -->
        <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Lista de Vouchers
                </h3>
                <span class="bg-white/20 px-3 py-1 rounded-full text-white text-sm">
                    {{ $vouchers->total() }} registros
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Motorista</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Uso Diário</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Expiração</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Código -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $voucher->voucher_type === 'unlimited' ? 'bg-indigo-100' : 'bg-emerald-100' }} flex items-center justify-center flex-shrink-0">
                                        @if($voucher->voucher_type === 'unlimited')
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-mono text-sm font-bold text-tocantins-green cursor-pointer hover:text-tocantins-dark-green" onclick="copyCode('{{ $voucher->code }}', this)" title="Clique para copiar">{{ $voucher->code }}</p>
                                        <p class="text-xs text-gray-400">{{ $voucher->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Motorista -->
                            <td class="px-5 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $voucher->driver_name }}</p>
                                    @if($voucher->driver_phone)
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $voucher->driver_phone }}
                                        </p>
                                    @endif
                                    @if($voucher->description)
                                        <p class="text-xs text-gray-400 mt-1 italic">{{ Str::limit($voucher->description, 30) }}</p>
                                    @endif
                                </div>
                            </td>

                            <!-- Tipo -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->voucher_type === 'unlimited')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        Ilimitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $voucher->daily_hours }}h/dia
                                    </span>
                                @endif
                            </td>

                            <!-- Uso Diário -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->voucher_type === 'unlimited')
                                    <span class="text-xs text-gray-400">—</span>
                                @else
                                    @php
                                        $percentage = $voucher->daily_hours > 0 ? ($voucher->daily_hours_used / $voucher->daily_hours) * 100 : 0;
                                        $barColor = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 75 ? 'bg-amber-500' : 'bg-emerald-500');
                                    @endphp
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium {{ $percentage >= 100 ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $voucher->daily_hours_used }}h / {{ $voucher->daily_hours }}h
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Expiração -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->expires_at)
                                    @if($voucher->expires_at->isPast())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.924-.833-2.694 0L4.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                            Expirado
                                        </span>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-medium text-gray-700">{{ $voucher->expires_at->format('d/m/Y') }}</span>
                                            <span class="text-xs text-gray-400">{{ $voucher->expires_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Sem expiração
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->is_active && (!$voucher->expires_at || !$voucher->expires_at->isPast()))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                        Ativo
                                    </span>
                                @elseif($voucher->expires_at && $voucher->expires_at->isPast())
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                        Expirado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                        Inativo
                                    </span>
                                @endif
                            </td>

                            <!-- Ações -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" 
                                       class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    
                                    <form action="{{ route('admin.vouchers.toggle', $voucher) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 rounded-lg {{ $voucher->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition-colors"
                                                title="{{ $voucher->is_active ? 'Desativar' : 'Ativar' }}">
                                            @if($voucher->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    @if($voucher->voucher_type === 'limited' && $voucher->daily_hours_used > 0)
                                        <form action="{{ route('admin.vouchers.reset', $voucher) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 rounded-lg text-purple-600 hover:bg-purple-50 transition-colors"
                                                    title="Resetar uso diário"
                                                    onclick="return confirm('Resetar o uso diário deste voucher?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.vouchers.destroy', $voucher) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este voucher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors"
                                                title="Excluir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                    </div>
                                    @if(request()->hasAny(['search', 'status', 'type']))
                                        <p class="text-gray-600 font-medium mb-1">Nenhum voucher encontrado</p>
                                        <p class="text-gray-400 text-sm mb-4">Tente ajustar os filtros de busca</p>
                                        <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center gap-2 bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Limpar Filtros
                                        </a>
                                    @else
                                        <p class="text-gray-600 font-medium mb-1">Nenhum voucher cadastrado</p>
                                        <p class="text-gray-400 text-sm mb-4">Comece criando o primeiro voucher para motoristas</p>
                                        <a href="{{ route('admin.vouchers.create') }}" class="inline-flex items-center gap-2 bg-tocantins-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-tocantins-dark-green transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Criar Primeiro Voucher
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>

    <script>
        function copyCode(code, el) {
            navigator.clipboard.writeText(code).then(() => {
                const original = el.textContent;
                el.textContent = 'Copiado!';
                el.classList.add('text-emerald-600');
                setTimeout(() => {
                    el.textContent = original;
                    el.classList.remove('text-emerald-600');
                }, 1500);
            });
        }
    </script>
@endsection
