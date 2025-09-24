<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PixQRCodeService;

class PixQRCodeTest extends TestCase
{
    private PixQRCodeService $pixService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pixService = new PixQRCodeService();
    }

    /** @test */
    public function pode_gerar_qr_code_pix_valido()
    {
        $valor = 0.10;
        $transactionId = 'TEST_TXN_123';

        $result = $this->pixService->generatePixQRCode($valor, $transactionId);

        $this->assertArrayHasKey('emv_string', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertArrayHasKey('location', $result);
        
        
        // Verificar se EMV é válida
        $this->assertTrue($this->pixService->validateEMV($result['emv_string']));
        
        // Verificar se valor está correto
        $this->assertEquals('0.10', $result['amount']);
        
        // Verificar se contém campos obrigatórios
        $emvString = $result['emv_string'];
        $this->assertStringContainsString('0002', $emvString); // Payload Format
        $this->assertStringContainsString('0102', $emvString); // Point of Initiation
        $this->assertStringContainsString('br.gov.bcb.pix', $emvString); // GUI
        $this->assertStringContainsString('5802BR', $emvString); // Country Code
    }

    /** @test */
    public function pode_gerar_url_imagem_qr_code()
    {
        $emvString = '00020101021226850014br.gov.bcb.pix2563pix.tocantins.com.br/qr/v2/test-uuid520400005303986545.995802BR5925WiFi Tocantins Express6006Palmas62070503***6304ABCD';
        
        $imageUrl = $this->pixService->generateQRCodeImageUrl($emvString);
        
        $this->assertStringContainsString('api.qrserver.com', $imageUrl);
        $this->assertStringContainsString('300x300', $imageUrl);
        $this->assertStringContainsString(urlencode($emvString), $imageUrl);
    }

    /** @test */
    public function valida_emv_corretamente()
    {
        // Primeiro gerar um EMV válido
        $result = $this->pixService->generatePixQRCode(10.50, 'TEST');
        $emvValido = $result['emv_string'];
        
        // Deve validar como verdadeiro
        $this->assertTrue($this->pixService->validateEMV($emvValido));
        
        // EMV inválido (muito curto)
        $this->assertFalse($this->pixService->validateEMV('123'));
        
        // EMV inválido (CRC errado)
        $emvInvalido = substr($emvValido, 0, -4) . '0000';
        $this->assertFalse($this->pixService->validateEMV($emvInvalido));
    }
}
