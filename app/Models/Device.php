<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'mac_address',
        'device_name',
        'device_type',
        'user_agent',
        'first_seen',
        'last_seen',
        'total_connections',
    ];

    protected function casts(): array
    {
        return [
            'first_seen' => 'datetime',
            'last_seen' => 'datetime',
        ];
    }

    /**
     * Atualiza última vez visto
     */
    public function updateLastSeen()
    {
        $this->update([
            'last_seen' => now(),
            'total_connections' => $this->total_connections + 1,
        ]);
    }
}
