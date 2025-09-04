<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'discount',
        'discount_percent',
        'expires_at',
        'max_uses',
        'used_count',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'discount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Verifica se o voucher é válido
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Usa o voucher
     */
    public function use(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->increment('used_count');
        return true;
    }
}
