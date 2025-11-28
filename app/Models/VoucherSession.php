<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherSession extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'mac_address',
        'ip_address',
        'started_at',
        'ended_at',
        'hours_granted',
        'minutes_used',
        'status',
        'mikrotik_response',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'hours_granted' => 'integer',
            'minutes_used' => 'integer',
        ];
    }

    /**
     * Relacionamento com voucher
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a sessão está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->ended_at || $this->ended_at->isFuture());
    }

    /**
     * Calcula tempo restante em minutos
     */
    public function getRemainingMinutes(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        $totalMinutes = $this->hours_granted * 60;
        $usedMinutes = $this->minutes_used;
        
        return max(0, $totalMinutes - $usedMinutes);
    }

    /**
     * Atualiza tempo usado
     */
    public function updateUsage(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $minutesElapsed = $this->started_at->diffInMinutes(now());
        $this->minutes_used = min($minutesElapsed, $this->hours_granted * 60);
        
        // Se o tempo acabou, marca como expirado
        if ($this->minutes_used >= ($this->hours_granted * 60)) {
            $this->status = 'expired';
            $this->ended_at = now();
        }
        
        $this->save();
    }

    /**
     * Finaliza a sessão
     */
    public function end(string $reason = 'disconnected'): void
    {
        $this->updateUsage();
        $this->status = $reason;
        $this->ended_at = now();
        $this->save();
    }

    /**
     * Scope para sessões ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('ended_at')
                          ->orWhere('ended_at', '>', now());
                    });
    }

    /**
     * Scope para sessões de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }
}
