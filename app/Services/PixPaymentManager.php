<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Support\HotspotIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PixPaymentManager
{
    public function __construct()
    {
        $this->wooviService = app(WooviPixService::class);
        $this->santanderService = app(SantanderPixService::class);
        $this->pagbankService = app(PagBankPixService::class);
        $this->pixQRCodeService = app(PixQRCodeService::class);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{payment: Payment, gateway: string, qr_code: array<string, mixed>}
     */
    public function createPixPayment(User $user, float $amount, array $context = []): array
    {
        $macCandidate = $context['mac_address'] ?? $user->mac_address;
        $ipCandidate = $context['ip_address'] ?? $user->ip_address;

        $macAddress = HotspotIdentity::resolveRealMac($macCandidate, $ipCandidate);
        if (! $macAddress) {
            throw new RuntimeException('Não foi possível identificar o dispositivo. Reconecte ao Wi-Fi e tente novamente.');
        }

        $ipAddress = $ipCandidate ?? HotspotIdentity::resolveClientIp(request());

        $this->updateUserDeviceMetadata($user, $macAddress, $ipAddress);

        $gateway = config('wifi.payment.default_gateway', config('wifi.payment_gateways.pix.gateway'));
        $amount = max($amount, config('wifi.pricing.default_price', 5.99));

        return DB::transaction(function () use ($user, $amount, $macAddress, $ipAddress, $gateway, $context) {
            $this->cancelPreviousPending($user, $context['cancel_previous'] ?? true);

            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_type' => 'pix',
                'status' => 'pending',
                'transaction_id' => $this->generateTransactionId(),
            ]);

            $qrData = $this->generateGatewayQrCode($payment, $gateway, $amount);

            Log::info('💳 Pagamento PIX gerado via Portal', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'gateway' => $gateway,
                'source' => $context['source'] ?? 'portal/dashboard',
            ]);

            return [
                'payment' => $payment,
                'gateway' => $gateway,
                'qr_code' => $qrData,
            ];
        });
    }

    private function updateUserDeviceMetadata(User $user, string $macAddress, ?string $ipAddress): void
    {
        $updates = [];

        if (HotspotIdentity::shouldReplaceMac($user->mac_address, $macAddress)) {
            $updates['mac_address'] = $macAddress;
        }

        if ($ipAddress && $user->ip_address !== $ipAddress) {
            $updates['ip_address'] = $ipAddress;
        }

        if (! empty($updates)) {
            $user->update($updates);
        }
    }

    private function cancelPreviousPending(User $user, bool $cancelPrevious): void
    {
        if (! $cancelPrevious) {
            return;
        }

        $user->payments()
            ->where('payment_type', 'pix')
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * @return array<string, mixed>
     */
    private function generateGatewayQrCode(Payment $payment, string $gateway, float $amount): array
    {
        return match ($gateway) {
            'woovi' => $this->handleWooviGateway($payment, $amount),
            'santander' => $this->handleSantanderGateway($payment, $amount),
            'pagbank' => $this->handlePagBankGateway($payment, $amount),
            default => $this->handleDefaultGateway($payment, $amount),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function handleWooviGateway(Payment $payment, float $amount): array
    {
        if (! config('wifi.payment_gateways.pix.woovi_app_id')) {
            return $this->handleDefaultGateway($payment, $amount);
        }

        $qrData = $this->wooviService->createPixPayment(
            $amount,
            'WiFi Tocantins Express - Internet Premium',
            $payment->transaction_id
        );

        if (! ($qrData['success'] ?? false)) {
            throw new RuntimeException($qrData['message'] ?? 'Erro ao gerar cobrança Woovi.');
        }

        $payment->update([
            'pix_emv_string' => $qrData['qr_code_text'],
            'pix_location' => $qrData['correlation_id'],
            'gateway_payment_id' => $qrData['woovi_id'],
        ]);

        $imageUrl = $this->resolveQrCodeImageUrl($qrData['qr_code_text'], $qrData['qr_code_image'] ?? null, $qrData['qr_code_is_url'] ?? false);

        return [
            'emv_string' => $qrData['qr_code_text'],
            'image_url' => $imageUrl,
            'amount' => number_format($qrData['amount'], 2, '.', ''),
            'transaction_id' => $qrData['correlation_id'],
            'payment_id' => $qrData['woovi_id'],
            'expires_at' => $qrData['expires_at'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleSantanderGateway(Payment $payment, float $amount): array
    {
        if (! config('wifi.payment_gateways.pix.client_id')) {
            return $this->handleDefaultGateway($payment, $amount);
        }

        $qrData = $this->santanderService->createPixPayment(
            $amount,
            'WiFi Tocantins Express - Internet',
            $payment->transaction_id
        );

        if (! ($qrData['success'] ?? false)) {
            throw new RuntimeException($qrData['message'] ?? 'Erro ao gerar cobrança Santander.');
        }

        $payment->update([
            'pix_emv_string' => $qrData['qr_code_text'],
            'pix_location' => $qrData['external_id'],
            'gateway_payment_id' => $qrData['payment_id'],
        ]);

        return [
            'emv_string' => $qrData['qr_code_text'],
            'image_url' => $this->santanderService->generateQRCodeImageUrl($qrData['qr_code_text']),
            'amount' => number_format($qrData['amount'], 2, '.', ''),
            'transaction_id' => $qrData['external_id'],
            'payment_id' => $qrData['payment_id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handlePagBankGateway(Payment $payment, float $amount): array
    {
        if (! config('wifi.payment_gateways.pix.pagbank_token')) {
            return $this->handleDefaultGateway($payment, $amount);
        }

        $qrData = $this->pagbankService->createPixPayment(
            $amount,
            'WiFi Tocantins Express - Internet Premium',
            $payment->transaction_id
        );

        if (! ($qrData['success'] ?? false)) {
            throw new RuntimeException($qrData['message'] ?? 'Erro ao gerar cobrança PagBank.');
        }

        $payment->update([
            'pix_emv_string' => $qrData['qr_code_text'],
            'pix_location' => $qrData['reference_id'],
            'gateway_payment_id' => $qrData['order_id'],
        ]);

        return [
            'emv_string' => $qrData['qr_code_text'],
            'image_url' => $qrData['qr_code_image'],
            'amount' => number_format($qrData['amount'], 2, '.', ''),
            'transaction_id' => $qrData['reference_id'],
            'payment_id' => $qrData['order_id'],
            'expires_at' => $qrData['expires_at'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleDefaultGateway(Payment $payment, float $amount): array
    {
        $qrData = $this->pixQRCodeService->generatePixQRCode($amount, $payment->transaction_id);

        $payment->update([
            'pix_emv_string' => $qrData['emv_string'],
            'pix_location' => $qrData['location'],
        ]);

        return [
            'emv_string' => $qrData['emv_string'],
            'image_url' => $this->pixQRCodeService->generateQRCodeImageUrl($qrData['emv_string']),
            'amount' => number_format($qrData['amount'], 2, '.', ''),
            'transaction_id' => $qrData['transaction_id'],
        ];
    }

    private function resolveQrCodeImageUrl(string $emv, ?string $qrCodeImage, bool $isUrl): string
    {
        if ($qrCodeImage) {
            if ($isUrl) {
                return $qrCodeImage;
            }

            return 'data:image/png;base64,'.$qrCodeImage;
        }

        return $this->pixQRCodeService->generateQRCodeImageUrl($emv);
    }

    private function generateTransactionId(): string
    {
        return 'TXN_'.time().'_'.strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }
}

