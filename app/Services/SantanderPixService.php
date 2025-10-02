<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Serviço de integração PIX Santander
 * 
 * Documentação oficial: Portal do Desenvolvedor Santander
 * Protocolo: OAuth 2.0 com mTLS
 * Versão API: v1
 */
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
    private $merchantName;
    private $merchantCity;

    public function __construct()
    {
        $this->clientId = config('wifi.payment_gateways.pix.client_id');
        $this->clientSecret = config('wifi.payment_gateways.pix.client_secret');
        $this->workspaceId = config('wifi.payment_gateways.pix.workspace_id');
        $this->pixKey = config('wifi.pix.key');
        $this->certificatePath = config('wifi.payment_gateways.pix.certificate_path');
        $this->certificatePassword = config('wifi.payment_gateways.pix.certificate_password', '');
        $this->environment = config('wifi.payment_gateways.pix.environment', 'sandbox');
        $this->merchantName = $this->sanitizeMerchantName(config('wifi.pix.merchant_name', 'TocantinsTransportWiFi'));
        $this->merchantCity = $this->sanitizeMerchantName(config('wifi.pix.merchant_city', 'Palmas'));
        
        // URLs oficiais do Santander conforme documentação
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://trust-pix.santander.com.br' 
            : 'https://trust-pix-h.santander.com.br';
    }

    /**
     * ===================================================================
     * AUTENTICAÇÃO OAuth 2.0 com mTLS
     * ===================================================================
     * Endpoint: POST /oauth/token
     * Documentação: Portal do Desenvolvedor > OAuth 2.0
     * Token válido por: 15 minutos
     */
    private function getAccessToken(): string
    {
        try {
            Log::info('🔐 Iniciando autenticação OAuth 2.0 Santander', [
                'environment' => $this->environment,
                'base_url' => $this->baseUrl,
                'client_id' => $this->clientId ? 'PRESENTE' : 'AUSENTE',
                'client_secret' => $this->clientSecret ? 'PRESENTE' : 'AUSENTE',
                'workspace_id' => $this->workspaceId ? 'PRESENTE' : 'AUSENTE',
            ]);

            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            // Verificar se certificado existe
            if (!file_exists($certificateFullPath)) {
                throw new Exception("Certificado não encontrado: {$certificateFullPath}");
            }

            // Verificar credenciais
            if (empty($this->clientId) || empty($this->clientSecret)) {
                throw new Exception("Client ID ou Client Secret não configurados no .env");
            }

            Log::info('📤 Enviando requisição OAuth', [
                'url' => $this->baseUrl . '/oauth/token',
                'grant_type' => 'client_credentials',
                'client_id_length' => strlen($this->clientId),
                'client_secret_length' => strlen($this->clientSecret),
                'client_id_first_4' => substr($this->clientId, 0, 4),
                'client_id_last_4' => substr($this->clientId, -4),
            ]);

            // Santander PIX requer Basic Authentication
            // Authorization: Basic base64(client_id:client_secret)
            $basicAuth = base64_encode($this->clientId . ':' . $this->clientSecret);

            // Testar com diferentes formatos de autenticação
            // Algumas APIs Santander exigem o parâmetro 'scope'
            $bodyParams = [
                'grant_type' => 'client_credentials',
                'scope' => 'cob.write cob.read pix.write pix.read webhook.read webhook.write',
            ];

            Log::info('🔍 Tentando autenticação (formato 1: Basic Auth com scope)', [
                'authorization_header' => 'Basic ' . substr($basicAuth, 0, 20) . '...',
                'body' => $bodyParams,
                'full_url' => $this->baseUrl . '/oauth/token',
                'certificate_path' => storage_path('app/' . $this->certificatePath),
            ]);

            // asForm() já adiciona Content-Type automaticamente, não duplicar!
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic ' . $basicAuth,
                ])
                ->withOptions([
                    'cert' => empty($this->certificatePassword) 
                        ? $certificateFullPath 
                        : [$certificateFullPath, $this->certificatePassword],
                    'verify' => true,
                    'timeout' => 30,
                ])
                ->post($this->baseUrl . '/oauth/token', $bodyParams);

            // Log detalhado da resposta
            Log::info('📥 Resposta do Santander (Basic Auth)', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                
                if (!$token) {
                    throw new Exception('Token não retornado na resposta');
                }

                Log::info('✅ Token OAuth 2.0 obtido com sucesso (Basic Auth)');
                return $token;
            }

            // Se Basic Auth falhou, tentar com credenciais no body
            Log::warning('⚠️ Basic Auth falhou, tentando com credenciais no body', [
                'status' => $response->status(),
                'error_body' => $response->body(),
            ]);

            $bodyParamsAlt = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'cob.write cob.read pix.write pix.read webhook.read webhook.write',
            ];

            Log::info('🔍 Tentando autenticação (formato 2: Body params com scope)', [
                'body_keys' => array_keys($bodyParamsAlt),
            ]);

            // asForm() já adiciona Content-Type automaticamente, não duplicar!
            $response2 = Http::asForm()
                ->withOptions([
                    'cert' => empty($this->certificatePassword) 
                        ? $certificateFullPath 
                        : [$certificateFullPath, $this->certificatePassword],
                    'verify' => true,
                    'timeout' => 30,
                ])
                ->post($this->baseUrl . '/oauth/token', $bodyParamsAlt);

            // Log detalhado da resposta
            Log::info('📥 Resposta do Santander (Body params)', [
                'status' => $response2->status(),
                'headers' => $response2->headers(),
                'body' => $response2->body(),
            ]);

            if ($response2->successful()) {
                $data = $response2->json();
                $token = $data['access_token'] ?? null;
                
                if (!$token) {
                    throw new Exception('Token não retornado na resposta');
                }

                Log::info('✅ Token OAuth 2.0 obtido com sucesso (Body params)');
                return $token;
            }

            // Ambas as tentativas falharam
            $errorBody = $response2->body();
            Log::error('❌ Ambas tentativas de autenticação falharam', [
                'basic_auth_status' => $response->status(),
                'body_params_status' => $response2->status(),
                'last_error' => $errorBody,
            ]);

            throw new Exception('Erro na autenticação Santander: ' . $errorBody);

        } catch (Exception $e) {
            Log::error('❌ Erro ao obter token Santander: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw new Exception('Falha na autenticação com o Santander: ' . $e->getMessage());
        }
    }

    /**
     * ===================================================================
     * CRIAR COBRANÇA PIX (QR Code Dinâmico)
     * ===================================================================
     * Endpoint: PUT /api/v1/cob/{txid}
     * Documentação: Portal do Desenvolvedor > API Pix
     * 
     * @param float $amount Valor da cobrança em reais
     * @param string $description Descrição do pagamento
     * @param string|null $txid Identificador único (26-35 caracteres)
     * @return array
     */
    public function createPixPayment(float $amount, string $description, ?string $txid = null): array
    {
        try {
            // Gerar TXId único se não fornecido (26-35 caracteres alfanuméricos)
            if (!$txid) {
                $txid = 'WIFI' . strtoupper(Str::random(26)); // 30 caracteres total
            }

            // Validar TXId
            if (strlen($txid) < 26 || strlen($txid) > 35) {
                throw new Exception('TXId deve ter entre 26 e 35 caracteres');
            }

            Log::info('📲 Criando cobrança PIX Santander', [
                'txid' => $txid,
                'amount' => $amount,
                'description' => $description,
            ]);

            $accessToken = $this->getAccessToken();
            
            // Payload conforme documentação Santander PIX
            $payload = [
                'calendario' => [
                    'expiracao' => 900 // 15 minutos (900 segundos)
                ],
                'devedor' => [
                    // Opcional: dados do pagador se conhecidos
                ],
                'valor' => [
                    'original' => number_format($amount, 2, '.', '') // Formato: "0.10"
                ],
                'chave' => $this->pixKey, // Chave PIX cadastrada no Santander
                'solicitacaoPagador' => substr($description, 0, 140), // Máximo 140 caracteres
                'infoAdicionais' => [
                    [
                        'nome' => 'Referencia',
                        'valor' => $txid
                    ]
                ]
            ];

            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->put($this->baseUrl . '/api/v1/cob/' . $txid, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('✅ Cobrança PIX criada com sucesso', [
                    'txid' => $data['txid'] ?? $txid,
                    'location' => $data['location'] ?? null,
                ]);

                // Gerar string EMV (Pix Copia e Cola)
                $location = $data['location'] ?? null;
                if (!$location) {
                    throw new Exception('Location não retornada pela API Santander');
                }

                $emvString = $this->generateEMVString($location, $amount, $txid);
                
                return [
                    'success' => true,
                    'txid' => $data['txid'] ?? $txid,
                    'location' => $location,
                    'qr_code_text' => $emvString, // Pix Copia e Cola
                    'qr_code_image' => $this->generateQRCodeImageUrl($emvString),
                    'amount' => $amount,
                    'status' => $data['status'] ?? 'ATIVA',
                    'expiration' => $data['calendario']['expiracao'] ?? 900,
                    'created_at' => $data['calendario']['criacao'] ?? now()->toISOString(),
                ];
            }

            $errorBody = $response->body();
            Log::error('❌ Erro ao criar cobrança PIX Santander', [
                'status' => $response->status(),
                'body' => $errorBody,
                'txid' => $txid,
            ]);

            throw new Exception('Erro na API Santander: ' . $errorBody);

        } catch (Exception $e) {
            Log::error('❌ Erro ao criar pagamento PIX Santander: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => get_class($e),
            ];
        }
    }

    /**
     * ===================================================================
     * CONSULTAR COBRANÇA PIX
     * ===================================================================
     * Endpoint: GET /api/v1/cob/{txid}
     * Documentação: Portal do Desenvolvedor > API Pix
     */
    public function getPaymentStatus(string $txid): array
    {
        try {
            Log::info('🔍 Consultando cobrança PIX Santander', ['txid' => $txid]);

            $accessToken = $this->getAccessToken();
            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->get($this->baseUrl . '/api/v1/cob/' . $txid);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'txid' => $data['txid'] ?? $txid,
                    'status' => $data['status'] ?? 'ATIVA', // ATIVA, CONCLUIDA, REMOVIDA_PELO_USUARIO_RECEBEDOR
                    'amount' => $data['valor']['original'] ?? null,
                    'paid_at' => $data['pix'][0]['horario'] ?? null,
                    'e2eid' => $data['pix'][0]['endToEndId'] ?? null,
                    'payer_name' => $data['pix'][0]['pagador']['nome'] ?? null,
                    'payer_document' => $data['pix'][0]['pagador']['cpf'] ?? $data['pix'][0]['pagador']['cnpj'] ?? null,
                    'location' => $data['location'] ?? null,
                ];
            }

            throw new Exception('Erro ao consultar cobrança: ' . $response->body());

        } catch (Exception $e) {
            Log::error('❌ Erro ao consultar status PIX Santander: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * CONSULTAR LISTA DE PIX RECEBIDOS (Padrão Banco Central)
     * ===================================================================
     * Endpoint: GET /api/v1/pix
     * Documentação: Portal do Desenvolvedor > API Pix > Consulta de lista de Pix recebidos
     * 
     * @param string $dataInicio Data início (formato: YYYY-MM-DD)
     * @param string $dataFim Data fim (formato: YYYY-MM-DD)
     * @param int $paginaAtual Página atual (padrão: 0)
     * @param int $itensPorPagina Itens por página (padrão: 100)
     * @return array
     */
    public function listPixReceivedBCB(string $dataInicio, string $dataFim, int $paginaAtual = 0, int $itensPorPagina = 100): array
    {
        try {
            Log::info('📋 Consultando lista de Pix recebidos (Padrão BCB)', [
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim,
            ]);

            $accessToken = $this->getAccessToken();
            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $queryParams = [
                'inicio' => $dataInicio,
                'fim' => $dataFim,
                'paginacao.paginaAtual' => $paginaAtual,
                'paginacao.itensPorPagina' => $itensPorPagina,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->get($this->baseUrl . '/api/v1/pix', $queryParams);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            throw new Exception('Erro ao consultar lista: ' . $response->body());

        } catch (Exception $e) {
            Log::error('❌ Erro ao consultar lista PIX: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * CONFIGURAR WEBHOOK PIX
     * ===================================================================
     * Endpoint: PUT /api/v1/webhook/{chave}
     * Documentação: Portal do Desenvolvedor > Gerenciamento de notificações via Webhook
     * 
     * @param string $webhookUrl URL do webhook (deve aceitar GET para validação e POST para notificações)
     * @param string|null $chave Chave PIX (se null, usa a configurada)
     * @return array
     */
    public function configureWebhook(string $webhookUrl, ?string $chave = null): array
    {
        try {
            $chave = $chave ?? $this->pixKey;

            Log::info('🔔 Configurando webhook PIX Santander', [
                'chave' => $chave,
                'webhook_url' => $webhookUrl,
            ]);

            $accessToken = $this->getAccessToken();
            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $payload = [
                'webhookUrl' => $webhookUrl
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->put($this->baseUrl . '/api/v1/webhook/' . urlencode($chave), $payload);

            if ($response->successful()) {
                Log::info('✅ Webhook configurado com sucesso');
                
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            throw new Exception('Erro ao configurar webhook: ' . $response->body());

        } catch (Exception $e) {
            Log::error('❌ Erro ao configurar webhook: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * CONSULTAR WEBHOOK CONFIGURADO
     * ===================================================================
     * Endpoint: GET /api/v1/webhook/{chave}
     */
    public function getWebhookConfig(?string $chave = null): array
    {
        try {
            $chave = $chave ?? $this->pixKey;

            Log::info('🔍 Consultando webhook configurado', ['chave' => $chave]);

            $accessToken = $this->getAccessToken();
            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->get($this->baseUrl . '/api/v1/webhook/' . urlencode($chave));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            throw new Exception('Erro ao consultar webhook: ' . $response->body());

        } catch (Exception $e) {
            Log::error('❌ Erro ao consultar webhook: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * CANCELAR WEBHOOK
     * ===================================================================
     * Endpoint: DELETE /api/v1/webhook/{chave}
     */
    public function deleteWebhook(?string $chave = null): array
    {
        try {
            $chave = $chave ?? $this->pixKey;

            Log::info('🗑️ Cancelando webhook PIX Santander', ['chave' => $chave]);

            $accessToken = $this->getAccessToken();
            $certificateFullPath = storage_path('app/' . $this->certificatePath);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->withOptions([
                'cert' => empty($this->certificatePassword) 
                    ? $certificateFullPath 
                    : [$certificateFullPath, $this->certificatePassword],
                'verify' => true,
                'timeout' => 30,
            ])
            ->delete($this->baseUrl . '/api/v1/webhook/' . urlencode($chave));

            if ($response->successful()) {
                Log::info('✅ Webhook cancelado com sucesso');
                
                return [
                    'success' => true,
                    'message' => 'Webhook cancelado com sucesso',
                ];
            }

            throw new Exception('Erro ao cancelar webhook: ' . $response->body());

        } catch (Exception $e) {
            Log::error('❌ Erro ao cancelar webhook: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * PROCESSAR WEBHOOK RECEBIDO
     * ===================================================================
     * Processa notificações de pagamento recebidas do Santander
     * 
     * @param array $webhookData Dados recebidos do webhook
     * @return array
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            Log::info('📨 Processando webhook Santander', [
                'data' => $webhookData,
            ]);

            // Extrair informações do webhook
            $pix = $webhookData['pix'] ?? [];
            
            if (empty($pix)) {
                throw new Exception('Webhook sem informações de PIX');
            }

            // Pegar o primeiro PIX da lista
            $pixData = is_array($pix) && isset($pix[0]) ? $pix[0] : $pix;

            $endToEndId = $pixData['endToEndId'] ?? null;
            $txid = $pixData['txid'] ?? null;

            if (!$endToEndId) {
                throw new Exception('EndToEndId não encontrado no webhook');
            }

            // Consultar detalhes completos da cobrança se TXId fornecido
            if ($txid) {
                $paymentStatus = $this->getPaymentStatus($txid);
                
                if ($paymentStatus['success'] && $paymentStatus['status'] === 'CONCLUIDA') {
                    return [
                        'success' => true,
                        'payment_confirmed' => true,
                        'txid' => $txid,
                        'e2eid' => $endToEndId,
                        'amount' => $paymentStatus['amount'],
                        'paid_at' => $paymentStatus['paid_at'],
                        'payer_name' => $paymentStatus['payer_name'],
                        'payer_document' => $paymentStatus['payer_document'],
                    ];
                }
            }

            // Webhook de notificação genérica
            return [
                'success' => true,
                'payment_confirmed' => false,
                'e2eid' => $endToEndId,
                'txid' => $txid,
                'webhook_data' => $webhookData,
            ];

        } catch (Exception $e) {
            Log::error('❌ Erro ao processar webhook Santander: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ===================================================================
     * TESTE DE CONECTIVIDADE
     * ===================================================================
     * Testa se a autenticação e configuração estão corretas
     */
    public function testConnection(): array
    {
        try {
            Log::info('🧪 Testando conexão com Santander PIX');

            $accessToken = $this->getAccessToken();
            
            $checks = [
                'environment' => $this->environment,
                'base_url' => $this->baseUrl,
                'client_id' => !empty($this->clientId),
                'client_secret' => !empty($this->clientSecret),
                'pix_key' => !empty($this->pixKey),
                'certificate_exists' => file_exists(storage_path('app/' . $this->certificatePath)),
                'token_obtained' => !empty($accessToken),
            ];

            $allChecksPass = array_reduce($checks, function($carry, $item) {
                return $carry && $item;
            }, true);

            return [
                'success' => $allChecksPass,
                'message' => $allChecksPass 
                    ? 'Conexão com Santander estabelecida com sucesso' 
                    : 'Algumas verificações falharam',
                'checks' => $checks,
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
     * ===================================================================
     * GERAÇÃO DE STRING EMV (PIX COPIA E COLA)
     * ===================================================================
     * Gera string EMV conforme Manual de Iniciação do Banco Central
     * Documentação: Portal do Desenvolvedor > Geração de EMV
     * 
     * @param string $location URL da location gerada pelo Santander
     * @param float $amount Valor da transação
     * @param string $txid Identificador da transação
     * @return string String EMV completa com CRC16
     */
    private function generateEMVString(string $location, float $amount, string $txid): string
    {
        // Campo 00: Payload Format Indicator (sempre "01")
        $emv = $this->formatEMVField('00', '01');
        
        // Campo 01: Point of Initiation Method ("12" = dinâmico)
        $emv .= $this->formatEMVField('01', '12');
        
        // Campo 26: Merchant Account Information (GUI + Location)
        $merchantAccount = $this->formatEMVField('00', 'br.gov.bcb.pix');
        $merchantAccount .= $this->formatEMVField('25', $location);
        $emv .= $this->formatEMVField('26', $merchantAccount);
        
        // Campo 52: Merchant Category Code (sempre "0000")
        $emv .= $this->formatEMVField('52', '0000');
        
        // Campo 53: Transaction Currency (986 = Real Brasileiro)
        $emv .= $this->formatEMVField('53', '986');
        
        // Campo 54: Transaction Amount
        $formattedAmount = number_format($amount, 2, '.', '');
        $emv .= $this->formatEMVField('54', $formattedAmount);
        
        // Campo 58: Country Code (BR = Brasil)
        $emv .= $this->formatEMVField('58', 'BR');
        
        // Campo 59: Merchant Name (sem acentos ou caracteres especiais)
        $emv .= $this->formatEMVField('59', $this->merchantName);
        
        // Campo 60: Merchant City (sem acentos ou caracteres especiais)
        $emv .= $this->formatEMVField('60', $this->merchantCity);
        
        // Campo 62: Additional Data Field Template (opcional: TXId)
        $additionalData = $this->formatEMVField('05', '***');
        $emv .= $this->formatEMVField('62', $additionalData);
        
        // Campo 63: CRC16-CCITT (calculado sobre todo o EMV + "6304")
        $crcInput = $emv . '6304';
        $crc = $this->calculateCRC16($crcInput);
        $crcHex = strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
        $emv .= '6304' . $crcHex;

        Log::info('✅ String EMV gerada', [
            'length' => strlen($emv),
            'crc' => $crcHex,
        ]);

        return $emv;
    }

    /**
     * Formatar campo EMV no padrão: ID (2) + Tamanho (2) + Conteúdo
     */
    private function formatEMVField(string $id, string $content): string
    {
        $length = str_pad(strlen($content), 2, '0', STR_PAD_LEFT);
        return $id . $length . $content;
    }

    /**
     * Calcular CRC16-CCITT conforme especificação EMV
     * Algoritmo: CRC-16-CCITT (polinômio 0x1021)
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
     * Sanitizar nome do comerciante (remover acentos e caracteres especiais)
     */
    private function sanitizeMerchantName(string $name): string
    {
        // Remover acentos
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        
        // Manter apenas letras, números e espaços
        $name = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
        
        // Limitar a 25 caracteres
        return substr($name, 0, 25);
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
