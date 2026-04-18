<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatConversation extends Model
{
    protected $fillable = [
        'visitor_name',
        'visitor_phone',
        'visitor_email',
        'visitor_ip',
        'visitor_mac',
        'session_id',
        'status',
        'admin_id',
        'last_message_at',
        'unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWithUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Tenta achar o User cadastrado por trás deste visitante do chat.
     * Ordem: MAC (mais único) → telefone (últimos 9 dígitos, ignora DDI/formatação).
     * Usado no admin do chat para mostrar status de pagamento, ônibus, tempo restante.
     */
    public function getLinkedUserAttribute(): ?User
    {
        if ($this->visitor_mac) {
            $user = User::where('mac_address', strtoupper(trim($this->visitor_mac)))
                ->orderByDesc('connected_at')
                ->first();
            if ($user) return $user;
        }

        if ($this->visitor_phone) {
            $digits = preg_replace('/\D/', '', $this->visitor_phone);
            $tail = substr($digits, -9); // últimos 9 dígitos = DDD + número sem DDI
            if (strlen($tail) >= 8) {
                return User::where('phone', 'LIKE', '%' . $tail)
                    ->orderByDesc('connected_at')
                    ->first();
            }
        }

        return null;
    }

    /**
     * Nome amigável do ônibus atual do usuário vinculado (para exibir no chat).
     */
    public function getLinkedBusNameAttribute(): ?string
    {
        $user = $this->linked_user;
        if (!$user || !$user->last_mikrotik_id) return null;
        return Bus::nameFor($user->last_mikrotik_id);
    }
}
