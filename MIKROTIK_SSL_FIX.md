# Solução para Erro de SSL no Redirecionamento do MikroTik

## Problema
Quando o MikroTik redireciona para `http://login.tocantinswifi.local/login?dst=...`, o navegador bloqueia a requisição HTTP porque o site principal está em HTTPS (Mixed Content).

## Solução Implementada

### 1. **Rota Especial `/login`**
Criada uma rota que aceita tanto HTTP quanto HTTPS:

**Arquivo:** `routes/web.php`
```php
Route::match(['get', 'post'], '/login', [MikrotikLoginController::class, 'handleMikrotikLogin'])
    ->name('mikrotik.login')
    ->withoutMiddleware(['web']);
```

### 2. **Controller MikrotikLoginController**
**Arquivo:** `app/Http/Controllers/MikrotikLoginController.php`

Processa o redirecionamento do MikroTik:
- Captura parâmetros: `dst`, `mac`, `ip`, etc.
- Marca a sessão como verificada pelo MikroTik
- Redireciona para o portal principal com os parâmetros

### 3. **Configuração .htaccess**
**Arquivo:** `public/.htaccess`

Adicionada regra para permitir HTTP na rota `/login`:
```apache
# Permitir HTTP na rota /login para MikroTik (não forçar HTTPS)
RewriteCond %{REQUEST_URI} ^/login [NC]
RewriteRule ^ - [L]
```

### 4. **View de Fallback**
**Arquivo:** `resources/views/mikrotik/login.blade.php`

Página de transição caso o redirecionamento automático não funcione.

### 5. **AppServiceProvider**
**Arquivo:** `app/Providers/AppServiceProvider.php`

Configurado para forçar HTTPS apenas em produção, mas permitindo HTTP na rota `/login`.

## Como Funciona

1. **MikroTik redireciona** para: `http://login.tocantinswifi.local/login?dst=...`
2. **Servidor recebe** a requisição HTTP na rota `/login`
3. **Controller processa** e captura MAC/IP do cliente
4. **Redireciona** para o portal principal com os parâmetros
5. **Portal exibe** a página de conexão com os dados do cliente

## Configuração no MikroTik

No MikroTik, configure o Hotspot para redirecionar para:

```
http://login.tocantinswifi.local/login
```

ou

```
http://SEU_IP_OU_DOMINIO/login
```

## Testando

1. Conecte-se à rede WiFi do MikroTik
2. Abra o navegador
3. O MikroTik deve redirecionar automaticamente para `/login`
4. O sistema captura IP/MAC e redireciona para o portal
5. O portal exibe a página de conexão

## Observações

- ✅ Aceita HTTP e HTTPS
- ✅ Não gera erro de SSL/Mixed Content
- ✅ Captura MAC address do MikroTik
- ✅ Funciona em todos os navegadores
- ✅ Compatível com portal captivo

## Troubleshooting

### Erro "Mixed Content"
- Verifique se o .htaccess está configurado corretamente
- Certifique-se de que o mod_rewrite está ativo no Apache

### Redirecionamento não funciona
- Verifique os logs: `storage/logs/laravel.log`
- Confirme que a rota `/login` está acessível
- Teste acessando diretamente: `http://seu-dominio/login`

### MAC address não é capturado
- Verifique se o MikroTik está enviando o parâmetro `mac`
- Configure o Hotspot do MikroTik para incluir o MAC na URL
- Verifique os logs para ver quais parâmetros estão chegando

## Logs

Para debug, verifique:
```bash
tail -f storage/logs/laravel.log
```

Procure por:
- `🔵 Requisição recebida do MikroTik`
- `✅ Redirecionando para portal`
