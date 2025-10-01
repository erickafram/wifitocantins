#  O QUE VOCÊ PRECISA DO SANTANDER

##  Implementação: COMPLETA E PRONTA!

O código está 100% implementado seguindo a documentação oficial do Santander.

---

##  CHECKLIST: Informações necessárias

### 1 **Acesso ao Portal do Desenvolvedor Santander**
-  URL: https://developers.santander.com.br
-  Login com conta PJ + autenticação de 2 fatores

### 2 **Credenciais da API** (após criar aplicação)
-  Client ID
-  Client Secret  
-  Workspace ID

### 3 **Certificado Digital ICP A1**
-  Comprar de AC ICP-Brasil (R$ 150-300/ano)
-  Formato: .PEM (ou converter de .pfx)
-  Validade: mínimo 30 dias

### 4 **Chave PIX cadastrada**
-  Cadastrar no Internet Banking PJ
-  Recomendado: Chave Aleatória

---

##  O QUE PERGUNTAR AO SUPORTE

**Telefone**: (Ligue para o gerente Cash da sua conta)

### Perguntas principais:

1. **"Como acesso o Portal do Desenvolvedor Santander?"**

2. **"Preciso integrar PIX. Como obtenho Client ID, Client Secret e Workspace ID?"**

3. **"Onde devo comprar o Certificado Digital? Qual tipo exatamente?"**

4. **"Tenho chave PIX cadastrada. Posso usar para API ou preciso criar outra?"**

5. **"Há ambiente de testes (Sandbox) disponível? Como obtenho credenciais de teste?"**

6. **"Qual o canal de suporte técnico para problemas na integração?"**

---

##  APÓS OBTER AS INFORMAÇÕES

1. Configure o .env:
```env
PIX_GATEWAY=santander
PIX_ENVIRONMENT=sandbox

SANTANDER_CLIENT_ID=cole_aqui
SANTANDER_CLIENT_SECRET=cole_aqui
SANTANDER_WORKSPACE_ID=cole_aqui

PIX_KEY=sua_chave_pix

SANTANDER_CERTIFICATE_PATH=certificates/santander.pem
```

2. Salve o certificado em: storage/app/certificates/santander.pem

3. Teste: php artisan santander:pix test

4. Configure webhook: php artisan santander:pix webhook-config

---

##  Documentação completa

Veja SANTANDER_PIX_CONFIG.md para guia passo a passo detalhado.

---

**Boa sorte! **
