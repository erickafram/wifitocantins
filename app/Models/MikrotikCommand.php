<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikCommand extends Model
{
    protected $fillable = [
        'command_type',
        'mac_address',
        'status',
        'response',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExecuted($query)
    {
        return $query->where('status', 'executed');
    }

    public function markAsExecuted($response = null)
    {
        $this->update([
            'status' => 'executed',
            'response' => $response,
            'executed_at' => now(),
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'response' => $error,
            'executed_at' => now(),
        ]);
    }
}
