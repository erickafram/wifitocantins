# 🚀 TESTE IMEDIATO - COMANDO DIRETO

## Execute APENAS este comando no MikroTik:

```bash
/ip hotspot profile set default redirect-to-link="https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F&source=mikrotik"
```

## ✅ RESULTADO ESPERADO:

1. **Qualquer acesso** a `https://www.tocantinstransportewifi.com.br/` 
2. **Será automaticamente redirecionado** para `https://www.tocantinstransportewifi.com.br/?mac=5C:CD:5B:2F:B9:3F&source=mikrotik`
3. **Console mostrará**: `🎯 MAC REAL capturado da URL: 5C:CD:5B:2F:B9:3F`

## 🧪 COMO TESTAR:

1. Execute o comando acima no MikroTik
2. Acesse `https://www.tocantinstransportewifi.com.br/` (SEM parâmetros)
3. Verifique se foi redirecionado automaticamente
4. Console deve mostrar MAC real, não mock

## 📋 SE FUNCIONAR:

- ✅ Problema resolvido para seu dispositivo
- 🔄 Depois implementaremos detecção dinâmica para qualquer MAC

## 📋 SE NÃO FUNCIONAR:

- Execute: `/ip hotspot profile print` para verificar se aplicou
- Tente logout/login no hotspot
- Ou execute um dos scripts mais complexos acima
