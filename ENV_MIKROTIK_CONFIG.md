# Configurações MikroTik para o arquivo .env

Adicione estas configurações ao seu arquivo `.env`:

```bash
# =====================================================
# CONFIGURAÇÕES MIKROTIK
# =====================================================

# IP do MikroTik (padrão do script: 10.10.10.1)
MIKROTIK_HOST=10.10.10.1

# Usuário da API (criado pelo script)
MIKROTIK_USERNAME=api-tocantins

# Senha da API (definida no script)
MIKROTIK_PASSWORD=TocantinsWiFi2024!

# Porta da API (padrão RouterOS)
MIKROTIK_PORT=8728

# Habilitar/desabilitar integração real
MIKROTIK_API_ENABLED=true

# Nome do servidor hotspot (criado pelo script)
MIKROTIK_HOTSPOT_SERVER=tocantins-hotspot

# Interface bridge (criada pelo script)
MIKROTIK_BRIDGE_INTERFACE=bridge-hotspot

# Pool de IPs (criado pelo script)
MIKROTIK_POOL_NAME=hotspot-pool

# Domínio do portal (seu domínio)
PORTAL_DOMAIN=tocantinstransportewifi.com.br
```

## ⚠️ IMPORTANTE

1. **Substitua o IP** se seu MikroTik estiver em outro endereço
2. **Mantenha a senha segura** - mude se necessário no MikroTik também
3. **Use seu domínio real** no PORTAL_DOMAIN
4. **Defina API_ENABLED=false** se quiser testar sem MikroTik conectado

## 🧪 Para Testes

Se você ainda não configurou o MikroTik, use temporariamente:

```bash
MIKROTIK_API_ENABLED=false
```

Isso fará o sistema funcionar em modo simulação até você configurar o hardware.

## 🔧 Após Configurar

1. Execute: `php artisan config:cache`
2. Teste: `php test_mikrotik_integration.php`
3. Se tudo OK, configure o MikroTik com o script `mikrotik-hotspot-integrado.rsc` 