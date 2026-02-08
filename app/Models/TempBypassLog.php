<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBypassLog extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'mac_address',
        'phone',
        'ip_address',
        'bypass_number',
        'expires_at',
        'was_denied',
        'deny_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'was_denied' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
