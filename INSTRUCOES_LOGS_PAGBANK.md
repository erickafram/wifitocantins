# 📋 Instruções para Capturar e Enviar Logs do PagBank

## 🎯 Objetivo

O PagBank solicitou logs de integração mostrando os **requests e responses** das chamadas de API para validar sua integração antes de aprovar para produção.

## ❓ Onde Obter os Logs?

**IMPORTANTE**: Os logs devem ser capturados **NO SEU SISTEMA**, não no painel do PagBank.

### Por quê?

- O PagBank não fornece logs detalhados de request/response no dashboard deles
- Eles querem validar se sua aplicação está enviando os dados corretamente
- Os logs devem mostrar a estrutura completa do payload e das respostas

## 📍 Localização dos Logs

Seu sistema já está configurado para salvar logs do PagBank em:

```
c:\wamp64\www\wifitocantins\storage\logs\pagbank.log
```

Este arquivo contém:
- ✅ Todos os requests enviados para a API do PagBank
- ✅ Todos os responses recebidos
- ✅ Todos os webhooks recebidos
- ✅ Timestamps e detalhes de cada transação

## 🚀 Como Gerar os Logs para Validação

### Passo 1: Executar Transações de Teste

Você precisa fazer transações reais no sistema para cada meio de pagamento que pretende usar:

#### PIX (QR Code)
1. Acesse o sistema de WiFi
2. Selecione um plano
3. Escolha pagamento via PIX
4. Gere o QR Code
5. **Importante**: Faça o pagamento de verdade (mesmo em sandbox)
6. Aguarde o webhook de confirmação

#### Cartão de Crédito (se implementado)
1. Acesse o sistema
2. Selecione um plano
3. Escolha pagamento via Cartão
4. Preencha os dados do cartão de teste
5. Confirme o pagamento
6. Aguarde a confirmação

### Passo 2: Exportar os Logs

Execute o script de exportação:

```bash
cd c:\wamp64\www\wifitocantins
php exportar_logs_pagbank.php
```

Este script irá:
- ✅ Ler todos os logs do PagBank
- ✅ Organizar em formato estruturado
- ✅ Criar arquivo JSON com todos os dados
- ✅ Gerar exemplos individuais por transação

### Passo 3: Localizar os Arquivos Gerados

Após executar o script, você terá:

**Arquivo principal:**
```
storage/logs/pagbank_validation_logs.json
```

**Exemplos individuais:**
```
storage/logs/pagbank_examples/transacao_1.json
storage/logs/pagbank_examples/transacao_2.json
...
```

## 📤 Como Enviar ao PagBank

### Opção 1: Enviar Arquivo Completo

Envie o arquivo `pagbank_validation_logs.json` ao suporte do PagBank contendo:
- Todas as transações
- Todos os webhooks
- Informações do ambiente

### Opção 2: Enviar Exemplos Individuais

Se o PagBank pedir exemplos específicos, use os arquivos da pasta `pagbank_examples/`:
- `transacao_1.json` - Primeira transação
- `transacao_2.json` - Segunda transação
- etc.

### Formato do Email ao PagBank

```
Assunto: Logs de Integração PagBank - WiFi Tocantins

Prezado suporte PagBank,

Segue em anexo os logs de integração solicitados para validação.

Sistema: WiFi Tocantins
Ambiente: [Sandbox/Produção]
Meios de pagamento testados:
- PIX (QR Code)
- [Outros meios, se aplicável]

Arquivos anexos:
- pagbank_validation_logs.json (arquivo completo)
- Exemplos individuais (pasta zipada)

Aguardo retorno para aprovação da integração.

Atenciosamente,
[Seu nome]
```

## 🔍 Verificar Logs Manualmente

Se preferir verificar os logs manualmente antes de exportar:

### Via Arquivo de Log Direto

Abra o arquivo:
```
c:\wamp64\www\wifitocantins\storage\logs\pagbank.log
```

Procure por:
- `=== REQUEST: Criar Pedido PIX ===` - Início de uma transação
- `REQUEST PAYLOAD:` - Dados enviados
- `RESPONSE:` - Resposta recebida
- `=== WEBHOOK RECEBIDO ===` - Confirmação de pagamento

### Via Laravel Log Viewer (se instalado)

Acesse: `http://seu-dominio/log-viewer`

## 📊 Exemplo do Formato Esperado

O PagBank espera ver algo assim:

```json
{
  "REQUEST": {
    "reference_id": "WIFI_1730379600_ABC123",
    "customer": {
      "name": "Cliente WiFi Tocantins",
      "email": "cliente.wifi@tocantinstransportewifi.com.br",
      "tax_id": "12345678909",
      "phones": [
        {
          "country": "55",
          "area": "63",
          "number": "999999999",
          "type": "MOBILE"
        }
      ]
    },
    "items": [
      {
        "reference_id": "WIFI_1730379600_ABC123",
        "name": "WiFi Tocantins Express - Internet Premium",
        "quantity": 1,
        "unit_amount": 500
      }
    ],
    "qr_codes": [
      {
        "amount": {
          "value": 500
        },
        "arrangements": ["PAGBANK"]
      }
    ],
    "notification_urls": [
      "https://seu-dominio.com/api/payment/webhook/pagbank"
    ]
  },
  "RESPONSE": {
    "status": 201,
    "body": {
      "id": "ORDE_XXXX-XXXX-XXXX",
      "reference_id": "WIFI_1730379600_ABC123",
      "qr_codes": [
        {
          "id": "QRCO_XXXX",
          "text": "00020126580014br.gov.bcb.pix...",
          "arrangements": ["PAGBANK"]
        }
      ]
    }
  }
}
```

## ⚠️ Importante

1. **Ambiente**: Certifique-se de informar se os logs são de **sandbox** ou **produção**
2. **Dados Sensíveis**: Os logs NÃO contêm o token de autenticação (apenas "Bearer ***")
3. **Webhooks**: Inclua também os logs de webhooks recebidos
4. **Múltiplos Testes**: Faça pelo menos 2-3 transações de cada tipo

## 🆘 Solução de Problemas

### Arquivo de log vazio ou não existe

**Problema**: O arquivo `pagbank.log` não existe ou está vazio.

**Solução**: 
1. Execute transações no sistema primeiro
2. Verifique se o gateway está configurado como "pagbank" em `config/wifi.php`
3. Verifique permissões da pasta `storage/logs`

### Script de exportação não funciona

**Problema**: Erro ao executar `exportar_logs_pagbank.php`

**Solução**:
```bash
# Verificar se o Composer está instalado
composer install

# Dar permissões à pasta storage
chmod -R 775 storage
```

### Logs não aparecem formatados

**Problema**: Os logs estão em formato texto puro.

**Solução**: Os logs do Laravel são em formato texto, mas o script de exportação converte para JSON automaticamente.

## 📞 Contato

Se tiver dúvidas sobre os logs ou a integração:
- Suporte PagBank: https://dev.pagbank.uol.com.br
- Documentação: https://dev.pagbank.uol.com.br/reference

---

**Última atualização**: 31/10/2024
