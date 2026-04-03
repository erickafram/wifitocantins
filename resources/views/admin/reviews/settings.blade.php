@extends('layouts.admin')

@section('title', 'Configuracoes de Avaliacao')

@section('breadcrumb')
    <span class="mx-2">/</span>
    <a href="{{ route('admin.reviews.index') }}" class="hover:text-tocantins-green transition-colors">Avaliacoes</a>
    <span class="mx-2">/</span>
    <span class="text-tocantins-green font-medium">Configuracoes</span>
@endsection

@section('page-title', 'Configuracoes da Avaliacao')

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

    @if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-2xl">
        {{ session('error') }}
    </div>
    @endif

    @if(session('manual_review_link'))
    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl">
        <p class="font-medium">Link gerado para teste manual:</p>
        <a href="{{ session('manual_review_link') }}" target="_blank" class="underline break-all">{{ session('manual_review_link') }}</a>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Configuracoes do envio</h2>
                <p class="mt-1 text-sm text-gray-500">O scheduler dispara automaticamente todo dia as 06:30 e considera a janela de passageiros cadastrados entre 18:30 do dia anterior e 06:00 do dia atual.</p>
            </div>

            <form method="POST" action="{{ route('admin.reviews.settings.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                    <div>
                        <p class="font-semibold text-gray-800">Enviar por WhatsApp</p>
                        <p class="text-sm text-gray-500">Envia link de avaliação via WhatsApp para os passageiros.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="review_auto_send_enabled" value="1" class="sr-only peer" {{ $settings['review_auto_send_enabled'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                    <div>
                        <p class="font-semibold text-gray-800">Enviar por E-mail</p>
                        <p class="text-sm text-gray-500">Envia link de avaliação por e-mail para passageiros que informaram e-mail.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="review_email_enabled" value="1" class="sr-only peer" {{ $settings['review_email_enabled'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem da pesquisa</label>
                    <p class="text-xs text-gray-500 mb-2">Use essa mensagem para pedir a opiniao do passageiro sobre atendimento, servico e experiencia durante a viagem. Variaveis: <span class="font-mono">{nome}</span>, <span class="font-mono">{telefone}</span>, <span class="font-mono">{link}</span>, <span class="font-mono">{data_viagem}</span></p>
                    <textarea name="review_message_template" rows="8" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent font-mono text-sm" required>{{ old('review_message_template', $settings['review_message_template']) }}</textarea>
                    @error('review_message_template')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-tocantins-green to-green-600 text-white px-5 py-3 rounded-xl font-medium hover:from-green-600 hover:to-green-700 transition-all shadow-lg">
                        Salvar configuracoes
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800">Status do WhatsApp</h2>
                <div class="mt-4 flex items-center justify-between rounded-2xl p-4 {{ $settings['is_connected'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div>
                        <p class="font-semibold {{ $settings['is_connected'] ? 'text-green-800' : 'text-red-800' }}">{{ $settings['is_connected'] ? 'Conectado' : 'Desconectado' }}</p>
                        <p class="text-sm {{ $settings['is_connected'] ? 'text-green-600' : 'text-red-600' }}">{{ $settings['connected_phone'] ?: 'Nenhum numero conectado' }}</p>
                    </div>
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.whatsapp.index') }}" class="text-sm font-medium {{ $settings['is_connected'] ? 'text-green-700' : 'text-red-700' }} underline">Abrir modulo WhatsApp</a>
                    @else
                    <span class="text-xs font-medium {{ $settings['is_connected'] ? 'text-green-700' : 'text-red-700' }}">Acompanhamento disponivel</span>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800">Janela do lote atual</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                        <span>Lote</span>
                        <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($currentWindow['batch_date'])->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Inicio</span>
                        <span class="font-semibold text-gray-900">{{ $currentWindow['start']->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Fim</span>
                        <span class="font-semibold text-gray-900">{{ $currentWindow['end']->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Disparo</span>
                        <span class="font-semibold text-gray-900">{{ $currentWindow['dispatch_at']->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">Disparo manual para teste</h2>
            <p class="mt-1 text-sm text-gray-500">Use este formulario para enviar a pesquisa de opiniao para um numero especifico e testar o fluxo imediatamente.</p>
        </div>

        <form method="POST" action="{{ route('admin.reviews.send-test') }}" class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone do teste</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="63999999999" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm" required>
                @error('phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome exibido</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Opcional para teste sem cadastro" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do lote</label>
                <input type="date" name="batch_date" value="{{ old('batch_date', $currentWindow['batch_date']) }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                @error('batch_date')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail (opcional)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@exemplo.com" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-tocantins-green focus:border-transparent text-sm">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-5 py-2.5 rounded-xl font-medium hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg">
                    Enviar teste agora
                </button>
            </div>
        </form>

        <div class="px-6 pb-6">
            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
                Se o telefone informado ja existir em um cadastro, o convite de avaliacao sera vinculado automaticamente ao usuario encontrado. Caso contrario, o teste sera enviado mesmo assim, apenas como link manual.
            </div>
        </div>
    </div>
</div>
@endsection