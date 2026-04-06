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

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Convites gerados</p>
            <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_invites'] }}</p>
            <p class="mt-2 text-xs text-gray-400">Lotes diarios com link unico por passageiro</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Respostas recebidas</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['answered'] }}</p>
            <p class="mt-2 text-xs text-gray-400">Pendentes de resposta: {{ $stats['pending_answers'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Nota media</p>
            <p class="mt-2 text-3xl font-bold text-amber-500">{{ $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1, ',', '.') : '-' }}</p>
            <p class="mt-2 text-xs text-gray-400">Notas baixas (1 a 3): {{ $stats['low_ratings'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Envio WhatsApp</p>
            <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['sent'] }}</p>
            <p class="mt-2 text-xs text-gray-400">Falhas de envio: {{ $stats['failed'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="">Todos</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Respondidas</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Aguardando resposta</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Falha no envio</option>
                        <option value="not_sent" {{ request('status') === 'not_sent' ? 'selected' : '' }}>Nao enviados</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="">Todas</option>
                        @for($rating = 1; $rating <= 5; $rating++)
                        <option value="{{ $rating }}" {{ (string) request('rating') === (string) $rating ? 'selected' : '' }}>{{ $rating }} estrela{{ $rating > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" placeholder="63999999999" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-tocantins-green text-white py-2 px-4 rounded-xl font-medium hover:bg-green-700 transition-colors text-sm">Filtrar</button>
                    <a href="{{ route('admin.reviews.index') }}" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-xl font-medium hover:bg-gray-300 transition-colors text-sm">Limpar</a>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lote de</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lote ate</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondida de</label>
                    <input type="date" name="answered_from" value="{{ request('answered_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondida ate</label>
                    <input type="date" name="answered_to" value="{{ request('answered_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800">Distribuicao das notas</h2>
            <div class="mt-4 space-y-3">
                @for($rating = 5; $rating >= 1; $rating--)
                <div>
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                        <span>{{ $rating }} estrela{{ $rating > 1 ? 's' : '' }}</span>
                        <span>{{ $distribution[$rating] ?? 0 }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full bg-amber-400" style="width: {{ $stats['answered'] > 0 ? (($distribution[$rating] ?? 0) / $stats['answered']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endfor
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.reviews.settings') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium transition-colors">
                    Abrir configuracoes e teste manual
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Lista de avaliacoes</h2>
            <span class="text-sm text-gray-500">{{ $reviews->total() }} registro(s)</span>
        </div>

        {{-- Barra de acoes em lote --}}
        <div id="bulkBar" class="hidden px-6 py-3 bg-blue-50 border-b border-blue-200 flex items-center gap-3 flex-wrap">
            <span class="text-sm text-blue-800 font-medium"><span id="bulkCount">0</span> selecionado(s)</span>
            <button type="button" onclick="openBulkEditModal()" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg text-xs font-medium transition-colors">Editar em lote</button>
            @if(Auth::user()->role === 'admin')
            <button type="button" onclick="openBulkDeleteModal()" class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition-colors">Excluir em lote</button>
            @endif
            <button type="button" onclick="clearSelection()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium transition-colors">Limpar selecao</button>
        </div>

        @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Passageiro</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Telefone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Cadastro da viagem</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Envio</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nota</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Motivo</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Respondido em</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reviews as $review)
                    <tr class="hover:bg-gray-50 transition-colors align-top">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" class="row-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" value="{{ $review->id }}" onchange="updateBulkBar()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $review->user?->name ?: 'Passageiro sem nome' }}</div>
                            <div class="text-xs text-gray-500">Usuario #{{ $review->user_id ?? '-' }} | Lote {{ $review->batch_date?->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700">{{ $review->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $review->registration_at?->format('d/m/Y H:i') ?: '-' }}
                        </td>
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
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $sendBadge }}">{{ $sendLabel }}</span>
                            @if($review->whatsapp_error_message)
                            <div class="mt-2 text-xs text-red-500 max-w-xs">{{ \Illuminate\Support\Str::limit($review->whatsapp_error_message, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($review->rating)
                            <div class="text-amber-500 text-base leading-none">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('☆', 5 - $review->rating) }}</span></div>
                            <div class="mt-1 text-xs text-gray-500">{{ $review->rating }}/5</div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-sm">
                            {{ $review->reason ? \Illuminate\Support\Str::limit($review->reason, 120) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $review->submitted_at?->format('d/m/Y H:i') ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openEditModal({{ $review->id }}, '{{ $review->submitted_at?->format('Y-m-d\TH:i') }}', {{ $review->rating ?? 'null' }}, '{{ addslashes($review->reason ?? '') }}')" class="inline-flex items-center justify-center px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg text-xs font-medium transition-colors">
                                    Editar
                                </button>
                                @if(Auth::user()->role === 'admin')
                                <button type="button" onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($review->user?->name ?: 'Passageiro sem nome') }}')" class="inline-flex items-center justify-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition-colors">
                                    Excluir
                                </button>
                                @endif
                            </div>
                        </td>
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
            <p>Nenhuma avaliacao encontrada para os filtros informados.</p>
        </div>
        @endif
    </div>
</div>

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

{{-- Modal de confirmacao de exclusao --}}
@if(Auth::user()->role === 'admin')
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Excluir avaliacao</h3>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-1">Tem certeza que deseja excluir a avaliacao de:</p>
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
@endif

{{-- Modal de edicao em lote --}}
<div id="bulkEditModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Editar em lote</h3>
            <button type="button" onclick="closeBulkEditModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Apenas os campos preenchidos serao alterados nos <span id="bulkEditCount" class="font-semibold text-gray-800">0</span> registros selecionados.</p>
        <form id="bulkEditForm" method="POST" action="{{ route('admin.reviews.bulk-update') }}">
            @csrf
            @method('PUT')
            <div id="bulkEditIds"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondido em</label>
                    <input type="datetime-local" name="submitted_at" id="bulkEditSubmittedAt" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <select name="rating" id="bulkEditRating" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                        <option value="">Nao alterar</option>
                        @for($r = 1; $r <= 5; $r++)
                        <option value="{{ $r }}">{{ $r }} estrela{{ $r > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="reason" id="bulkEditReason" rows="3" maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm" placeholder="Deixe vazio para nao alterar..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeBulkEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300 transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">Salvar em lote</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de exclusao em lote --}}
@if(Auth::user()->role === 'admin')
<div id="bulkDeleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Excluir em lote</h3>
            <button type="button" onclick="closeBulkDeleteModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-2">Tem certeza que deseja excluir <span id="bulkDeleteCount" class="font-semibold">0</span> avaliacao(oes)?</p>
        <p class="text-xs text-red-500 mb-6">Esta acao nao pode ser desfeita.</p>
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.reviews.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            <div id="bulkDeleteIds"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeBulkDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300 transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">Excluir tudo</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
// Selecao
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
}

function updateBulkBar() {
    const ids = getSelectedIds();
    const bar = document.getElementById('bulkBar');
    const count = document.getElementById('bulkCount');
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.row-checkbox');

    count.textContent = ids.length;

    if (ids.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }

    selectAll.checked = allCheckboxes.length > 0 && ids.length === allCheckboxes.length;
    selectAll.indeterminate = ids.length > 0 && ids.length < allCheckboxes.length;
}

function toggleSelectAll(el) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = el.checked);
    updateBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkBar();
}

// Edicao individual
function openEditModal(id, submittedAt, rating, reason) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    const queryString = window.location.search;
    form.action = '{{ url("admin/avaliacoes") }}/' + id + queryString;
    document.getElementById('editSubmittedAt').value = submittedAt || '';
    document.getElementById('editRating').value = rating || '';
    document.getElementById('editReason').value = reason || '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Exclusao individual
function openDeleteModal(id, name) {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;
    document.getElementById('deleteForm').action = '{{ url("admin/avaliacoes") }}/' + id;
    document.getElementById('deleteReviewName').textContent = name;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
if (document.getElementById('deleteModal')) {
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
}

// Edicao em lote
function injectIds(containerId, ids) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

function openBulkEditModal() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    injectIds('bulkEditIds', ids);
    document.getElementById('bulkEditCount').textContent = ids.length;
    document.getElementById('bulkEditSubmittedAt').value = '';
    document.getElementById('bulkEditRating').value = '';
    document.getElementById('bulkEditReason').value = '';
    const modal = document.getElementById('bulkEditModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeBulkEditModal() {
    const modal = document.getElementById('bulkEditModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('bulkEditModal').addEventListener('click', function(e) {
    if (e.target === this) closeBulkEditModal();
});

// Exclusao em lote
function openBulkDeleteModal() {
    const modal = document.getElementById('bulkDeleteModal');
    if (!modal) return;
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    injectIds('bulkDeleteIds', ids);
    document.getElementById('bulkDeleteCount').textContent = ids.length;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeBulkDeleteModal() {
    const modal = document.getElementById('bulkDeleteModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
if (document.getElementById('bulkDeleteModal')) {
    document.getElementById('bulkDeleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeBulkDeleteModal();
    });
}
</script>
@endsection