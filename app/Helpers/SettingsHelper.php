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
     * Calcular preço original (preço promocional * multiplicador)
     * Exemplo: Se preço atual é R$ 5,99, o original seria R$ 20,99
     */
    public static function getOriginalPrice(): float
    {
        $currentPrice = self::getWifiPrice();
        
        // Multiplicador fixo para calcular o "preço de" (aproximadamente 3.5x)
        // Isso garante um desconto de ~70% que é mais realista
        // Exemplo: R$ 5,99 × 3.5 = R$ 20,96 (~70% de desconto)
        $multiplier = 3.5;
        
        return round($currentPrice * $multiplier, 2);
    }

    /**
     * Calcular porcentagem de desconto
     */
    public static function getDiscountPercentage(): int
    {
        $currentPrice = self::getWifiPrice();
        $originalPrice = self::getOriginalPrice();
        
        if ($originalPrice <= 0) {
            return 0;
        }
        
        $discount = (($originalPrice - $currentPrice) / $originalPrice) * 100;
        
        return (int) round($discount);
    }

    /**
     * Obter informações completas de preço com desconto
     */
    public static function getPriceInfo(): array
    {
        $currentPrice = self::getWifiPrice();
        $originalPrice = self::getOriginalPrice();
        $discountPercentage = self::getDiscountPercentage();
        
        return [
            'current_price' => $currentPrice,
            'original_price' => $originalPrice,
            'discount_percentage' => $discountPercentage,
            'savings' => round($originalPrice - $currentPrice, 2),
        ];
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
