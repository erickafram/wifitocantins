# 🔄 Fluxo da Splash Screen com MikroTik

## ❌ Problema Identificado

**Iframe NÃO funciona** porque:
1. MikroTik bloqueia iframe com `X-Frame-Options: sameorigin`
2. Mixed Content (HTTPS → HTTP)

## ✅ Nova Solução: Redirecionamento Direto

### Fluxo Completo:

```
1. Usuário acessa tocantinstransportewifi.com.br
   ↓
2. Sistema detecta: precisa capturar MAC/IP
   ↓
3. Mostra SPLASH SCREEN (5 segundos)
   ↓
4. Após 5s: Redireciona para MikroTik
   http://login.tocantinswifi.local/login?dst=...
   ↓
5. MikroTik captura MAC e IP real
   ↓
6. MikroTik redireciona de volta para:
   https://www.tocantinstransportewifi.com.br/?source=mikrotik&captive=true&from_mikrotik=1&mac=XX:XX:XX&ip=10.5.50.XXX
   ↓
7. Sistema detecta parâmetros from_mikrotik
   ↓
8. NÃO mostra splash novamente
   ↓
9. Carrega página principal com MAC/IP reais
```

## 🎯 Vantagens

✅ **Funciona sem iframe** - Não tem bloqueio de X-Frame-Options  
✅ **MAC/IP reais** - Capturados diretamente pelo MikroTik  
✅ **Sem Mixed Content** - Redirecionamento normal  
✅ **Experiência suave** - Usuário vê splash bonita antes do processo técnico  

## ⏱️ Timing

- **Splash exibida**: 5 segundos
- **Redirecionamento MikroTik**: ~2-3 segundos
- **Total**: ~7-8 segundos (experiência rápida)

## 🔍 Como Funciona

### 1. Primeira Visita (sem contexto MikroTik)

```php
// PortalController detecta: precisa capturar MAC/IP
if ($this->shouldForceMikrotikRedirect($request)) {
    return $this->showSplashScreen($request);
}
```

### 2. Splash Screen (5 segundos)

```javascript
// splash.blade.php
setTimeout(function() {
    // Redireciona para MikroTik
    window.location.href = 'http://login.tocantinswifi.local/login?dst=...';
}, 5000);
```

### 3. MikroTik Processa

- Captura MAC address real do dispositivo
- Captura IP interno (10.5.50.XXX)
- Redireciona de volta com parâmetros

### 4. Retorno com Parâmetros

```
https://www.tocantinstransportewifi.com.br/
  ?source=mikrotik
  &captive=true
  &from_mikrotik=1
  &mac=D6:DE:C4:66:F2:84
  &ip=10.5.50.249
```

### 5. Sistema Detecta Retorno

```php
// Não mostra splash novamente
if ($request->has('from_mikrotik') || $request->has('from_splash')) {
    return false; // Pula splash
}

// Captura MAC/IP dos parâmetros
$clientInfo = $this->getClientInfo($request);
// MAC: D6:DE:C4:66:F2:84
// IP: 10.5.50.249
```

## 🧪 Como Testar

### 1. Limpar Sessão
```bash
# Modo anônimo no navegador
# OU
curl https://www.tocantinstransportewifi.com.br/sair
```

### 2. Acessar Site
```
https://www.tocantinstransportewifi.com.br
```

### 3. Observar Console (F12)
```
🚀 Splash iniciada
⏱️ Tempo de exibição: 5 segundos
🔗 URL do MikroTik: http://login.tocantinswifi.local/...
⏳ Tempo decorrido: 1s | Restante: 4s
⏳ Tempo decorrido: 2s | Restante: 3s
⏳ Tempo decorrido: 3s | Restante: 2s
⏳ Tempo decorrido: 4s | Restante: 1s
⏳ Tempo decorrido: 5s | Restante: 0s
✅ Redirecionando para MikroTik para captura de MAC/IP...
```

### 4. MikroTik Processa
- Tela do MikroTik pode aparecer brevemente
- Ou redireciona direto (depende da config)

### 5. Volta para Site
```
URL: https://www.tocantinstransportewifi.com.br/
      ?source=mikrotik
      &captive=true
      &from_mikrotik=1
      &mac=D6:DE:C4:66:F2:84
      &ip=10.5.50.249
```

### 6. Verificar Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

Procurar por:
```
🎯 MAC REAL capturado via URL do MikroTik
mac: D6:DE:C4:66:F2:84
ip: 10.5.50.249
```

## 📝 Configuração do MikroTik

O MikroTik precisa estar configurado para:

1. **Capturar MAC/IP** quando usuário acessa `login.tocantinswifi.local`
2. **Redirecionar de volta** para a URL especificada em `dst`
3. **Adicionar parâmetros** `mac` e `ip` na URL de retorno

Exemplo de configuração:
```
/ip hotspot walled-garden
add dst-host=www.tocantinstransportewifi.com.br

/ip hotspot user profile
set default shared-users=1
```

## ⚠️ Importante

- **Não use iframe** - MikroTik bloqueia
- **Tempo de splash**: Ajustável em `SPLASH_DISPLAY_TIME` (atualmente 5s)
- **Parâmetros obrigatórios**: `from_mikrotik` ou `from_splash` para evitar loop
- **MAC/IP**: Vêm diretamente do MikroTik, não são gerados

## 🎨 Personalização

Para ajustar o tempo da splash:

```javascript
// Em splash.blade.php
const SPLASH_DISPLAY_TIME = 5000; // Altere aqui (em milissegundos)
```

Exemplos:
- 3 segundos: `3000`
- 5 segundos: `5000` (atual)
- 8 segundos: `8000`
