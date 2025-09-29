<?php

// Script para testar validação de assinatura Woovi/OpenPix

echo "🔐 TESTE DE VALIDAÇÃO DE ASSINATURA WOOVI\n";
echo "=========================================\n\n";

// Dados do webhook real dos logs
$receivedSignature = "BDQ1FdZPYKZXzKx3MuBkgp+GcTRTkSdjyzYF9F9/lxRiXGR/yfBAua4sgVZFyz4TQYRArWdK/novfBZYtJ4tFTlY4pLDjMFFjXyVivUMYQ2uIakp3UgDSlMtrf3wHWNxo5FmFwZ+Rs2WKAjAUBCJFy1yoKaSExxO5yYVKLZ8GjQ=";

// Payload do webhook (JSON)
$payload = '{"event":"OPENPIX:CHARGE_COMPLETED","charge":{"customer":{"name":"ERICK VINICIUS RODRIGUES","email":"cliente@wifitocantins.com.br","phone":"+556399999999","taxID":{"taxID":"57732545000100","type":"BR:CNPJ"},"correlationID":"3c91229a-c993-46ee-8765-a0fa227a1491"},"value":10,"comment":"WiFi Tocantins Express - Internet Premium","identifier":"4675089b5a5445e2886fb42db1e33f46","correlationID":"TXN_1759115640_49B57B85","transactionID":"4675089b5a5445e2886fb42db1e33f46","status":"ACTIVE","additionalInfo":[],"fee":85,"discount":0,"valueWithDiscount":10,"expiresDate":"2025-09-29T04:14:01.466Z","type":"DYNAMIC","paymentLinkID":"00ca5e6f-e923-4cf6-9e77-778e9cf14b34","createdAt":"2025-09-29T03:14:01.563Z","updatedAt":"2025-09-29T03:14:01.563Z","ensureSameTaxID":false,"brCode":"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/1df7b3cb-c9ea-4ec6-913f-e8b4546ce52552040000530398654040.105802BR592357732545_ERICK_VINICIUS6009Sao_Paulo622905254675089b5a5445e2886fb630460B8","expiresIn":3600,"pixKey":"acd05342-22f3-49c2-8598-45daaf15744b","paymentLinkUrl":"https://openpix.com.br/pay/00ca5e6f-e923-4cf6-9e77-778e9cf14b34","qrCodeImage":"https://api.openpix.com.br/openpix/charge/brcode/image/00ca5e6f-e923-4cf6-9e77-778e9cf14b34.png","globalID":"Q2hhcmdlOjY4ZDlmOTc5MmE1MThhNDY3M2E3NWFlMw==","paymentMethods":{"pix":{"method":"PIX_COB","status":"ACTIVE","value":10,"txId":"4675089b5a5445e2886fb42db1e33f46","fee":85,"brCode":"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/1df7b3cb-c9ea-4ec6-913f-e8b4546ce52552040000530398654040.105802BR592357732545_ERICK_VINICIUS6009Sao_Paulo622905254675089b5a5445e2886fb630460B8","transactionID":"4675089b5a5445e2886fb42db1e33f46","identifier":"4675089b5a5445e2886fb42db1e33f46","qrCodeImage":"https://api.openpix.com.br/openpix/charge/brcode/image/00ca5e6f-e923-4cf6-9e77-778e9cf14b34.png"}}},"pix":{"debitParty":{"account":{"branch":"0001","account":"1039136990","accountType":"CACC"},"psp":{"id":"22896431","name":"PICPAY"},"holder":{"taxID":{"taxID":"07886155130","type":"BR:CPF"},"name":"KAUANY NERES DE NAZARE"}},"creditParty":{"pixKey":{"pixKey":"acd05342-22f3-49c2-8598-45daaf15744b","type":"RANDOM"},"account":{"branch":"0001","account":"1235400","accountType":"TRAN"},"psp":{"id":"54811417","name":"WOOVI IP LTDA."},"holder":{"taxID":{"taxID":"57732545000100","type":"BR:CNPJ"}}},"customer":{"name":"ERICK VINICIUS RODRIGUES","email":"cliente@wifitocantins.com.br","phone":"+556399999999","taxID":{"taxID":"57732545000100","type":"BR:CNPJ"},"correlationID":"3c91229a-c993-46ee-8765-a0fa227a1491"},"payer":{"name":"KAUANY NERES DE NAZARE","taxID":{"taxID":"07886155130","type":"BR:CPF"},"correlationID":"6a0bdb7f-f510-4757-8d4f-0e81cd89891f"},"charge":{"customer":{"name":"ERICK VINICIUS RODRIGUES","email":"cliente@wifitocantins.com.br","phone":"+556399999999","taxID":{"taxID":"57732545000100","type":"BR:CNPJ"},"correlationID":"3c91229a-c993-46ee-8765-a0fa227a1491"},"value":10,"comment":"WiFi Tocantins Express - Internet Premium","identifier":"4675089b5a5445e2886fb42db1e33f46","correlationID":"TXN_1759115640_49B57B85","transactionID":"4675089b5a5445e2886fb42db1e33f46","status":"ACTIVE","additionalInfo":[],"fee":85,"discount":0,"valueWithDiscount":10,"expiresDate":"2025-09-29T04:14:01.466Z","type":"DYNAMIC","paymentLinkID":"00ca5e6f-e923-4cf6-9e77-778e9cf14b34","createdAt":"2025-09-29T03:14:01.563Z","updatedAt":"2025-09-29T03:14:01.563Z","ensureSameTaxID":false,"brCode":"00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/1df7b3cb-c9ea-4ec6-913f-e8b4546ce52552040000530398654040.105802BR592357732545_ERICK_VINICIUS6009Sao_Paulo622905254675089b5a5445e2886fb630460B8","expiresIn":3600,"pixKey":"acd05342-22f3-49c2-8598-45daaf15744b","paymentLinkUrl":"https://openpix.com.br/pay/00ca5e6f-e923-4cf6-9e77-778e9cf14b34","qrCodeImage":"https://api.openpix.com.br/openpix/charge/brcode/image/00ca5e6f-e923-4cf6-9e77-778e9cf14b34.png","globalID":"Q2hhcmdlOjY4ZDlmOTc5MmE1MThhNDY3M2E3NWFlMw=="},"value":10,"time":"2025-09-29T03:15:52.000Z","endToEndId":"E22896431202509290315qgI6V7ETcse","transactionID":"4675089b5a5445e2886fb42db1e33f46","status":"CONFIRMED","type":"PAYMENT","createdAt":"2025-09-29T03:15:52.307Z","globalID":"UGl4VHJhbnNhY3Rpb246NjhkOWY5ZThjN2U0YmNmODRhNjQ1ZGUy"},"company":{"id":"68caca98941631e25550170c","name":"57.732.545 ERICK VINICIUS RODRIGUES","taxID":"57732545000100"},"account":[],"authorization":null}';

