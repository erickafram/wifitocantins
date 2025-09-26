@extends('layouts.admin')

@section('title', 'Integrações de API')

@section('breadcrumb')
    <span>›</span>
    <a href="{{ route('admin.api') }}" class="text-tocantins-green font-medium">Integrações</a>
@endsection

@section('page-title', 'Integrações de Pagamento')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h2 class="text-lg font-semibold text-tocantins-gray-green mb-4">Gateway PIX</h2>

            @if(session('success'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.api.update-gateway') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Selecione o Gateway PIX</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($gateways as $value => $label)
                        <label class="relative">
                            <input type="radio" name="pix_gateway" value="{{ $value }}" class="sr-only" {{ $currentGateway === $value ? 'checked' : '' }}>
                            <div class="p-4 border rounded-xl transition-all duration-200 {{ $currentGateway === $value ? 'border-tocantins-green bg-tocantins-green/5 shadow-md ring-2 ring-tocantins-green/40' : 'border-gray-200 hover:border-tocantins-green hover:bg-tocantins-green/5' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-semibold text-tocantins-gray-green">{{ $label }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            @if($value === 'santander')
                                                Integração direta com Santander, recomendada para produção.
                                            @else
                                                Integração com Woovi OpenPix, ideal para fallback.
                                            @endif
                                        </p>
                                    </div>
                                    @if($currentGateway === $value)
                                        <span class="inline-flex items-center rounded-full bg-tocantins-green/10 px-2 py-1 text-xs font-medium text-tocantins-green">Ativo</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white px-6 py-2 rounded-lg text-sm font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200/50 p-6">
                <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">Status das Integrações</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-700">Santander PIX</p>
                            <p class="text-xs text-gray-500">CLIENT ID: {{ config('wifi.payment_gateways.pix.client_id') ? '••••' . substr(config('wifi.payment_gateways.pix.client_id'), -4) : 'Não configurado' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs {{ $currentGateway === 'santander' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $currentGateway === 'santander' ? 'Ativo' : 'Disponível' }}</span>
                    </div>

                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-700">Woovi OpenPix</p>
                            <p class="text-xs text-gray-500">APP ID: {{ config('wifi.payment_gateways.pix.woovi_app_id') ? '••••' . substr(config('wifi.payment_gateways.pix.woovi_app_id'), -4) : 'Não configurado' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs {{ $currentGateway === 'woovi' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $currentGateway === 'woovi' ? 'Ativo' : 'Disponível' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-200/50 p-6">
                <h3 class="text-sm font-semibold text-tocantins-gray-green mb-3">Credenciais da Empresa</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><span class="font-medium">CNPJ:</span> 00.018.127/0001-38</p>
                    <p><span class="font-medium">Agência:</span> 3932</p>
                    <p><span class="font-medium">Conta:</span> 13000872-2</p>
                </div>
            </div>
        </div>
    </div>
@endsection
