<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Servico - WiFi Tocantins</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(34,139,34,0.16),_transparent_38%),linear-gradient(180deg,#f8fafc_0%,#eefbf1_45%,#ffffff_100%)] text-slate-800">
    <div class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 text-3xl shadow-sm">🚌</div>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Como foi sua experiencia?</h1>
            <p class="mt-2 text-sm sm:text-base text-slate-600">
                Sua opiniao ajuda o WiFi Tocantins a melhorar o atendimento e a internet durante a viagem.
            </p>
        </div>

        <div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-white overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-green-700 text-white">
                <p class="text-xs uppercase tracking-[0.24em] text-white/70">Avaliacao da viagem</p>
                <h2 class="mt-2 text-xl font-bold">{{ $review->user?->name ?: 'Passageiro WiFi Tocantins' }}</h2>
                <p class="mt-1 text-sm text-emerald-50">Cadastro da viagem: {{ $review->registration_at?->format('d/m/Y H:i') ?: 'Nao informado' }}</p>
            </div>

            <div class="p-6 sm:p-8">
                @if(session('success'))
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('info'))
                <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                    {{ session('info') }}
                </div>
                @endif

                @if($review->submitted_at)
                <div class="text-center py-8">
                    <div class="text-5xl text-amber-400 leading-none">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300">{{ str_repeat('☆', 5 - $review->rating) }}</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-800">Avaliacao enviada</p>
                    <p class="mt-2 text-slate-600">Recebemos sua nota {{ $review->rating }}/5 em {{ $review->submitted_at->format('d/m/Y H:i') }}.</p>
                    @if($review->reason)
                    <div class="mt-6 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-left">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Motivo informado</p>
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-wrap">{{ $review->reason }}</p>
                    </div>
                    @endif
                </div>
                @else
                <form method="POST" action="{{ route('reviews.store', $review->token) }}" class="space-y-6" id="review-form">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Selecione sua nota</label>
                        <div class="flex justify-center gap-2 sm:gap-3 text-4xl sm:text-5xl">
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
                                <label for="rating-{{ $rating }}" data-rating="{{ $rating }}" class="star-label cursor-pointer transition-transform duration-150 hover:scale-110 text-slate-300">★</label>
                            </div>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="mt-3 text-sm text-red-600 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="reason-box" class="hidden rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <label for="reason" class="block text-sm font-semibold text-slate-700 mb-2">Conte o motivo da sua avaliacao</label>
                        <p class="text-sm text-slate-600 mb-3">Para notas de 1 a 3 estrelas, o motivo e obrigatorio.</p>
                        <textarea id="reason" name="reason" rows="5" class="w-full px-4 py-3 border border-amber-200 rounded-2xl focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm" placeholder="Descreva rapidamente o que aconteceu...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-700 mb-1">Importante</p>
                        <p>Esta avaliacao fica vinculada ao cadastro da sua viagem e pode ser respondida uma unica vez.</p>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-base shadow-lg hover:from-emerald-700 hover:to-green-800 transition-all">
                        Enviar avaliacao
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
        const starLabels = document.querySelectorAll('.star-label');

        function updateReasonVisibility() {
            const selected = document.querySelector('input[name="rating"]:checked');
            const value = selected ? Number(selected.value) : null;

            starLabels.forEach((label) => {
                const labelValue = Number(label.dataset.rating);
                const active = value !== null && labelValue <= value;

                label.classList.toggle('text-amber-400', active);
                label.classList.toggle('text-slate-300', !active);
            });

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