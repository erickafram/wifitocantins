#!/usr/bin/env node

/**
 * Gerar JWS (JSON Web Signature) para Santander PIX
 * Algoritmo: RS256 (RSA with SHA-256)
 * Uso: node gerar_jws_santander.js
 */

const fs = require('fs');
const crypto = require('crypto');
const path = require('path');

// ========================================
// CONFIGURAÇÕES
// ========================================

const CERT_PATH = path.join(__dirname, 'storage/app/certificado/santander.pem');
const CERT_PASSWORD = ''; // Deixe vazio se não tiver senha

// Payload da requisição PIX (PERSONALIZE AQUI)
const payload = {
  calendario: { 
    expiracao: 900 // 15 minutos
  },
  valor: { 
    original: "5.99" // R$ 0,05
  },
  chave: "pix@tocantinstransportewifi.com.br",
  solicitacaoPagador: "Teste WiFi Tocantins"
};

// ========================================
// FUNÇÕES
// ========================================

/**
 * Codificar em Base64 URL-safe (sem padding)
 */
function base64UrlEncode(data) {
  if (typeof data === 'string') {
    data = Buffer.from(data);
  }
  return data
    .toString('base64')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=/g, '');
}

/**
 * Gerar JWS (JSON Web Signature)
 */
function generateJWS(payload) {
  // Header JWS - ⚠️ OBRIGATÓRIO: RS256
  const header = {
    alg: "RS256",  // NUNCA use PS256!
    typ: "JWT"
  };

  console.log('\n📋 Configurações:');
  console.log('   Algoritmo: RS256 (RSA with SHA-256)');
  console.log('   Certificado:', CERT_PATH);
  console.log('   Payload:', JSON.stringify(payload, null, 2));
  console.log('');

  // Verificar se certificado existe
  if (!fs.existsSync(CERT_PATH)) {
    console.error('❌ ERRO: Certificado não encontrado!');
    console.error('   Caminho esperado:', CERT_PATH);
    console.error('');
    console.error('💡 Solução:');
    console.error('   1. Verifique se o arquivo existe');
    console.error('   2. Ajuste CERT_PATH no início do script');
    process.exit(1);
  }

  console.log('✅ Certificado encontrado');

  try {
    // Ler chave privada do certificado
    const privateKeyPem = fs.readFileSync(CERT_PATH, 'utf8');
    
    const privateKeyOptions = {
      key: privateKeyPem,
      format: 'pem'
    };
    
    // Adicionar senha se configurada
    if (CERT_PASSWORD) {
      privateKeyOptions.passphrase = CERT_PASSWORD;
    }
    
    const privateKey = crypto.createPrivateKey(privateKeyOptions);
    
    console.log('✅ Chave privada carregada');
    console.log('');

    // Codificar header e payload em Base64 URL-safe
    const headerEncoded = base64UrlEncode(JSON.stringify(header));
    const payloadEncoded = base64UrlEncode(JSON.stringify(payload));

    console.log('📦 Componentes do JWS:');
    console.log('   Header (Base64URL):', headerEncoded);
    console.log('   Payload (Base64URL):', payloadEncoded.substring(0, 50) + '...');
    console.log('');

    // Criar mensagem para assinar: header.payload
    const message = `${headerEncoded}.${payloadEncoded}`;

    // Assinar com SHA256 (RS256 = RSA with SHA-256)
    const signature = crypto.sign('sha256', Buffer.from(message), {
      key: privateKey,
      padding: crypto.constants.RSA_PKCS1_PADDING
    });

    // Codificar assinatura em Base64 URL-safe
    const signatureEncoded = base64UrlEncode(signature);

    console.log('🔐 Assinatura gerada (Base64URL):', signatureEncoded.substring(0, 50) + '...');
    console.log('');

    // Retornar JWS completo: header.payload.signature
    const jws = `${message}.${signatureEncoded}`;

    return jws;

  } catch (error) {
    console.error('❌ ERRO ao gerar JWS:', error.message);
    console.error('');
    
    if (error.message.includes('bad decrypt')) {
      console.error('💡 Solução:');
      console.error('   - Senha do certificado incorreta');
      console.error('   - Ajuste CERT_PASSWORD no início do script');
    } else if (error.message.includes('bad password')) {
      console.error('💡 Solução:');
      console.error('   - Certificado requer senha, mas CERT_PASSWORD está vazio');
      console.error('   - Configure CERT_PASSWORD no início do script');
    } else {
      console.error('💡 Possíveis causas:');
      console.error('   - Certificado corrompido ou inválido');
      console.error('   - Formato do certificado incorreto (deve ser PEM)');
      console.error('   - Chave privada não presente no certificado');
    }
    
    process.exit(1);
  }
}

/**
 * Validar JWS gerado
 */
function validateJWS(jws) {
  const parts = jws.split('.');
  
  if (parts.length !== 3) {
    console.warn('⚠️ ATENÇÃO: JWS deve ter exatamente 3 partes separadas por "."');
    return false;
  }

  // Decodificar header
  const headerJson = Buffer.from(parts[0], 'base64').toString();
  const header = JSON.parse(headerJson);

  console.log('🔍 Validação do JWS:');
  console.log('   Partes:', parts.length, '(esperado: 3)');
  console.log('   Algoritmo:', header.alg, header.alg === 'RS256' ? '✅' : '❌');
  console.log('   Tipo:', header.typ, header.typ === 'JWT' ? '✅' : '❌');
  console.log('   Tamanho total:', jws.length, 'caracteres');
  console.log('');

  if (header.alg !== 'RS256') {
    console.error('❌ ERRO: Algoritmo deve ser RS256, encontrado:', header.alg);
    return false;
  }

  if (header.typ !== 'JWT') {
    console.warn('⚠️ AVISO: Tipo esperado é JWT, encontrado:', header.typ);
  }

  return true;
}

// ========================================
// EXECUÇÃO PRINCIPAL
// ========================================

console.log('\n=================================================');
console.log('🔐 GERADOR DE JWS PARA SANTANDER PIX');
console.log('=================================================\n');

const jws = generateJWS(payload);

if (validateJWS(jws)) {
  console.log('=================================================');
  console.log('✅ JWS GERADO COM SUCESSO!');
  console.log('=================================================\n');
  
  console.log('📋 JWS para usar no header x-jws-signature:\n');
  console.log(jws);
  console.log('');
  
  console.log('=================================================');
  console.log('📤 Como usar no Postman:');
  console.log('=================================================\n');
  
  console.log('1. Copie o JWS acima');
  console.log('2. No Postman, adicione o header:');
  console.log('');
  console.log('   x-jws-signature: ' + jws.substring(0, 50) + '...');
  console.log('');
  console.log('3. Faça a requisição PUT para:');
  console.log('   https://trust-pix.santander.com.br/api/v1/cob/{txid}');
  console.log('');
  console.log('=================================================\n');

  // Salvar em arquivo
  const outputFile = path.join(__dirname, 'jws_gerado.txt');
  fs.writeFileSync(outputFile, jws);
  console.log('💾 JWS salvo em:', outputFile);
  console.log('');
  
} else {
  console.error('❌ JWS inválido! Verifique os erros acima.');
  process.exit(1);
}

