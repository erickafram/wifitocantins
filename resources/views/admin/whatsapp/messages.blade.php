@extends('layouts.admin')

@section('title', 'Mensagens WhatsApp')

@section('breadcrumb')
    <span class="mx-2">/</span>
    <a href="{{ route('admin.whatsapp.index') }}" class="hover:text-tocantins-green transition-colors">WhatsApp</a>
    <span class="mx-2">/</span>
    <span class="text-tocantins-green font-medium">Mensagens</span>
@endsection

@section('page-title', 'Histórico de Mensagens')

@section('content')
<div class="space-y-6">
    
    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregue</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lida</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falha</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="phone" value="{{ request('phone') }}" placeholder="Ex: 63999999999" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-tocantins-green text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors text-sm">
                    Filtrar
                </button>
                <a href="{{ route('admin.whatsapp.messages') }}" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 transition-colors text-sm">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Mensagens -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">💬</span>
                    Mensagens ({{ $messages->total() }})
                </h2>
            </div>

            @if($messages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Telefone</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Usuário</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Mensagem</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Enviada em</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($messages as $message)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-gray-500">#{{ $message->id }}</td>
                            <td class="px-4 py-3 font-mono text-gray-800">{{ $message->phone }}</td>
                            <td class="px-4 py-3">
                                @if($message->user)
                                    <div class="font-medium text-gray-800">{{ $message->user->name ?? 'Sem nome' }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $message->user_id }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-xs truncate text-gray-600" title="{{ $message->message }}">
                                    {{ Str::limit($message->message, 60) }}
                                </div>
                                @if($message->error_message)
                                    <div class="text-xs text-red-500 mt-1">{{ Str::limit($message->error_message, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($message->status == 'sent') bg-green-100 text-green-700
                                    @elseif($message->status == 'delivered') bg-blue-100 text-blue-700
                                    @elseif($message->status == 'read') bg-purple-100 text-purple-700
                                    @elseif($message->status == 'failed') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    @switch($message->status)
                                        @case('sent') Enviada @break
                                        @case('delivered') Entregue @break
                                        @case('read') Lida @break
                                        @case('failed') Falha @break
                                        @default Pendente
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($message->sent_at)
                                    {{ $message->sent_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                                <div class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($message->status == 'failed')
                                    <button onclick="resendMessage({{ $message->id }})" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                        Reenviar
                                    </button>
                                @endif
                                <button onclick="viewMessage({{ $message->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                    Ver
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-4">
                {{ $messages->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-12 text-gray-500">
                <span class="text-4xl mb-2 block">📭</span>
                <p>Nenhuma mensagem encontrada</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div id="view-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Detalhes da Mensagem</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modal-content" class="space-y-4">
            <!-- Conteúdo dinâmico -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Reenviar mensagem
async function resendMessage(id) {
    if (!confirm('Reenviar esta mensagem?')) return;

    try {
        const response = await fetch(`/admin/whatsapp/resend/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Mensagem reenviada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao reenviar mensagem');
    }
}

// Ver mensagem
function viewMessage(id) {
    // Buscar dados da mensagem na tabela
    const row = document.querySelector(`tr:has(button[onclick="viewMessage(${id})"])`);
    if (!row) return;

    const phone = row.cells[1].textContent.trim();
    const user = row.cells[2].innerHTML;
    const message = row.cells[3].querySelector('div').getAttribute('title') || row.cells[3].textContent.trim();
    const status = row.cells[4].textContent.trim();
    const sentAt = row.cells[5].textContent.trim();

    document.getElementById('modal-content').innerHTML = `
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 mb-1">Telefone</p>
            <p class="font-mono font-medium">${phone}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 mb-1">Usuário</p>
            <div>${user}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 mb-1">Mensagem</p>
            <p class="whitespace-pre-wrap text-sm">${message}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Status</p>
                <p class="font-medium">${status}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Enviada em</p>
                <p class="font-medium">${sentAt}</p>
            </div>
        </div>
    `;

    document.getElementById('view-modal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('view-modal').classList.add('hidden');
}
</script>
@endpush
