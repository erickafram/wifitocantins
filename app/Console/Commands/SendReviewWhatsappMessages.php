<?php

namespace App\Console\Commands;

use App\Models\ServiceReview;
use App\Models\User;
use App\Models\WhatsappSetting;
use App\Services\ServiceReviewWhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReviewWhatsappMessages extends Command
{
    protected $signature = 'reviews:send-whatsapp
                            {--date= : Data de referencia do lote no formato YYYY-MM-DD}
                            {--force : Forcar envio mesmo se o toggle estiver desabilitado}';

    protected $description = 'Envia links de avaliacao via WhatsApp para passageiros do lote 18:30-06:00';

    public function handle(ServiceReviewWhatsappService $reviewWhatsappService): int
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
            $review = $reviewWhatsappService->prepareReviewForUser($user, $batchDate);

            if ($review->whatsapp_status === 'sent') {
                $skipped++;
                continue;
            }

            $result = $reviewWhatsappService->sendPreparedReview($review, $user->name ?: 'Passageiro');

            if ($result['success']) {
                $sent++;
                $this->line('  ✓ Link enviado para ' . ($review->phone ?: $user->phone));
            } else {
                $failed++;
                $this->error('  ✗ Falha ao enviar para ' . ($review->phone ?: $user->phone));
            }

            usleep(500000);
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