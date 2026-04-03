<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'mikrotik_serial',
        'name',
        'plate',
        'route_description',
        'is_active',
        'last_public_ip',
        'last_sync_at',
        'last_city',
        'last_state',
        'last_lat',
        'last_lng',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_lat' => 'decimal:7',
        'last_lng' => 'decimal:7',
    ];

    /**
     * Retorna mapa serial → nome para uso em queries
     */
    public static function getSerialNameMap(): array
    {
        return static::pluck('name', 'mikrotik_serial')->toArray();
    }

    /**
     * Busca nome amigável pelo serial, fallback para o serial
     */
    public static function nameFor(?string $serial): string
    {
        if (!$serial) return 'Desconhecido';
        return static::where('mikrotik_serial', $serial)->value('name') ?? $serial;
    }
}
