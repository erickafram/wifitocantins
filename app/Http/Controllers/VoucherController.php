<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Session;

class VoucherController extends Controller
{
    /**
     * Aplica um voucher
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
            'mac_address' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Buscar voucher
            $voucher = Voucher::where('code', $request->code)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de voucher não encontrado.'
                ], 404);
            }

            if (!$voucher->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher expirado ou já utilizado.'
                ], 400);
            }

            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($request->mac_address, $request->ip());

            // Usar o voucher
            if (!$voucher->use()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao aplicar voucher.'
                ], 400);
            }

            // Criar sessão ativa
            $session = Session::create([
                'user_id' => $user->id,
                'payment_id' => null, // Sem pagamento para voucher
                'started_at' => now(),
                'session_status' => 'active'
            ]);

            // Atualizar status do usuário
            $user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => now()->addHours(24) // 24h de acesso
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher aplicado com sucesso!',
                'session_id' => $session->id,
                'expires_at' => $user->expires_at
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao aplicar voucher: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Valida um voucher sem aplicá-lo
     */
    public function validate($code)
    {
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher não encontrado.'
            ]);
        }

        if (!$voucher->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher expirado ou já utilizado.',
                'details' => [
                    'expires_at' => $voucher->expires_at,
                    'used_count' => $voucher->used_count,
                    'max_uses' => $voucher->max_uses,
                    'is_active' => $voucher->is_active
                ]
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Voucher válido.',
            'details' => [
                'description' => $voucher->description,
                'discount' => $voucher->discount,
                'discount_percent' => $voucher->discount_percent,
                'expires_at' => $voucher->expires_at,
                'remaining_uses' => $voucher->max_uses - $voucher->used_count
            ]
        ]);
    }

    /**
     * Cria um novo voucher (para admins)
     */
    public function create(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:vouchers,code',
            'description' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'required|integer|min:1'
        ]);

        try {
            $voucher = Voucher::create([
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'discount' => $request->discount,
                'discount_percent' => $request->discount_percent,
                'expires_at' => $request->expires_at,
                'max_uses' => $request->max_uses,
                'used_count' => 0,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher criado com sucesso!',
                'voucher' => $voucher
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao criar voucher: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar voucher.'
            ], 500);
        }
    }

    /**
     * Gera vouchers em lote
     */
    public function generateBatch(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'required|integer|min:1'
        ]);

        try {
            $vouchers = [];
            $prefix = $request->prefix ?? 'WIFI';

            for ($i = 0; $i < $request->quantity; $i++) {
                $code = $this->generateVoucherCode($prefix);
                
                $voucher = Voucher::create([
                    'code' => $code,
                    'description' => $request->description ?? 'Voucher gerado automaticamente',
                    'discount' => null,
                    'discount_percent' => 100, // Acesso gratuito
                    'expires_at' => $request->expires_at,
                    'max_uses' => $request->max_uses,
                    'used_count' => 0,
                    'is_active' => true
                ]);

                $vouchers[] = $voucher;
            }

            return response()->json([
                'success' => true,
                'message' => "Criados {$request->quantity} vouchers com sucesso!",
                'vouchers' => $vouchers
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar vouchers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar vouchers.'
            ], 500);
        }
    }

    /**
     * Busca ou cria usuário baseado no MAC address
     */
    private function findOrCreateUser($macAddress, $ipAddress)
    {
        $user = User::where('mac_address', $macAddress)->first();

        if (!$user) {
            $user = User::create([
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'status' => 'offline'
            ]);
        } else {
            $user->update(['ip_address' => $ipAddress]);
        }

        return $user;
    }

    /**
     * Gera código único para voucher
     */
    private function generateVoucherCode($prefix = 'WIFI')
    {
        do {
            $code = $prefix . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}
