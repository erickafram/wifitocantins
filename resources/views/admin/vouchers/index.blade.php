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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-tocantins-green to-tocantins-dark-green rounded-xl flex items-center justify-center">
                    <span class="text-xl">🎫</span>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $vouchers->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center">
                    <span class="text-xl">✅</span>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Ativos</p>
                    <p class="text-2xl font-bold text-green-600">{{ $vouchers->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl flex items-center justify-center">
                    <span class="text-xl">⏸️</span>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Inativos</p>
                    <p class="text-2xl font-bold text-gray-600">{{ $vouchers->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                    <span class="text-xl">♾️</span>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Ilimitados</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $vouchers->where('voucher_type', 'unlimited')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Vouchers -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header da tabela -->
        <div class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green px-6 py-4">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Lista de Vouchers
            </h3>
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
                                    <div class="w-10 h-10 rounded-lg {{ $voucher->voucher_type === 'unlimited' ? 'bg-blue-100' : 'bg-green-100' }} flex items-center justify-center">
                                        <span class="text-lg">{{ $voucher->voucher_type === 'unlimited' ? '♾️' : '⏱️' }}</span>
                                    </div>
                                    <div>
                                        <p class="font-mono text-sm font-bold text-tocantins-green">{{ $voucher->code }}</p>
                                        <p class="text-xs text-gray-400">{{ $voucher->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Motorista -->
                            <td class="px-5 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $voucher->driver_name }}</p>
                                    @if($voucher->driver_phone)
                                        <p class="text-xs text-gray-500">📱 {{ $voucher->driver_phone }}</p>
                                    @endif
                                    @if($voucher->description)
                                        <p class="text-xs text-gray-400 mt-1 italic">{{ Str::limit($voucher->description, 30) }}</p>
                                    @endif
                                </div>
                            </td>

                            <!-- Tipo -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->voucher_type === 'unlimited')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                                        ♾️ Ilimitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
                                        ⏱️ {{ $voucher->daily_hours }}h/dia
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
                                        $barColor = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 75 ? 'bg-yellow-500' : 'bg-green-500');
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
                                            ⚠️ Expirado
                                        </span>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-medium text-gray-700">{{ $voucher->expires_at->format('d/m/Y') }}</span>
                                            <span class="text-xs text-gray-400">{{ $voucher->expires_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Sem expiração</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 text-center">
                                @if($voucher->is_active)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
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
                                                class="p-2 rounded-lg {{ $voucher->is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} transition-colors"
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
                                                    title="Resetar uso diário">
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
                                        <span class="text-3xl">🎫</span>
                                    </div>
                                    <p class="text-gray-600 font-medium mb-1">Nenhum voucher cadastrado</p>
                                    <p class="text-gray-400 text-sm mb-4">Comece criando o primeiro voucher para motoristas</p>
                                    <a href="{{ route('admin.vouchers.create') }}" class="inline-flex items-center gap-2 bg-tocantins-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-tocantins-dark-green transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Criar Primeiro Voucher
                                    </a>
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
@endsection
