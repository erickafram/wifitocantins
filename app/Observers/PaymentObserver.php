<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NtfyService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Disparado quando um pagamento é atualizado.
     * Envia push notification se o status mudou para 'completed'.
     */
    public function updated(Payment $payment): void
    {
        // Verifica se o status mudou para 'completed'
        if ($payment->wasChanged('status') && $payment->status === 'completed') {
            $this->sendPaymentNotification($payment);
        }
    }

    protected function sendPaymentNotification(Payment $payment): void
    {
        try {
            $user = $payment->user;
            $userName = $user?->name ?? $user?->phone ?? 'Usuário #' . $payment->user_id;
            $amount = number_format($payment->amount, 2, ',', '.');
            $method = strtoupper($payment->payment_type ?? 'N/A');

            // Tenta pegar o nome do ônibus
            $busName = null;
            if ($user?->last_mikrotik_id) {
                $busName = \App\Models\Bus::getSerialNameMap()[$user->last_mikrotik_id] ?? $user->last_mikrotik_id;
            }

            $ntfy = app(NtfyService::class);
            $ntfy->notifyPaymentCompleted($userName, $amount, $method, $busName);
        } catch (\Throwable $e) {
            Log::error('PaymentObserver: erro ao enviar notificação push', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
