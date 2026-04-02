<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceReview;
use App\Models\WhatsappSetting;
use Illuminate\Http\Request;

class ServiceReviewController extends Controller
{
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

        if ($request->filled('date_from')) {
            $query->whereDate('batch_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('batch_date', '<=', $request->date_to);
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

        $settings = [
            'review_auto_send_enabled' => WhatsappSetting::isReviewAutoSendEnabled(),
            'review_message_template' => WhatsappSetting::getReviewMessageTemplate(),
        ];

        $currentWindow = ServiceReview::resolveBatchWindow(today());

        return view('admin.reviews.index', compact(
            'reviews',
            'stats',
            'distribution',
            'settings',
            'currentWindow'
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'review_message_template' => 'required|string|max:1500',
            'review_auto_send_enabled' => 'nullable|boolean',
        ]);

        WhatsappSetting::set(
            'review_auto_send_enabled',
            $request->has('review_auto_send_enabled') ? 'true' : 'false'
        );
        WhatsappSetting::set('review_message_template', $request->review_message_template);

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Configuracoes do modulo de avaliacao atualizadas com sucesso!');
    }
}