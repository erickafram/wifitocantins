# ⚡ CORREÇÃO RÁPIDA - Santander PIX JWS

## 🎯 O Problema
O Santander está rejeitando porque falta o **JWS (assinatura digital)** nas requisições.  
Erro: `Algorithm in header did not match... algorithm(RS256)`

## ✅ A Solução (3 comandos)

### No seu terminal local, faça commit e push:
```bash
git add .
git commit -m "fix: habilitar JWS RS256 para Santander PIX"
git push origin main
```

### No servidor (SSH):
```bash
# 1. Conectar
ssh root@206.189.217.189

# 2. Ir para o projeto
cd /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br

# 3. Executar script de correção
git pull && bash habilitar_jws_santander.sh
```

---

## 📊 O que o script faz:

1. ✅ Atualiza código do GitHub
2. ✅ Habilita `SANTANDER_USE_JWS=true` no .env
3. ✅ Instala biblioteca `firebase/php-jwt`
4. ✅ Limpa cache do Laravel
5. ✅ Testa o certificado

---

## 🔍 Verificar se funcionou:

```bash
# Ver se JWS está habilitado
grep SANTANDER_USE_JWS .env

# Deve mostrar:
# SANTANDER_USE_JWS=true
```

```bash
# Testar no tinker
php artisan tinker
```
```php
config('wifi.payment_gateways.pix.use_jws')
// Retorno esperado: true
exit
```

---

## 🧪 Testar QR Code PIX

1. Acesse o portal WiFi
2. Tente gerar um QR Code
3. Monitore os logs:

```bash
tail -f storage/logs/laravel.log | grep -E "(JWS|PIX|Santander)"
```

**Logs de sucesso:**
```
✅ JWS gerado com sucesso
✅ Cobrança PIX criada com sucesso
```

---

## ⚠️ Se der erro de "certificado sem chave privada"

Seu certificado pode estar incompleto. Verifique:

```bash
cat storage/app/certificado/santander.pem | grep "BEGIN PRIVATE KEY"
```

**Se não mostrar nada:** você precisa baixar o certificado completo do portal Santander (incluindo chave privada).

---

## 📞 Troubleshooting Rápido

| Erro | Solução |
|------|---------|
| `SANTANDER_USE_JWS` não existe | Execute: `bash habilitar_jws_santander.sh` |
| Certificado sem chave privada | Baixe novamente do portal Santander |
| Erro 401 persiste | Verifique Client ID/Secret no .env |
| JWS não é gerado | Execute: `php artisan config:clear` |

---

## 📋 Checklist Final

- [ ] Git pull executado
- [ ] Script `habilitar_jws_santander.sh` rodado
- [ ] `.env` mostra `SANTANDER_USE_JWS=true`
- [ ] Config cache limpo
- [ ] Certificado tem chave privada
- [ ] QR Code PIX testado
- [ ] Logs mostram "JWS gerado com sucesso"

---

**Tempo estimado:** 2 minutos  
**Última atualização:** 06/10/2025

