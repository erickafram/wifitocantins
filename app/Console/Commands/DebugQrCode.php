<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WooviPixService;
use App\Services\PixQRCodeService;

class DebugQrCode extends Command
{
    protected $signature = 'debug:qrcode {--test-payment : Criar um pagamento de teste}';
    protected $description = 'Debug da geração de QR Code PIX';

    public function handle()
    {
        $this->info('=== DEBUG QR CODE PIX ===');
        $this->line('Ambiente: ' . config('app.env'));
        $this->line('URL da App: ' . config('app.url'));
        $this->line('PIX Habilitado: ' . (config('wifi.payment_gateways.pix.enabled') ? 'SIM' : 'NÃO'));
        $this->line('Gateway PIX: ' . config('wifi.payment_gateways.pix.gateway'));
        $this->line('Ambiente PIX: ' . config('wifi.payment_gateways.pix.environment'));
        $this->line('Woovi App ID: ' . substr(config('wifi.payment_gateways.pix.woovi_app_id'), 0, 20) . '...');
        $this->line('Woovi App Secret: ' . (config('wifi.payment_gateways.pix.woovi_app_secret') ? 'CONFIGURADO' : 'NÃO CONFIGURADO'));
        $this->newLine();

        // Testar conexão com Woovi
        $this->info('=== TESTANDO CONEXÃO WOOVI ===');
        $wooviService = new WooviPixService();
        $connectionTest = $wooviService->testConnection();

        if ($connectionTest['success']) {
            $this->info('✅ Status: SUCESSO');
            $this->line('Mensagem: ' . $connectionTest['message']);
            if (isset($connectionTest['status_code'])) {
                $this->line('Status Code: ' . $connectionTest['status_code']);
            }
        } else {
            $this->error('❌ Status: ERRO');
            $this->line('Mensagem: ' . $connectionTest['message']);
        }
        $this->newLine();

        if ($connectionTest['success'] && $this->option('test-payment')) {
            // Testar criação de pagamento
            $this->info('=== TESTANDO CRIAÇÃO DE PAGAMENTO ===');
            
            $testAmount = 5.99;
            $testDescription = 'Teste QR Code - Debug Artisan';
            $testCorrelationId = 'DEBUG_ARTISAN_' . time();
            
            $this->line('Valor: R$ ' . number_format($testAmount, 2, ',', '.'));
            $this->line('Descrição: ' . $testDescription);
            $this->line('Correlation ID: ' . $testCorrelationId);
            $this->newLine();
            
            $paymentResult = $wooviService->createPixPayment($testAmount, $testDescription, $testCorrelationId);
            
            if ($paymentResult['success']) {
                $this->info('✅ PAGAMENTO CRIADO COM SUCESSO!');
                $this->line('Woovi ID: ' . $paymentResult['woovi_id']);
                $this->line('Correlation ID: ' . $paymentResult['correlation_id']);
                $this->line('Status: ' . $paymentResult['status']);
                $this->line('Expira em: ' . $paymentResult['expires_at']);
                $this->newLine();
                
                // Verificar QR Code
                $this->info('=== VERIFICANDO QR CODE ===');
                $this->line('EMV String: ' . substr($paymentResult['qr_code_text'], 0, 50) . '...');
                $this->line('Tamanho EMV: ' . strlen($paymentResult['qr_code_text']) . ' caracteres');
                
                if (!empty($paymentResult['qr_code_image'])) {
                    $this->info('✅ Imagem QR Code: PRESENTE (Base64)');
                    $this->line('Tamanho da imagem: ' . strlen($paymentResult['qr_code_image']) . ' caracteres');
                    
                    // Verificar se é base64 válido
                    $decodedImage = base64_decode($paymentResult['qr_code_image'], true);
                    if ($decodedImage !== false) {
                        $this->info('✅ Base64: VÁLIDO');
                        $this->line('Tamanho decodificado: ' . strlen($decodedImage) . ' bytes');
                        
                        // Verificar se é PNG válido
                        $imageInfo = @getimagesizefromstring($decodedImage);
                        if ($imageInfo !== false) {
                            $this->info('✅ Imagem: VÁLIDA (' . $imageInfo[0] . 'x' . $imageInfo[1] . ' pixels, ' . $imageInfo['mime'] . ')');
                            
                            // Salvar imagem para teste
                            $filename = storage_path('app/debug_qrcode_' . time() . '.png');
                            if (file_put_contents($filename, $decodedImage)) {
                                $this->info('✅ Imagem salva como: ' . $filename);
                            } else {
                                $this->error('❌ Erro ao salvar imagem');
                            }
                        } else {
                            $this->error('❌ Imagem: INVÁLIDA - Não é uma imagem válida');
                        }
                    } else {
                        $this->error('❌ Base64: INVÁLIDO');
                    }
                } else {
                    $this->error('❌ Imagem QR Code: AUSENTE');
                }
                
                $this->newLine();
                $this->info('=== URL DATA COMPLETA ===');
                $dataUrl = 'data:image/png;base64,' . $paymentResult['qr_code_image'];
                $this->line('Tamanho total da URL: ' . strlen($dataUrl) . ' caracteres');
                $this->line('Início da URL: ' . substr($dataUrl, 0, 100) . '...');
                
            } else {
                $this->error('❌ ERRO AO CRIAR PAGAMENTO!');
                $this->line('Mensagem: ' . $paymentResult['message']);
            }
        } else if (!$connectionTest['success']) {
            $this->error('❌ Não foi possível testar criação de pagamento devido ao erro de conexão.');
        }

        $this->newLine();
        $this->info('=== FIM DO DEBUG ===');
        
        if (!$this->option('test-payment')) {
            $this->comment('Use --test-payment para criar um pagamento de teste e verificar o QR Code');
        }
    }
} 