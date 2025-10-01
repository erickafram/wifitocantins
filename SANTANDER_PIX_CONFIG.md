# 🏦 Configuração PIX Santander - Guia Completo

## 📋 Checklist de Pré-requisitos

Antes de começar, você precisa ter:

- [ ] **Conta PJ no Santander** (Pessoa Jurídica)
- [ ] **Certificado Digital ICP A1** (válido e em formato .PEM, .CER ou .CRT)
- [ ] **Acesso ao Portal do Desenvolvedor Santander** (https://developers.santander.com.br)
- [ ] **Chave PIX cadastrada** no Internet Banking PJ do Santander

---

## 🔐 PASSO 1: Obter Credenciais no Portal do Desenvolvedor

### 1.1 Acessar o Portal
1. Acesse: https://developers.santander.com.br
2. Faça login com sua conta PJ
3. Use autenticação de dois fatores

### 1.2 Criar uma Aplicação
1. No menu, clique em **"Aplicações"** > **"Nova Aplicação"**
2. Preencha os dados:
   - **Nome**: `WiFi Tocantins PIX`
   - **Descrição**: `Integração PIX para portal WiFi`
   - **Ambiente**: Comece com `Sandbox` para testes
3. Clique em **"Criar"**

### 1.3 Copiar Credenciais
Após criar, você verá:
- ✅ **Client ID** (exemplo: `RA4UP23L7tQLlAlcsk8O9QF9Q6Oih6NB`)
- ✅ **Client Secret** (exemplo: `nSkWIV8TFJUGRBur`)
- ✅ **Workspace ID** (fornecido pelo Santander - solicite ao gerente se necessário)

> ⚠️ **ATENÇÃO**: Guarde essas credenciais em local seguro! Nunca compartilhe.

---

## 📜 PASSO 2: Preparar o Certificado Digital

### 2.1 Certificado Necessário
Você precisa de um **Certificado Digital ICP A1** com as seguintes características:

- **Formato**: .PEM, .CER ou .CRT
- **Tipo**: A1 (ICP-Brasil)
- **Tamanho**: 2048 bits
- **Cadeia completa**: root + intermediário + folha
- **Validade mínima**: 30 dias
- **Key Usage**: "Digital Signature" ou "Key Agreement"
- **Enhanced Key Usage**: "TLS Web Client Authentication (1.3.6.1.5.5.7.3.2)"

### 2.2 Onde comprar?
Autoridades Certificadoras ICP-Brasil confiáveis:
- Serasa Experian (https://www.serasaexperian.com.br/certificado-digital/)
- Certisign (https://www.certisign.com.br/)
- Valid Certificadora (https://www.validcertificadora.com.br/)
- AC Safeweb (https://www.acsafeweb.com.br/)
- Lista completa: https://estrutura.iti.gov.br/

### 2.3 Converter Certificado (se necessário)
Se você recebeu o certificado em formato `.pfx` ou `.p12`, converta para `.pem`:

```bash
# No Linux/Mac ou Git Bash no Windows
openssl pkcs12 -in certificado.pfx -out santander.pem -nodes

# Será solicitada a senha do certificado
```

### 2.4 Salvar Certificado no Projeto
1. Copie o arquivo `santander.pem` para:
   ```
   storage/app/certificates/santander.pem
   ```

2. Verifique as permissões (Linux/Mac):
   ```bash
   chmod 600 storage/app/certificates/santander.pem
   ```

> ⚠️ **IMPORTANTE**: O certificado **NUNCA** deve ser commitado no Git! O `.gitignore` já está configurado para ignorá-lo.

---

## 🔑 PASSO 3: Cadastrar Chave PIX no Santander

### 3.1 Acessar Internet Banking PJ
1. Acesse: https://internetbanking.santander.com.br/
2. Faça login com sua conta PJ
3. Vá em **"PIX"** > **"Minhas Chaves"**

### 3.2 Criar Chave PIX
Recomendamos usar **Chave Aleatória** (mais seguro):

1. Clique em **"Cadastrar Chave"**
2. Escolha **"Chave Aleatória"**
3. Copie a chave gerada (exemplo: `7d9f0335-8a17-4f3f-9e5d-5e8b3d6c8e7f`)

> 💡 **DICA**: Você pode criar até 20 chaves por conta PJ. Use uma exclusiva para o WiFi.

Alternativamente, você pode usar:
- **CNPJ** da empresa
- **E-mail** cadastrado
- **Telefone** cadastrado

> ⚠️ Se usar CNPJ, e-mail ou telefone, deve ser **exatamente** o mesmo cadastrado no Santander.

---

## ⚙️ PASSO 4: Configurar o `.env`

### 4.1 Ambiente de SANDBOX (Testes)

Edite o arquivo `.env` e adicione/atualize:

```env
# ===================================================
# CONFIGURAÇÃO SANTANDER PIX - SANDBOX (TESTES)
# ===================================================

# Gateway PIX ativo
PIX_ENABLED=true
PIX_GATEWAY=santander

# Ambiente (sandbox para testes, production para produção)
PIX_ENVIRONMENT=sandbox

# Credenciais do Portal do Desenvolvedor Santander
SANTANDER_CLIENT_ID=seu_client_id_aqui
SANTANDER_CLIENT_SECRET=seu_client_secret_aqui
SANTANDER_WORKSPACE_ID=seu_workspace_id_aqui

# Chave PIX (cadastrada no Santander Internet Banking)
# Para SANDBOX, solicite uma chave de teste ao gerente Cash
PIX_KEY=chave_pix_sandbox_fornecida_pelo_santander

# Dados do comerciante (sem acentos ou caracteres especiais)
PIX_MERCHANT_NAME=TocantinsTransportWiFi
PIX_MERCHANT_CITY=Palmas

# Certificado Digital
SANTANDER_CERTIFICATE_PATH=certificates/santander.pem
SANTANDER_CERTIFICATE_PASSWORD=

# Nota: Se o certificado tiver senha, coloque aqui. Senão, deixe vazio.
```

### 4.2 Ambiente de PRODUÇÃO

Após testar no Sandbox, atualize para produção:

```env
# ===================================================
# CONFIGURAÇÃO SANTANDER PIX - PRODUÇÃO
# ===================================================

PIX_ENABLED=true
PIX_GATEWAY=santander
PIX_ENVIRONMENT=production

# Credenciais de PRODUÇÃO (diferentes do Sandbox!)
SANTANDER_CLIENT_ID=seu_client_id_producao
SANTANDER_CLIENT_SECRET=seu_client_secret_producao
SANTANDER_WORKSPACE_ID=seu_workspace_id_producao

# Chave PIX de PRODUÇÃO (cadastrada no Internet Banking)
PIX_KEY=sua_chave_pix_real

PIX_MERCHANT_NAME=TocantinsTransportWiFi
PIX_MERCHANT_CITY=Palmas

SANTANDER_CERTIFICATE_PATH=certificates/santander.pem
SANTANDER_CERTIFICATE_PASSWORD=
```

### 4.3 Limpar Cache
Sempre que alterar o `.env`, limpe o cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 PASSO 5: Testar a Integração

### 5.1 Testar Conexão

Execute o comando de teste:

```bash
php artisan santander:pix test
```

**Resultado esperado:**
```
🏦 Santander PIX - test

🧪 Testando conexão com Santander PIX...

✅ Conexão estabelecida com sucesso!

+------------------+--------------------+
| Verificação      | Status             |
+------------------+--------------------+
| Ambiente         | sandbox            |
| Base URL         | https://trust-... |
| Client ID        | ✅ Configurado     |
| Client Secret    | ✅ Configurado     |
| Chave PIX        | ✅ Configurado     |
| Certificado      | ✅ Encontrado      |
| Token OAuth      | ✅ Obtido          |
+------------------+--------------------+
```

### 5.2 Diagnosticar Erros Comuns

#### ❌ Erro: "Certificado não encontrado"
**Solução**: Verifique se o arquivo existe em `storage/app/certificates/santander.pem`

#### ❌ Erro: "Erro na autenticação Santander"
**Possíveis causas**:
1. Client ID ou Client Secret incorretos
2. Certificado inválido ou expirado
3. Certificado não está no formato correto
4. Ambiente errado (sandbox vs production)

**Solução**: Verifique suas credenciais no Portal do Desenvolvedor

#### ❌ Erro: "Token não retornado"
**Solução**: Verifique se o certificado é válido e tem as permissões corretas

---

## 🔔 PASSO 6: Configurar Webhook

O webhook permite que o Santander notifique seu sistema automaticamente quando um pagamento for realizado.

### 6.1 Pré-requisitos do Webhook

Sua URL de webhook **DEVE**:
1. ✅ Aceitar requisições **GET** (para validação inicial do Santander)
2. ✅ Aceitar requisições **POST** (para receber notificações)
3. ✅ Estar categorizada na CISCO: https://www.talosintelligence.com/
4. ✅ Ter HTTPS válido (certificado SSL)
5. ✅ Responder rapidamente (timeout: 5 segundos)

### 6.2 URL do Webhook

Sua URL de webhook será:
```
https://www.tocantinstransportewifi.com.br/api/payment/webhook/santander
```

### 6.3 Cadastrar o Webhook

Execute o comando:

```bash
php artisan santander:pix webhook-config --url=https://www.tocantinstransportewifi.com.br/api/payment/webhook/santander
```

**Ou** de forma interativa:

```bash
php artisan santander:pix webhook-config
```

O sistema perguntará a URL e confirmará antes de registrar.

### 6.4 Verificar Webhook Cadastrado

```bash
php artisan santander:pix webhook-status
```

### 6.5 Deletar Webhook (se necessário)

```bash
php artisan santander:pix webhook-delete
```

> ⚠️ **ATENÇÃO**: Você só pode ter **UMA** URL de webhook por chave PIX. Se mudar a URL, precisa reconfigurar.

---

## 🚀 PASSO 7: Testar Pagamento Real

### 7.1 Gerar QR Code de Teste

1. Acesse o portal WiFi: https://www.tocantinstransportewifi.com.br
2. Clique em **"Conectar Agora"**
3. Preencha seus dados
4. Escolha o valor (ex: R$ 0,10 para teste)
5. Clique em **"Gerar QR Code PIX"**

### 7.2 Pagar o PIX

No **Sandbox**, use o aplicativo de teste fornecido pelo Santander.
Em **Produção**, pague normalmente pelo app do seu banco.

### 7.3 Verificar Liberação

Após o pagamento:
1. O webhook será chamado automaticamente
2. O sistema liberará o acesso na rede WiFi
3. Você será redirecionado para navegação

### 7.4 Monitorar Logs

Acompanhe os logs em tempo real:

```bash
tail -f storage/logs/laravel.log
```

Procure por:
- `🔔 Webhook Santander recebido`
- `💰 Pagamento Santander confirmado`
- `✅ Acesso liberado`

---

## 📊 PASSO 8: Monitoramento e Manutenção

### 8.1 Renovação do Certificado

Certificados A1 têm validade de **1 ano**. Agende renovação com **30 dias de antecedência**.

**Checklist de Renovação**:
1. Comprar novo certificado antes de expirar
2. Converter para .pem
3. Substituir em `storage/app/certificates/santander.pem`
4. Testar conexão: `php artisan santander:pix test`
5. Não precisa atualizar webhook (mesma chave PIX)

### 8.2 Trocar de Ambiente (Sandbox → Produção)

1. Atualize o `.env`:
   ```env
   PIX_ENVIRONMENT=production
   ```
2. Limpe o cache:
   ```bash
   php artisan config:clear
   ```
3. Teste a conexão:
   ```bash
   php artisan santander:pix test
   ```
4. Reconfigure o webhook com a URL de produção

### 8.3 Consultar Transações

Para consultar PIX recebidos:

```php
$service = new \App\Services\SantanderPixService();

$result = $service->listPixReceivedBCB(
    dataInicio: '2025-10-01',
    dataFim: '2025-10-01',
    paginaAtual: 0,
    itensPorPagina: 100
);

if ($result['success']) {
    foreach ($result['data']['pix'] as $pix) {
        echo "TXId: " . $pix['txid'] . "\n";
        echo "Valor: R$ " . $pix['valor'] . "\n";
        echo "Data: " . $pix['horario'] . "\n";
    }
}
```

---

## 🆘 Suporte e Troubleshooting

### Contatos Santander

**Portal do Desenvolvedor**: https://developers.santander.com.br
**Suporte Técnico**: Abra um chamado no portal
**Gerente Cash**: Entre em contato pelo Internet Banking PJ

### Logs Importantes

Todos os logs ficam em: `storage/logs/laravel.log`

Procure por:
- `🔐 Iniciando autenticação OAuth 2.0 Santander`
- `📲 Criando cobrança PIX Santander`
- `🔔 Webhook Santander recebido`
- `❌ Erro` (qualquer linha com este emoji indica problema)

### Perguntas Frequentes

**P: Preciso ter conta no Santander?**
R: Sim, precisa ser uma conta PJ (Pessoa Jurídica).

**P: Quanto custa o certificado digital?**
R: Varia de R$ 150 a R$ 300/ano, dependendo da AC.

**P: Posso usar o mesmo certificado para outros serviços?**
R: Sim, mas recomendamos certificados distintos para segurança.

**P: O TXId pode ser qualquer string?**
R: Deve ter entre 26 e 35 caracteres alfanuméricos (letras e números).

**P: Quantos webhooks posso cadastrar?**
R: Apenas 1 URL por chave PIX. Para múltiplas URLs, use múltiplas chaves.

**P: O que acontece se o webhook falhar?**
R: O sistema tem polling automático a cada 10 segundos para verificar pagamentos pendentes.

---

## ✅ Checklist Final

Antes de ir para produção, verifique:

- [ ] Certificado digital válido e instalado
- [ ] Credenciais de produção configuradas no `.env`
- [ ] Chave PIX de produção cadastrada
- [ ] Teste de conexão bem-sucedido
- [ ] Webhook configurado e validado
- [ ] Pagamento de teste realizado com sucesso
- [ ] Liberação de acesso funcionando
- [ ] Logs sendo gravados corretamente
- [ ] Backup do certificado em local seguro
- [ ] Lembrete de renovação agendado

---

## 📚 Referências

- [Portal do Desenvolvedor Santander](https://developers.santander.com.br)
- [Manual de PIX - Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix)
- [ICP-Brasil - Estrutura de ACs](https://estrutura.iti.gov.br/)
- [CISCO Talos Intelligence](https://www.talosintelligence.com/)

---

**Desenvolvido com ❤️ para WiFi Tocantins**

*Última atualização: Outubro 2025* 