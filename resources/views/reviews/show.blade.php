<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Opiniao - Tocantins Transporte</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(11,116,85,0.18),_transparent_35%),linear-gradient(180deg,#f6f8ef_0%,#fffdf8_55%,#ffffff_100%)] text-slate-800">
    <div class="max-w-md mx-auto px-4 py-4 sm:py-6">
        <div class="mb-4 flex items-center justify-between">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 backdrop-blur px-3 py-1.5 shadow-sm border border-white text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-800">
                <span>Pesquisa</span>
                <span class="w-1 h-1 rounded-full bg-emerald-700"></span>
                <span>Viagem</span>
            </div>
            <div class="rounded-full bg-amber-100 text-amber-800 px-3 py-1 text-xs font-semibold">
                Leva 20s
            </div>
        </div>

        <div class="bg-white/92 backdrop-blur rounded-[28px] shadow-[0_18px_50px_rgba(15,23,42,0.08)] border border-white overflow-hidden">
            <div class="px-5 pt-5 pb-4 bg-[linear-gradient(135deg,#0f766e_0%,#166534_55%,#14532d_100%)] text-white">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-white/70">Sua opiniao importa</p>
                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight leading-tight">Como foi o atendimento e o servico da sua viagem?</h1>
                    </div>
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-white/12 flex items-center justify-center text-2xl">🚌</div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-white/12 text-white/90">Atendimento</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/12 text-white/90">Servico</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/12 text-white/90">Experiencia</span>
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="mb-4 rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Passageiro</p>
                            <p class="mt-1 text-sm font-bold text-slate-800">{{ $review->user?->name ?: 'Passageiro da viagem' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Viagem</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $review->registration_at?->format('d/m H:i') ?: 'Nao informado' }}</p>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('info'))
                <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    {{ session('info') }}
                </div>
                @endif

                @if($review->submitted_at)
                <div class="text-center py-4">
                    <div class="text-4xl leading-none text-amber-400">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300">{{ str_repeat('☆', 5 - $review->rating) }}</span></div>
                    <p class="mt-3 text-lg font-extrabold text-slate-800">Resposta recebida</p>
                    <p class="mt-1 text-sm text-slate-600">Sua nota foi {{ $review->rating }}/5 em {{ $review->submitted_at->format('d/m/Y H:i') }}.</p>
                    @if($review->reason)
                    <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-left">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">O que foi informado</p>
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-wrap">{{ $review->reason }}</p>
                    </div>
                    @endif
                </div>
                @else
                <form method="POST" action="{{ route('reviews.store', $review->token) }}" class="space-y-4" id="review-form">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <label class="block text-sm font-semibold text-slate-700">Sua nota geral</label>
                            <p id="rating-hint" class="text-xs font-semibold text-slate-500">Toque nas estrelas</p>
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            @for($rating = 1; $rating <= 5; $rating++)
                            <div class="star-option">
                                <input
                                    type="radio"
                                    name="rating"
                                    id="rating-{{ $rating }}"
                                    value="{{ $rating }}"
                                    class="sr-only"
                                    {{ (int) old('rating') === $rating ? 'checked' : '' }}
                                >
                                <label for="rating-{{ $rating }}" data-rating="{{ $rating }}" class="rating-card flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white px-1 py-3 text-center transition-all duration-150 active:scale-[0.98]">
                                    <span class="rating-star text-2xl text-slate-300">★</span>
                                    <span class="mt-1 text-sm font-extrabold text-slate-700">{{ $rating }}</span>
                                </label>
                            </div>
                            @endfor
                        </div>

                        <div class="mt-3 grid grid-cols-5 gap-2 text-[10px] text-center font-medium text-slate-500">
                            <span>Muito ruim</span>
                            <span>Ruim</span>
                            <span>Regular</span>
                            <span>Bom</span>
                            <span>Excelente</span>
                        </div>

                        @error('rating')
                            <p class="mt-3 text-sm text-red-600 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="reason-box" class="hidden rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <label for="reason" class="block text-sm font-semibold text-slate-700 mb-1">O que podemos melhorar?</label>
                        <p class="text-xs text-slate-600 mb-3">Para notas de 1 a 3 estrelas, esse campo e obrigatorio.</p>
                        <textarea id="reason" name="reason" rows="4" class="w-full px-4 py-3 border border-amber-200 rounded-2xl focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm resize-none" placeholder="Ex.: atendimento demorado, organizacao, conforto, informacoes da viagem...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
                        Resposta rapida, vinculada a esta viagem e enviada uma unica vez.
                    </div>

                    <button type="submit" class="w-full py-3 rounded-2xl bg-[linear-gradient(135deg,#0f766e_0%,#166534_100%)] text-white font-bold text-[15px] shadow-lg hover:opacity-95 transition-all">
                        Enviar opiniao
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        const reasonBox = document.getElementById('reason-box');
        const reasonInput = document.getElementById('reason');
        const ratingCards = document.querySelectorAll('.rating-card');
        const ratingHint = document.getElementById('rating-hint');
        const ratingMap = {
            1: '1 estrela • Muito ruim',
            2: '2 estrelas • Ruim',
            3: '3 estrelas • Regular',
            4: '4 estrelas • Bom',
            5: '5 estrelas • Excelente',
        };

        function updateReasonVisibility() {
            const selected = document.querySelector('input[name="rating"]:checked');
            const value = selected ? Number(selected.value) : null;

            ratingCards.forEach((label) => {
                const labelValue = Number(label.dataset.rating);
                const active = value !== null && labelValue <= value;
                const star = label.querySelector('.rating-star');

                label.classList.toggle('bg-amber-50', active);
                label.classList.toggle('border-amber-300', active);
                label.classList.toggle('shadow-sm', active);
                label.classList.toggle('bg-white', !active);
                label.classList.toggle('border-slate-200', !active);

                if (star) {
                    star.classList.toggle('text-amber-400', active);
                    star.classList.toggle('text-slate-300', !active);
                }
            });

            if (ratingHint) {
                ratingHint.textContent = value !== null ? ratingMap[value] : 'Toque nas estrelas';
            }

            if (value !== null && value <= 3) {
                reasonBox?.classList.remove('hidden');
                reasonInput?.setAttribute('required', 'required');
                return;
            }

            reasonBox?.classList.add('hidden');
            reasonInput?.removeAttribute('required');
        }

        ratingInputs.forEach((input) => {
            input.addEventListener('change', updateReasonVisibility);
        });

        updateReasonVisibility();
    </script>
</body>
</html>