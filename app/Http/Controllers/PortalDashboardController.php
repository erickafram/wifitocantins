<?php

namespace App\Http\Controllers;

use App\Services\PixPaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function __construct(private readonly PixPaymentManager $pixPaymentManager)
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $payments = $user->payments()
            ->latest()
            ->take(10)
            ->get();

        $latestPayment = $payments->firstWhere(fn ($payment) => $payment->status === 'pending')
            ?? $payments->first();

        // Verificar se é usuário de voucher (sem pagamentos)
        $isVoucherUser = $payments->isEmpty() && $user->sessions()->whereNull('payment_id')->exists();
        
        // Buscar voucher ativo se for usuário de voucher
        $activeVoucher = null;
        $voucherStatus = null;
        
        if ($isVoucherUser) {
            // Buscar voucher pelo nome do usuário (pode ter múltiplos vouchers)
            $activeVoucher = \App\Models\Voucher::where('driver_name', $user->name)
                ->where('is_active', true)
                ->first();
                
            if ($activeVoucher) {
                $voucherStatus = $this->getVoucherStatus($activeVoucher, $user);
            }
        }

        return view('portal.dashboard', [
            'user' => $user,
            'payments' => $payments,
            'latestPayment' => $latestPayment,
            'defaultPrice' => \App\Helpers\SettingsHelper::getWifiPrice(),
            'isVoucherUser' => $isVoucherUser,
            'activeVoucher' => $activeVoucher,
            'voucherStatus' => $voucherStatus,
        ]);
    }
    
    private function getVoucherStatus(\App\Models\Voucher $voucher, $user): array
    {
        $now = now();
        $remainingHours = $voucher->getRemainingHoursToday();
        $isValid = $voucher->isValid();
        
        // Calcular tempo restante da sessão atual
        $sessionTimeLeft = null;
        if ($user->expires_at && $user->expires_at->isFuture()) {
            $sessionTimeLeft = $user->expires_at->diffInMinutes($now);
        }
        
        // Calcular quando pode usar novamente (próximo reset)
        $nextResetTime = null;
        if (!$voucher->hasHoursAvailableToday()) {
            $nextResetTime = $now->copy()->addDay()->startOfDay()->addMinute(); // 00:01 do próximo dia
        }
        
        return [
            'is_valid' => $isValid,
            'remaining_hours_today' => $remainingHours,
            'hours_used_today' => $voucher->daily_hours_used,
            'total_daily_hours' => $voucher->daily_hours,
            'session_time_left_minutes' => $sessionTimeLeft,
            'next_reset_time' => $nextResetTime,
            'voucher_type' => $voucher->voucher_type,
            'last_used_date' => $voucher->last_used_date,
            'can_use_today' => $voucher->hasHoursAvailableToday(),
        ];
    }

    public function regeneratePix(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        $user = $request->user();

        $amount = \App\Helpers\SettingsHelper::getWifiPrice();
        if ($request->filled('payment_id')) {
            $payment = $user->payments()
                ->where('id', $request->payment_id)
                ->firstOrFail();
            $amount = $payment->amount;
        }

        $result = $this->pixPaymentManager->createPixPayment(
            $user,
            $amount,
            [
                'mac_address' => $user->mac_address,
                'ip_address' => $user->ip_address,
                'cancel_previous' => true,
                'source' => 'dashboard-regenerate',
            ]
        );

        return redirect()->route('portal.dashboard')
            ->with('success', 'Novo QR Code gerado com sucesso!')
            ->with('qr_code', $result['qr_code'])
            ->with('gateway', $result['gateway']);
    }
}

