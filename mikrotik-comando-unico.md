# 🚀 COMANDO ÚNICO QUE FUNCIONA

## ⚠️ PROBLEMAS IDENTIFICADOS:
1. URL estava `/mac=` - **CORRETO** é `/?mac=`
2. Sintaxe do `redirect-to-link` com problemas

## 🔧 COMANDO CORRETO (Execute APENAS este):

```bash
/ip hotspot profile set default redirect-to-link=""
```

**E depois:**

```bash
/ip hotspot profile set default redirect-to-link="https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F"
```

## ✅ VERIFICAR SE APLICOU:

```bash
/ip hotspot profile print
```

**Deve mostrar:**
```
redirect-to-link="https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F"
```

## 🧪 TESTE:

1. Execute os comandos acima
2. Acesse: `https://www.tocantinstransportewifi.com.br/`
3. Deve redirecionar automaticamente para: `https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F`
4. Console deve mostrar: `🎯 MAC REAL capturado da URL: 5C:CD:5B:2F:B9:3F`
