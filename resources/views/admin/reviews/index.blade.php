@extends('layouts.admin')

@section('title', 'Avaliacoes')

@section('breadcrumb')
    <span class="mx-2">/</span>
    <span class="text-tocantins-green font-medium">Avaliacoes</span>
@endsection

@section('page-title', 'Lista de Avaliacoes')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-2">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.reviews.index') ? 'bg-emerald-600 text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">Lista</a>
            <a href="{{ route('admin.reviews.settings') }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.reviews.settings*') ? 'bg-emerald-600 text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">Configuracoes</a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-2xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Convites</p>
                    <p class="text-xl font-bold text-slate-800">{{ $stats['total_invites'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Respostas</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $stats['answered'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Nota media</p>
                    <p class="text-xl font-bold text-amber-500">{{ $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1, ',', '.') : '-' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Taxa resposta</p>
                    <p class="text-xl font-bold text-blue-600">{{ $responseRate }}%</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Enviados</p>
                    <p class="text-xl font-bold text-green-600">{{ $stats['sent'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Notas baixas</p>
                    <p class="text-xl font-bold text-red-500">{{ $stats['low_ratings'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Graficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_0.8fr] gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-gray-800 mb-4">Convites vs Respostas (ultimos 14 dias)</h2>
            <div class="h-56">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-gray-800 mb-4">Distribuicao das notas</h2>
            <div class="flex items-center gap-6">
                <div class="w-40 h-40 flex-shrink-0">
                    <canvas id="ratingDonut"></canvas>
                </div>
                <div class="flex-1 space-y-2">
                    @for($rating = 5; $rating >= 1; $rating--)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-amber-500">{{ str_repeat('★', $rating) }}</span>
                        <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $stats['answered'] > 0 ? (($distribution[$rating] ?? 0) / $stats['answered']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-8 text-right">{{ $distribution[$rating] ?? 0 }}</span>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Nota media diaria --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-sm font-bold text-gray-800 mb-4">Nota media diaria (ultimos 14 dias)</h2>
        <div class="h-44">
            <canvas id="avgRatingChart"></canvas>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                    <option value="">Todos</option>
                    <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Respondidas</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Aguardando</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Falha</option>
                    <option value="not_sent" {{ request('status') === 'not_sent' ? 'selected' : '' }}>Nao enviados</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nota</label>
                <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                    <option value="">Todas</option>
                    @for($rating = 1; $rating <= 5; $rating++)
                    <option value="{{ $rating }}" {{ (string) request('rating') === (string) $rating ? 'selected' : '' }}>{{ $rating }} ★</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Telefone</label>
                <input type="text" name="phone" value="{{ request('phone') }}" placeholder="63999..." class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Lote de</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Lote ate</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Respondida de</label>
                <input type="date" name="answered_from" value="{{ request('answered_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Respondida ate</label>
                <input type="date" name="answered_to" value="{{ request('answered_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 px-3 rounded-xl font-medium hover:bg-emerald-700 transition-colors text-sm">Filtrar</button>
                <a href="{{ route('admin.reviews.index') }}" class="bg-gray-200 text-gray-600 py-2 px-3 rounded-xl font-medium hover:bg-gray-300 transition-colors text-sm">✕</a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-800">Avaliacoes</h2>
            <span class="text-xs text-gray-500">{{ $reviews->total() }} registro(s)</span>
        </div>

        @if(Auth::user()->role === 'admin')
        <div id="bulkBar" class="hidden px-6 py-3 bg-blue-50 border-b border-blue-200 flex items-center gap-3 flex-wrap">
            <span class="text-sm text-blue-800 font-medium"><span id="bulkCount">0</span> selecionado(s)</span>
            <button type="button" onclick="openBulkEditModal()" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg text-xs font-medium transition-colors">Editar em lote</button>
            <button type="button" onclick="openBulkDeleteModal()" class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition-colors">Excluir em lote</button>
            <button type="button" onclick="clearSelection()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium transition-colors">Limpar</button>
        </div>
        @endif

        @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        @if(Auth::user()->role === 'admin')
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" onchange="toggleSelectAll(this)">
                        </th>
                        @endif
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Passageiro</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Telefone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Viagem</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Envio</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nota</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Motivo</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Respondido em</th>
                        @if(Auth::user()->role === 'admin')
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Acoes</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reviews as $review)
                    <tr class="hover:bg-gray-50 transition-colors align-top">
                        @if(Auth::user()->role === 'admin')
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" class="row-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" value="{{ $review->id }}" onchange="updateBulkBar()">
                        </td>
                        @endif
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $review->user?->name ?: 'Sem nome' }}</div>
                            <div class="text-xs text-gray-400">#{{ $review->user_id ?? '-' }} | Lote {{ $review->batch_date?->format('d/m') }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700 text-xs">{{ $review->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $review->registration_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $sendBadge = match($review->whatsapp_status) {
                                    'sent' => 'bg-green-100 text-green-700',
                                    'failed' => 'bg-red-100 text-red-700',
                                    'skipped' => 'bg-gray-100 text-gray-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };
                                $sendLabel = match($review->whatsapp_status) {
                                    'sent' => 'Enviado',
                                    'failed' => 'Falha',
                                    'skipped' => 'Ignorado',
                                    default => 'Pendente',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sendBadge }}">{{ $sendLabel }}</span>
                            @if($review->whatsapp_error_message)
                            <div class="mt-1 text-xs text-red-500 max-w-[150px] truncate" title="{{ $review->whatsapp_error_message }}">{{ $review->whatsapp_error_message }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($review->rating)
                            <div class="text-amber-500 text-sm leading-none">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('☆', 5 - $review->rating) }}</span></div>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs max-w-[200px] truncate" title="{{ $review->reason }}">
                            {{ $review->reason ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $review->submitted_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        @if(Auth::user()->role === 'admin')
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openEditModal({{ $review->id }}, '{{ $review->submitted_at?->format('Y-m-d\TH:i') }}', {{ $review->rating ?? 'null' }}, '{{ addslashes($review->reason ?? '') }}')" class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded text-xs font-medium transition-colors">Editar</button>
                                <button type="button" onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($review->user?->name ?: 'Sem nome') }}')" class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-xs font-medium transition-colors">Excluir</button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-gray-100">
            {{ $reviews->withQueryString()->links() }}
        </div>
        @else
        <div class="p-12 text-center text-gray-500">
            <span class="block text-4xl mb-3">📭</span>
            <p>Nenhuma avaliacao encontrada.</p>
        </div>
        @endif
    </div>
</div>

@if(Auth::user()->role === 'admin')
{{-- Modal de edicao --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Editar avaliacao</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondido em</label>
                    <input type="datetime-local" name="submitted_at" id="editSubmittedAt" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <select name="rating" id="editRating" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="">Sem nota</option>
                        @for($r = 1; $r <= 5; $r++)
                        <option value="{{ $r }}">{{ $r }} estrela{{ $r > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="reason" id="editReason" rows="3" maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm" placeholder="Motivo da avaliacao..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300 transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">Salvar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de exclusao --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Excluir avaliacao</h3>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-1">Excluir avaliacao de:</p>
        <p class="text-sm font-semibold text-gray-800 mb-4" id="deleteReviewName"></p>
        <p class="text-xs text-red-500 mb-6">Esta acao nao pode ser desfeita.</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300 transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">Excluir</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal edicao em lote --}}
<div id="bulkEditModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Editar em lote</h3>
            <button type="button" onclick="closeBulkEditModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Campos preenchidos serao aplicados nos <span id="bulkEditCount" class="font-semibold text-gray-800">0</span> registros.</p>
        <form id="bulkEditForm" method="POST" action="{{ route('admin.reviews.bulk-update') }}">
            @csrf
            @method('PUT')
            <div id="bulkEditIds"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondido em</label>
                    <input type="datetime-local" name="submitted_at" id="bulkEditSubmittedAt" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <select name="rating" id="bulkEditRating" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">Nao alterar</option>
                        @for($r = 1; $r <= 5; $r++)<option value="{{ $r }}">{{ $r }} ★</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="reason" id="bulkEditReason" rows="2" maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm" placeholder="Vazio = nao alterar"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeBulkEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium">Salvar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal exclusao em lote --}}
<div id="bulkDeleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Excluir em lote</h3>
            <button type="button" onclick="closeBulkDeleteModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-2">Excluir <span id="bulkDeleteCount" class="font-semibold">0</span> avaliacao(oes)?</p>
        <p class="text-xs text-red-500 mb-6">Esta acao nao pode ser desfeita.</p>
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.reviews.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            <div id="bulkDeleteIds"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeBulkDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium">Excluir</button>
            </div>
        </form>
    </div>
</div>

<script>
function getSelectedIds() { return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value); }
function updateBulkBar() {
    const ids = getSelectedIds(), bar = document.getElementById('bulkBar'), all = document.querySelectorAll('.row-checkbox');
    document.getElementById('bulkCount').textContent = ids.length;
    ids.length > 0 ? (bar.classList.remove('hidden'), bar.classList.add('flex')) : (bar.classList.add('hidden'), bar.classList.remove('flex'));
    document.getElementById('selectAll').checked = all.length > 0 && ids.length === all.length;
    document.getElementById('selectAll').indeterminate = ids.length > 0 && ids.length < all.length;
}
function toggleSelectAll(el) { document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = el.checked); updateBulkBar(); }
function clearSelection() { document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false); document.getElementById('selectAll').checked = false; updateBulkBar(); }

function modal(id, show) { const m = document.getElementById(id); show ? (m.classList.remove('hidden'), m.classList.add('flex')) : (m.classList.add('hidden'), m.classList.remove('flex')); }
function openEditModal(id, s, r, reason) { document.getElementById('editForm').action = '{{ url("admin/avaliacoes") }}/' + id + window.location.search; document.getElementById('editSubmittedAt').value = s||''; document.getElementById('editRating').value = r||''; document.getElementById('editReason').value = reason||''; modal('editModal', true); }
function closeEditModal() { modal('editModal', false); }
function openDeleteModal(id, name) { document.getElementById('deleteForm').action = '{{ url("admin/avaliacoes") }}/' + id; document.getElementById('deleteReviewName').textContent = name; modal('deleteModal', true); }
function closeDeleteModal() { modal('deleteModal', false); }

function injectIds(cid, ids) { const c = document.getElementById(cid); c.innerHTML = ''; ids.forEach(id => { const i = document.createElement('input'); i.type='hidden'; i.name='ids[]'; i.value=id; c.appendChild(i); }); }
function openBulkEditModal() { const ids = getSelectedIds(); if(!ids.length) return; injectIds('bulkEditIds', ids); document.getElementById('bulkEditCount').textContent = ids.length; document.getElementById('bulkEditSubmittedAt').value=''; document.getElementById('bulkEditRating').value=''; document.getElementById('bulkEditReason').value=''; modal('bulkEditModal', true); }
function closeBulkEditModal() { modal('bulkEditModal', false); }
function openBulkDeleteModal() { const ids = getSelectedIds(); if(!ids.length) return; injectIds('bulkDeleteIds', ids); document.getElementById('bulkDeleteCount').textContent = ids.length; modal('bulkDeleteModal', true); }
function closeBulkDeleteModal() { modal('bulkDeleteModal', false); }

['editModal','deleteModal','bulkEditModal','bulkDeleteModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) { if(e.target === this) modal(id, false); });
});
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const labels = @json($chartLabels);
const defaultFont = { family: 'Inter, sans-serif', size: 11 };
Chart.defaults.font = defaultFont;

// Grafico diario
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Convites', data: @json($chartInvites), backgroundColor: 'rgba(100,116,139,0.25)', borderColor: 'rgb(100,116,139)', borderWidth: 1, borderRadius: 6 },
            { label: 'Respostas', data: @json($chartAnswered), backgroundColor: 'rgba(16,185,129,0.35)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 6 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
});

// Donut de notas
new Chart(document.getElementById('ratingDonut'), {
    type: 'doughnut',
    data: {
        labels: ['5★','4★','3★','2★','1★'],
        datasets: [{ data: [{{ $distribution[5] ?? 0 }}, {{ $distribution[4] ?? 0 }}, {{ $distribution[3] ?? 0 }}, {{ $distribution[2] ?? 0 }}, {{ $distribution[1] ?? 0 }}], backgroundColor: ['#10b981','#34d399','#fbbf24','#f97316','#ef4444'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: true, cutout: '65%', plugins: { legend: { display: false } } }
});

// Nota media diaria
new Chart(document.getElementById('avgRatingChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Nota media',
            data: @json($chartAvgRating),
            borderColor: 'rgb(245,158,11)',
            backgroundColor: 'rgba(245,158,11,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: 'rgb(245,158,11)'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 5, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
});
</script>
@endsection
