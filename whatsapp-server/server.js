/**
 * Servidor WhatsApp com Baileys
 * WiFi Tocantins - Sistema de envio automático de mensagens
 */

const express = require('express');
const cors = require('cors');
const QRCode = require('qrcode');
const axios = require('axios');
const pino = require('pino');
const { 
    default: makeWASocket, 
    DisconnectReason, 
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore
} = require('@whiskeysockets/baileys');
const { Boom } = require('@hapi/boom');
const fs = require('fs');
const path = require('path');

// Configurações
const PORT = process.env.PORT || 3001;
const LARAVEL_WEBHOOK_URL = process.env.LARAVEL_WEBHOOK_URL || 'http://localhost:8000/api/whatsapp/webhook';
const AUTH_FOLDER = path.join(__dirname, 'auth_info');

// Logger
const logger = pino({ level: 'info' });

// Express App
const app = express();
app.use(cors());
app.use(express.json());

// Estado global
let sock = null;
let qrCode = null;
let connectionStatus = 'disconnected';
let connectedPhone = null;
let isConnecting = false;

/**
 * Notificar Laravel sobre mudanças de status
 */
async function notifyLaravel(type, data) {
    try {
        await axios.post(LARAVEL_WEBHOOK_URL, { type, data }, {
            timeout: 5000,
            headers: { 'Content-Type': 'application/json' }
        });
    } catch (error) {
        logger.warn('Não foi possível notificar Laravel:', error.message);
    }
}

/**
 * Iniciar conexão com WhatsApp
 */
async function startConnection() {
    if (isConnecting) {
        logger.info('Já está conectando...');
        return;
    }

    isConnecting = true;
    connectionStatus = 'connecting';

    try {
        // Criar pasta de autenticação se não existir
        if (!fs.existsSync(AUTH_FOLDER)) {
            fs.mkdirSync(AUTH_FOLDER, { recursive: true });
        }

        // Carregar estado de autenticação
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_FOLDER);
        
        // Obter versão mais recente do Baileys
        const { version } = await fetchLatestBaileysVersion();
        
        logger.info(`Usando Baileys versão: ${version.join('.')}`);

        // Criar socket
        sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            printQRInTerminal: true,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' }))
            },
            generateHighQualityLinkPreview: true,
            getMessage: async (key) => {
                return { conversation: '' };
            }
        });

        // Eventos de conexão
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            // QR Code recebido
            if (qr) {
                logger.info('QR Code recebido');
                qrCode = await QRCode.toDataURL(qr);
                connectionStatus = 'waiting_scan';
                
                await notifyLaravel('qr', { qrcode: qrCode });
            }

            // Conexão estabelecida
            if (connection === 'open') {
                logger.info('✅ Conectado ao WhatsApp!');
                connectionStatus = 'connected';
                qrCode = null;
                isConnecting = false;
                
                // Obter número conectado
                const user = sock.user;
                if (user) {
                    connectedPhone = user.id.split(':')[0].replace('@s.whatsapp.net', '');
                    logger.info(`Número conectado: ${connectedPhone}`);
                }

                await notifyLaravel('connection', { 
                    status: 'connected', 
                    phone: connectedPhone 
                });
            }

            // Conexão fechada
            if (connection === 'close') {
                const shouldReconnect = (lastDisconnect?.error instanceof Boom) 
                    ? lastDisconnect.error.output.statusCode !== DisconnectReason.loggedOut
                    : true;

                logger.info(`Conexão fechada. Reconectar: ${shouldReconnect}`);
                
                if (lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut) {
                    // Usuário deslogou - limpar credenciais
                    logger.info('Usuário deslogou. Limpando credenciais...');
                    connectionStatus = 'disconnected';
                    connectedPhone = null;
                    qrCode = null;
                    isConnecting = false;
                    
                    // Remover pasta de autenticação
                    if (fs.existsSync(AUTH_FOLDER)) {
                        fs.rmSync(AUTH_FOLDER, { recursive: true, force: true });
                    }
                    
                    await notifyLaravel('connection', { status: 'disconnected' });
                } else if (shouldReconnect) {
                    connectionStatus = 'reconnecting';
                    isConnecting = false;
                    
                    // Tentar reconectar após 3 segundos
                    setTimeout(startConnection, 3000);
                }
            }
        });

        // Salvar credenciais quando atualizadas
        sock.ev.on('creds.update', saveCreds);

        // Eventos de mensagens (para status de entrega)
        sock.ev.on('messages.update', async (updates) => {
            for (const update of updates) {
                if (update.update.status) {
                    const statusMap = {
                        2: 'sent',
                        3: 'delivered',
                        4: 'read'
                    };
                    
                    const status = statusMap[update.update.status];
                    if (status) {
                        await notifyLaravel('message_status', {
                            messageId: update.key.id,
                            status: status
                        });
                    }
                }
            }
        });

    } catch (error) {
        logger.error('Erro ao conectar:', error);
        connectionStatus = 'error';
        isConnecting = false;
    }
}

/**
 * Verificar se número existe no WhatsApp
 */
async function checkNumberExists(phone) {
    if (!sock || connectionStatus !== 'connected') {
        return false;
    }

    try {
        const jid = phone.includes('@') ? phone : `${phone}@s.whatsapp.net`;
        const [result] = await sock.onWhatsApp(jid);
        return result?.exists || false;
    } catch (error) {
        logger.error(`Erro ao verificar número ${phone}:`, error);
        return false;
    }
}

/**
 * Enviar mensagem
 */
