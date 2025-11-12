@extends('layouts.admin')

@section('title', 'Gerenciar Vouchers')

@section('breadcrumb')
    <span>›</span>
    <a href="{{ route('admin.vouchers.index') }}" class="text-tocantins-green font-medium">Vouchers</a>
@endsection

@section('page-title', 'Gerenciamento de Vouchers')

@section('content')
    <div class="mb-4">
        <div class="flex justify-between items-center">
            <p class="text-gray-500 text-xs">Gerencie os vouchers de acesso para motoristas</p>
            <a href="{{ route('admin.vouchers.create') }}" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-4 py-2 rounded-lg text-xs font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                ➕ Novo Voucher
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200">
            <p class="text-green-800 text-xs font-medium">✅ {{ session('success') }}</p>
        </div>
    @endif

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs">Total de Vouchers</p>
                    <p class="text-2xl font-bold text-tocantins-green mt-1">{{ $vouchers->total() }}</p>
                </div>
                <div class="text-2xl">🎫</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs">Vouchers Ativos</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $vouchers->where('is_active', true)->count() }}</p>
                </div>
                <div class="text-2xl">✅</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs">Vouchers Inativos</p>
                    <p class="text-2xl font-bold text-gray-600 mt-1">{{ $vouchers->where('is_active', false)->count() }}</p>
                </div>
                <div class="text-2xl">⏸️</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs">Vouchers Ilimitados</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $vouchers->where('voucher_type', 'unlimited')->count() }}</p>
                </div>
                <div class="text-2xl">♾️</div>
            </div>
        </div>
    </div>

    <!-- Tabela de Vouchers -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Código</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Motorista</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Documento</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Tipo</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Horas Diárias</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Usado Hoje</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Expira em</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-bold text-tocantins-green">{{ $voucher->code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-900">{{ $voucher->driver_name }}</p>
                                    @if($voucher->description)
                                        <p class="text-xs text-gray-500">{{ $voucher->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $voucher->driver_document ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($voucher->voucher_type === 'unlimited')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        ♾️ Ilimitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        ⏱️ Limitado
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-medium text-gray-900">
                                {{ $voucher->daily_hours }}h
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($voucher->voucher_type === 'unlimited')
                                    <span class="text-gray-400">-</span>
                                @else
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-medium {{ $voucher->daily_hours_used >= $voucher->daily_hours ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $voucher->daily_hours_used }}h / {{ $voucher->daily_hours }}h
                                        </span>
                                        @if($voucher->last_used_date)
                                            <span class="text-xs text-gray-500">{{ $voucher->last_used_date->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-600">
                                @if($voucher->expires_at)
                                    <div class="flex flex-col items-center">
                                        <span>{{ $voucher->expires_at->format('d/m/Y') }}</span>
                                        @if($voucher->expires_at->isPast())
                                            <span class="text-xs text-red-600 font-medium">Expirado</span>
                                        @else
                                            <span class="text-xs text-gray-500">{{ $voucher->expires_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">Sem expiração</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($voucher->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ❌ Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                       title="Editar">
                                        ✏️
                                    </a>
                                    
                                    <form action="{{ route('admin.vouchers.toggle', $voucher) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-yellow-600 hover:text-yellow-800 font-medium text-sm"
                                                title="{{ $voucher->is_active ? 'Desativar' : 'Ativar' }}">
                                            {{ $voucher->is_active ? '⏸️' : '▶️' }}
                                        </button>
                                    </form>

                                    @if($voucher->voucher_type === 'limited' && $voucher->daily_hours_used > 0)
                                        <form action="{{ route('admin.vouchers.reset', $voucher) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-purple-600 hover:text-purple-800 font-medium text-sm"
                                                    title="Resetar uso diário">
                                                🔄
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
                                                class="text-red-600 hover:text-red-800 font-medium text-sm"
                                                title="Excluir">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-2">🎫</span>
                                    <p class="text-sm font-medium">Nenhum voucher cadastrado</p>
                                    <p class="text-xs mt-1">Clique em "Novo Voucher" para criar o primeiro</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
@endsection
