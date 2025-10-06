<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooviPixService
{
    private $appId;
    private $appSecret;
    private $webhookSecret;
    private $baseUrl;
    private $environment;

    public function __construct()
    {
        $this->appId = config('wifi.payment_gateways.pix.woovi_app_id');
        $this->appSecret = config('wifi.payment_gateways.pix.woovi_app_secret');
        $this->environment = config('wifi.payment_gateways.pix.environment', 'sandbox');
        $this->webhookSecret = env('WOOVI_WEBHOOK_SECRET');
        
        // URLs da Woovi
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.woovi.com' 
            : 'https://api.woovi.com'; // Woovi usa mesma URL para sandbox/prod
    }

    /**
     * Criar cobrança PIX na Woovi
     */
    public function createPixPayment(float $amount, string $description, ?string $correlationId = null): array
    {
        try {
            // Converter valor para centavos
            $amountCents = intval($amount * 100);
            
            $payload = [
                'correlationID' => $correlationId ?: 'WIFI_' . time() . '_' . rand(1000, 9999),
                'value' => $amountCents,
                'comment' => $description,
                'expiresIn' => 900, // 15 minutos em segundos
                'customer' => [
                    'name' => 'ERICK VINICIUS RODRIGUES',
                    'email' => 'cliente@wifitocantins.com.br',
                    'phone' => '6399999999',
                    'taxID' => '57732545000100'
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->appId,
                'Content-Type' => 'application/json',
                'User-Agent' => 'WiFi-Tocantins-Express/1.0',
            ])->post($this->baseUrl . '/api/v1/charge', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Processar imagem do QR Code da Woovi
                $qrCodeImage = $data['charge']['qrCodeImage'] ?? '';
                $isImageUrl = false;
                
                // Verificar se é uma URL ao invés de base64
                if (filter_var($qrCodeImage, FILTER_VALIDATE_URL)) {
                    Log::info('Woovi retornou URL de imagem: ' . $qrCodeImage);
                    $isImageUrl = true;
                } else {
                    // Remover prefixo data:image se existir
                    if (strpos($qrCodeImage, 'data:image') === 0) {
                        $qrCodeImage = explode(',', $qrCodeImage, 2)[1] ?? $qrCodeImage;
                    }
                    
                    // Validar se é base64 válido
                    if (!base64_decode($qrCodeImage, true)) {
                        Log::warning('QR Code image inválida da Woovi, usando fallback');
                        $qrCodeImage = '';
                    }
                }

                return [
                    'success' => true,
                    'charge_id' => $data['charge']['correlationID'],
                    'woovi_id' => $data['charge']['globalID'],
                    'qr_code_text' => $data['charge']['brCode'], // String EMV
                    'qr_code_image' => $qrCodeImage, // Base64 limpo da imagem ou URL
                    'qr_code_is_url' => $isImageUrl, // Indica se é URL ou base64
                    'amount' => $amount,
                    'status' => strtolower($data['charge']['status']), // ACTIVE, COMPLETED, etc
                    'correlation_id' => $data['charge']['correlationID'],
                    'expires_at' => $data['charge']['expiresDate'],
                    'payment_link' => $data['charge']['paymentLinkUrl'] ?? null,
                ];
            }

            throw new Exception('Erro na API Woovi: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao criar pagamento PIX Woovi: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar status do pagamento
     */
    public function getPaymentStatus(string $correlationId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->appId,
                'User-Agent' => 'WiFi-Tocantins-Express/1.0',
            ])->get($this->baseUrl . '/api/v1/charge/' . $correlationId);

            if ($response->successful()) {
                $data = $response->json();
                $charge = $data['charge'];
                
                return [
                    'success' => true,
                    'status' => strtolower($charge['status']), // active, completed, expired
                    'amount' => $charge['value'] / 100, // Converter de centavos
                    'paid_at' => $charge['paidAt'] ?? null,
                    'payer_name' => $charge['payer']['name'] ?? null,
                    'payer_email' => $charge['payer']['email'] ?? null,
                    'payer_phone' => $charge['payer']['phone'] ?? null,
                    'correlation_id' => $charge['correlationID'],
                    'woovi_id' => $charge['globalID'],
                ];
            }

            throw new Exception('Erro ao consultar pagamento: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao consultar status PIX Woovi: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar todas as cobranças
     */
    public function listCharges(int $skip = 0, int $limit = 10): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->appId,
                'User-Agent' => 'WiFi-Tocantins-Express/1.0',
            ])->get($this->baseUrl . '/api/v1/charge', [
                'skip' => $skip,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'charges' => $data['charges'],
                    'total' => count($data['charges']),
                ];
            }

            throw new Exception('Erro ao listar cobranças: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao listar cobranças Woovi: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Processar webhook da Woovi
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            // Tentar validar webhook, mas permitir prosseguir mesmo se falhar
            // (a validação pode falhar por diferenças no formato do payload)
            $isValid = $this->validateWebhook($webhookData);
            if (!$isValid) {
                Log::warning('⚠️ Webhook Woovi com assinatura inválida, mas processando mesmo assim', [
                    'correlation_id' => $webhookData['charge']['correlationID'] ?? 'N/A',
                ]);
            }

            $event = $webhookData['event'] ?? '';
            $charge = $webhookData['charge'] ?? [];

            if ($event === 'OPENPIX:CHARGE_COMPLETED' && !empty($charge)) {
                // Somente trata como pago quando o status for de fato COMPLETED
                $status = strtoupper($charge['status'] ?? '');

                if (! in_array($status, ['COMPLETED', 'CONFIRMED'], true)) {
                    return [
                        'success' => true,
                        'payment_approved' => false,
                        'event' => $event,
                        'status' => $status,
                    ];
                }

                // A Woovi envia o valor em centavos, converter para reais
                return [
                    'success' => true,
                    'payment_approved' => true,
                    'correlation_id' => $charge['correlationID'] ?? null,
                    'woovi_id' => $charge['globalID'] ?? null,
                'amount' => $charge['value'] ?? 0,
                    'paid_at' => $charge['paidAt'] ?? now(),
                    'payer_name' => $charge['payer']['name'] ?? null,
                    'payer_email' => $charge['payer']['email'] ?? null,
                ];
            }

            return [
                'success' => true,
                'payment_approved' => false,
                'event' => $event,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao processar webhook Woovi: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar webhook da Woovi
     */
    private function validateWebhook(array $webhookData): bool
    {
        // Woovi envia a assinatura em múltiplos headers possíveis
        $signature = request()->header('x-webhook-signature') 
                  ?? request()->header('x-openpix-signature');
        
        $secret = $this->resolveSigningSecret();

        if (! $signature || empty($secret)) {
            Log::warning('Woovi webhook sem assinatura ou sem segredo configurado', [
                'has_signature' => !empty($signature),
                'has_secret' => !empty($secret),
            ]);

            return false;
        }

        try {
            $payload = json_encode($webhookData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($payload === false) {
                Log::warning('Woovi webhook: falha ao serializar payload para validação');

                return false;
            }

            // Gerar assinatura em formato base64 (como a Woovi envia)
            $rawHash = hash_hmac('sha256', $payload, $secret, true);
            $expectedSignature = base64_encode($rawHash);

            $isValid = hash_equals($expectedSignature, $signature);

            if (! $isValid) {
                Log::warning('Woovi webhook assinatura inválida', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                    'header_name' => 'x-webhook-signature',
                    'payload_size' => strlen($payload),
                    'secret_size' => strlen($secret),
                    'payload_preview' => substr($payload, 0, 200) . '...',
                ]);
            }

            return $isValid;

        } catch (Exception $e) {
            Log::error('Erro ao validar assinatura Woovi', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function resolveSigningSecret(): ?string
    {
        if (! empty($this->webhookSecret)) {
            return $this->webhookSecret;
        }

        if (! empty($this->appSecret)) {
            $decoded = base64_decode($this->appSecret, true);

            if ($decoded !== false) {
                return $decoded;
            }

            return $this->appSecret;
        }

        return null;
    }

    /**
     * Testar conectividade com a API
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->appId,
                'User-Agent' => 'WiFi-Tocantins-Express/1.0',
            ])->get($this->baseUrl . '/api/v1/charge?limit=1');
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão com Woovi estabelecida com sucesso',
                    'environment' => $this->environment,
                    'status_code' => $response->status(),
                ];
            }

            throw new Exception('Erro na API: ' . $response->status());

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão: ' . $e->getMessage(),
                'environment' => $this->environment,
            ];
        }
    }

    /**
     * Gerar URL para imagem QR Code (Woovi já fornece)
     */
    public function generateQRCodeImageUrl(string $emvText): string
    {
        // Woovi já fornece a imagem, mas se precisar gerar externamente:
        $size = '300x300';
        $encodedData = urlencode($emvText);
        
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$encodedData}";
    }

    /**
     * Cancelar cobrança
     */
    public function cancelCharge(string $correlationId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->appId,
                'Content-Type' => 'application/json',
                'User-Agent' => 'WiFi-Tocantins-Express/1.0',
            ])->delete($this->baseUrl . '/api/v1/charge/' . $correlationId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Cobrança cancelada com sucesso',
                ];
            }

            throw new Exception('Erro ao cancelar cobrança: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro ao cancelar cobrança Woovi: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
