<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NtfyService
{
    protected string $serverUrl;
    protected string $topic;
    protected bool $enabled;

    public function __construct()
    {
        $this->serverUrl = rtrim(config('services.ntfy.server_url', 'https://ntfy.sh'), '/');
        $this->topic = config('services.ntfy.topic', '');
        $this->enabled = config('services.ntfy.enabled', false);
    }

    /**
     * Envia uma notificação push via ntfy.sh
     */
    public function send(string $title, string $message, string $priority = 'default', array $tags = []): bool
    {
        if (!$this->enabled || empty($this->topic)) {
            Log::debug('Ntfy: notificação desabilitada ou tópico não configurado.');
            return false;
        }

        try {
            $headers = [
                'Title' => $title,
                'Priority' => $priority,
            ];

            if (!empty($tags)) {
                $headers['Tags'] = implode(',', $tags);
            }

            $response = Http::withHeaders($headers)
                ->post("{$this->serverUrl}/{$this->topic}", $message);

            if ($response->successful()) {
                Log::info("Ntfy: notificação enviada - {$title}");
                return true;
            }

            Log::warning("Ntfy: falha ao enviar notificação", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error("Ntfy: erro ao enviar notificação", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notificação específica de pagamento confirmado
     */
    public function notifyPaymentCompleted(string $userName, string $amount, string $method, ?string $busName = null): bool
    {
        $busInfo = $busName ? " | Bus: {$busName}" : '';
        $title = "Pagamento Confirmado!";
        $message = "{$userName}\nR\$ {$amount} - {$method}{$busInfo}\n" . now()->format('d-m-Y H:i:s');

        return $this->send($title, $message, 'high', ['white_check_mark', 'moneybag']);
    }
}
