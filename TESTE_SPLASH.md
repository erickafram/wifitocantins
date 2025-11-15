# 🧪 Como Testar a Splash Screen

## Problema Identificado

Você está **autenticado** no sistema, por isso vai direto para o dashboard.

## Soluções para Testar

### ✅ Opção 1: Limpar Sessão (Mais Fácil)

1. Abra o navegador em **modo anônimo/privado**
2. Acesse: `https://www.tocantinstransportewifi.com.br`
3. Você verá a splash screen!

### ✅ Opção 2: Fazer Logout

1. Acesse: `https://www.tocantinstransportewifi.com.br/sair`
2. Depois acesse: `https://www.tocantinstransportewifi.com.br`
3. Você verá a splash screen!

### ✅ Opção 3: Limpar Cookies Manualmente

No navegador:
- Chrome: F12 → Application → Cookies → Deletar todos
- Firefox: F12 → Storage → Cookies → Deletar todos

### ✅ Opção 4: Forçar Splash (Para Testes)

Acesse com parâmetro especial:
```
https://www.tocantinstransportewifi.com.br?force_splash=1
```

## Configurações Importantes

### No arquivo `.env`:

```env
MIKROTIK_ENABLED=true
MIKROTIK_FORCE_LOGIN_REDIRECT=true
MIKROTIK_FORCE_LOGIN_REDIRECT_OUTSIDE_HOTSPOT=true
```

### Para testar em ambiente local:

```env
MIKROTIK_FORCE_LOGIN_REDIRECT_LOCAL=true
```

## Como Funciona

1. **Primeira visita** (sem autenticação):
   - Mostra splash screen (10-15s)
   - Iframe carrega MikroTik em background
   - Captura MAC/IP
   - Redireciona para página principal

2. **Visitas seguintes** (com sessão):
   - Pula splash
   - Vai direto para conteúdo

3. **Usuários autenticados**:
   - Vão direto para dashboard
   - Para ver splash: fazer logout primeiro

## Logs para Debug

Verifique os logs do Laravel:
```bash
tail -f storage/logs/laravel.log
```

Procure por:
- `🎬 Exibindo splash screen com MikroTik em background`
- `🔁 Redirecionando usuário para login do MikroTik`

## Console do Navegador

Abra F12 e veja os logs:
- `🚀 Splash iniciada`
- `📡 Iframe carregado`
- `✅ Processo MikroTik completo!`
- `✅ Avançando para página principal`
