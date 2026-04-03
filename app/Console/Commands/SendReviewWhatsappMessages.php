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

    protected $description = 'Envia links de avaliacao via WhatsApp e Email para passageiros do lote 18:30-06:00';

    public function handle(ServiceReviewWhatsappService $reviewWhatsappService): int
    {
        $whatsappConnected = WhatsappSetting::isConnected();

        if (! $this->option('force') && ! WhatsappSetting::isReviewAutoSendEnabled()) {
            $this->info('Envio de avaliacao esta desabilitado. Use --force para ignorar o toggle.');
            return self::SUCCESS;
        }

        if (! $whatsappConnected) {
            $this->warn('WhatsApp nao esta conectado. Enviando apenas por email.');
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
        $emailSent = 0;

        foreach ($users as $user) {
            $review = $reviewWhatsappService->prepareReviewForUser($user, $batchDate);

            if ($review->whatsapp_status === 'sent' && !$user->email) {
                $skipped++;
                continue;
            }

            // Enviar por WhatsApp (se conectado e ainda não enviou)
            $whatsappOk = false;
            if ($whatsappConnected && $review->whatsapp_status !== 'sent') {
                $result = $reviewWhatsappService->sendPreparedReview($review, $user->name ?: 'Passageiro');
                if ($result['success']) {
                    $sent++;
                    $whatsappOk = true;
                    $this->line('  ✓ WhatsApp enviado para ' . ($review->phone ?: $user->phone));
                } else {
                    $failed++;
                    $this->error('  ✗ WhatsApp falhou para ' . ($review->phone ?: $user->phone));
                }
                // Delay longo entre WhatsApp para evitar ban (30s)
                sleep(30);
            } elseif ($review->whatsapp_status === 'sent') {
                $skipped++;
            }

            // Enviar por Email (se tem email) — sem delay grande
            if ($user->email) {
                try {
                    $link = $reviewWhatsappService->resolveReviewLink($review);
                    $displayName = $user->name ?: 'Passageiro';
                    \Illuminate\Support\Facades\Mail::send([], [], function ($m) use ($user, $displayName, $link) {
                        $m->to($user->email)
                          ->subject('Avalie sua viagem - WiFi Tocantins Transporte')
                          ->html(
                              '<div style="font-family:Arial,sans-serif;max-width:480px;margin:0 auto;background:#fff;border-radius:12px;border:1px solid #E5E5E5;overflow:hidden">'
                            . '<div style="background:linear-gradient(135deg,#007A28,#00A335);padding:24px;text-align:center">'
                            . '<p style="color:#fff;font-size:18px;font-weight:bold;margin:0">🚌 WiFi Tocantins Transporte</p>'
                            . '</div>'
                            . '<div style="padding:24px">'
                            . '<p style="color:#111;font-size:15px">Olá <strong>' . $displayName . '</strong>,</p>'
                            . '<p style="color:#333;font-size:14px">Como foi sua experiência com nosso WiFi? Sua opinião é muito importante!</p>'
                            . '<div style="text-align:center;margin:24px 0">'
                            . '<a href="' . $link . '" style="background:#00A335;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;display:inline-block">Avaliar agora</a>'
                            . '</div>'
                            . '<p style="color:#888;font-size:12px">Leva menos de 1 minuto.</p>'
                            . '</div>'
                            . '<div style="background:#F8F9FA;padding:16px;text-align:center;border-top:1px solid #E5E5E5">'
                            . '<p style="color:#888;font-size:10px;margin:0">© ' . date('Y') . ' Tocantins Transporte WiFi</p>'
                            . '</div></div>'
                          );
                    });
                    $emailSent++;
                    $this->line('  📧 Email enviado para ' . $user->email);
                } catch (\Exception $e) {
                    $this->error('  📧 Email falhou para ' . $user->email . ': ' . $e->getMessage());
                }
            }

            // Delay entre emails (2s para não sobrecarregar)
            if ($user->email) {
                usleep(2000000);
            }
        }

        $this->newLine();
        $this->info("Resumo: WhatsApp {$sent} enviados, {$failed} falhas, {$skipped} ignorados | Email {$emailSent} enviados.");

        Log::info('Avaliacao envio finalizado.', [
            'batch_date' => $window['batch_date'],
            'whatsapp_sent' => $sent,
            'whatsapp_failed' => $failed,
            'whatsapp_skipped' => $skipped,
            'email_sent' => $emailSent,
        ]);

        return self::SUCCESS;
    }
}