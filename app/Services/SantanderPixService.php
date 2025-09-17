<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SantanderPixService
{
    private $clientId;
    private $clientSecret;
    private $workspaceId;
    private $pixKey;
    private $certificatePath;
    private $certificatePassword;
    private $baseUrl;
    private $environment;

    public function __construct()
    {
        $this->clientId = config('wifi.payment_gateways.pix.client_id');
        $this->clientSecret = config('wifi.payment_gateways.pix.client_secret');
        $this->workspaceId = config('wifi.payment_gateways.pix.workspace_id');
        $this->pixKey = config('wifi.pix.key');
        $this->certificatePath = config('wifi.payment_gateways.pix.certificate_path');
        $this->certificatePassword = config('wifi.payment_gateways.pix.certificate_password');
        $this->environment = config('wifi.payment_gateways.pix.environment', 'sandbox');
        
        // URLs do Santander
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.santander.com.br' 
            : 'https://api-sandbox.santander.com.br';
    }

    /**
     * Obter token de acesso OAuth 2.0
     */
    private function getAccessToken(): string
    {
        try {
            $response = Http::asForm()
                ->withOptions([
                    'cert' => [storage_path('app/' . $this->certificatePath), $this->certificatePassword],
                    'verify' => true,
                ])
                ->post($this->baseUrl . '/auth/oauth/v2/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'pix_payments'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'];
            }

            throw new Exception('Erro na autenticação Santander: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao obter token Santander: ' . $e->getMessage());
            throw new Exception('Falha na autenticação com o Santander');
        }
    }

    /**
     * Criar cobrança PIX dinâmica
     */
    public function createPixPayment(float $amount, string $description, string $externalId = null): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            // Converter valor para centavos
            $amountCents = intval($amount * 100);
            
            $payload = [
                'key' => $this->pixKey,
                'amount' => [
                    'value' => $amountCents
                ],
                'expiration_datetime' => now()->addMinutes(30)->toISOString(),
                'external_id' => $externalId ?: 'WIFI_' . time(),
                'additional_information' => [
                    'description' => $description
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Application-Key' => $this->workspaceId,
            ])
            ->withOptions([
                'cert' => [storage_path('app/' . $this->certificatePath), $this->certificatePassword],
                'verify' => true,
            ])
            ->post($this->baseUrl . '/pix_payments/v1/qr_codes', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'payment_id' => $data['payment_id'],
                    'qr_code_text' => $data['qr_code_text'], // String EMV
                    'qr_code_image' => $data['qr_code_image'] ?? null, // Base64 da imagem
                    'amount' => $amount,
                    'status' => 'pending',
                    'external_id' => $data['external_id'],
                    'expiration_datetime' => $data['expiration_datetime'],
                ];
            }

            throw new Exception('Erro na API Santander: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao criar pagamento PIX Santander: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar status do pagamento
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Application-Key' => $this->workspaceId,
            ])
            ->withOptions([
                'cert' => [storage_path('app/' . $this->certificatePath), $this->certificatePassword],
                'verify' => true,
            ])
            ->get($this->baseUrl . '/pix_payments/v1/payments/' . $paymentId);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'], // PENDING, APPROVED, REJECTED, EXPIRED
                    'amount' => $data['amount']['value'] / 100, // Converter de centavos
                    'paid_at' => $data['payment_datetime'] ?? null,
                    'payer_name' => $data['payer']['name'] ?? null,
                    'payer_document' => $data['payer']['document'] ?? null,
                ];
            }

            throw new Exception('Erro ao consultar pagamento: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao consultar status PIX Santander: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Processar webhook do Santander
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            // Validar assinatura do webhook (implementar conforme documentação)
            if (!$this->validateWebhookSignature($webhookData)) {
                throw new Exception('Assinatura do webhook inválida');
            }

            $eventType = $webhookData['event_type'] ?? '';
            $paymentId = $webhookData['payment_id'] ?? '';

            if ($eventType === 'payment_approved' && $paymentId) {
                // Consultar dados completos do pagamento
                $paymentStatus = $this->getPaymentStatus($paymentId);
                
                if ($paymentStatus['success'] && $paymentStatus['status'] === 'APPROVED') {
                    return [
                        'success' => true,
                        'payment_approved' => true,
                        'payment_id' => $paymentId,
                        'amount' => $paymentStatus['amount'],
                        'paid_at' => $paymentStatus['paid_at'],
                        'payer_name' => $paymentStatus['payer_name'],
                        'payer_document' => $paymentStatus['payer_document'],
                    ];
                }
            }

            return [
                'success' => true,
                'payment_approved' => false,
                'event_type' => $eventType,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao processar webhook Santander: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar assinatura do webhook (implementar conforme documentação Santander)
     */
    private function validateWebhookSignature(array $webhookData): bool
    {
        // TODO: Implementar validação de assinatura conforme documentação Santander
        // Por enquanto, retorna true para desenvolvimento
        return true;
    }

    /**
     * Testar conectividade com a API
     */
    public function testConnection(): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            return [
                'success' => true,
                'message' => 'Conexão com Santander estabelecida com sucesso',
                'environment' => $this->environment,
                'token_obtained' => !empty($accessToken),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão: ' . $e->getMessage(),
                'environment' => $this->environment,
            ];
        }
    }

    /**
     * Gerar URL para imagem QR Code a partir do texto EMV
     */
    public function generateQRCodeImageUrl(string $emvText): string
    {
        $size = '300x300';
        $encodedData = urlencode($emvText);
        
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$encodedData}";
    }
}
