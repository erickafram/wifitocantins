<?php

namespace App\Services;

use App\Models\ServiceReview;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ServiceReviewWhatsappService
{
    protected string $baileysServerUrl;

    public function __construct()
    {
        $this->baileysServerUrl = env('BAILEYS_SERVER_URL', 'http://localhost:3001');
    }

    public function findUserByPhone(?string $phone): ?User
    {
        $cleanPhone = preg_replace('/[^\d]/', '', (string) $phone);

        if ($cleanPhone === '') {
            return null;
        }

        $user = User::where('phone', $cleanPhone)
            ->orderByDesc('updated_at')
            ->first();

        if (! $user) {
            $user = User::where('phone', '55' . $cleanPhone)
                ->orderByDesc('updated_at')
                ->first();
        }

        if (! $user) {
            $user = User::where('phone', 'LIKE', '%' . substr($cleanPhone, -9))
                ->orderByDesc('updated_at')
                ->first();
        }

        return $user;
    }

    public function prepareReviewForUser(User $user, Carbon|string|null $batchDate = null): ServiceReview
    {
        $window = ServiceReview::resolveBatchWindow($batchDate);

        $review = ServiceReview::firstOrNew([
            'batch_date' => $window['batch_date'],
            'user_id' => $user->id,
        ]);

        if (! $review->exists) {
            $review->token = (string) Str::uuid();
        }

        $review->fill([
            'phone' => $user->phone,
            'registration_at' => $user->registered_at,
        ]);

        $review->save();

        return $review;
    }

    public function sendManualTest(string $phone, ?string $recipientName = null, Carbon|string|null $batchDate = null): array
    {
        $user = $this->findUserByPhone($phone);

        if ($user) {
            $review = $this->prepareReviewForUser($user, $batchDate);
            $displayName = $user->name ?: ($recipientName ?: 'Passageiro');
        } else {
            $window = ServiceReview::resolveBatchWindow($batchDate);
            $cleanPhone = preg_replace('/[^\d]/', '', $phone);

            $review = ServiceReview::firstOrNew([
                'batch_date' => $window['batch_date'],
                'user_id' => null,
                'phone' => $cleanPhone,
            ]);

            if (! $review->exists) {
                $review->token = (string) Str::uuid();
            }

            $review->fill([
                'registration_at' => now(),
            ]);
            $review->save();

            $displayName = trim((string) $recipientName) !== '' ? trim((string) $recipientName) : 'Passageiro';
        }

        $result = $this->sendPreparedReview($review, $displayName);
        $result['matched_user'] = $user;

        return $result;
    }

    public function sendPreparedReview(ServiceReview $review, ?string $recipientName = null): array
    {
        if (! WhatsappSetting::isConnected()) {
            return [
                'success' => false,
                'error' => 'WhatsApp nao esta conectado.',
                'review' => $review,
            ];
        }

        $phone = WhatsappMessage::formatPhone($review->phone ?: $review->user?->phone);
        $digits = preg_replace('/[^\d]/', '', (string) $phone);

        if (strlen($digits) < 12) {
            $review->update([
                'whatsapp_status' => 'failed',
                'whatsapp_error_message' => 'Telefone invalido para envio via WhatsApp.',
            ]);

            return [
                'success' => false,
                'error' => 'Telefone invalido para envio via WhatsApp.',
                'review' => $review->fresh(),
            ];
        }

        $message = $this->buildMessage($review, $recipientName);

        $whatsappMessage = WhatsappMessage::create([
            'user_id' => $review->user_id,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            $response = Http::timeout(30)->post($this->baileysServerUrl . '/send', [
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $whatsappMessage->markAsSent($data['messageId'] ?? null);
                $review->markWhatsappSent($whatsappMessage);

                return [
                    'success' => true,
                    'review' => $review->fresh(),
                    'whatsapp_message' => $whatsappMessage->fresh(),
                    'link' => $this->resolveReviewLink($review),
                ];
            }

            $errorMessage = $response->body();
            $whatsappMessage->markAsFailed($errorMessage);
            $review->markWhatsappFailed($errorMessage, $whatsappMessage);

            return [
                'success' => false,
                'error' => $errorMessage,
                'review' => $review->fresh(),
                'whatsapp_message' => $whatsappMessage->fresh(),
            ];
        } catch (\Throwable $exception) {
            $whatsappMessage->markAsFailed($exception->getMessage());
            $review->markWhatsappFailed($exception->getMessage(), $whatsappMessage);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'review' => $review->fresh(),
                'whatsapp_message' => $whatsappMessage->fresh(),
            ];
        }
    }

    public function resolveReviewLink(ServiceReview $review): string
    {
        return route('reviews.show', $review->token);
    }

    protected function buildMessage(ServiceReview $review, ?string $recipientName = null): string
    {
        $name = trim((string) ($recipientName ?: $review->user?->name ?: 'Passageiro'));

        return strtr(WhatsappSetting::getReviewMessageTemplate(), [
            '{nome}' => $name !== '' ? $name : 'Passageiro',
            '{telefone}' => $review->phone ?: ($review->user?->phone ?: '-'),
            '{link}' => $this->resolveReviewLink($review),
            '{data_viagem}' => optional($review->batch_date)->format('d/m/Y') ?: now()->format('d/m/Y'),
        ]);
    }
}