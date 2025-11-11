<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Obter preço do WiFi configurado no painel admin
     */
    public static function getWifiPrice(): float
    {
        return (float) Cache::remember('wifi_price', 3600, function () {
            return SystemSetting::getValue('wifi_price', config('wifi.pricing.default_price', 5.99));
        });
    }

    /**
     * Obter gateway PIX ativo
     */
    public static function getPixGateway(): string
    {
        return Cache::remember('pix_gateway', 3600, function () {
            return SystemSetting::getValue('pix_gateway', 'pagbank');
        });
    }

    /**
     * Obter duração da sessão em horas
     */
    public static function getSessionDuration(): int
    {
        return (int) Cache::remember('session_duration', 3600, function () {
            return SystemSetting::getValue('session_duration', 24);
        });
    }

    /**
     * Limpar cache de configurações
     */
    public static function clearCache(): void
    {
        Cache::forget('wifi_price');
        Cache::forget('pix_gateway');
        Cache::forget('session_duration');
    }
}
