<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use App\Services\PixQRCodeService;
use App\Services\SantanderPixService;

class PaymentController extends Controller
{

    /**
     * Gera QR Code PIX para pagamento
     */
    public function generatePixQRCode(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5.99',
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
                'payment_gateway' => 'santander',
                'transaction_id' => $this->generateTransactionId()
            ]);

            // Verificar se deve usar Santander ou gerador manual
            $gateway = config('wifi.payment_gateways.pix.gateway');
            
            if ($gateway === 'santander' && config('wifi.payment_gateways.pix.client_id')) {
                // Usar API do Santander
                $santanderService = new SantanderPixService();
                $qrData = $santanderService->createPixPayment(
                    $request->amount,
                    'WiFi Tocantins Express - Internet',
                    $payment->transaction_id
                );

                if (!$qrData['success']) {
                    throw new \Exception($qrData['message']);
                }

                // Atualizar payment com dados do Santander
                $payment->update([
                    'pix_emv_string' => $qrData['qr_code_text'],
                    'pix_location' => $qrData['external_id'],
                    'gateway_payment_id' => $qrData['payment_id']
                ]);

                $response = [
                    'emv_string' => $qrData['qr_code_text'],
                    'image_url' => $santanderService->generateQRCodeImageUrl($qrData['qr_code_text']),
                    'amount' => number_format($qrData['amount'], 2, '.', ''),
                    'transaction_id' => $qrData['external_id'],
                    'payment_id' => $qrData['payment_id']
                ];
            } else {
                // Fallback: Usar gerador EMV manual
                $pixService = new PixQRCodeService();
                $qrData = $pixService->generatePixQRCode($request->amount, $payment->transaction_id);

                // Atualizar payment com dados do PIX
                $payment->update([
                    'pix_emv_string' => $qrData['emv_string'],
                    'pix_location' => $qrData['location']
                ]);

                $response = [
                    'emv_string' => $qrData['emv_string'],
                    'image_url' => $pixService->generateQRCodeImageUrl($qrData['emv_string']),
                    'amount' => $qrData['amount'],
                    'transaction_id' => $qrData['transaction_id']
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QR Code PIX gerado com sucesso!',
                'payment_id' => $payment->id,
                'gateway' => $gateway,
                'qr_code' => $response
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao gerar QR Code PIX: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar QR Code PIX: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa pagamento PIX (mantido para compatibilidade)
     */
    public function processPix(Request $request)
    {
        // Redirecionar para geração de QR Code
        return $this->generatePixQRCode($request);
    }

    /**
     * Processa pagamento com cartão
     */
    public function processCard(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5.99',
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

    /**
     * Verificar status do pagamento PIX
     */
    public function checkPixStatus(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id'
        ]);

        $payment = Payment::find($request->payment_id);
        
        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'created_at' => $payment->created_at,
                'paid_at' => $payment->paid_at
            ]
        ]);
    }

    /**
     * Webhook específico do Santander
     */
    public function santanderWebhook(Request $request)
    {
        try {
            $webhookData = $request->all();
            
            $santanderService = new SantanderPixService();
            $result = $santanderService->processWebhook($webhookData);
            
            if ($result['success'] && $result['payment_approved']) {
                // Buscar pagamento pelo gateway_payment_id
                $payment = Payment::where('gateway_payment_id', $result['payment_id'])->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'paid_at' => $result['paid_at']
                    ]);

                    // Criar sessão ativa
                    Session::create([
                        'user_id' => $payment->user_id,
                        'payment_id' => $payment->id,
                        'started_at' => now(),
                        'session_status' => 'active'
                    ]);

                    // Liberar acesso do usuário
                    $payment->user->update([
                        'status' => 'connected',
                        'connected_at' => now(),
                        'expires_at' => now()->addHours(24)
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro no webhook Santander: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Testar conexão com Santander
     */
    public function testSantanderConnection()
    {
        try {
            $santanderService = new SantanderPixService();
            $result = $santanderService->testConnection();
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
