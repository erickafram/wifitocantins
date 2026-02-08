<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'mac_address',
        'ip_address',
        'device_name',
        'last_mikrotik_id',
        'connected_at',
        'expires_at',
        'data_used',
        'status',
        'role',
        'registered_at',
        'email_verified_at',
        'voucher_id',
        'driver_phone',
        'voucher_activated_at',
        'voucher_last_connection',
        'voucher_daily_minutes_used',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'connected_at' => 'datetime',
            'expires_at' => 'datetime',
            'registered_at' => 'datetime',
            'voucher_activated_at' => 'datetime',
            'voucher_last_connection' => 'datetime',
        ];
    }

    /**
     * Relacionamentos
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Verifica se o usuário está conectado
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected' && $this->expires_at && $this->expires_at->isFuture();
    }

    /**
     * Verifica se é um motorista com voucher
     */
    public function isDriver(): bool
    {
        return !empty($this->voucher_id) && !empty($this->driver_phone);
    }

    /**
     * Verifica se tem voucher ativo
     */
    public function hasActiveVoucher(): bool
    {
        return $this->isDriver() 
            && $this->voucher 
            && $this->voucher->isValid() 
            && $this->isConnected();
    }
}
