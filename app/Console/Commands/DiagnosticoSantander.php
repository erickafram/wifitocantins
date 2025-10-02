<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiagnosticoSantander extends Command
{
    protected $signature = 'santander:diagnostico';
    protected $description = 'Diagnóstico DETALHADO da integração Santander PIX';

    public function handle()
    {
        $this->info('');
        $this->line('==========================================');
        $this->info('🔬 DIAGNÓSTICO DETALHADO - SANTANDER PIX');
        $this->line('==========================================');
        $this->info('');

        // ===========================
        // PARTE 1: CONFIGURAÇÕES
        // ===========================
        $this->line('📋 PARTE 1: CONFIGURAÇÕES');
        $this->line('==========================================');
        $this->info('');

        $config = [
            'environment' => config('wifi.payment_gateways.pix.santander.environment'),
            'client_id' => config('wifi.payment_gateways.pix.santander.client_id'),
            'client_secret' => config('wifi.payment_gateways.pix.santander.client_secret'),
            'certificate_path' => config('wifi.payment_gateways.pix.santander.certificate_path'),
            'pix_key' => config('wifi.payment_gateways.pix.santander.pix_key'),
        ];

        $baseUrl = $config['environment'] === 'production' 
            ? 'https://trust-pix.santander.com.br' 
            : 'https://trust-pix-h.santander.com.br';

        $this->table(
            ['Configuração', 'Valor'],
            [
                ['Ambiente', $config['environment']],
                ['Base URL', $baseUrl],
                ['Client ID', substr($config['client_id'], 0, 20) . '...'],
                ['Client Secret', str_repeat('*', 10) . ' (OK)'],
                ['PIX Key', $config['pix_key']],
                ['Cert Path', $config['certificate_path']],
            ]
        );

        $this->info('');

        // Verificar certificado
        $certPathConfig = $config['certificate_path'];
        
        // Construir caminho correto
        if (empty($certPathConfig)) {
            $this->error("❌ ERRO: SANTANDER_CERTIFICATE_PATH não configurado no .env");
            return 1;
        }
        
        // Se o caminho começa com /, é absoluto
        if (substr($certPathConfig, 0, 1) === '/') {
            $certPath = $certPathConfig;
        } else {
            // Caso contrário, é relativo ao storage/app
            $certPath = storage_path('app/' . str_replace('storage/app/', '', $certPathConfig));
        }
        
        if (!file_exists($certPath)) {
            $this->error("❌ ERRO: Certificado não encontrado em: $certPath");
            return 1;
        }

        if (is_dir($certPath)) {
            $this->error("❌ ERRO: O caminho aponta para um DIRETÓRIO, não um arquivo!");
            $this->warn("Caminho: $certPath");
            $this->warn("Verifique SANTANDER_CERTIFICATE_PATH no .env - deve apontar para o arquivo .pem");
            return 1;
        }

        $this->info("✅ Certificado encontrado");
        
        // Contar chaves privadas e certificados
        $certContent = file_get_contents($certPath);
        $privateKeyCount = substr_count($certContent, 'BEGIN PRIVATE KEY') + 
                          substr_count($certContent, 'BEGIN RSA PRIVATE KEY') + 
                          substr_count($certContent, 'BEGIN ENCRYPTED PRIVATE KEY');
        $certCount = substr_count($certContent, 'BEGIN CERTIFICATE');
        
        $this->line("   Chaves Privadas: $privateKeyCount");
        $this->line("   Certificados: $certCount");
        $this->info('');

        // ===========================
        // PARTE 2: AUTENTICAÇÃO OAUTH
        // ===========================
        $this->line('==========================================');
        $this->line('📋 PARTE 2: AUTENTICAÇÃO OAUTH');
        $this->line('==========================================');
        $this->info('');

        $this->info('📤 Enviando requisição OAuth...');
        
        $basicAuth = base64_encode($config['client_id'] . ':' . $config['client_secret']);
        
        $this->line('   URL: ' . $baseUrl . '/auth/oauth/v2/token');
        $this->line('   Method: POST');
        $this->line('   Headers:');
        $this->line('     Authorization: Basic [REDACTED]');
        $this->line('     Content-Type: application/x-www-form-urlencoded');
        $this->info('');

        try {
            $response = Http::withOptions([
                'cert' => $certPath,
                'verify' => true,
            ])
            ->withHeaders([
                'Authorization' => 'Basic ' . $basicAuth,
            ])
            ->asForm()
            ->post($baseUrl . '/auth/oauth/v2/token', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'grant_type' => 'client_credentials',
            ]);

            $this->info('📥 Resposta OAuth:');
            $this->line('   HTTP Status: ' . $response->status());
            $this->info('');

            if (!$response->successful()) {
                $this->error('❌ ERRO: Falha na autenticação OAuth!');
                $this->line('');
                $this->line('Resposta:');
                $this->line($response->body());
                return 1;
            }

            $this->info('✅ Autenticação OAuth bem-sucedida!');
            
            $tokenData = $response->json();
            $token = $tokenData['access_token'] ?? null;

            if (!$token) {
                $this->error('❌ ERRO: Token não encontrado na resposta');
                return 1;
            }

            $this->line('   Token obtido (primeiros 50 chars): ' . substr($token, 0, 50) . '...');
            $this->info('');

        } catch (\Exception $e) {
            $this->error('❌ ERRO na requisição OAuth: ' . $e->getMessage());
            return 1;
        }

        // ===========================
        // PARTE 3: ANÁLISE DO TOKEN JWT
        // ===========================
        $this->line('==========================================');
        $this->line('📋 PARTE 3: ANÁLISE DO TOKEN JWT');
        $this->line('==========================================');
        $this->info('');

        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            $this->error('❌ ERRO: Token JWT inválido');
            return 1;
        }

        // Decodificar header
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        
        $this->line('📌 HEADER do JWT:');
        $this->line(json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('');

        // Decodificar payload
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        
        $this->line('📌 PAYLOAD do JWT:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('');

        // Análise crítica
        $this->line('📌 CAMPOS CRÍTICOS DO TOKEN:');
        $this->table(
            ['Campo', 'Valor', 'Status'],
            [
                ['Issuer (iss)', $payload['iss'] ?? 'N/A', ''],
                ['Audience (aud)', $payload['aud'] ?? 'N/A', $payload['aud'] === 'Santander Open API' ? '⚠️ Genérico' : ''],
                ['Algorithm (alg)', $header['alg'] ?? 'N/A', $header['alg'] === 'RS256' ? '✅' : '⚠️'],
                ['Scope', '"' . ($payload['scope'] ?? '') . '"', empty($payload['scope']) ? '⚠️ VAZIO' : '✅'],
                ['Client ID', $payload['clientId'] ?? 'N/A', ''],
                ['Expires (exp)', isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : 'N/A', ''],
            ]
        );
        $this->info('');

        // ===========================
        // PARTE 4: TESTE API PIX
        // ===========================
        $this->line('==========================================');
        $this->line('📋 PARTE 4: REQUISIÇÃO À API PIX');
        $this->line('==========================================');
        $this->info('');

        $txid = 'TESTE' . time() . 'WIFI' . strtoupper(substr(md5(rand()), 0, 15));
        $endpoint = '/api/v1/cob/' . $txid;

        $testPayload = [
            'calendario' => ['expiracao' => 900],
            'valor' => ['original' => '0.01'],
            'chave' => $config['pix_key'],
            'solicitacaoPagador' => 'Teste diagnostico detalhado',
        ];

        $this->info('📤 REQUISIÇÃO PIX:');
        $this->line('   URL: ' . $baseUrl . $endpoint);
        $this->line('   Method: PUT');
        $this->line('   Headers:');
        $this->line('     Authorization: Bearer [TOKEN]');
        $this->line('     Content-Type: application/json');
        $this->line('     X-Application-Key: ' . $config['client_id']);
        $this->line('   Body:');
        $this->line('   ' . json_encode($testPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('');

        try {
            $pixResponse = Http::withOptions([
                'cert' => $certPath,
                'verify' => true,
                'timeout' => 30,
            ])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'X-Application-Key' => $config['client_id'],
            ])
            ->put($baseUrl . $endpoint, $testPayload);

            $this->info('📥 RESPOSTA PIX:');
            $this->line('   HTTP Status: ' . $pixResponse->status());
            $this->info('');

            // Headers da resposta
            $this->line('📌 Headers da Resposta:');
            foreach ($pixResponse->headers() as $key => $values) {
                if (in_array(strtolower($key), ['content-type', 'www-authenticate', 'x-application-key', 'x-correlation-id'])) {
                    $this->line('   ' . $key . ': ' . implode(', ', $values));
                }
            }
            $this->info('');

            $this->line('📌 Body da Resposta:');
            $this->line($pixResponse->body());
            $this->info('');

            // ===========================
            // PARTE 5: ANÁLISE DO ERRO
            // ===========================
            $this->line('==========================================');
            $this->line('📋 PARTE 5: ANÁLISE DO ERRO');
            $this->line('==========================================');
            $this->info('');

            if ($pixResponse->successful()) {
                $this->info('✅ SUCESSO! A cobrança PIX foi criada!');
                $this->info('');
                $this->info('🎉 Integração funcionando corretamente!');
                return 0;
            }

            $errorBody = $pixResponse->json();
            
            if (isset($errorBody['fault'])) {
                $this->line('📌 ERRO IDENTIFICADO:');
                $this->line('   Tipo: ' . ($errorBody['fault']['detail']['errorcode'] ?? 'N/A'));
                $this->line('   Mensagem: ' . ($errorBody['fault']['faultstring'] ?? 'N/A'));
                $this->info('');

                // Diagnóstico específico
                if (strpos($errorBody['fault']['faultstring'] ?? '', 'AlgorithmMismatch') !== false) {
                    $this->line('==========================================');
                    $this->line('📋 PARTE 6: DIAGNÓSTICO DO PROBLEMA');
                    $this->line('==========================================');
                    $this->info('');
                    
                    $this->error('🔴 ERRO: AlgorithmMismatch na policy VJWT-Token');
                    $this->info('');
                    
                    $this->line('📌 CAUSAS POSSÍVEIS:');
                    $this->info('');
                    
                    $this->warn('1️⃣ API PIX NÃO HABILITADA na aplicação (MAIS PROVÁVEL)');
                    $this->line('   ├─ As credenciais funcionam para OAuth');
                    $this->line('   ├─ Mas a aplicação "STARLINK QR CODE" não tem permissão para API PIX');
                    $this->line('   └─ Solução: Habilitar API PIX no Portal do Desenvolvedor');
                    $this->info('');
                    
                    $this->warn('2️⃣ FALTA ASSINATURA JWS (JSON Web Signature)');
                    $this->line('   ├─ API PIX pode requerer assinatura do payload');
                    $this->line('   ├─ Header necessário: x-jws-signature');
                    $this->line('   └─ Solução: Confirmar com Santander se JWS é obrigatório');
                    $this->info('');
                    
                    $this->warn('3️⃣ ESCOPO VAZIO no token JWT');
                    $this->line('   ├─ Scope atual: "' . ($payload['scope'] ?? '') . '"');
                    $this->line('   ├─ Esperado: scopes PIX (cob.write, cob.read, etc.)');
                    $this->line('   └─ Solução: Habilitar API PIX para obter scopes corretos');
                    $this->info('');
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ ERRO na requisição PIX: ' . $e->getMessage());
            $this->info('');
        }

        // ===========================
        // PARTE 7: PRÓXIMOS PASSOS
        // ===========================
        $this->line('==========================================');
        $this->line('📋 PARTE 7: PRÓXIMOS PASSOS');
        $this->line('==========================================');
        $this->info('');

        $this->info('✅ AÇÕES RECOMENDADAS:');
        $this->info('');
        
        $this->line('1. Acessar: https://developer.santander.com.br');
        $this->line('   └─ Ir em "Minhas Aplicações" > "STARLINK QR CODE" > "APIs Associadas"');
        $this->line('   └─ Verificar se "API Pix - Geração de QRCode" está HABILITADA');
        $this->info('');
        
        $this->line('2. Se NÃO estiver habilitada:');
        $this->line('   └─ Clicar em "Adicionar API" ou "Associar Produto"');
        $this->line('   └─ Habilitar a "API Pix - Geração de QRCode"');
        $this->info('');
        
        $this->line('3. Entrar em contato com suporte Santander:');
        $this->line('   └─ Assunto: API PIX - Erro AlgorithmMismatch na policy VJWT-Token');
        $this->line('   └─ Perguntar: A aplicação tem a API PIX habilitada?');
        $this->line('   └─ Perguntar: A API PIX requer JWS (assinatura do payload)?');
        $this->info('');

        $this->line('==========================================');
        $this->line('📄 DOCUMENTOS DE APOIO');
        $this->line('==========================================');
        $this->info('');
        $this->line('Use estes documentos ao contatar o Santander:');
        $this->line('  ✅ DIAGNOSTICO_FINAL_SANTANDER_PIX.md');
        $this->line('  ✅ PERGUNTAS_CRITICAS_SANTANDER.md');
        $this->info('');

        $this->line('==========================================');
        $this->info('FIM DO DIAGNÓSTICO');
        $this->line('==========================================');

        return 1;
    }
} 