<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MikrotikMacReport extends Model
{
    protected $fillable = [
        'ip_address',
        'mac_address',
        'transaction_id',
        'mikrotik_ip',
        'mikrotik_id',
        'reported_at',
        'last_seen',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    /**
     * Scope para buscar reports recentes (última hora)
     */
    public function scopeRecent($query)
    {
        return $query->where('reported_at', '>=', Carbon::now()->subHour());
    }

    /**
     * Buscar MAC mais recente para um IP
     */
    public static function getLatestMacForIp($ipAddress)
    {
        return static::where('ip_address', $ipAddress)
            ->recent()
            ->orderBy('reported_at', 'desc')
            ->first();
    }

    /**
     * Limpar reports antigos (mais de 1 hora)
     */
    public static function cleanOldReports()
    {
        return static::where('reported_at', '<', Carbon::now()->subHour())->delete();
    }
}
