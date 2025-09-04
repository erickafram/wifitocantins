<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'wifi_sessions';
    protected $fillable = [
        'user_id',
        'payment_id',
        'started_at',
        'ended_at',
        'data_used',
        'session_status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Finaliza a sessão
     */
    public function end()
    {
        $this->update([
            'ended_at' => now(),
            'session_status' => 'ended',
        ]);
    }
}
