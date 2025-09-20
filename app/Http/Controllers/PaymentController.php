<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Payment;
use App\Models\Session;
use App\Services\PixQRCodeService;
use App\Services\SantanderPixService;
use App\Services\WooviPixService;

class PaymentController extends Controller
{

    /**
     * Gera QR Code PIX para pagamento
     */
    public function generatePixQRCode(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.05',
            'mac_address' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($request->mac_address, $request->ip());

            // Verificar qual gateway usar
            $gateway = config('wifi.payment_gateways.pix.gateway');

            // Criar registro de pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'payment_type' => 'pix',
                'status' => 'pending',
                'transaction_id' => $this->generateTransactionId()
            ]);
            
            if ($gateway === 'woovi' && config('wifi.payment_gateways.pix.woovi_app_id')) {
                // Usar API da Woovi
                $wooviService = new WooviPixService();
                $qrData = $wooviService->createPixPayment(
                    $request->amount,
                    'WiFi Tocantins Express - Internet Premium',
                    $payment->transaction_id
                );

                if (!$qrData['success']) {
                    throw new \Exception($qrData['message']);
                }

                // Atualizar payment com dados da Woovi
                $payment->update([
                    'pix_emv_string' => $qrData['qr_code_text'],
                    'pix_location' => $qrData['correlation_id'],
                    'gateway_payment_id' => $qrData['woovi_id']
                ]);

                // Gerar image_url baseado no tipo retornado pela Woovi
                $imageUrl = '';
                if (!empty($qrData['qr_code_image'])) {
                    if (!empty($qrData['qr_code_is_url']) && $qrData['qr_code_is_url']) {
                        // Woovi retornou uma URL direta para a imagem
                        $imageUrl = $qrData['qr_code_image'];
                        Log::info('Usando URL da Woovi para QR Code: ' . $imageUrl);
                    } else {
                        // Woovi retornou base64
                        $imageUrl = 'data:image/png;base64,' . $qrData['qr_code_image'];
                        Log::info('Usando base64 da Woovi para QR Code');
                    }
                } else {
                    // Fallback: usar API externa para gerar QR Code
                    $encodedEmv = urlencode($qrData['qr_code_text']);
                    $imageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedEmv}";
                    Log::info('Usando fallback para QR Code image: ' . $imageUrl);
                }

                $response = [
                    'emv_string' => $qrData['qr_code_text'],
                    'image_url' => $imageUrl,
                    'amount' => number_format($qrData['amount'], 2, '.', ''),
                    'transaction_id' => $qrData['correlation_id'],
                    'payment_id' => $qrData['woovi_id'],
                    'payment_link' => $qrData['payment_link'],
                    'expires_at' => $qrData['expires_at']
                ];
                
            } elseif ($gateway === 'santander' && config('wifi.payment_gateways.pix.client_id')) {
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
            'amount' => 'required|numeric|min:0.05',
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
                'payment_type' => 'card',
                'status' => 'pending',
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
            $this->activateUserAccess($payment);
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

                    // Liberar acesso do usuário automaticamente
                    $this->activateUserAccess($payment);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro no webhook Santander: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Webhook específico da Woovi - MELHORADO
     */
    public function wooviWebhook(Request $request)
    {
        $startTime = microtime(true);
        
        Log::info('🔔 Webhook Woovi MELHORADO recebido', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            $webhookData = $request->all();
            
            // Processar webhook com serviço melhorado
            $wooviService = new WooviPixService();
            $result = $wooviService->processWebhook($webhookData);
            
            Log::info('📊 Resultado do processamento Woovi', [
                'success' => $result['success'],
                'payment_approved' => $result['payment_approved'] ?? false,
                'correlation_id' => $result['correlation_id'] ?? 'N/A',
                'woovi_id' => $result['woovi_id'] ?? 'N/A'
            ]);
            
            if ($result['success'] && $result['payment_approved']) {
                
                DB::beginTransaction();
                
                try {
                    // Buscar pagamento com múltiplas estratégias
                    $payment = Payment::where('pix_location', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['woovi_id'])
                        ->orWhere('transaction_id', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['correlation_id'])
                        ->first();
                    
                    if (!$payment) {
                        Log::warning('❌ Pagamento não encontrado', [
                            'correlation_id' => $result['correlation_id'],
                            'woovi_id' => $result['woovi_id']
                        ]);
                        
                        DB::rollback();
                        return response()->json([
                            'success' => false, 
                            'message' => 'Pagamento não encontrado'
                        ], 404);
                    }

                    Log::info('💳 Pagamento encontrado', [
                        'payment_id' => $payment->id,
                        'current_status' => $payment->status,
                        'user_mac' => $payment->user->mac_address ?? 'N/A'
                    ]);

                    // Só processar se ainda está pendente
                    if ($payment->status === 'pending') {
                        
                        // Atualizar pagamento
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => $result['paid_at'] ?? now(),
                            'payment_data' => $webhookData
                        ]);

                        // Ativar acesso do usuário
                        $this->activateUserAccess($payment);
                        
                        DB::commit();
                        
                        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

                        Log::info('✅ Pagamento Woovi processado com SUCESSO', [
                            'payment_id' => $payment->id,
                            'user_mac' => $payment->user->mac_address,
                            'expires_at' => $payment->user->expires_at,
                            'total_processing_time' => $processingTime . 'ms'
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Pagamento processado com sucesso',
                            'processing_time' => $processingTime . 'ms'
                        ]);
                        
                    } else {
                        DB::rollback();
                        Log::info('ℹ️ Pagamento já processado anteriormente', [
                            'payment_id' => $payment->id,
                            'status' => $payment->status
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            // Webhook recebido mas não processado
            return response()->json(['success' => true, 'message' => 'Webhook recebido']);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }
            
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('❌ ERRO CRÍTICO no webhook Woovi', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'processing_time' => $processingTime . 'ms',
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Erro interno do servidor',
                'processing_time' => $processingTime . 'ms'
            ], 500);
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

    /**
     * Testar conexão com Woovi
     */
    public function testWooviConnection()
    {
        try {
            $wooviService = new WooviPixService();
            $result = $wooviService->testConnection();
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativa o acesso do usuário e libera no MikroTik - MELHORADO
     */
    private function activateUserAccess(Payment $payment)
    {
        $startTime = microtime(true);
        
        try {
            Log::info('🔓 Iniciando ativação de acesso do usuário', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'mac_address' => $payment->user->mac_address
            ]);

            // Criar sessão ativa
            $session = Session::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'started_at' => now(),
                'session_status' => 'active'
            ]);

            Log::info('✅ Sessão criada', ['session_id' => $session->id]);

            // Atualizar status do usuário com duração configurável
            $sessionDurationHours = config('wifi.pricing.session_duration_hours', 24);
            $expiresAt = now()->addHours($sessionDurationHours);
            
            $payment->user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => $expiresAt
            ]);

            Log::info('✅ Status do usuário atualizado', [
                'status' => 'connected',
                'expires_at' => $expiresAt->toISOString(),
                'duration_hours' => $sessionDurationHours
            ]);

            // Tentar liberar no MikroTik
            try {
                if (class_exists('\App\Http\Controllers\MikrotikController')) {
                    $mikrotikController = new \App\Http\Controllers\MikrotikController();
                    $result = $mikrotikController->allowDeviceByUser($payment->user);

                    if ($result) {
                        Log::info('🌐 Usuário liberado no MikroTik IMEDIATAMENTE', [
                            'mac_address' => $payment->user->mac_address,
                            'result' => $result,
                            'success' => true
                        ]);
                    } else {
                        Log::warning('⚠️ Falha ao liberar no MikroTik - será liberado no próximo sync', [
                            'mac_address' => $payment->user->mac_address
                        ]);
                    }
                } else {
                    Log::info('ℹ️ MikroTik Controller não disponível - usuário será liberado no próximo sync');
                }
                
            } catch (\Exception $e) {
                Log::warning('⚠️ Falha ao liberar no MikroTik imediatamente', [
                    'error' => $e->getMessage(),
                    'mac_address' => $payment->user->mac_address,
                    'note' => 'Usuário será liberado no próximo sync automático (1 minuto)'
                ]);
                // Não falhar a ativação por causa do MikroTik
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('🎉 Acesso do usuário ativado com SUCESSO', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'mac_address' => $payment->user->mac_address,
                'session_id' => $session->id,
                'expires_at' => $expiresAt->toISOString(),
                'processing_time' => $processingTime . 'ms'
            ]);

        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('❌ ERRO CRÍTICO ao ativar acesso do usuário', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'mac_address' => $payment->user->mac_address ?? 'N/A',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'processing_time' => $processingTime . 'ms',
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw para não perder o erro
        }
    }
}
