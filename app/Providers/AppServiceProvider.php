<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Payment;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        config()->set('wifi.payment.default_gateway', config('wifi.payment.default_gateway', 'santander'));

        // Observer para notificações push de pagamento
        Payment::observe(PaymentObserver::class);
    }
}
