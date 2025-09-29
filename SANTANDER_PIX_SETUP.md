# Configuração Santander PIX - Guia de Integração

## 📋 Dados Necessários para Integração

### 1. Credenciais de API (Obrigatórias)
- **Client ID**: Identificador único da aplicação
- **Client Secret**: Chave secreta para autenticação  
- **Workspace ID**: Identificador do workspace/convênio
- **Código de Estação**: Obtido com gerente de relacionamento

### 2. Certificado Digital A1 (Obrigatório)
- **Formato**: `.pem`, `.cer` ou `.crt`
- **Requisitos específicos**:
  - Incluir cadeia completa (root, intermediário e folha)
  - Tamanho máximo: 2048 bits
  - Validade mínima: 30 dias
  - Key Usage: "Digital Signature" ou "Key Agreement"
  - Enhanced Key Usage: "TLS Web Client Authentication (1.3.6.1.5.5.7.3.2)"
- **Senha do certificado** (se protegido)

### 3. Dados PIX do Recebedor
- **Chave PIX**: Para receber os pagamentos
- **Nome do Comerciante**: Para exibir no QR Code
- **Cidade do Comerciante**: Para exibir no QR Code

## 🚀 Processo de Obtenção das Credenciais

### Passo 1: Acesso ao Portal
1. Acesse: https://developer.santander.com.br/
2. Faça login com suas credenciais do **Internet Banking PJ**

### Passo 2: Criar Aplicação
1. Vá para seção "Aplicações"
2. Selecione ambiente desejado (homologação/produção)
3. Clique em "Nova Aplicação"
4. Preencha:
   - **Nome**: "Sua Empresa - API PIX"
   - **Descrição**: "Integração PIX para sistema WiFi"
   - **Produto**: Selecione "API de PIX"
   - **Certificado**: Faça upload do certificado .pem

### Passo 3: Aprovação e Credenciais
1. Envie aplicação para aprovação
2. Aguarde aprovação (pode levar alguns dias úteis)
3. Após aprovação, você receberá:
   - Client ID
   - Client Secret
   - Workspace ID

### Passo 4: Código de Estação
- Entre em contato com seu **gerente de relacionamento** Santander
- Solicite o **Código de Estação** para PIX

## 🔧 Configuração no Projeto

### Variáveis de Ambiente (.env)

```bash
# Credenciais Santander PIX
SANTANDER_CLIENT_ID=seu_client_id_aqui
SANTANDER_CLIENT_SECRET=seu_client_secret_aqui
SANTANDER_WORKSPACE_ID=seu_workspace_id_aqui
SANTANDER_STATION_CODE=seu_codigo_estacao_aqui

# Certificado Digital
SANTANDER_CERTIFICATE_PATH=certificates/santander.pem
SANTANDER_CERTIFICATE_PASSWORD=senha_do_certificado

# Ambiente (sandbox para testes, production para produção)
PIX_ENVIRONMENT=sandbox

# Dados PIX
PIX_KEY=sua-chave@exemplo.com.br
PIX_MERCHANT_NAME=Sua Empresa LTDA
PIX_MERCHANT_CITY=Sua Cidade

# Gateway PIX
PIX_GATEWAY=santander
PIX_ENABLED=true
```

### Estrutura de Arquivos

```
storage/app/certificates/
├── santander.pem          # Seu certificado digital A1
└── README.md             # Documentação dos certificados
```

## 🌐 URLs da API por Ambiente

### Homologação (Sandbox)
- **Token**: `https://trust-pix-h.santander.com.br/oauth/token`
- **API PIX**: `https://trust-pix-h.santander.com.br/pix/v2/cob/`

### Produção
- **Token**: `https://trust-pix.santander.com.br/oauth/token`
- **API PIX**: `https://trust-pix.santander.com.br/pix/v2/cob/`

## 📝 Como Obter Certificado Digital A1

### Opção 1: Autoridades Certificadoras Reconhecidas
- **Serasa Experian**
- **Certisign** 
- **AC Safeweb**
- **Valid Certificadora**
- **ICP-Brasil** (outras ACs)

### Opção 2: Processo Geral
1. Acesse site da Autoridade Certificadora
2. Solicite certificado A1 para Pessoa Jurídica
3. Prepare documentos da empresa
4. Realize validação presencial ou por videoconferência
5. Baixe o certificado no formato `.p12` ou `.pfx`
6. Converta para `.pem` se necessário:

```bash
# Converter .p12/.pfx para .pem
openssl pkcs12 -in certificado.p12 -out certificado.pem -nodes
```

## 🧪 Testando a Integração

### 1. Teste de Conectividade
```php
use App\Services\SantanderPixService;

$service = new SantanderPixService();
$result = $service->testConnection();

if ($result['success']) {
    echo "Conexão estabelecida com sucesso!";
} else {
    echo "Erro: " . $result['message'];
}
```

### 2. Criar Cobrança PIX
```php
$payment = $service->createPixPayment(
    amount: 0.10,
    description: 'Acesso WiFi - 24h',
    externalId: 'WIFI_' . time()
);

if ($payment['success']) {
    echo "QR Code EMV: " . $payment['qr_code_text'];
    echo "URL Imagem: " . $payment['qr_code_image'];
}
```

## 🔍 Ferramentas de Validação

### Validar String EMV
- **Decodificador PIX**: https://pix.nascent.com.br/tools/pix-qr-decoder/
- **Calculadora CRC16**: https://www.lammertbies.nl/comm/info/crc-calculation

### Testar QR Code
1. Gere a string EMV
2. Use decodificador para validar
3. Teste leitura com app bancário
4. Verifique se dados estão corretos

## ⚠️ Pontos Importantes

### Segurança
- **NUNCA** commite certificados no Git
- Use variáveis de ambiente para dados sensíveis
- Mantenha certificados com permissões restritas
- Renove certificados antes do vencimento

### Ambiente
- **SEMPRE** teste em homologação primeiro
- Use dados reais apenas em produção
- Configure logs para monitoramento
- Implemente tratamento de erros robusto

### Documentação Oficial
- Portal: https://developer.santander.com.br/
- Documentação técnica disponível após login
- Suporte via portal do desenvolvedor

## 📞 Suporte

### Santander
- **Portal**: https://developer.santander.com.br/
- **Suporte técnico**: Via portal após login
- **Gerente de relacionamento**: Para questões comerciais

### Implementação
- Todos os serviços estão no arquivo: `app/Services/SantanderPixService.php`
- Configurações em: `config/wifi.php`
- Geração EMV conforme documentação oficial
- Webhook implementado para notificações automáticas
