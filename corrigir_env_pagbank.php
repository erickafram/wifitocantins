#!/usr/bin/env php
<?php
/**
 * Script para corrigir automaticamente o .env para usar PagBank
 * 
 * Execução: php corrigir_env_pagbank.php
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     CORREÇÃO AUTOMÁTICA - PAGBANK PIX              ║\n";
echo "╚══════════════════════════════════════════════════════╝\n";
echo "\n";

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ ERRO: Arquivo .env não encontrado!\n";
    echo "   Certifique-se de estar na pasta raiz do projeto.\n\n";
    exit(1);
}

echo "📄 Lendo arquivo .env...\n";
$envContent = file_get_contents($envFile);

if ($envContent === false) {
    echo "❌ ERRO: Não foi possível ler o arquivo .env\n\n";
    exit(1);
}

echo "✅ Arquivo .env carregado com sucesso\n\n";

// Fazer backup
$backupFile = __DIR__ . '/.env.backup.' . date('Y-m-d_H-i-s');
file_put_contents($backupFile, $envContent);
echo "💾 Backup criado: " . basename($backupFile) . "\n\n";

// Contador de alterações
$alteracoes = 0;

echo "🔧 Aplicando correções...\n";
echo str_repeat("-", 60) . "\n\n";

// 1. Corrigir PIX_GATEWAY
if (preg_match('/^PIX_GATEWAY=.*/m', $envContent)) {
    $novoConteudo = preg_replace(
        '/^PIX_GATEWAY=.*/m',
        'PIX_GATEWAY=pagbank',
        $envContent
    );
    
    if ($novoConteudo !== $envContent) {
        echo "✅ PIX_GATEWAY alterado para: pagbank\n";
        $envContent = $novoConteudo;
        $alteracoes++;
    } else {
        echo "ℹ️  PIX_GATEWAY já está correto: pagbank\n";
    }
} else {
    // Adicionar se não existe
    $envContent .= "\nPIX_GATEWAY=pagbank\n";
    echo "✅ PIX_GATEWAY adicionado: pagbank\n";
    $alteracoes++;
}

// 2. Corrigir PIX_ENVIRONMENT
if (preg_match('/^PIX_ENVIRONMENT=.*/m', $envContent)) {
    $novoConteudo = preg_replace(
        '/^PIX_ENVIRONMENT=.*/m',
        'PIX_ENVIRONMENT=sandbox',
        $envContent
    );
    
    if ($novoConteudo !== $envContent) {
        echo "✅ PIX_ENVIRONMENT alterado para: sandbox\n";
        $envContent = $novoConteudo;
        $alteracoes++;
    } else {
        echo "ℹ️  PIX_ENVIRONMENT já está correto: sandbox\n";
    }
} else {
    // Adicionar se não existe
    $envContent .= "PIX_ENVIRONMENT=sandbox\n";
    echo "✅ PIX_ENVIRONMENT adicionado: sandbox\n";
    $alteracoes++;
}

// 3. Verificar se PAGBANK_TOKEN existe
if (!preg_match('/^PAGBANK_TOKEN=.*/m', $envContent)) {
    echo "⚠️  PAGBANK_TOKEN não encontrado no .env\n";
    echo "   Por favor, adicione manualmente:\n";
    echo "   PAGBANK_TOKEN=seu_token_aqui\n\n";
}

// 4. Verificar se PAGBANK_EMAIL existe
if (!preg_match('/^PAGBANK_EMAIL=.*/m', $envContent)) {
    echo "⚠️  PAGBANK_EMAIL não encontrado no .env\n";
    echo "   Por favor, adicione manualmente:\n";
    echo "   PAGBANK_EMAIL=seu_email@example.com\n\n";
}

echo "\n";
echo str_repeat("-", 60) . "\n";

if ($alteracoes > 0) {
    // Salvar alterações
    if (file_put_contents($envFile, $envContent)) {
        echo "✅ Arquivo .env atualizado com sucesso!\n";
        echo "   Total de alterações: $alteracoes\n\n";
        
        echo "📋 Configuração final:\n";
        echo "   PIX_GATEWAY=pagbank\n";
        echo "   PIX_ENVIRONMENT=sandbox\n\n";
        
        echo "🔄 Próximo passo:\n";
        echo "   Execute: php artisan config:clear\n\n";
        
        echo "✨ Pronto! Agora você pode testar o PIX no portal.\n\n";
        
    } else {
        echo "❌ ERRO: Não foi possível salvar o arquivo .env\n";
        echo "   Verifique as permissões do arquivo.\n\n";
        exit(1);
    }
} else {
    echo "ℹ️  Nenhuma alteração necessária.\n";
    echo "   O .env já está configurado corretamente.\n\n";
}

echo "═══════════════════════════════════════════════════════\n";
echo "                 CORREÇÃO CONCLUÍDA                     \n";
echo "═══════════════════════════════════════════════════════\n\n";

