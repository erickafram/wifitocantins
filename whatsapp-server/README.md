# Servidor WhatsApp Baileys - WiFi Tocantins

Este servidor Node.js utiliza a biblioteca Baileys para conectar ao WhatsApp e enviar mensagens automáticas para usuários com pagamentos pendentes.

## Requisitos

- Node.js 18 ou superior
- NPM ou Yarn

## Instalação

```bash
cd whatsapp-server
npm install
```

## Executar o Servidor

```bash
# Modo produção
npm start

# Modo desenvolvimento (com hot reload)
npm run dev
```

O servidor irá rodar na porta **3001** por padrão.

## Configuração

### Variáveis de Ambiente

Crie um arquivo `.env` na pasta `whatsapp-server` (opcional):

```env
PORT=3001
LARAVEL_WEBHOOK_URL=http://localhost:8000/api/whatsapp/webhook
```

### No Laravel

Adicione no arquivo `.env` do Laravel:

```env
BAILEYS_SERVER_URL=http://localhost:3001
```

## Endpoints da API

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/status` | Verifica status da conexão |
| GET | `/qrcode` | Obtém QR Code para conexão |
| POST | `/send` | Envia mensagem |
| POST | `/disconnect` | Desconecta do WhatsApp |
| POST | `/reconnect` | Força reconexão |
| GET | `/health` | Health check |

### Enviar Mensagem

```bash
curl -X POST http://localhost:3001/send \
  -H "Content-Type: application/json" \
  -d '{"phone": "5563999999999", "message": "Olá!"}'
```

## Como Funciona

1. **Primeira Conexão**: Ao acessar `/qrcode`, o servidor gera um QR Code
2. **Escaneie**: Abra o WhatsApp no celular > Menu > Aparelhos conectados > Conectar um aparelho
3. **Conectado**: Após escanear, o servidor mantém a sessão ativa
4. **Envio**: Use o endpoint `/send` para enviar mensagens

## Persistência da Sessão

As credenciais são salvas na pasta `auth_info/`. Para desconectar completamente e gerar novo QR Code, use o endpoint `/disconnect` ou delete a pasta `auth_info/`.

## Integração com Laravel

O servidor notifica o Laravel sobre mudanças de status através de webhooks:

- **connection**: Mudança no status da conexão
- **qr**: Novo QR Code gerado
- **message_status**: Status de entrega das mensagens

## Logs

Os logs são exibidos no console. Em produção, considere usar PM2 ou similar para gerenciar o processo:

```bash
# Instalar PM2 globalmente
npm install -g pm2

# Iniciar com PM2
pm2 start server.js --name whatsapp-server

# Ver logs
pm2 logs whatsapp-server

# Reiniciar
pm2 restart whatsapp-server
```

## Troubleshooting

### QR Code não aparece
- Verifique se o Node.js está na versão 18+
- Delete a pasta `auth_info/` e tente novamente

### Mensagens não são enviadas
- Verifique se o WhatsApp está conectado (`/status`)
- Verifique se o número está no formato correto (55 + DDD + número)

### Conexão cai frequentemente
- O WhatsApp pode desconectar se detectar atividade suspeita
- Evite enviar muitas mensagens em pouco tempo
- Mantenha o celular conectado à internet

## Aviso Legal

Este projeto utiliza a biblioteca Baileys que não é oficialmente suportada pelo WhatsApp. Use por sua conta e risco. O WhatsApp pode banir números que violam seus termos de serviço.
