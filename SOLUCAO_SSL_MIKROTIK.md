# 🔒 Solução para Erro SSL no Redirecionamento MikroTik

## 🎯 Problema Identificado

O MikroTik redireciona para: `http://login.tocantinswifi.local/login?dst=...`

**Erro:** Navegadores modernos bloqueiam HTTP quando o site principal usa HTTPS, causando erro de SSL.

---

## ✅ Soluções Possíveis (SEM mexer no MikroTik)

### **Opção 1: Certificado SSL para o Domínio .local (RECOMENDADO)**

Instalar um certificado SSL auto-assinado no servidor que responde por `login.tocantinswifi.local`.

**Vantagens:**
- ✅ Resolve completamente o problema
- ✅ Segurança mantida
- ✅ Funciona em todos os navegadores

**Desvantagens:**
- ⚠️ Usuários verão aviso de "Certificado não confiável" na primeira vez
- ⚠️ Precisa instalar certificado no servidor

**Como fazer:**
```bash
# Gerar certificado auto-assinado
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/tocantinswifi.key \
  -out /etc/ssl/certs/tocantinswifi.crt \
  -subj "/CN=login.tocantinswifi.local"

# Configurar Apache/Nginx para usar HTTPS
```

---

### **Opção 2: Proxy Reverso no Laravel (IMPLEMENTADA)**

Criar uma rota no Laravel que funciona como proxy para o MikroTik.

**Vantagens:**
- ✅ Não precisa mexer no MikroTik
- ✅ Não precisa certificado adicional
- ✅ Funciona imediatamente

**Desvantagens:**
- ⚠️ Adiciona uma camada extra de processamento
- ⚠️ Pode ter pequeno delay

**Implementação:**
- Rota: `https://seudominio.com/mikrotik-login`
- Redireciona internamente para o MikroTik
- Captura MAC/IP e retorna para o portal

---

### **Opção 3: Usar IP Direto ao Invés de .local**

Configurar o MikroTik para usar o IP público/interno ao invés de `login.tocantinswifi.local`.

**Exemplo:** `http://192.168.88.1/login` ou `http://SEU_IP_PUBLICO/login`

**Vantagens:**
- ✅ Simples de implementar
- ✅ Evita problema de DNS .local

**Desvantagens:**
- ⚠️ Ainda terá problema HTTP vs HTTPS
- ⚠️ Precisa mexer na configuração do MikroTik (você disse que não quer)

---

### **Opção 4: Página de Captura Intermediária (MELHOR PARA SEU CASO)**

Criar uma página intermediária no Laravel que:
1. Recebe o redirecionamento do MikroTik via HTTP
2. Captura MAC/IP dos parâmetros
3. Redireciona para o portal HTTPS com os dados

**Vantagens:**
- ✅ Não precisa mexer no MikroTik
- ✅ Não precisa certificado SSL adicional
- ✅ Funciona em todos os navegadores
- ✅ Transparente para o usuário

**Desvantagens:**
- ⚠️ Precisa configurar o servidor web para aceitar HTTP nesta rota específica

---

## 🚀 Solução Implementada

Implementei a **Opção 4** com melhorias:

### 1. Rota Especial para MikroTik
```php
// routes/web.php
Route::get('/login', [PortalController::class, 'mikrotikLogin'])->name('mikrotik.login');
```

### 2. Controller que Processa o Redirecionamento
```php
public function mikrotikLogin(Request $request)
{
    // Captura parâmetros do MikroTik
    $mac = $request->get('mac');
    $ip = $request->get('ip') ?: $request->ip();
    $dst = $request->get('dst');
    
    // Redireciona para o portal com os dados
    return redirect()->route('portal.index', [
        'mac' => $mac,
        'ip' => $ip,
        'from_mikrotik' => 1,
        'captive' => 1
    ]);
}
```

### 3. Configuração do Servidor Web

**Para Apache (.htaccess):**
```apache
# Permitir HTTP apenas para /login
<If "%{REQUEST_URI} =~ m#^/login#">
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
</If>
```

**Para Nginx:**
```nginx
# Permitir HTTP apenas para /login
location /login {
    # Não força HTTPS nesta rota
    # Permite HTTP
}

# Forçar HTTPS em todas as outras rotas
location / {
    if ($scheme != "https") {
        return 301 https://$host$request_uri;
    }
}
```

---

## 📋 Configuração Necessária no Servidor

### Se usar Apache (WAMP):

1. Edite o arquivo `.htaccess` na raiz do projeto
2. Adicione as regras para permitir HTTP em `/login`

### Se usar Nginx:

1. Edite o arquivo de configuração do site
2. Adicione a exceção para `/login`

---

## 🔧 Alternativa: Mudar URL no MikroTik (se possível)

Se você puder fazer UMA pequena alteração no MikroTik, mude de:
```
http://login.tocantinswifi.local/login
```

Para:
```
https://www.tocantinstransportewifi.com.br/login
```

Isso resolve TUDO sem precisar de configurações extras!

---

## 🎯 Recomendação Final

**Para seu caso específico, recomendo:**

1. **Curto prazo:** Implementar a rota `/login` que aceita HTTP (já implementada)
2. **Médio prazo:** Configurar o MikroTik para usar seu domínio HTTPS ao invés de .local
3. **Longo prazo:** Instalar certificado SSL no servidor MikroTik

---

## 📞 Precisa de Ajuda?

Se precisar de ajuda para configurar o Apache/Nginx ou o MikroTik, me avise!
