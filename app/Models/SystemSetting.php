<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    private static array $runtimeCache = [];

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, $default = null)
    {
        if (array_key_exists($key, self::$runtimeCache)) {
            return self::$runtimeCache[$key];
        }

        $cacheKey = 'system_setting:'.$key;

        $value = Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            return static::query()->where('key', $key)->value('value') ?? $default;
        });

        self::$runtimeCache[$key] = $value;

        return $value;
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        self::$runtimeCache[$key] = $value;
        Cache::forget('system_setting:'.$key);
    }
}
