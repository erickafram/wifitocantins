<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        
        // Forçar HTTPS em produção, exceto para a rota /login (MikroTik)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
