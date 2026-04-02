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

        @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Passageiro</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Telefone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Cadastro da viagem</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Envio</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nota</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Motivo</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Respondido em</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Link</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reviews as $review)
                    <tr class="hover:bg-gray-50 transition-colors align-top">
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
                            <a href="{{ route('reviews.show', $review->token) }}" target="_blank" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-medium transition-colors">
                                Abrir
                            </a>
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
@endsection