<?php

namespace App\Http\Controllers;

use App\Models\MikrotikMacReport;
use App\Models\TempBypassLog;
use App\Models\User;
use App\Support\HotspotIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportDiagnosticController extends Controller
{
    public function show(Request $request)
    {
        return view('portal.support-diagnostic', [
            'prefillPhone' => $this->normalizePhone($request->query('phone')),
            'prefillIp' => HotspotIdentity::resolveClientIp($request),
            'prefillMac' => HotspotIdentity::resolveRealMac($request->query('mac'), HotspotIdentity::resolveClientIp($request)),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
        ]);

        $ipAddress = $validated['ip_address'] ?? HotspotIdentity::resolveClientIp($request);
        $macAddress = HotspotIdentity::resolveRealMac($validated['mac_address'] ?? null, $ipAddress);
        $phone = $this->normalizePhone($validated['phone'] ?? null);

        $userByPhone = $phone ? $this->findUserByPhone($phone) : null;
        $userByMac = $macAddress ? User::where('mac_address', $macAddress)->first() : null;
        $userByIp = $ipAddress
            ? User::where('ip_address', $ipAddress)
                ->orderByRaw("CASE WHEN status IN ('connected', 'active', 'temp_bypass') THEN 0 ELSE 1 END")
                ->orderByDesc('connected_at')
                ->orderByDesc('updated_at')
                ->first()
            : null;

        $user = $userByPhone ?: $userByMac ?: $userByIp;
        $matchedBy = array_values(array_filter([
            $userByPhone ? 'telefone' : null,
            $userByMac ? 'mac' : null,
            $userByIp ? 'ip' : null,
        ]));

        $latestCompletedPayment = $user
            ? $user->payments()
                ->where('status', 'completed')
                ->orderByDesc('paid_at')
                ->orderByDesc('created_at')
                ->first()
            : null;

        $latestPendingPayment = $user
            ? $user->payments()
                ->where('status', 'pending')
                ->latest()
                ->first()
            : null;

        $recentPayments = $user
            ? $user->payments()
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $activeSession = $user
            ? $user->sessions()
                ->where('session_status', 'active')
                ->latest('started_at')
                ->first()
            : null;

        $mikrotikReport = $this->resolveMikrotikReport($ipAddress, $macAddress);
        $recentBypass = $this->resolveRecentBypass($user, $phone, $macAddress);
        $warnings = $this->buildWarnings($phone, $macAddress, $userByPhone, $userByMac, $userByIp);
        $summary = $this->buildSummary($user, $latestCompletedPayment, $latestPendingPayment, $recentBypass);

        return response()->json([
            'success' => true,
            'resolved' => [
                'phone_input' => $phone,
                'phone_registered' => $user?->phone,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress,
                'matched_by' => $matchedBy,
            ],
            'summary' => [
                ...$summary,
                'warnings' => $warnings,
            ],
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'mac_address' => $user->mac_address,
                'ip_address' => $user->ip_address,
                'status' => $user->status,
                'connected_at' => optional($user->connected_at)?->toDateTimeString(),
                'expires_at' => optional($user->expires_at)?->toDateTimeString(),
                'registered_at' => optional($user->registered_at)?->toDateTimeString(),
                'has_active_access' => $this->hasActiveAccess($user),
            ] : null,
            'payments' => [
                'latest_completed' => $latestCompletedPayment ? $this->formatPayment($latestCompletedPayment) : null,
                'latest_pending' => $latestPendingPayment ? $this->formatPayment($latestPendingPayment) : null,
                'recent' => $recentPayments->map(fn ($payment) => $this->formatPayment($payment))->values(),
            ],
            'session' => $activeSession ? [
                'started_at' => optional($activeSession->started_at)?->toDateTimeString(),
                'ended_at' => optional($activeSession->ended_at)?->toDateTimeString(),
                'status' => $activeSession->session_status,
                'data_used' => $activeSession->data_used,
            ] : null,
            'mikrotik_report' => $mikrotikReport ? [
                'ip_address' => $mikrotikReport->ip_address,
                'mac_address' => $mikrotikReport->mac_address,
                'mikrotik_id' => $mikrotikReport->mikrotik_id,
                'reported_at' => optional($mikrotikReport->reported_at)?->toDateTimeString(),
            ] : null,
            'temp_bypass' => $recentBypass ? [
                'bypass_number' => $recentBypass->bypass_number,
                'was_denied' => (bool) $recentBypass->was_denied,
                'deny_reason' => $recentBypass->deny_reason,
                'expires_at' => optional($recentBypass->expires_at)?->toDateTimeString(),
                'created_at' => optional($recentBypass->created_at)?->toDateTimeString(),
            ] : null,
        ]);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? $digits : null;
    }

    private function findUserByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)
            ->orWhere('phone', '55' . $phone)
            ->orWhere('phone', 'like', '%' . $phone)
            ->orderByRaw("CASE WHEN status IN ('connected', 'active', 'temp_bypass') THEN 0 ELSE 1 END")
            ->orderByDesc('connected_at')
            ->orderByDesc('updated_at')
            ->first();
    }

    private function hasActiveAccess(User $user): bool
    {
        return in_array($user->status, ['connected', 'active', 'temp_bypass'], true)
            && $user->expires_at
            && $user->expires_at->isFuture();
    }

    private function buildWarnings(?string $phone, ?string $macAddress, ?User $userByPhone, ?User $userByMac, ?User $userByIp): array
    {
        $warnings = [];

        if (! $macAddress) {
            $warnings[] = 'Nao foi possivel detectar o MAC automaticamente. Peca para o passageiro abrir esta pagina conectado ao Wi-Fi do onibus com 4G/5G desligado.';
        }

        if ($phone && ! $userByPhone) {
            $warnings[] = 'Nenhum cadastro foi encontrado com o telefone informado.';
        }

        if ($userByPhone && $userByMac && $userByPhone->isNot($userByMac)) {
            $warnings[] = 'O telefone informado esta vinculado a um cadastro diferente do dispositivo detectado agora.';
        }

        if (! $userByPhone && ! $userByMac && ! $userByIp) {
            $warnings[] = 'Nenhum usuario foi localizado por telefone, MAC ou IP.';
        }

        return $warnings;
    }

    private function buildSummary(?User $user, $latestCompletedPayment, $latestPendingPayment, ?TempBypassLog $recentBypass): array
    {
        if (! $user) {
            return [
                'status' => 'not_found',
                'headline' => 'Nenhum cadastro localizado',
                'detail' => 'Nao encontramos um cadastro associado a este telefone, MAC ou IP.',
            ];
        }

        if ($this->hasActiveAccess($user)) {
            $headline = $user->status === 'temp_bypass'
                ? 'Liberacao temporaria ativa'
                : 'Acesso liberado no sistema';

            return [
                'status' => 'active',
                'headline' => $headline,
                'detail' => 'O cadastro esta com acesso valido no sistema e expiracao futura.',
            ];
        }

        if ($latestPendingPayment) {
            return [
                'status' => 'pending_payment',
                'headline' => 'Pagamento ainda pendente',
                'detail' => 'Existe um PIX gerado para este cadastro, mas ele ainda nao foi confirmado como pago.',
            ];
        }

        if ($latestCompletedPayment) {
            return [
                'status' => 'paid_without_access',
                'headline' => 'Pagamento encontrado sem acesso ativo',
                'detail' => 'Ha pagamento confirmado para este cadastro, mas o usuario nao esta com liberacao ativa neste momento.',
            ];
        }

        if ($recentBypass && ! $recentBypass->was_denied) {
            return [
                'status' => 'bypass_recent',
                'headline' => 'Bypass recente registrado',
                'detail' => 'O sistema registrou uma liberacao temporaria recente para este cadastro.',
            ];
        }

        return [
            'status' => 'no_payment',
            'headline' => 'Sem pagamento confirmado',
            'detail' => 'Nao existe pagamento concluido vinculado ao cadastro localizado.',
        ];
    }

    private function resolveMikrotikReport(?string $ipAddress, ?string $macAddress): ?MikrotikMacReport
    {
        if ($ipAddress) {
            $report = MikrotikMacReport::where('ip_address', $ipAddress)
                ->orderByDesc('reported_at')
                ->first();

            if ($report) {
                return $report;
            }
        }

        if ($macAddress) {
            return MikrotikMacReport::where('mac_address', $macAddress)
                ->orderByDesc('reported_at')
                ->first();
        }

        return null;
    }

    private function resolveRecentBypass(?User $user, ?string $phone, ?string $macAddress): ?TempBypassLog
    {
        if (! $user && ! $phone && ! $macAddress) {
            return null;
        }

        $query = TempBypassLog::query();

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where(function ($builder) use ($phone, $macAddress) {
                if ($phone) {
                    $builder->orWhere('phone', $phone);
                }

                if ($macAddress) {
                    $builder->orWhere('mac_address', $macAddress);
                }
            });
        }

        return $query->latest()->first();
    }

    private function formatPayment($payment): array
    {
        return [
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'payment_type' => $payment->payment_type,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
            'created_at' => optional($payment->created_at)?->toDateTimeString(),
        ];
    }
}