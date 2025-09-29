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
        
        // URLs do Santander conforme documentação oficial
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://trust-pix.santander.com.br' 
            : 'https://trust-pix-h.santander.com.br';
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
                ->post($this->baseUrl . '/oauth/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    // Sem scope específico conforme documentação Santander
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
            
            // Payload conforme documentação Santander PIX
            $payload = [
                'calendario' => [
                    'expiracao' => 900 // 15 minutos em segundos
                ],
                'valor' => [
                    'original' => number_format($amount, 2, '.', '')
                ],
                'chave' => $this->pixKey,
                'solicitacaoPagador' => $description,
                'infoAdicionais' => [
                    [
                        'nome' => 'Referencia',
                        'valor' => $externalId ?: 'WIFI_' . time()
                    ]
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
            ->put($this->baseUrl . '/pix/v2/cob/' . ($externalId ?: 'WIFI_' . time()), $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Gerar string EMV conforme documentação
                $emvString = $this->generateEMVString($data['location'], $amount, $data['txid']);
                
                return [
                    'success' => true,
                    'payment_id' => $data['txid'],
                    'qr_code_text' => $emvString, // String EMV gerada
                    'qr_code_image' => $this->generateQRCodeImageUrl($emvString),
                    'amount' => $amount,
                    'status' => 'pending',
                    'external_id' => $externalId ?: 'WIFI_' . time(),
                    'expiration_datetime' => $data['calendario']['criacao'] ?? now()->toISOString(),
                    'location' => $data['location'],
                    'txid' => $data['txid'],
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
     * Gerar string EMV conforme documentação Santander
     */
    private function generateEMVString(string $location, float $amount, string $txid): string
    {
        $merchantName = config('wifi.pix.merchant_name', 'TocantinsTransportWiFi');
        $merchantCity = config('wifi.pix.merchant_city', 'Palmas');
        
        // Formatar valor no padrão EMV
        $formattedAmount = number_format($amount, 2, '.', '');
        
        // Campo 00: Payload Format Indicator
        $payload = $this->formatEMVField('00', '01');
        
        // Campo 01: Point of Initiation Method (12 = dinâmico)
        $payload .= $this->formatEMVField('01', '12');
        
        // Campo 26: Merchant Account Information
        $merchantInfo = $this->formatEMVField('00', 'br.gov.bcb.pix');
        $merchantInfo .= $this->formatEMVField('25', $location);
        $payload .= $this->formatEMVField('26', $merchantInfo);
        
        // Campo 52: Merchant Category Code
        $payload .= $this->formatEMVField('52', '0000');
        
        // Campo 53: Transaction Currency (986 = BRL)
        $payload .= $this->formatEMVField('53', '986');
        
        // Campo 54: Transaction Amount
        $payload .= $this->formatEMVField('54', $formattedAmount);
        
        // Campo 58: Country Code
        $payload .= $this->formatEMVField('58', 'BR');
        
        // Campo 59: Merchant Name
        $payload .= $this->formatEMVField('59', $merchantName);
        
        // Campo 60: Merchant City
        $payload .= $this->formatEMVField('60', $merchantCity);
        
        // Campo 62: Additional Data Field Template
        $additionalData = $this->formatEMVField('05', '***'); // Referência
        $payload .= $this->formatEMVField('62', $additionalData);
        
        // Campo 63: CRC16 (calculado sobre todo o payload + "6304")
        $crcPayload = $payload . '6304';
        $crc = $this->calculateCRC16($crcPayload);
        $payload .= '6304' . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
        
        return $payload;
    }

    /**
     * Formatar campo EMV (ID + Tamanho + Conteúdo)
     */
    private function formatEMVField(string $id, string $content): string
    {
        $length = str_pad(strlen($content), 2, '0', STR_PAD_LEFT);
        return $id . $length . $content;
    }

    /**
     * Calcular CRC16-CCITT para validação EMV
     */
    private function calculateCRC16(string $data): int
    {
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ $polynomial) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        
        return $crc;
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
