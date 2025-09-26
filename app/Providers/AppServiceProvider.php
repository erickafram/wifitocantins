<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (Schema::hasTable('system_settings')) {
            if (SystemSetting::query()->where('key', 'pix_gateway')->doesntExist()) {
                SystemSetting::setValue('pix_gateway', 'santander');
            }
        }
    }
}
