<?php

namespace App\Http\Controllers;

use App\Models\ServiceReview;
use Illuminate\Http\Request;

class ServiceReviewController extends Controller
{
    public function show(string $token)
    {
        $review = ServiceReview::with('user')
            ->where('token', $token)
            ->firstOrFail();

        return view('reviews.show', compact('review'));
    }

    public function store(Request $request, string $token)
    {
        $review = ServiceReview::where('token', $token)->firstOrFail();

        if ($review->submitted_at) {
            return redirect()
                ->route('reviews.show', $review->token)
                ->with('info', 'Esta avaliacao ja foi registrada.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'reason' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Selecione uma nota de 1 a 5 estrelas.',
            'rating.between' => 'A nota deve estar entre 1 e 5 estrelas.',
            'reason.max' => 'O motivo pode ter no maximo 1000 caracteres.',
        ]);

        $rating = (int) $validated['rating'];
        $reason = trim((string) ($validated['reason'] ?? ''));

        if ($rating <= 3 && $reason === '') {
            return back()
                ->withErrors([
                    'reason' => 'Conte o que podemos melhorar no atendimento ou no servico quando a nota for 1, 2 ou 3 estrelas.',
                ])
                ->withInput();
        }

        $review->markSubmitted(
            $rating,
            $reason !== '' ? $reason : null,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('reviews.show', $review->token)
            ->with('success', 'Obrigado! Sua opiniao sobre o atendimento e o servico foi registrada.');
    }
}