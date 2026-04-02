<?php

namespace App\Console\Commands;

use App\Models\ServiceReview;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendReviewWhatsappMessages extends Command
{
    protected $signature = 'reviews:send-whatsapp
                            {--date= : Data de referencia do lote no formato YYYY-MM-DD}
                            {--force : Forcar envio mesmo se o toggle estiver desabilitado}';

    protected $description = 'Envia links de avaliacao via WhatsApp para passageiros do lote 18:30-06:00';

    protected string $baileysServerUrl;

    public function __construct()
    {
        parent::__construct();
        $this->baileysServerUrl = env('BAILEYS_SERVER_URL', 'http://localhost:3001');
    }

    public function handle(): int
    {
        if (! $this->option('force') && ! WhatsappSetting::isReviewAutoSendEnabled()) {
            $this->info('Envio de avaliacao via WhatsApp esta desabilitado. Use --force para ignorar o toggle.');
            return self::SUCCESS;
        }

        if (! WhatsappSetting::isConnected()) {
            $this->warn('WhatsApp nao esta conectado.');
            Log::warning('Avaliacao WhatsApp: tentativa de envio sem conexao.');
            return self::FAILURE;
        }

        $batchDateOption = $this->option('date');

        try {
            $batchDate = $batchDateOption
                ? Carbon::createFromFormat('Y-m-d', $batchDateOption)->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable) {
            $this->error('Data invalida. Use o formato YYYY-MM-DD.');
            return self::INVALID;
        }

        $window = ServiceReview::resolveBatchWindow($batchDate);
        $messageTemplate = WhatsappSetting::getReviewMessageTemplate();

        $this->info(sprintf(
            'Buscando passageiros cadastrados entre %s e %s...',
            $window['start']->format('d/m/Y H:i'),
            $window['end']->format('d/m/Y H:i')
        ));

        $users = User::query()
            ->whereBetween('registered_at', [$window['start'], $window['end']])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where(function ($query) {
                $query->whereNull('role')
                    ->orWhereNotIn('role', ['admin', 'manager']);
            })
            ->orderBy('registered_at')
            ->get();

        $this->info("Encontrados {$users->count()} passageiros elegiveis.");

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($users as $user) {
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

            if ($review->whatsapp_status === 'sent') {
                $skipped++;
                continue;
            }

            $phone = WhatsappMessage::formatPhone($user->phone);
            $digits = preg_replace('/\D/', '', $phone ?? '');

            if (strlen($digits) < 12) {
                $review->update([
                    'whatsapp_status' => 'skipped',
                    'whatsapp_error_message' => 'Telefone invalido para envio via WhatsApp.',
                ]);
                $skipped++;
                continue;
            }

            $link = route('reviews.show', $review->token);
            $message = strtr($messageTemplate, [
                '{nome}' => $user->name ?: 'Passageiro',
                '{telefone}' => $user->phone ?: $digits,
                '{link}' => $link,
                '{data_viagem}' => Carbon::parse($window['batch_date'])->format('d/m/Y'),
            ]);

            $whatsappMessage = WhatsappMessage::create([
                'user_id' => $user->id,
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
                    $sent++;
                    $this->line("  ✓ Link enviado para {$phone}");
                } else {
                    $errorMessage = $response->body();
                    $whatsappMessage->markAsFailed($errorMessage);
                    $review->markWhatsappFailed($errorMessage, $whatsappMessage);
                    $failed++;
                    $this->error("  ✗ Falha ao enviar para {$phone}");
                }

                usleep(500000);
            } catch (\Throwable $exception) {
                $whatsappMessage->markAsFailed($exception->getMessage());
                $review->markWhatsappFailed($exception->getMessage(), $whatsappMessage);
                $failed++;
                $this->error("  ✗ Erro ao enviar para {$phone}: {$exception->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Resumo: {$sent} enviados, {$failed} falhas, {$skipped} ignorados.");

        Log::info('Avaliacao WhatsApp: envio finalizado.', [
            'batch_date' => $window['batch_date'],
            'window_start' => $window['start']->toDateTimeString(),
            'window_end' => $window['end']->toDateTimeString(),
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        return self::SUCCESS;
    }
}