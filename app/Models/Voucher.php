<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'driver_name',
        'driver_document',
        'daily_hours',
        'daily_hours_used',
        'last_used_date',
        'expires_at',
        'activated_at',
        'is_active',
        'voucher_type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'last_used_date' => 'date',
            'is_active' => 'boolean',
            'daily_hours' => 'integer',
            'daily_hours_used' => 'integer',
        ];
    }

    /**
     * Verifica se o voucher é válido para uso
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Se for ilimitado, sempre válido (desde que ativo e não expirado)
        if ($this->voucher_type === 'unlimited') {
            return true;
        }

        // Para vouchers limitados, verifica horas diárias
        return $this->hasHoursAvailableToday();
    }

    /**
     * Verifica se ainda tem horas disponíveis hoje
     */
    public function hasHoursAvailableToday(): bool
    {
        // Se for ilimitado, sempre tem horas
        if ($this->voucher_type === 'unlimited') {
            return true;
        }

        // Se nunca foi usado, está disponível
        if (!$this->last_used_date) {
            return true;
        }

        // Se foi usado em outro dia, reseta e está disponível
        if (!$this->last_used_date->isToday()) {
            return true;
        }

        // Se foi usado hoje, verifica se ainda tem horas disponíveis
        // Para vouchers limitados, o motorista pode usar até X horas por dia
        return $this->daily_hours_used < $this->daily_hours;
    }

    /**
     * Obtém horas restantes para hoje
     */
    public function getRemainingHoursToday(): int
    {
        if ($this->voucher_type === 'unlimited') {
            return 999; // Valor simbólico para ilimitado
        }

        // Reseta se for novo dia
        if ($this->last_used_date && !$this->last_used_date->isToday()) {
            return $this->daily_hours;
        }

        return max(0, $this->daily_hours - $this->daily_hours_used);
    }

    /**
     * Registra uso do voucher
     */
    public function recordUsage(int $hours = 24): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Ativa o voucher se for o primeiro uso
        if (!$this->activated_at) {
            $this->activated_at = now();
        }

        // Para vouchers ilimitados, apenas atualiza data
        if ($this->voucher_type === 'unlimited') {
            $this->last_used_date = now()->toDateString();
            $this->save();
            return true;
        }

        // Para vouchers limitados, apenas marca como usado hoje
        // NÃO incrementa horas - o voucher dá acesso pelo período todo
        $today = now()->toDateString();
        
        // Se for um novo dia, reseta o contador
        if (!$this->last_used_date || $this->last_used_date != $today) {
            $this->daily_hours_used = 0;
        }

        // Marca que foi usado hoje (mas não incrementa as horas)
        // O motorista tem direito às horas configuradas por dia
        $this->last_used_date = $today;
        $this->save();

        return true;
    }

    /**
     * Incrementa horas usadas quando sessão expira
     */
    public function incrementHoursUsed(int $hours = 1): void
    {
        // Só incrementa se for voucher limitado e for o mesmo dia
        if ($this->voucher_type === 'limited' && 
            $this->last_used_date && 
            $this->last_used_date->isToday()) {
            
            $this->daily_hours_used = min(
                $this->daily_hours_used + $hours, 
                $this->daily_hours
            );
            $this->save();
        }
    }

    /**
     * Finaliza sessão do voucher e incrementa horas usadas
     */
    public function endSession(int $hoursUsed = null): void
    {
        if ($this->voucher_type === 'unlimited') {
            return; // Vouchers ilimitados não têm controle de horas
        }

        // Se não especificou horas, usar 1 hora como padrão
        if ($hoursUsed === null) {
            $hoursUsed = 1;
        }

        // Só incrementa se for o mesmo dia
        if ($this->last_used_date && $this->last_used_date->isToday()) {
            $this->daily_hours_used = min(
                $this->daily_hours_used + $hoursUsed, 
                $this->daily_hours
            );
            $this->save();

            Log::info('🎫 Sessão de voucher finalizada', [
                'voucher_code' => $this->code,
                'driver_name' => $this->driver_name,
                'hours_used' => $hoursUsed,
                'total_used_today' => $this->daily_hours_used,
                'daily_limit' => $this->daily_hours,
            ]);
        }
    }

    /**
     * Reseta contador diário (executado automaticamente)
     */
    public function resetDailyUsage(): void
    {
        $this->daily_hours_used = 0;
        $this->save();
    }

    /**
     * Scope para vouchers ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope para vouchers de motoristas
     */
    public function scopeDriverVouchers($query)
    {
        return $query->whereNotNull('driver_name');
    }
}