async function sendMessage(phone, message, skipCheck = false) {
    if (!sock || connectionStatus !== 'connected') {
        throw new Error('WhatsApp não está conectado');
    }

    logger.info(`[SEND] Iniciando envio para: ${phone}`);

    // Verificar se o número existe no WhatsApp
    if (!skipCheck) {
        logger.info(`[SEND] Verificando se número existe...`);
        const jidCheck = phone.includes('@') ? phone : `${phone}@s.whatsapp.net`;
        
        try {
            const [result] = await sock.onWhatsApp(jidCheck);
            logger.info(`[SEND] Resultado onWhatsApp: ${JSON.stringify(result)}`);
            
            if (!result || !result.exists) {
                throw new Error(`Número ${phone} não possui WhatsApp`);
            }
            
            // Usar o JID retornado pelo onWhatsApp (formato correto)
            const correctJid = result.jid;
            logger.info(`[SEND] JID correto: ${correctJid}`);
            
            // Enviar mensagem usando o JID correto
            logger.info(`[SEND] Enviando mensagem...`);
            const sendResult = await sock.sendMessage(correctJid, { text: message });
            
            logger.info(`[SEND] Resultado do envio: ${JSON.stringify(sendResult)}`);
            logger.info(`[SEND] ✅ Mensagem enviada para ${phone} - ID: ${sendResult.key.id}`);
            
            return {
                success: true,
                messageId: sendResult.key.id,
                jid: correctJid
            };
        } catch (error) {
            logger.error(`[SEND] ❌ Erro: ${error.message}`);
            throw error;
        }
    } else {
        // Envio sem verificação
        const jid = phone.includes('@') ? phone : `${phone}@s.whatsapp.net`;
        
        try {
            const result = await sock.sendMessage(jid, { text: message });
            logger.info(`[SEND] ✅ Mensagem enviada (sem verificação) para ${phone}`);
            return {
                success: true,
                messageId: result.key.id
            };
        } catch (error) {
            logger.error(`[SEND] ❌ Erro ao enviar: ${error.message}`);
            throw error;
        }
    }
}

/**
 * Desconectar
 */
async function disconnect() {
    if (sock) {
        await sock.logout();
        sock = null;
    }
    
    connectionStatus = 'disconnected';
    connectedPhone = null;
    qrCode = null;
    isConnecting = false;

    // Limpar credenciais
    if (fs.existsSync(AUTH_FOLDER)) {
        fs.rmSync(AUTH_FOLDER, { recursive: true, force: true });
    }

    await notifyLaravel('connection', { status: 'disconnected' });
}

// ==================== ROTAS API ====================

// Status da conexão
app.get('/status', (req, res) => {
    res.json({
        status: connectionStatus,
        phone: connectedPhone,
        isConnecting: isConnecting
    });
});

// Obter QR Code
app.get('/qrcode', async (req, res) => {
    // Se já está conectado, retornar status
    if (connectionStatus === 'connected') {
        return res.json({
            status: 'connected',
            phone: connectedPhone,
            message: 'Já está conectado'
        });
    }

    // Se não está conectando, iniciar conexão
    if (!isConnecting && connectionStatus !== 'waiting_scan') {
        startConnection();
    }

    // Aguardar QR Code (máximo 30 segundos)
    let attempts = 0;
    const maxAttempts = 30;

    const checkQR = () => {
        return new Promise((resolve) => {
            const interval = setInterval(() => {
                attempts++;
                
                if (qrCode) {
                    clearInterval(interval);
                    resolve({ qrcode: qrCode, status: 'waiting_scan' });
                } else if (connectionStatus === 'connected') {
                    clearInterval(interval);
                    resolve({ status: 'connected', phone: connectedPhone });
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    resolve({ error: 'Timeout ao aguardar QR Code', status: connectionStatus });
                }
            }, 1000);
        });
    };

    const result = await checkQR();
    res.json(result);
});

// Verificar se número tem WhatsApp
app.get('/check/:phone', async (req, res) => {
    const { phone } = req.params;

    if (!phone) {
        return res.status(400).json({ error: 'Phone é obrigatório' });
    }

    try {
        const exists = await checkNumberExists(phone);
        res.json({ phone, exists });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Enviar mensagem
app.post('/send', async (req, res) => {
    const { phone, message, skipCheck } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ error: 'Phone e message são obrigatórios' });
    }

    try {
        const result = await sendMessage(phone, message, skipCheck);
        res.json(result);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Desconectar
app.post('/disconnect', async (req, res) => {
    try {
        await disconnect();
        res.json({ success: true, message: 'Desconectado com sucesso' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Reconectar
app.post('/reconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.end();
            sock = null;
        }
        isConnecting = false;
        startConnection();
        res.json({ success: true, message: 'Reconectando...' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'ok', 
        whatsapp: connectionStatus,
        uptime: process.uptime()
    });
});

// ==================== INICIAR SERVIDOR ====================

app.listen(PORT, () => {
    logger.info(`🚀 Servidor WhatsApp rodando na porta ${PORT}`);
    logger.info(`📱 Acesse http://localhost:${PORT}/status para verificar status`);
    
    // Tentar conectar automaticamente se houver credenciais salvas
    if (fs.existsSync(path.join(AUTH_FOLDER, 'creds.json'))) {
        logger.info('Credenciais encontradas. Conectando automaticamente...');
        startConnection();
    } else {
        logger.info('Nenhuma credencial encontrada. Aguardando solicitação de QR Code...');
    }
});

// Tratamento de erros não capturados
process.on('uncaughtException', (error) => {
    logger.error('Erro não capturado:', error);
});

process.on('unhandledRejection', (error) => {
    logger.error('Promise rejeitada:', error);
});

// Graceful shutdown
process.on('SIGINT', async () => {
    logger.info('Encerrando servidor...');
    if (sock) {
        await sock.end();
    }
    process.exit(0);
});
