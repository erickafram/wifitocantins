@extends('layouts.admin')

@section('title', 'Novo Voucher')

@section('breadcrumb')
    <span class="text-muted">›</span>
    <a href="{{ route('admin.vouchers.index') }}" class="text-muted hover:text-green transition-colors">Vouchers</a>
    <span class="text-muted">›</span>
    <span class="text-green font-semibold">Novo</span>
@endsection

@section('page-title', 'Novo Voucher')

@section('content')
<div class="max-w-8xl mx-auto">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-green-dark via-green to-green-light rounded-xl px-5 py-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/60 mb-0.5">Motoristas · Acesso WiFi</p>
            <h1 class="text-xl font-bold text-white">Novo Voucher</h1>
            <p class="text-xs text-white/70 mt-0.5">Cadastre um voucher para liberar acesso de motoristas</p>
        </div>
        <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/15 border border-white/20 text-white font-semibold text-xs rounded-lg hover:bg-white/25 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-red/20 bg-red-pale px-4 py-3">
            <svg class="w-4 h-4 text-red flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <ul class="text-xs text-red font-medium space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Formulário -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-border shadow-card overflow-hidden">
                <div class="border-b border-border px-5 py-3">
                    <h2 class="text-sm font-bold text-ink">Dados do Voucher</h2>
                </div>

                <form action="{{ route('admin.vouchers.store') }}" method="POST" class="p-5 space-y-6">
                    @csrf

                    <!-- Seção 1: Motorista -->
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 bg-green text-white rounded-full flex items-center justify-center text-[10px] font-bold">1</span>
                            <h3 class="text-[11px] font-bold text-muted uppercase tracking-wider">Dados do Motorista</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="driver_name" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Nome do Motorista <span class="text-red normal-case">*</span></label>
                                <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name') }}"
                                       class="w-full px-3 py-2.5 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all"
                                       placeholder="Nome completo do motorista" required>
                            </div>
                            <div>
                                <label for="driver_phone" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Telefone <span class="text-red normal-case">*</span></label>
                                <input type="tel" name="driver_phone" id="driver_phone" value="{{ old('driver_phone') }}"
                                       class="w-full px-3 py-2.5 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all"
                                       placeholder="(00) 00000-0000" required>
                            </div>
                            <div>
                                <label for="driver_document" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">CPF/CNH <span class="text-muted normal-case font-normal">(opcional)</span></label>
                                <input type="text" name="driver_document" id="driver_document" value="{{ old('driver_document') }}"
                                       class="w-full px-3 py-2.5 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all"
                                       placeholder="000.000.000-00">
                            </div>
                        </div>
                    </div>

                    <!-- Seção 2: Configuração -->
                    <div class="border-t border-border pt-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 bg-green text-white rounded-full flex items-center justify-center text-[10px] font-bold">2</span>
                            <h3 class="text-[11px] font-bold text-muted uppercase tracking-wider">Configuração do Voucher</h3>
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-2">Tipo de Voucher <span class="text-red normal-case">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label id="label_limited" class="relative flex items-center p-3.5 border-2 rounded-xl cursor-pointer transition-all hover:border-green {{ old('voucher_type', 'limited') === 'limited' ? 'border-green bg-green-pale' : 'border-border' }}">
                                    <input type="radio" name="voucher_type" value="limited" {{ old('voucher_type', 'limited') === 'limited' ? 'checked' : '' }} class="sr-only" onchange="toggleDailyHours()">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-9 h-9 bg-green-pale rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-ink text-sm">Limitado</p>
                                            <p class="text-[10px] text-muted">Horas diárias</p>
                                        </div>
                                    </div>
                                </label>
                                <label id="label_unlimited" class="relative flex items-center p-3.5 border-2 rounded-xl cursor-pointer transition-all hover:border-blue {{ old('voucher_type') === 'unlimited' ? 'border-blue bg-blue-pale' : 'border-border' }}">
                                    <input type="radio" name="voucher_type" value="unlimited" {{ old('voucher_type') === 'unlimited' ? 'checked' : '' }} class="sr-only" onchange="toggleDailyHours()">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-9 h-9 bg-blue-pale rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2m4 0h2M5 12a4 4 0 004 4h6a4 4 0 100-8H9a4 4 0 00-4 4z"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-ink text-sm">Ilimitado</p>
                                            <p class="text-[10px] text-muted">Sem limite</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div id="daily_hours_container" class="transition-all duration-200">
                                <label for="daily_hours" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Tempo Diário <span class="text-red normal-case" id="daily_hours_required">*</span></label>
                                <div class="relative">
                                    <input type="number" name="daily_hours" id="daily_hours" value="{{ old('daily_hours', 2) }}" min="0.01" max="24" step="0.01"
                                           class="w-full px-3 py-2.5 pr-14 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted">horas</span>
                                </div>
                                <p class="mt-1 text-[10px] text-muted">Ex: 2.5 = 2h30min</p>
                            </div>
                            <div>
                                <label for="activation_interval_hours" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Intervalo entre Ativações <span class="text-red normal-case">*</span></label>
                                <div class="relative">
                                    <input type="number" name="activation_interval_hours" id="activation_interval_hours" value="{{ old('activation_interval_hours', 24) }}" min="0.01" max="168" step="0.01"
                                           class="w-full px-3 py-2.5 pr-14 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted">horas</span>
                                </div>
                                <p class="mt-1 text-[10px] text-muted">Ex: 24 = 1x por dia</p>
                            </div>
                        </div>
                    </div>

                    <!-- Seção 3: Opções -->
                    <div class="border-t border-border pt-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 bg-muted text-white rounded-full flex items-center justify-center text-[10px] font-bold">3</span>
                            <h3 class="text-[11px] font-bold text-muted uppercase tracking-wider">Opções Adicionais</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="expires_at" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Data de Expiração <span class="text-muted normal-case font-normal">(opcional)</span></label>
                                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="w-full px-3 py-2.5 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all">
                                <p class="mt-1 text-[10px] text-muted">Deixe vazio para sem expiração</p>
                            </div>
                            <div>
                                <label for="description" class="block text-[11px] font-semibold text-ink2 uppercase tracking-wider mb-1.5">Observações <span class="text-muted normal-case font-normal">(opcional)</span></label>
                                <input type="text" name="description" id="description" value="{{ old('description') }}"
                                       class="w-full px-3 py-2.5 text-sm text-ink bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-green/30 focus:border-green transition-all"
                                       placeholder="Ex: Rota Palmas-Araguaína">
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-border">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-green hover:bg-green-light text-white font-semibold text-sm rounded-lg transition-colors shadow-card">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Criar Voucher
                        </button>
                        <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-surface border border-border text-ink2 font-semibold text-sm rounded-lg hover:bg-border transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar de dicas -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-border shadow-card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-blue-pale rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-ink">Dicas Rápidas</h3>
                </div>
                <ul class="text-xs text-muted space-y-1.5">
                    <li class="flex items-start gap-1.5"><span class="text-green mt-0.5">•</span> O código é gerado automaticamente.</li>
                    <li class="flex items-start gap-1.5"><span class="text-green mt-0.5">•</span> Telefone é usado para vincular o voucher.</li>
                    <li class="flex items-start gap-1.5"><span class="text-green mt-0.5">•</span> Voucher pode ser desativado a qualquer momento.</li>
                </ul>
            </div>

            <div class="bg-white rounded-xl border border-green/30 shadow-card p-5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-green-pale rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-ink">Voucher Limitado</h3>
                </div>
                <p class="text-xs text-muted mb-2">O motorista tem limite de horas por dia. O contador reseta à meia-noite.</p>
                <div class="bg-green-pale rounded-lg p-2.5">
                    <p class="text-[11px] text-green"><strong>Exemplo:</strong> 2h/dia = motorista pode usar até 2 horas por dia.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-blue/30 shadow-card p-5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-blue-pale rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2m4 0h2M5 12a4 4 0 004 4h6a4 4 0 100-8H9a4 4 0 00-4 4z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-ink">Voucher Ilimitado</h3>
                </div>
                <p class="text-xs text-muted mb-2">Sem limite de horas. O motorista pode usar a internet sem restrições de tempo.</p>
                <div class="bg-blue-pale rounded-lg p-2.5">
                    <p class="text-[11px] text-blue"><strong>Ideal para:</strong> Motoristas VIP ou parceiros especiais.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDailyHours() {
    const voucherType = document.querySelector('input[name="voucher_type"]:checked')?.value || 'limited';
    const container = document.getElementById('daily_hours_container');
    const input = document.getElementById('daily_hours');
    const requiredMark = document.getElementById('daily_hours_required');
    const labelLimited = document.getElementById('label_limited');
    const labelUnlimited = document.getElementById('label_unlimited');

    if (voucherType === 'unlimited') {
        container.classList.add('opacity-40', 'pointer-events-none');
        input.removeAttribute('required');
        input.value = '';
        requiredMark.classList.add('hidden');
        labelLimited.className = labelLimited.className.replace('border-green bg-green-pale', 'border-border');
        labelUnlimited.classList.add('border-blue', 'bg-blue-pale');
        labelUnlimited.classList.remove('border-border');
    } else {
        container.classList.remove('opacity-40', 'pointer-events-none');
        input.setAttribute('required', 'required');
        if (!input.value) input.value = '2';
        requiredMark.classList.remove('hidden');
        labelUnlimited.classList.remove('border-blue', 'bg-blue-pale');
        labelUnlimited.classList.add('border-border');
        labelLimited.classList.add('border-green', 'bg-green-pale');
        labelLimited.classList.remove('border-border');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleDailyHours();
    const phoneInput = document.getElementById('driver_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 10) value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            else if (value.length > 6) value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            else if (value.length > 2) value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            e.target.value = value;
        });
    }
});
</script>
@endsection
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Novo Voucher</h1>
            <p class="text-sm text-gray-500 mt-1">Cadastre um voucher para liberar acesso de motoristas</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Voltar
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-center gap-2 text-red-700 text-sm font-medium mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5.5l6.5 11.25H5.5L12 5.5z"/></svg>
                Verifique os campos abaixo
            </div>
            <ul class="text-sm text-red-700 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-gray-800 font-semibold">Dados do Voucher</h2>
                </div>

                <form action="{{ route('admin.vouchers.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-6 h-6 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-semibold">1</span>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Dados do Motorista</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="driver_name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Motorista <span class="text-red-500">*</span></label>
                                <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nome completo do motorista" required>
                            </div>
                            <div>
                                <label for="driver_phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                                <input type="tel" name="driver_phone" id="driver_phone" value="{{ old('driver_phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="(00) 00000-0000" required>
                            </div>
                            <div>
                                <label for="driver_document" class="block text-sm font-medium text-gray-700 mb-1">CPF/CNH <span class="text-gray-400 text-xs">(opcional)</span></label>
                                <input type="text" name="driver_document" id="driver_document" value="{{ old('driver_document') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="000.000.000-00">
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-6 h-6 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs font-semibold">2</span>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Configuracao do Voucher</h3>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Voucher <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label id="label_limited" class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ old('voucher_type', 'limited') === 'limited' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200' }}">
                                    <input type="radio" name="voucher_type" value="limited" {{ old('voucher_type', 'limited') === 'limited' ? 'checked' : '' }} class="sr-only" onchange="toggleDailyHours()">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Limitado</p>
                                            <p class="text-xs text-gray-500">Horas diarias</p>
                                        </div>
                                    </div>
                                </label>

                                <label id="label_unlimited" class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-500 {{ old('voucher_type') === 'unlimited' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200' }}">
                                    <input type="radio" name="voucher_type" value="unlimited" {{ old('voucher_type') === 'unlimited' ? 'checked' : '' }} class="sr-only" onchange="toggleDailyHours()">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2m4 0h2M5 12a4 4 0 004 4h6a4 4 0 100-8H9a4 4 0 00-4 4z"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Ilimitado</p>
                                            <p class="text-xs text-gray-500">Sem limite</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div id="daily_hours_container" class="transition-all duration-300">
                                <label for="daily_hours" class="block text-sm font-medium text-gray-700 mb-1">Tempo Diario <span class="text-red-500" id="daily_hours_required">*</span></label>
                                <div class="relative">
                                    <input type="number" name="daily_hours" id="daily_hours" value="{{ old('daily_hours', 2) }}" min="0.01" max="24" step="0.01" class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">horas</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Ex: 2.5 = 2h30min</p>
                            </div>

                            <div>
                                <label for="activation_interval_hours" class="block text-sm font-medium text-gray-700 mb-1">Intervalo entre Ativacoes <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" name="activation_interval_hours" id="activation_interval_hours" value="{{ old('activation_interval_hours', 24) }}" min="0.01" max="168" step="0.01" class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">horas</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Ex: 24 = 1x por dia</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-semibold">3</span>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Opcoes Adicionais</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Data de Expiracao <span class="text-gray-400 text-xs">(opcional)</span></label>
                                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Deixe vazio para sem expiracao</p>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Observacoes <span class="text-gray-400 text-xs">(opcional)</span></label>
                                <input type="text" name="description" id="description" value="{{ old('description') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Rota Palmas-Araguaina">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Criar Voucher
                        </button>
                        <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white border rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800">Dicas Rapidas</h3>
                </div>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>O codigo e gerado automaticamente.</li>
                    <li>Telefone e usado para vincular o voucher.</li>
                    <li>Voucher pode ser desativado a qualquer momento.</li>
                </ul>
            </div>

            <div class="bg-white border rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800">Voucher Limitado</h3>
                </div>
                <p class="text-sm text-gray-600 mb-3">O motorista tem limite de horas por dia. O contador reseta a meia-noite.</p>
                <div class="bg-emerald-50 rounded-lg p-3">
                    <p class="text-xs text-emerald-700"><strong>Exemplo:</strong> 2h/dia = motorista pode usar ate 2 horas por dia.</p>
                </div>
            </div>

            <div class="bg-white border rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2m4 0h2M5 12a4 4 0 004 4h6a4 4 0 100-8H9a4 4 0 00-4 4z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800">Voucher Ilimitado</h3>
                </div>
                <p class="text-sm text-gray-600 mb-3">Sem limite de horas. O motorista pode usar a internet sem restricoes de tempo.</p>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-700"><strong>Ideal para:</strong> Motoristas VIP ou parceiros especiais.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDailyHours() {
    const voucherType = document.querySelector('input[name="voucher_type"]:checked')?.value || 'limited';
    const container = document.getElementById('daily_hours_container');
    const input = document.getElementById('daily_hours');
    const requiredMark = document.getElementById('daily_hours_required');
    const labelLimited = document.getElementById('label_limited');
    const labelUnlimited = document.getElementById('label_unlimited');

    if (voucherType === 'unlimited') {
        container.classList.add('opacity-50', 'pointer-events-none');
        input.removeAttribute('required');
        input.value = '';
        requiredMark.classList.add('hidden');

        labelLimited.classList.remove('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-200');
        labelLimited.classList.add('border-gray-200');
        labelUnlimited.classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
        labelUnlimited.classList.remove('border-gray-200');
    } else {
        container.classList.remove('opacity-50', 'pointer-events-none');
        input.setAttribute('required', 'required');
        if (!input.value) input.value = '2';
        requiredMark.classList.remove('hidden');

        labelUnlimited.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
        labelUnlimited.classList.add('border-gray-200');
        labelLimited.classList.add('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-200');
        labelLimited.classList.remove('border-gray-200');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleDailyHours();

    const phoneInput = document.getElementById('driver_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }

            e.target.value = value;
        });
    }
});
</script>
@endsection