// Tentar com vários segredos possíveis
$secrets = [
    // Segredo direto do .env
    'Q2xpZW50X0lkXzZlMTFjNjRmLTI1ZDgtNDUzZS1iMDc5LWJhNWIyZDIwNTc0ZTpDbGllbnRfU2VjcmV0X0hyMHZZV3NKOE8wRjJicVhqYkFuMHB6alh3c0JVVUlnT1NVQ01ZWW05Qnc9',
    
    // Segredo decodificado
    base64_decode('Q2xpZW50X0lkXzZlMTFjNjRmLTI1ZDgtNDUzZS1iMDc5LWJhNWIyZDIwNTc0ZTpDbGllbnRfU2VjcmV0X0hyMHZZV3NKOE8wRjJicVhqYkFuMHB6alh3c0JVVUlnT1NVQ01ZWW05Qnc9'),
    
    // Só a parte do segredo (depois dos :)
    'Client_Secret_Hr0vYWsJ8O0F2bqXjbAn0pzjXwsBUUIgOSUCMYYm9Bw=',
    base64_decode('Client_Secret_Hr0vYWsJ8O0F2bqXjbAn0pzjXwsBUUIgOSUCMYYm9Bw=')
];

echo "📊 Dados do teste:\n";
echo "  Assinatura recebida: " . substr($receivedSignature, 0, 50) . "...\n";
echo "  Tamanho do payload: " . strlen($payload) . " bytes\n\n";

foreach ($secrets as $index => $secret) {
    echo "🔐 TESTE " . ($index + 1) . ":\n";
    echo "  Segredo: " . substr($secret, 0, 30) . "...\n";
    echo "  Tamanho: " . strlen($secret) . " bytes\n";
    
    // Testar HMAC-SHA256 + Base64
    $rawHash = hash_hmac('sha256', $payload, $secret, true);
    $expectedSignature = base64_encode($rawHash);
    
    echo "  HMAC+Base64: " . substr($expectedSignature, 0, 50) . "...\n";
    
    $isValid = hash_equals($expectedSignature, $receivedSignature);
    echo "  ✅ Válida: " . ($isValid ? "SIM" : "NÃO") . "\n\n";
    
    if ($isValid) {
        echo "🎉 ASSINATURA VÁLIDA ENCONTRADA!\n";
        echo "  Usar segredo: " . $secret . "\n";
        break;
    }
}

echo "🔍 Detalhes adicionais:\n";
echo "  - Payload original em JSON (sem formatação)\n";
echo "  - Usando header 'x-webhook-signature'\n";
echo "  - Algoritmo: HMAC-SHA256 + Base64\n";
