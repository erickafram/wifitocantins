<?php

namespace App\Http\Controllers;

use App\Models\MikrotikMacReport;
use App\Models\Payment;
use App\Models\Session;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PixQRCodeService;
use App\Services\SantanderPixService;
use App\Services\WooviPixService;
use App\Support\HotspotIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Gera QR Code PIX para pagamento
     */
    public function generatePixQRCode(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.05',
            'mac_address' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'ip_address' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 🔥 FORÇAR report de MAC real antes de criar pagamento
            $this->forceMacReport($request);

            // 🎯 BUSCAR OU CRIAR USUÁRIO COM MAC
            $clientIp = HotspotIdentity::resolveClientIp($request);
            $macAddress = HotspotIdentity::resolveRealMac($request->input('mac_address'), $clientIp);

            if (! $macAddress) {
                Log::warning('⚠️ MAC inválido ou ausente no request', [
                    'mac_address' => $request->input('mac_address'),
                    'ip' => $clientIp,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível identificar o dispositivo. Tente reconectar ao Wi-Fi.',
                ], 422);
            }

            // Se tem user_id, usar usuário existente
            if ($request->user_id) {
                $user = User::find($request->user_id);
                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuário não encontrado',
                    ], 404);
                }

                // Atualizar MAC e IP do usuário se ainda não tem
                $userUpdates = [];
                if (HotspotIdentity::shouldReplaceMac($user->mac_address, $macAddress)) {
                    $userUpdates['mac_address'] = $macAddress;
                }
                if ($clientIp && $user->ip_address !== $clientIp) {
                    $userUpdates['ip_address'] = $clientIp;
                }
                if (! empty($userUpdates)) {
                    $user->update([
                        ...$userUpdates,
                        'status' => $user->status === 'connected' ? $user->status : 'offline',
                    ]);
                }
            } else {
                // Buscar ou criar usuário pelo MAC
                $user = $this->findOrCreateUser($macAddress, $clientIp);
            }

            // Verificar qual gateway usar
            $gateway = SystemSetting::getValue('pix_gateway', config('wifi.payment_gateways.pix.gateway'));

            // Criar registro de pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'payment_type' => 'pix',
                'status' => 'pending',
                'transaction_id' => $this->generateTransactionId(),
            ]);

            if ($gateway === 'woovi' && config('wifi.payment_gateways.pix.woovi_app_id')) {
                // Usar API da Woovi
                $wooviService = new WooviPixService;
                $qrData = $wooviService->createPixPayment(
                    $request->amount,
                    'WiFi Tocantins Express - Internet Premium',
                    $payment->transaction_id
                );

                if (! $qrData['success']) {
                    throw new \Exception($qrData['message']);
                }

                // Atualizar payment com dados da Woovi
                $payment->update([
                    'pix_emv_string' => $qrData['qr_code_text'],
                    'pix_location' => $qrData['correlation_id'],
                    'gateway_payment_id' => $qrData['woovi_id'],
                ]);

                // Gerar image_url baseado no tipo retornado pela Woovi
                $imageUrl = '';
                if (! empty($qrData['qr_code_image'])) {
                    if (! empty($qrData['qr_code_is_url']) && $qrData['qr_code_is_url']) {
                        // Woovi retornou uma URL direta para a imagem
                        $imageUrl = $qrData['qr_code_image'];
                        Log::info('Usando URL da Woovi para QR Code: '.$imageUrl);
                    } else {
                        // Woovi retornou base64
                        $imageUrl = 'data:image/png;base64,'.$qrData['qr_code_image'];
                        Log::info('Usando base64 da Woovi para QR Code');
                    }
                } else {
                    // Fallback: usar API externa para gerar QR Code
                    $encodedEmv = urlencode($qrData['qr_code_text']);
                    $imageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedEmv}";
                    Log::info('Usando fallback para QR Code image: '.$imageUrl);
                }

                $response = [
                    'emv_string' => $qrData['qr_code_text'],
                    'image_url' => $imageUrl,
                    'amount' => number_format($qrData['amount'], 2, '.', ''),
                    'transaction_id' => $qrData['correlation_id'],
                    'payment_id' => $qrData['woovi_id'],
                    'payment_link' => $qrData['payment_link'],
                    'expires_at' => $qrData['expires_at'],
                ];

            } elseif ($gateway === 'pagbank' && config('wifi.payment_gateways.pix.pagbank_token')) {
                // Usar API do PagBank
                $pagbankService = new \App\Services\PagBankPixService;
                $qrData = $pagbankService->createPixPayment(
                    $request->amount,
                    'WiFi Tocantins Express - Internet Premium',
                    $payment->transaction_id
                );

                if (! $qrData['success']) {
                    throw new \Exception($qrData['message'] ?? 'Erro ao criar pagamento PagBank');
                }

                Log::info('✅ QR Code PagBank gerado', [
                    'order_id' => $qrData['order_id'] ?? null,
                    'reference_id' => $qrData['reference_id'] ?? null,
                ]);

                // Atualizar payment com dados do PagBank
                $payment->update([
                    'pix_emv_string' => $qrData['qr_code_text'],
                    'pix_location' => $qrData['reference_id'],
                    'gateway_payment_id' => $qrData['order_id'],
                ]);

                $response = [
                    'emv_string' => $qrData['qr_code_text'],
                    'image_url' => $qrData['qr_code_image'],
                    'amount' => number_format($qrData['amount'], 2, '.', ''),
                    'transaction_id' => $qrData['reference_id'],
                    'payment_id' => $qrData['order_id'],
                    'expires_at' => $qrData['expires_at'] ?? null,
                ];

            } elseif ($gateway === 'santander' && config('wifi.payment_gateways.pix.client_id')) {
                // Usar API do Santander PIX
                $santanderService = new SantanderPixService;
                $qrData = $santanderService->createPixPayment(
                    $request->amount,
                    'WiFi Tocantins Express - Internet Premium',
                    $payment->transaction_id // Usar o TXId gerado pelo sistema (já tem 30 caracteres)
                );

                if (!$qrData['success']) {
                    throw new \Exception($qrData['message'] ?? 'Erro ao criar pagamento Santander');
                }

                // Atualizar transaction_id com o TXId do Santander
                $payment->transaction_id = $qrData['txid'];
                $payment->save();

                Log::info('✅ QR Code Santander gerado', [
                    'txid' => $qrData['txid'] ?? null,
                    'location' => $qrData['location'] ?? null,
                    'status' => $qrData['status'] ?? null,
                ]);

                // Atualizar payment com dados do Santander
                $payment->update([
                    'pix_emv_string' => $qrData['qr_code_text'],
                    'pix_location' => $qrData['location'],
                    'gateway_payment_id' => $qrData['txid'], // TXId é o identificador único
                ]);

                $response = [
                    'emv_string' => $qrData['qr_code_text'],
                    'image_url' => $qrData['qr_code_image'] ?? $santanderService->generateQRCodeImageUrl($qrData['qr_code_text']),
                    'amount' => number_format($qrData['amount'], 2, '.', ''),
                    'transaction_id' => $payment->transaction_id,
                    'payment_id' => $payment->id,
                    'txid' => $qrData['txid'], // TXId Santander
                    'location' => $qrData['location'], // Location Santander
                    'expires_in' => $qrData['expiration'] ?? 900, // Segundos até expirar
                ];

            } else {
                // Fallback: Usar gerador EMV manual
                $pixService = new PixQRCodeService;
                $qrData = $pixService->generatePixQRCode($request->amount, $payment->transaction_id);

                // Atualizar payment com dados do PIX
                $payment->update([
                    'pix_emv_string' => $qrData['emv_string'],
                    'pix_location' => $qrData['location'],
                ]);

                $response = [
                    'emv_string' => $qrData['emv_string'],
                    'image_url' => $pixService->generateQRCodeImageUrl($qrData['emv_string']),
                    'amount' => $qrData['amount'],
                    'transaction_id' => $qrData['transaction_id'],
                ];
            }

            DB::commit();

            // 🎯 LOG COMPLETO DO PAGAMENTO CRIADO
            Log::info('💳 PAGAMENTO PIX CRIADO COM SUCESSO', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'mac_address' => $user->mac_address,
                'ip_address' => $user->ip_address,
                'amount' => $request->amount,
                'gateway' => $gateway,
                'transaction_id' => $payment->transaction_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR Code PIX gerado com sucesso!',
                'payment_id' => $payment->id,
                'gateway' => $gateway,
                'qr_code' => $response,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao gerar QR Code PIX: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar QR Code PIX: '.$e->getMessage(),
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
            'mac_address' => 'required|string',
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
                'transaction_id' => $this->generateTransactionId(),
            ]);

            // REMOVIDO: Simulação de cartão que aprovava automaticamente
            // Cartão deve aguardar confirmação real do gateway de pagamento
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pagamento de cartão criado. Aguardando confirmação do gateway.',
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => 'pending',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro no pagamento cartão: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.',
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
                    'message' => 'Método de pagamento inválido.',
                ], 400);
        }
    }

    /**
     * Busca ou cria usuário baseado no MAC address - CORRIGIDO PARA EVITAR DUPLICATAS
     */
    private function findOrCreateUser($macAddress, $ipAddress)
    {
        $normalizedMac = HotspotIdentity::resolveRealMac($macAddress, $ipAddress);

        Log::info('🔍 BUSCAR/CRIAR USUÁRIO', [
            'mac_address' => $normalizedMac,
            'ip_address' => $ipAddress,
        ]);

        // 1. PRIORIDADE: Buscar usuário por MAC address
        $user = User::where('mac_address', $normalizedMac)->first();

        if ($user) {
            // Usuário já existe com este MAC - atualizar IP se necessário
            if ($ipAddress && $user->ip_address !== $ipAddress) {
                $user->update(['ip_address' => $ipAddress]);
            }
            Log::info('✅ Usuário encontrado por MAC', ['user_id' => $user->id, 'name' => $user->name]);

            return $user;
        }

        // 2. SEGUNDA CHANCE: Buscar usuário pendente sem MAC (qualquer IP recente)
        $pendingUser = User::whereNull('mac_address')
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(10))
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. TERCEIRA CHANCE: Buscar usuário pendente pelo IP (mesmo com MAC diferente)
        if (! $pendingUser) {
            $pendingUser = User::where('ip_address', $ipAddress)
                ->where('status', 'pending')
                ->where('created_at', '>', now()->subMinutes(10))
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if ($pendingUser) {
            // Atualizar usuário existente com o MAC
            $pendingUser->update([
                'mac_address' => $normalizedMac,
                'ip_address' => $ipAddress,
                'status' => 'offline',
            ]);
            Log::info('✅ Usuário pendente atualizado com MAC', [
                'user_id' => $pendingUser->id,
                'name' => $pendingUser->name,
                'mac_added' => $normalizedMac,
            ]);

            return $pendingUser;
        }

        // 4. ÚLTIMA OPÇÃO: Criar novo usuário
        $userData = [
            'mac_address' => $normalizedMac,
            'status' => 'offline',
        ];

        if ($ipAddress) {
            $userData['ip_address'] = $ipAddress;
        }

        $user = User::create($userData);

        Log::info('🆕 Novo usuário criado', ['user_id' => $user->id, 'mac_address' => $normalizedMac]);

        return $user;
    }

    /**
     * Gera ID único para transação
     * Formato: TXN_timestamp_hash (26-35 caracteres para Santander)
     */
    private function generateTransactionId()
    {
        // Gerar ID com 30 caracteres (dentro do range 26-35 exigido pelo Santander)
        // Formato: TXN_1234567890_ABCDEF123456 (30 chars)
        $timestamp = time();
        $hash = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
        
        return 'TXN_' . $timestamp . '_' . $hash;
    }

    /**
     * Força report de MAC real do MikroTik antes do pagamento
     */
    private function forceMacReport(Request $request)
    {
        try {
            // Simular request para MikroTik executar script de report
            $mikrotikUrl = 'http://mikrotik.local/rest/system/script/run';
            $macAddress = $request->mac_address;

            $clientIp = HotspotIdentity::resolveClientIp($request);
            $realMac = HotspotIdentity::resolveRealMac($macAddress, $clientIp);

            Log::info('🔥 FORÇANDO REPORT DE MAC', [
                'mac_address' => $realMac,
                'ip_address' => $clientIp,
                'note' => 'Report forçado antes do pagamento',
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao forçar report MAC: '.$e->getMessage());
            // Não falhar o pagamento por causa disso
        }
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
                'paid_at' => now(),
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
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::find($request->payment_id);

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'created_at' => $payment->created_at,
                'paid_at' => $payment->paid_at,
            ],
        ]);
    }

    /**
     * Webhook específico do Santander PIX
     * Documentação: Portal do Desenvolvedor > Gerenciamento de notificações via Webhook
     */
    public function santanderWebhook(Request $request)
    {
        try {
            Log::info('🔔 Webhook Santander recebido', [
                'timestamp' => now()->toISOString(),
                'ip' => $request->ip(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);

            // Santander pode enviar GET para validação da URL
            if ($request->isMethod('get')) {
                Log::info('✅ Validação GET do webhook Santander');
                return response()->json(['success' => true]);
            }

            $webhookData = $request->all();

            $santanderService = new SantanderPixService;
            $result = $santanderService->processWebhook($webhookData);

            if ($result['success'] && ($result['payment_confirmed'] ?? false)) {
                // Buscar pagamento pelo TXId (gateway_payment_id)
                $txid = $result['txid'] ?? null;
                
                if (!$txid) {
                    Log::warning('⚠️ Webhook sem TXId', ['result' => $result]);
                    return response()->json(['success' => true, 'message' => 'TXId não encontrado']);
                }

                $payment = Payment::where('gateway_payment_id', $txid)
                    ->orWhere('transaction_id', $txid)
                    ->first();

                if ($payment) {
                    Log::info('💰 Pagamento Santander confirmado', [
                        'payment_id' => $payment->id,
                        'txid' => $txid,
                        'e2eid' => $result['e2eid'] ?? null,
                        'amount' => $result['amount'] ?? null,
                    ]);

                    $payment->update([
                        'status' => 'completed',
                        'paid_at' => $result['paid_at'] ?? now(),
                    ]);

                    // Liberar acesso do usuário automaticamente
                    $this->activateUserAccess($payment);
                } else {
                    Log::warning('⚠️ Pagamento não encontrado para o TXId', ['txid' => $txid]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('❌ Erro no webhook Santander: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook com serviço melhorado
            $wooviService = new WooviPixService;
            $result = $wooviService->processWebhook($webhookData);

            if (! ($result['success'] ?? false)) {
                Log::warning('⚠️ Webhook Woovi rejeitado', [
                    'reason' => $result['message'] ?? 'Erro desconhecido',
                    'correlation_id' => $webhookData['charge']['correlationID'] ?? null,
                    'event' => $webhookData['event'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Webhook rejeitado',
                ], 400);
            }

            if (! ($result['payment_approved'] ?? false)) {
                Log::info('⏳ Webhook Woovi recebido, mas sem confirmação', [
                    'event' => $webhookData['event'] ?? null,
                    'status' => $result['status'] ?? null,
                    'correlation_id' => $result['correlation_id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pagamento ainda não confirmado na Woovi',
                    'pending' => true,
                ], 202);
            }

            Log::info('📊 Resultado do processamento Woovi', [
                'success' => $result['success'],
                'payment_approved' => $result['payment_approved'],
                'event' => $webhookData['event'] ?? null,
                'status' => $result['status'] ?? null,
                'correlation_id' => $result['correlation_id'] ?? 'N/A',
                'woovi_id' => $result['woovi_id'] ?? 'N/A',
            ]);

            if ($result['payment_approved']) {

                DB::beginTransaction();

                try {
                    // Buscar pagamento com múltiplas estratégias
                    $payment = Payment::where('pix_location', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['woovi_id'])
                        ->orWhere('transaction_id', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['correlation_id'])
                        ->first();

                    if (! $payment) {
                        Log::warning('❌ Pagamento não encontrado', [
                            'correlation_id' => $result['correlation_id'],
                            'woovi_id' => $result['woovi_id'],
                        ]);

                        DB::rollback();

                        return response()->json([
                            'success' => false,
                            'message' => 'Pagamento não encontrado',
                        ], 404);
                    }

                    Log::info('💳 Pagamento encontrado', [
                        'payment_id' => $payment->id,
                        'current_status' => $payment->status,
                        'user_mac' => $payment->user->mac_address ?? 'N/A',
                    ]);

                    // Só processar se ainda está pendente
                    if ($payment->status === 'pending') {

                        // Verificar se não é webhook duplicado (mesmo correlation_id processado recentemente)
                        $recentProcessed = Payment::where('gateway_payment_id', $result['correlation_id'])
                            ->where('status', 'completed')
                            ->where('updated_at', '>', now()->subMinutes(5))
                            ->count();

                        if ($recentProcessed > 0) {
                            DB::rollback();
                            Log::warning('⚠️ Webhook duplicado detectado', [
                                'correlation_id' => $result['correlation_id'],
                                'payment_id' => $payment->id,
                                'recent_processed_count' => $recentProcessed,
                            ]);

                            return response()->json([
                                'success' => true,
                                'message' => 'Webhook duplicado - já processado',
                                'duplicate' => true,
                            ]);
                        }

                        // Atualizar pagamento
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => $result['paid_at'] ?? now(),
                            'payment_data' => $webhookData,
                        ]);

                        // Ativar acesso do usuário
                        $this->activateUserAccess($payment);

                        DB::commit();

                        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

                        Log::info('✅ Pagamento Woovi processado com SUCESSO', [
                            'payment_id' => $payment->id,
                            'user_mac' => $payment->user->mac_address,
                            'expires_at' => $payment->user->expires_at,
                            'total_processing_time' => $processingTime.'ms',
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Pagamento processado com sucesso',
                            'processing_time' => $processingTime.'ms',
                        ]);

                    } else {
                        DB::rollback();
                        Log::info('ℹ️ Pagamento já processado anteriormente', [
                            'payment_id' => $payment->id,
                            'status' => $payment->status,
                            'paid_at' => $payment->paid_at,
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Pagamento já processado anteriormente',
                            'already_processed' => true,
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
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Testar conexão com Santander
     */
    public function testSantanderConnection()
    {
        try {
            $santanderService = new SantanderPixService;
            $result = $santanderService->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Testar conexão com Woovi
     */
    public function testWooviConnection()
    {
        try {
            $wooviService = new WooviPixService;
            $result = $wooviService->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ativa o acesso do usuário e libera no MikroTik - MELHORADO COM LIBERAÇÃO IMEDIATA
     */
    private function activateUserAccess(Payment $payment)
    {
        $startTime = microtime(true);

        try {
            Log::info('🔓 Iniciando ativação de acesso do usuário', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'mac_address' => $payment->user->mac_address,
            ]);

            // 🎯 REGISTRAR MAC NA TABELA MIKROTIK_MAC_REPORTS AUTOMATICAMENTE
            if ($payment->user->mac_address && $payment->user->ip_address) {
                try {
                    MikrotikMacReport::updateOrCreate(
                        [
                            'ip_address' => $payment->user->ip_address,
                            'mac_address' => $payment->user->mac_address,
                        ],
                        [
                            'transaction_id' => $payment->transaction_id,
                            'mikrotik_ip' => null, // Será preenchido quando MikroTik reportar
                            'reported_at' => now(),
                        ]
                    );

                    Log::info('✅ MAC registrado automaticamente na tabela mikrotik_mac_reports', [
                        'mac_address' => $payment->user->mac_address,
                        'ip_address' => $payment->user->ip_address,
                        'transaction_id' => $payment->transaction_id,
                        'reason' => 'payment_approved',
                    ]);
                } catch (\Exception $e) {
                    Log::error('❌ Erro ao registrar MAC automaticamente', [
                        'error' => $e->getMessage(),
                        'mac_address' => $payment->user->mac_address,
                        'ip_address' => $payment->user->ip_address,
                    ]);
                }
            } else {
                Log::warning('⚠️ Usuário sem MAC ou IP - não é possível registrar no mikrotik_mac_reports', [
                    'user_id' => $payment->user_id,
                    'mac_address' => $payment->user->mac_address,
                    'ip_address' => $payment->user->ip_address,
                ]);

                // 🔥 TENTAR RECUPERAR MAC DOS DADOS DO PAGAMENTO
                if (! $payment->user->mac_address && isset($payment->payment_data['mac_address'])) {
                    $recoveredMac = $payment->payment_data['mac_address'];
                    $payment->user->update(['mac_address' => $recoveredMac]);

                    Log::info('🔧 MAC recuperado dos dados do pagamento', [
                        'user_id' => $payment->user_id,
                        'recovered_mac' => $recoveredMac,
                    ]);
                }
            }

            // Criar sessão ativa
            $session = Session::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'started_at' => now(),
                'session_status' => 'active',
            ]);

            Log::info('✅ Sessão criada', ['session_id' => $session->id]);

            // Atualizar status do usuário com duração configurável
            $sessionDurationConfig = config('wifi.pricing.session_duration_hours', 12);
            $sessionDurationHours = 12;

            if (is_numeric($sessionDurationConfig)) {
                $sessionDurationHours = max((float) $sessionDurationConfig, 0.1);
            } elseif (is_string($sessionDurationConfig)) {
                if (preg_match('/\d+(?:[\.,]\d+)?/', $sessionDurationConfig, $matches)) {
                    $sessionDurationHours = max((float) str_replace(',', '.', $matches[0]), 0.1);
                }
            }

            $expiresAt = now()->addHours($sessionDurationHours);

            $payment->user->update([
                'status' => 'connected',
                'connected_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            Log::info('✅ Status do usuário atualizado', [
                'status' => 'connected',
                'expires_at' => $expiresAt->toISOString(),
                'duration_hours' => $sessionDurationHours,
                'mac_address' => $payment->user->mac_address,
            ]);

            // 🚀 LIBERAÇÃO IMEDIATA NO MIKROTIK VIA WEBHOOK
            try {
                // Usar o novo serviço de webhook
                $webhookService = new \App\Services\MikrotikWebhookService;
                $liberado = $webhookService->liberarMacAddress($payment->user->mac_address);

                if ($liberado) {
                    Log::info('🎉 ACESSO LIBERADO NO MIKROTIK VIA WEBHOOK COM SUCESSO!', [
                        'user_id' => $payment->user_id,
                        'mac_address' => $payment->user->mac_address,
                        'expires_at' => $expiresAt->toISOString(),
                        'method' => 'webhook_direct',
                    ]);
                } else {
                    // Tentar método antigo como fallback
                    try {
                        $liberacaoController = new \App\Http\Controllers\MikrotikLiberacaoController;
                        $liberado = $liberacaoController->liberarAcessoImediato($payment->user_id);

                        if ($liberado) {
                            Log::info('✅ Liberado via método fallback', [
                                'user_id' => $payment->user_id,
                            ]);
                        } else {
                            Log::warning('⚠️ Falha na liberação automática do MikroTik', [
                                'user_id' => $payment->user_id,
                                'note' => 'O acesso será liberado na próxima sincronização',
                            ]);
                        }
                    } catch (\Exception $fallbackError) {
                        Log::warning('⚠️ Métodos de liberação falharam', [
                            'error' => $fallbackError->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('❌ Erro ao liberar no MikroTik via webhook', [
                    'error' => $e->getMessage(),
                    'user_id' => $payment->user_id,
                ]);
                // Não falhar o pagamento por causa disso
            }

            // Tentar liberar no MikroTik
            try {
                if (class_exists('\App\Http\Controllers\MikrotikController')) {
                    $mikrotikController = new \App\Http\Controllers\MikrotikController;
                    $result = $mikrotikController->allowDeviceByUser($payment->user);

                    if ($result) {
                        Log::info('🌐 Usuário liberado no MikroTik IMEDIATAMENTE', [
                            'mac_address' => $payment->user->mac_address,
                            'result' => $result,
                            'success' => true,
                        ]);
                    } else {
                        Log::warning('⚠️ Falha ao liberar no MikroTik - será liberado no próximo sync', [
                            'mac_address' => $payment->user->mac_address,
                        ]);
                    }
                } else {
                    Log::info('ℹ️ MikroTik Controller não disponível - usuário será liberado no próximo sync');
                }

            } catch (\Exception $e) {
                Log::warning('⚠️ Falha ao liberar no MikroTik imediatamente', [
                    'error' => $e->getMessage(),
                    'mac_address' => $payment->user->mac_address,
                    'note' => 'Usuário será liberado no próximo sync automático (1 minuto)',
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
                'processing_time' => $processingTime.'ms',
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
                'processing_time' => $processingTime.'ms',
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw para não perder o erro
        }
    }

    /**
     * Webhook Woovi - Cobrança Criada
     */
    public function wooviWebhookCreated(Request $request)
    {
        $startTime = microtime(true);

        Log::info('🆕 Webhook Woovi CRIADA recebido', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook de criação
            $wooviService = new WooviPixService;
            $result = $wooviService->processWebhook($webhookData, 'OPENPIX:CHARGE_CREATED');

            Log::info('📊 Resultado webhook CRIADA', [
                'success' => $result['success'],
                'correlation_id' => $result['correlation_id'] ?? 'N/A',
            ]);

            // Para webhook de criação, apenas logar
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('✅ Webhook CRIADA processado', [
                'processing_time' => $processingTime.'ms',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook de criação processado',
                'processing_time' => $processingTime.'ms',
            ]);

        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('❌ ERRO webhook CRIADA', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Webhook Woovi - Cobrança Expirada
     */
    public function wooviWebhookExpired(Request $request)
    {
        $startTime = microtime(true);

        Log::info('⏰ Webhook Woovi EXPIRADA recebido', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook de expiração
            $wooviService = new WooviPixService;
            $result = $wooviService->processWebhook($webhookData, 'OPENPIX:CHARGE_EXPIRED');

            if ($result['success'] && isset($result['correlation_id'])) {

                DB::beginTransaction();

                try {
                    // Buscar pagamento expirado
                    $payment = Payment::where('pix_location', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['correlation_id'])
                        ->orWhere('transaction_id', $result['correlation_id'])
                        ->first();

                    if ($payment && $payment->status === 'pending') {

                        // Marcar como expirado
                        $payment->update([
                            'status' => 'cancelled',
                            'payment_data' => $webhookData,
                        ]);

                        Log::info('⏰ Pagamento marcado como expirado', [
                            'payment_id' => $payment->id,
                            'mac_address' => $payment->user->mac_address ?? 'N/A',
                        ]);
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('✅ Webhook EXPIRADA processado', [
                'processing_time' => $processingTime.'ms',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook de expiração processado',
                'processing_time' => $processingTime.'ms',
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('❌ ERRO webhook EXPIRADA', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Webhook Woovi - Transação Recebida
     */
    public function wooviWebhookTransaction(Request $request)
    {
        $startTime = microtime(true);

        Log::info('💰 Webhook Woovi TRANSAÇÃO RECEBIDA', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook de transação recebida
            $wooviService = new WooviPixService;
            $result = $wooviService->processWebhook($webhookData, 'OPENPIX:TRANSACTION_RECEIVED');

            if ($result['success'] && isset($result['correlation_id'])) {

                DB::beginTransaction();

                try {
                    // Buscar pagamento pela transação
                    $payment = Payment::where('pix_location', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['correlation_id'])
                        ->orWhere('transaction_id', $result['correlation_id'])
                        ->first();

                    if ($payment && $payment->status === 'pending') {

                        // Marcar como pago (transação recebida = pagamento confirmado)
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                            'payment_data' => $webhookData,
                        ]);

                        // Ativar acesso do usuário
                        $this->activateUserAccess($payment);

                        Log::info('💰 Transação processada - usuário liberado', [
                            'payment_id' => $payment->id,
                            'mac_address' => $payment->user->mac_address ?? 'N/A',
                        ]);
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('✅ Webhook TRANSAÇÃO processado', [
                'processing_time' => $processingTime.'ms',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook de transação processado',
                'processing_time' => $processingTime.'ms',
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('❌ ERRO webhook TRANSAÇÃO', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Webhook Woovi - Pagamento com Pessoa Diferente
     */
    public function wooviWebhookDifferentPayer(Request $request)
    {
        $startTime = microtime(true);

        Log::info('👤 Webhook Woovi PAGADOR DIFERENTE recebido', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook de pagamento com pessoa diferente
            $wooviService = new WooviPixService;
            $result = $wooviService->processWebhook($webhookData, 'OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER');

            if ($result['success'] && isset($result['correlation_id'])) {

                DB::beginTransaction();

                try {
                    // Buscar pagamento
                    $payment = Payment::where('pix_location', $result['correlation_id'])
                        ->orWhere('gateway_payment_id', $result['correlation_id'])
                        ->orWhere('transaction_id', $result['correlation_id'])
                        ->first();

                    if ($payment && $payment->status === 'pending') {

                        // Marcar como pago (mesmo com pagador diferente, é válido)
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                            'payment_data' => array_merge($webhookData, [
                                'different_payer' => true,
                                'note' => 'Pagamento feito por pessoa diferente do solicitante',
                            ]),
                        ]);

                        // Ativar acesso do usuário normalmente
                        $this->activateUserAccess($payment);

                        Log::info('👤 Pagamento com pagador diferente processado', [
                            'payment_id' => $payment->id,
                            'mac_address' => $payment->user->mac_address ?? 'N/A',
                            'note' => 'Usuário liberado mesmo com pagador diferente',
                        ]);
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('✅ Webhook PAGADOR DIFERENTE processado', [
                'processing_time' => $processingTime.'ms',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook pagador diferente processado',
                'processing_time' => $processingTime.'ms',
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('❌ ERRO webhook PAGADOR DIFERENTE', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Webhook PagBank
     */
    public function pagbankWebhook(Request $request)
    {
        $startTime = microtime(true);

        Log::info('🏦 Webhook PagBank recebido', [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        try {
            $webhookData = $request->all();

            // Processar webhook PagBank
            $pagbankService = new \App\Services\PagBankPixService;
            $result = $pagbankService->processWebhook($webhookData);

            if (! ($result['success'] ?? false)) {
                Log::warning('⚠️ Webhook PagBank rejeitado', [
                    'reason' => $result['message'] ?? 'Erro desconhecido',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Webhook rejeitado',
                ], 400);
            }

            if (! ($result['payment_approved'] ?? false)) {
                Log::info('⏳ Webhook PagBank sem aprovação', [
                    'status' => $result['status'] ?? null,
                    'reference_id' => $result['reference_id'] ?? null,
                    'message' => $result['message'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Pagamento ainda não confirmado',
                    'status' => $result['status'] ?? null,
                ], 202);
            }

            Log::info('📊 Resultado do processamento PagBank', [
                'success' => $result['success'],
                'payment_approved' => $result['payment_approved'],
                'reference_id' => $result['reference_id'] ?? 'N/A',
                'order_id' => $result['order_id'] ?? 'N/A',
            ]);

            if ($result['payment_approved']) {

                DB::beginTransaction();

                try {
                    // Buscar pagamento
                    $payment = Payment::where('transaction_id', $result['reference_id'])
                        ->orWhere('gateway_payment_id', $result['order_id'])
                        ->orWhere('pix_location', $result['reference_id'])
                        ->first();

                    if (! $payment) {
                        Log::warning('❌ Pagamento PagBank não encontrado', [
                            'reference_id' => $result['reference_id'],
                            'order_id' => $result['order_id'],
                        ]);

                        DB::rollback();

                        return response()->json([
                            'success' => false,
                            'message' => 'Pagamento não encontrado',
                        ], 404);
                    }

                    Log::info('💳 Pagamento PagBank encontrado', [
                        'payment_id' => $payment->id,
                        'current_status' => $payment->status,
                        'user_mac' => $payment->user->mac_address ?? 'N/A',
                    ]);

                    // Só processar se ainda está pendente
                    if ($payment->status === 'pending') {

                        // Atualizar pagamento
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => $result['paid_at'] ?? now(),
                            'payment_data' => $webhookData,
                        ]);

                        // Ativar acesso do usuário
                        $this->activateUserAccess($payment);

                        DB::commit();

                        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

                        Log::info('✅ Pagamento PagBank processado com SUCESSO', [
                            'payment_id' => $payment->id,
                            'user_mac' => $payment->user->mac_address,
                            'expires_at' => $payment->user->expires_at,
                            'total_processing_time' => $processingTime.'ms',
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Pagamento processado com sucesso',
                            'processing_time' => $processingTime.'ms',
                        ]);

                    } else {
                        DB::rollback();
                        Log::info('ℹ️ Pagamento PagBank já processado anteriormente', [
                            'payment_id' => $payment->id,
                            'status' => $payment->status,
                            'paid_at' => $payment->paid_at,
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Pagamento já processado anteriormente',
                            'already_processed' => true,
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

            Log::error('❌ ERRO CRÍTICO no webhook PagBank', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'processing_time' => $processingTime.'ms',
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'processing_time' => $processingTime.'ms',
            ], 500);
        }
    }

    /**
     * Testar conexão com PagBank
     */
    public function testPagBankConnection()
    {
        try {
            $pagbankService = new \App\Services\PagBankPixService;
            $result = $pagbankService->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar logs do PagBank para validação
     * Endpoint: GET /api/payment/export-pagbank-logs
     */
    public function exportPagBankLogs()
    {
        try {
            $logPath = storage_path('logs/pagbank.log');

            if (!file_exists($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum log encontrado. Execute transações primeiro.',
                    'log_path' => $logPath,
                ], 404);
            }

            // Ler arquivo de log
            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);

            $transactions = [];
            $webhooks = [];
            $currentContext = null;

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                // Parsear JSON do log
                if (preg_match('/\{.*\}/', $line, $matches)) {
                    $jsonStr = $matches[0];
                    $data = json_decode($jsonStr, true);
                    
                    if ($data && isset($data['message'])) {
                        $message = $data['message'];
                        
                        if (strpos($message, '=== REQUEST: Criar Pedido PIX ===') !== false) {
                            $currentContext = [
                                'type' => 'create_order',
                                'timestamp' => $data['context']['timestamp'] ?? date('c'),
                                'endpoint' => $data['context']['endpoint'] ?? '',
                                'method' => $data['context']['method'] ?? 'POST',
                            ];
                        } elseif (strpos($message, 'REQUEST PAYLOAD:') !== false) {
                            if ($currentContext && $currentContext['type'] === 'create_order') {
                                $currentContext['request'] = $data['context']['payload'] ?? [];
                            }
                        } elseif (strpos($message, 'RESPONSE:') !== false) {
                            if ($currentContext && $currentContext['type'] === 'create_order') {
                                $currentContext['response'] = [
                                    'status' => $data['context']['status'] ?? 0,
                                    'body' => $data['context']['body'] ?? [],
                                ];
                                $transactions[] = $currentContext;
                                $currentContext = null;
                            }
                        } elseif (strpos($message, '=== WEBHOOK RECEBIDO ===') !== false) {
                            $webhooks[] = [
                                'timestamp' => $data['context']['timestamp'] ?? date('c'),
                                'data' => $data['context']['webhook_data'] ?? [],
                            ];
                        }
                    }
                }
            }

            $exportData = [
                'export_info' => [
                    'generated_at' => now()->toISOString(),
                    'system' => 'WiFi Tocantins',
                    'purpose' => 'Validação de integração PagBank',
                    'environment' => config('wifi.payment_gateways.pix.environment', 'sandbox'),
                ],
                'statistics' => [
                    'total_transactions' => count($transactions),
                    'total_webhooks' => count($webhooks),
                ],
                'transactions' => $transactions,
                'webhooks' => $webhooks,
            ];

            // Salvar arquivo JSON
            $outputPath = storage_path('logs/pagbank_validation_export.json');
            file_put_contents($outputPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return response()->json([
                'success' => true,
                'message' => 'Logs exportados com sucesso',
                'file_path' => $outputPath,
                'statistics' => $exportData['statistics'],
                'download_url' => url('/storage/logs/pagbank_validation_export.json'),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar logs PagBank: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar logs: ' . $e->getMessage(),
            ], 500);
        }
    }
}
