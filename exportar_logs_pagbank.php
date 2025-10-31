<?php
/**
 * Script para exportar logs do PagBank para validação
 * 
 * Este script lê os logs do PagBank e exporta em formato JSON
 * para enviar ao suporte do PagBank para validação da integração.
 * 
 * Uso:
 * 1. Execute transações de teste no sistema
 * 2. Execute este script: php exportar_logs_pagbank.php
 * 3. O arquivo será salvo em: storage/logs/pagbank_validation_logs.json
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== EXPORTADOR DE LOGS PAGBANK PARA VALIDAÇÃO ===\n\n";

// Caminho do arquivo de log do PagBank
$logPath = storage_path('logs/pagbank.log');

// Verificar se o arquivo existe
if (!file_exists($logPath)) {
    echo "❌ Arquivo de log não encontrado: {$logPath}\n";
    echo "   Execute algumas transações primeiro para gerar os logs.\n";
    exit(1);
}

echo "📂 Lendo logs de: {$logPath}\n";

// Ler o arquivo de log
$logContent = file_get_contents($logPath);
$lines = explode("\n", $logContent);

$requests = [];
$responses = [];
$webhooks = [];
$currentContext = null;

echo "📊 Processando " . count($lines) . " linhas...\n";

foreach ($lines as $line) {
    if (empty(trim($line))) {
        continue;
    }

    // Tentar parsear como JSON (formato Laravel)
    if (preg_match('/\{.*\}/', $line, $matches)) {
        $jsonStr = $matches[0];
        $data = json_decode($jsonStr, true);
        
        if ($data && isset($data['message'])) {
            $message = $data['message'];
            
            // Identificar tipo de log
            if (strpos($message, '=== REQUEST: Criar Pedido PIX ===') !== false) {
                $currentContext = [
                    'type' => 'create_order',
                    'timestamp' => $data['context']['timestamp'] ?? date('c'),
                    'endpoint' => $data['context']['endpoint'] ?? '',
                    'method' => $data['context']['method'] ?? 'POST',
                    'environment' => $data['context']['environment'] ?? 'sandbox',
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
                    $requests[] = $currentContext;
                    $currentContext = null;
                }
            } elseif (strpos($message, '=== WEBHOOK RECEBIDO ===') !== false) {
                $webhooks[] = [
                    'timestamp' => $data['context']['timestamp'] ?? date('c'),
                    'webhook_data' => $data['context']['webhook_data'] ?? [],
                ];
            }
        }
    }
}

echo "✅ Processamento concluído!\n\n";
echo "📈 Estatísticas:\n";
echo "   - Requests/Responses: " . count($requests) . "\n";
echo "   - Webhooks: " . count($webhooks) . "\n\n";

// Preparar dados para exportação
$exportData = [
    'export_info' => [
        'generated_at' => date('c'),
        'system' => 'WiFi Tocantins',
        'purpose' => 'Validação de integração PagBank',
        'environment' => config('wifi.payment_gateways.pix.environment', 'sandbox'),
    ],
    'transactions' => [],
];

// Adicionar transações (requests + responses)
foreach ($requests as $index => $request) {
    $transaction = [
        'transaction_number' => $index + 1,
        'timestamp' => $request['timestamp'],
        'endpoint' => $request['endpoint'],
        'method' => $request['method'],
    ];
    
    if (isset($request['request'])) {
        $transaction['request'] = $request['request'];
    }
    
    if (isset($request['response'])) {
        $transaction['response'] = $request['response'];
    }
    
    $exportData['transactions'][] = $transaction;
}

// Adicionar webhooks
$exportData['webhooks'] = $webhooks;

// Salvar arquivo JSON
$outputPath = storage_path('logs/pagbank_validation_logs.json');
file_put_contents($outputPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "💾 Arquivo exportado com sucesso!\n";
echo "📁 Localização: {$outputPath}\n\n";

// Também criar um arquivo de exemplo formatado para cada transação
if (!empty($exportData['transactions'])) {
    echo "📝 Criando exemplos individuais...\n";
    
    $examplesDir = storage_path('logs/pagbank_examples');
    if (!is_dir($examplesDir)) {
        mkdir($examplesDir, 0755, true);
    }
    
    foreach ($exportData['transactions'] as $index => $transaction) {
        $exampleFile = $examplesDir . "/transacao_" . ($index + 1) . ".json";
        
        $example = [
            'TRANSACAO' => $index + 1,
            'TIMESTAMP' => $transaction['timestamp'],
            'REQUEST' => $transaction['request'] ?? [],
            'RESPONSE' => $transaction['response'] ?? [],
        ];
        
        file_put_contents($exampleFile, json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    
    echo "✅ " . count($exportData['transactions']) . " exemplos criados em: {$examplesDir}\n\n";
}

echo "=== INSTRUÇÕES PARA ENVIO AO PAGBANK ===\n\n";
echo "1. Abra o arquivo: {$outputPath}\n";
echo "2. Ou use os exemplos individuais em: " . storage_path('logs/pagbank_examples') . "\n";
echo "3. Envie ao suporte do PagBank para validação\n";
echo "4. Inclua informações sobre os meios de pagamento testados:\n";
echo "   - PIX (QR Code)\n";
echo "   - Cartão de Crédito (se implementado)\n\n";

echo "✅ Processo concluído!\n";
