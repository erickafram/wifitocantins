<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectivityProbe extends Model
{
    protected $fillable = [
        'token',
        'conversation_id',
        'created_by_admin_id',
        'user_id',
        'target_mac',
        'target_phone',
        'status',
        'results',
        'client_ip',
        'client_mac',
        'client_user_agent',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'results' => 'array',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Gera um token aleatório único (32 hex chars).
     */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Resumo one-line pro chat (ex: "✅ Download 42 Mbps, Latência 68ms").
     */
    public function getSummaryAttribute(): string
    {
        if (!$this->results) return '';

        $r = $this->results;
        $parts = [];

        if (isset($r['download_mbps']))  $parts[] = 'Download ' . number_format($r['download_mbps'], 1) . ' Mbps';
        if (isset($r['latency_ms']))     $parts[] = 'Latência ' . round($r['latency_ms']) . 'ms';

        return implode(' · ', $parts);
    }

    /**
     * Verdict objetivo baseado nos resultados.
     * Retorna: 'excellent' | 'good' | 'poor' | 'failed'
     */
    public function getVerdictAttribute(): string
    {
        if (!$this->results) return 'failed';

        $r = $this->results;
        $dnsOk = ($r['dns_ok'] ?? false);
        $googleOk = ($r['google_ok'] ?? false);
        $download = $r['download_mbps'] ?? 0;
        $latency = $r['latency_ms'] ?? 9999;

        if (!$dnsOk || !$googleOk) return 'failed';
        if ($download >= 10 && $latency < 200) return 'excellent';
        if ($download >= 2 && $latency < 500) return 'good';
        return 'poor';
    }
}
