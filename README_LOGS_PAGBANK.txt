================================================================================
  GUIA RÁPIDO: LOGS PAGBANK PARA VALIDAÇÃO
================================================================================

RESPOSTA RÁPIDA:
Os logs devem ser capturados NO SEU SISTEMA, não no painel do PagBank!

================================================================================
ONDE ESTÃO OS LOGS?
================================================================================

Arquivo de log:
  c:\wamp64\www\wifitocantins\storage\logs\pagbank.log

Este arquivo contém TODOS os requests, responses e webhooks do PagBank.

================================================================================
COMO GERAR OS LOGS?
================================================================================

PASSO 1: Fazer transações de teste
  - Acesse o sistema WiFi
  - Faça pagamentos via PIX
  - Aguarde a confirmação do webhook

PASSO 2: Exportar os logs

  OPÇÃO A - Via Script PHP:
    cd c:\wamp64\www\wifitocantins
    php exportar_logs_pagbank.php

  OPÇÃO B - Via API (mais fácil):
    Acesse no navegador:
    http://seu-dominio/api/payment/export-pagbank-logs

PASSO 3: Pegar os arquivos gerados
  - Arquivo completo: storage/logs/pagbank_validation_export.json
  - Exemplos individuais: storage/logs/pagbank_examples/

================================================================================
COMO ENVIAR AO PAGBANK?
================================================================================

1. Anexe o arquivo JSON ao email
2. Informe o ambiente (sandbox ou produção)
3. Liste os meios de pagamento testados:
   - PIX (QR Code)
   - Cartão de Crédito (se implementado)

Assunto do email:
  "Logs de Integração PagBank - WiFi Tocantins"

================================================================================
VERIFICAÇÃO RÁPIDA
================================================================================

Para ver se tem logs:
  - Abra: storage/logs/pagbank.log
  - Procure por: "=== REQUEST: Criar Pedido PIX ==="

Se o arquivo estiver vazio:
  - Execute transações no sistema primeiro
  - Verifique se o gateway está configurado como "pagbank"

================================================================================
ENDPOINTS ÚTEIS
================================================================================

Testar conexão:
  GET http://seu-dominio/api/payment/test-pagbank

Exportar logs:
  GET http://seu-dominio/api/payment/export-pagbank-logs

Webhook (para o PagBank enviar notificações):
  POST http://seu-dominio/api/payment/webhook/pagbank

================================================================================
DOCUMENTAÇÃO COMPLETA
================================================================================

Leia o arquivo: INSTRUCOES_LOGS_PAGBANK.md

================================================================================
