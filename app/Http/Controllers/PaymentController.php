<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;
use App\Models\Session;

class PaymentController extends Controller
{

    /**
     * Processa pagamento PIX
     */
    public function processPix(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:4.99',
            'mac_address' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($request->mac_address, $request->ip());

            // Criar registro de pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => 'pix',
                'status' => 'pending',
                'payment_gateway' => 'mercadopago', // ou outro gateway
                'transaction_id' => $this->generateTransactionId()
            ]);

            // Simular aprovação do PIX (em produção, aguardar webhook)
            $pixApproved = $this->simulatePixPayment($payment);

            if ($pixApproved) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now()
                ]);

                // Criar sessão ativa
                $session = Session::create([
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
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
                    'message' => 'Pagamento PIX aprovado!',
                    'payment_id' => $payment->id,
                    'session_id' => $session->id
                ]);
            } else {
                $payment->update(['status' => 'failed']);
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => 'Pagamento PIX não foi aprovado. Tente novamente.'
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro no pagamento PIX: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Processa pagamento com cartão
     */
    public function processCard(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:4.99',
            'mac_address' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($request->mac_address, $request->ip());

            // Criar registro de pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => 'card',
                'status' => 'pending',
                'payment_gateway' => 'stripe', // ou outro gateway
                'transaction_id' => $this->generateTransactionId()
            ]);

            // Simular processamento do cartão
            $cardApproved = $this->simulateCardPayment($payment);

            if ($cardApproved) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now()
                ]);

                // Criar sessão ativa
                $session = Session::create([
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
                    'started_at' => now(),
                    'session_status' => 'active'
                ]);

                // Atualizar status do usuário
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addHours(24)
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pagamento aprovado!',
                    'payment_id' => $payment->id,
                    'session_id' => $session->id
                ]);
            } else {
                $payment->update(['status' => 'failed']);
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => 'Cartão recusado. Verifique os dados.'
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro no pagamento cartão: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Processa pagamento genérico
     */
    public function process(Request $request)
    {
        $method = $request->input('method');
        
        switch ($method) {
            case 'pix':
                return $this->processPix($request);
            case 'card':
                return $this->processCard($request);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Método de pagamento inválido.'
                ], 400);
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
            // Atualizar IP se mudou
            $user->update(['ip_address' => $ipAddress]);
        }

        return $user;
    }

    /**
     * Gera ID único para transação
     */
    private function generateTransactionId()
    {
        return 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    /**
     * Simula processamento PIX (em produção, integrar com gateway real)
     */
    private function simulatePixPayment($payment)
    {
        // Simular 95% de taxa de aprovação
        return rand(1, 100) <= 95;
    }

    /**
     * Simula processamento cartão (em produção, integrar com gateway real)
     */
    private function simulateCardPayment($payment)
    {
        // Simular 90% de taxa de aprovação
        return rand(1, 100) <= 90;
    }

    /**
     * Webhook para receber confirmações de pagamento
     */
    public function webhook(Request $request)
    {
        // Implementar lógica do webhook do gateway de pagamento
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if ($payment && $status === 'approved') {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now()
            ]);

            // Liberar acesso do usuário
            $payment->user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => now()->addHours(24)
            ]);
        }

        return response()->json(['success' => true]);
    }
}
