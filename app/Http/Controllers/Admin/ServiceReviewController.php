<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceReview;
use App\Models\WhatsappSetting;
use App\Services\ServiceReviewWhatsappService;
use Illuminate\Http\Request;

class ServiceReviewController extends Controller
{
    public function __construct(
        protected ServiceReviewWhatsappService $reviewWhatsappService
    ) {
    }

    public function index(Request $request)
    {
        $query = ServiceReview::with(['user', 'whatsappMessage']);

        if ($request->filled('status')) {
            match ($request->status) {
                'answered' => $query->whereNotNull('submitted_at'),
                'pending' => $query->whereNull('submitted_at')->where('whatsapp_status', 'sent'),
                'failed' => $query->where('whatsapp_status', 'failed'),
                'not_sent' => $query->whereIn('whatsapp_status', ['pending', 'skipped']),
                default => null,
            };
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('batch_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('batch_date', '<=', $request->date_to);
        }

        if ($request->filled('answered_from')) {
            $query->whereDate('submitted_at', '>=', $request->answered_from);
        }

        if ($request->filled('answered_to')) {
            $query->whereDate('submitted_at', '<=', $request->answered_to);
        }

        $reviews = $query
            ->orderByDesc('batch_date')
            ->orderByDesc('created_at')
            ->paginate(30);

        $stats = [
            'total_invites' => ServiceReview::count(),
            'answered' => ServiceReview::whereNotNull('submitted_at')->count(),
            'pending_answers' => ServiceReview::whereNull('submitted_at')->where('whatsapp_status', 'sent')->count(),
            'low_ratings' => ServiceReview::whereNotNull('submitted_at')->where('rating', '<=', 3)->count(),
            'sent' => ServiceReview::where('whatsapp_status', 'sent')->count(),
            'failed' => ServiceReview::where('whatsapp_status', 'failed')->count(),
            'average_rating' => round((float) ServiceReview::whereNotNull('rating')->avg('rating'), 1),
        ];

        $distribution = collect(range(1, 5))
            ->mapWithKeys(fn (int $rating) => [
                $rating => ServiceReview::where('rating', $rating)->count(),
            ]);

        return view('admin.reviews.index', compact(
            'reviews',
            'stats',
            'distribution'
        ));
    }

    public function settings()
    {
        $settings = [
            'review_auto_send_enabled' => WhatsappSetting::isReviewAutoSendEnabled(),
            'review_email_enabled' => WhatsappSetting::get('review_email_enabled', 'true') === 'true',
            'review_message_template' => WhatsappSetting::getReviewMessageTemplate(),
            'is_connected' => WhatsappSetting::isConnected(),
            'connected_phone' => WhatsappSetting::getConnectedPhone(),
        ];

        $currentWindow = ServiceReview::resolveBatchWindow(today());

        return view('admin.reviews.settings', compact('settings', 'currentWindow'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'review_message_template' => 'required|string|max:1500',
            'review_auto_send_enabled' => 'nullable|boolean',
            'review_email_enabled' => 'nullable|boolean',
        ]);

        WhatsappSetting::set(
            'review_auto_send_enabled',
            $request->has('review_auto_send_enabled') ? 'true' : 'false'
        );
        WhatsappSetting::set(
            'review_email_enabled',
            $request->has('review_email_enabled') ? 'true' : 'false'
        );
        WhatsappSetting::set('review_message_template', $request->review_message_template);

        return redirect()
            ->route('admin.reviews.settings')
            ->with('success', 'Configuracoes atualizadas com sucesso!');
    }

    public function update(Request $request, ServiceReview $review)
    {
        $validated = $request->validate([
            'submitted_at' => 'nullable|date',
            'rating' => 'nullable|integer|min:1|max:5',
            'reason' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'submitted_at' => $validated['submitted_at'] ?: null,
            'rating' => $validated['rating'] ?: null,
            'reason' => $validated['reason'] ?: null,
        ]);

        return redirect()
            ->route('admin.reviews.index', $request->query())
            ->with('success', 'Avaliacao atualizada com sucesso!');
    }

    public function destroy(ServiceReview $review)
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Avaliacao excluida com sucesso!');
    }

    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10|max:20',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'batch_date' => 'nullable|date',
        ], [
            'phone.required' => 'Informe o numero que vai receber o teste.',
            'phone.min' => 'O numero precisa ter pelo menos 10 digitos.',
            'email.email' => 'E-mail invalido.',
            'batch_date.date' => 'A data do lote precisa ser valida.',
        ]);

        $result = $this->reviewWhatsappService->sendManualTest(
            $validated['phone'],
            $validated['name'] ?? null,
            $validated['batch_date'] ?? today()->toDateString(),
            $validated['email'] ?? null
        );

        $messages = [];

        if ($result['success']) {
            $messages[] = 'WhatsApp enviado para ' . ($result['review']->phone ?: $validated['phone']);
        } else {
            $messages[] = 'WhatsApp falhou: ' . ($result['error'] ?? 'erro desconhecido');
        }

        if (!empty($result['email_sent'])) {
            $messages[] = 'Email enviado para ' . $validated['email'];
        } elseif (!empty($validated['email']) && empty($result['email_sent'])) {
            $messages[] = 'Email falhou: ' . ($result['email_error'] ?? 'erro desconhecido');
        }

        $linkedUserMessage = ($result['matched_user'] ?? null)
            ? ' Usuario vinculado ao cadastro existente.'
            : '';

        $hasSuccess = ($result['success'] || !empty($result['email_sent']));

        return redirect()
            ->route('admin.reviews.settings')
            ->withInput()
            ->with($hasSuccess ? 'success' : 'error', implode(' | ', $messages) . $linkedUserMessage)
            ->with('manual_review_link', $result['link'] ?? null);
    }
}