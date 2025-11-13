<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DebugQrCode;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registrar comando de debug do QR Code
Artisan::command('debug:qrcode {--test-payment : Criar um pagamento de teste}', function () {
    $command = new DebugQrCode();
    $command->setLaravel($this->laravel);
    return $command->handle();
})->purpose('Debug da geração de QR Code PIX');

// Agendamento de tarefas
Schedule::command('vouchers:reset-daily-usage')
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->runInBackground();
