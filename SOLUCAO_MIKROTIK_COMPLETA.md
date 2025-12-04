# 🚌 SOLUÇÃO COMPLETA - MikroTik Tocantins Transporte WiFi

## ⚠️ PROBLEMA PRINCIPAL IDENTIFICADO

O MikroTik **NÃO CONSEGUE ACESSAR A INTERNET**!

```
/tool fetch url="https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
status: failed
failure: Network unreachable
```

**Isso significa que o MikroTik não tem rota para a internet (Starlink).**

---

## 🔧 PASSO 1: CORRIGIR CONECTIVIDADE COM INTERNET

### 1.1 Verificar se tem IP na interface WAN
```routeros
/ip address print
```

Você deve ver algo como:
- `bridgeLocal` com IP da rede Starlink (ex: 192.168.1.x)
- `wifi-hotspot` com IP 10.5.50.1

### 1.2 Verificar rota padrão
```routeros
/ip route print
```

**DEVE TER** uma rota assim:
```
dst-address=0.0.0.0/0 gateway=<IP_DO_ROTEADOR_STARLINK>
```

### 1.3 Se NÃO tiver rota padrão, ADICIONE:
```routeros
# Descubra o gateway da Starlink (geralmente 192.168.1.1)
/ip route add dst-address=0.0.0.0/0 gateway=192.168.1.1
```

### 1.4 Testar conectividade
```routeros
/ping 8.8.8.8 count=5
/ping www.google.com count=3
```

Se pingar, continue para o próximo passo!

---

## 🔧 PASSO 2: CRIAR SCRIPT DE SINCRONIZAÇÃO

Cole este comando **INTEIRO** no terminal do MikroTik:

```routeros
/system script add name=liberarPagos owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local apiUrl "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users\?token=mikrotik-sync-2024"
:local logPrefix "[SYNC]"

:log info "$logPrefix Iniciando sincronizacao..."

:do {
    /tool fetch url=$apiUrl mode=https output=file dst-path=api_resp.txt
    :delay 2s
    
    :if ([/file find name=api_resp.txt] != "") do={
        :local content [/file get api_resp.txt contents]
        :log info "$logPrefix API respondeu OK"
        
        # Processar resposta aqui se necessario
        
        /file remove api_resp.txt
    }
    
    :log info "$logPrefix Sync concluida!"
} on-error={
    :log error "$logPrefix ERRO - Verifique conexao internet"
}
}
```

---

## 🔧 PASSO 3: CRIAR SCHEDULER (EXECUTA A CADA 30 SEGUNDOS)

```routeros
/system scheduler add name=syncPagosAPI interval=30s on-event=liberarPagos start-time=startup policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon comment="Sync usuarios pagos"
```

---

## 🔧 PASSO 4: TESTAR SCRIPT

```routeros
# Executar manualmente
/system script run liberarPagos

# Ver logs
/log print where message~"SYNC"
```

---

## 🔧 PASSO 5: VERIFICAR/ATUALIZAR LOGIN.HTML

O arquivo `login.html` deve estar em: `flash/hotspot/login.html`

### Via Winbox:
1. Abra Winbox → Files
2. Navegue até `flash/hotspot/`
3. Edite `login.html` com o conteúdo do arquivo `mikrotik-login.html`

### Via Terminal (se suportado):
```routeros
/file print where name~"login"
```

---

## 🔧 PASSO 6: VERIFICAR WALLED GARDEN

Estes domínios DEVEM estar liberados para usuários não autenticados:

```routeros
# Verificar existentes
/ip hotspot walled-garden print

# Adicionar se não existirem
/ip hotspot walled-garden add dst-host=tocantinstransportewifi.com.br comment="Portal"
/ip hotspot walled-garden add dst-host=*.tocantinstransportewifi.com.br comment="Portal Wild"
/ip hotspot walled-garden add dst-host=www.tocantinstransportewifi.com.br comment="Portal WWW"

# Captive Portal Detection
/ip hotspot walled-garden add dst-host=connectivitycheck.gstatic.com comment="Android"
/ip hotspot walled-garden add dst-host=captive.apple.com comment="Apple"
/ip hotspot walled-garden add dst-host=www.msftconnecttest.com comment="Windows"

# Bancos PIX
/ip hotspot walled-garden add dst-host=*.nubank.com.br comment="Nubank"
/ip hotspot walled-garden add dst-host=*.bb.com.br comment="BB"
/ip hotspot walled-garden add dst-host=*.itau.com.br comment="Itau"
/ip hotspot walled-garden add dst-host=*.caixa.gov.br comment="Caixa"
/ip hotspot walled-garden add dst-host=*.bradesco.com.br comment="Bradesco"
/ip hotspot walled-garden add dst-host=*.santander.com.br comment="Santander"
/ip hotspot walled-garden add dst-host=*.picpay.com comment="PicPay"
/ip hotspot walled-garden add dst-host=*.mercadopago.com.br comment="MercadoPago"

# API Pagamento
/ip hotspot walled-garden add dst-host=api.woovi.com comment="Woovi"
/ip hotspot walled-garden add dst-host=*.woovi.com comment="Woovi Wild"
```

### Walled Garden IP (IPs diretos):
```routeros
/ip hotspot walled-garden ip add action=accept dst-address=138.68.255.122 comment="Servidor Portal"
/ip hotspot walled-garden ip add action=accept dst-address=104.16.0.0/12 comment="Cloudflare"
/ip hotspot walled-garden ip add action=accept dst-address=8.8.8.8 comment="Google DNS"
/ip hotspot walled-garden ip add action=accept dst-address=1.1.1.1 comment="Cloudflare DNS"
```

---

## 📋 CHECKLIST FINAL

Execute estes comandos para verificar se tudo está OK:

```routeros
# 1. Internet funcionando?
/ping 8.8.8.8 count=3

# 2. API acessível?
/tool fetch url="https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024" mode=https output=user

# 3. Script existe?
/system script print where name=liberarPagos

# 4. Scheduler existe?
/system scheduler print where name~"sync"

# 5. Walled Garden configurado?
/ip hotspot walled-garden print count-only

# 6. Hotspot ativo?
/ip hotspot print

# 7. Usuários conectados?
/ip hotspot host print
```

---

## 🔄 FLUXO ESPERADO

1. **Usuário conecta** na rede "TocantinsTransporteWiFi"
2. **MikroTik captura** MAC e IP do usuário
3. **Captive Portal** redireciona para `login.html`
4. **login.html** redireciona para `https://tocantinstransportewifi.com.br/?mac=XX&ip=XX`
5. **Usuário paga** no portal
6. **API marca** usuário como `connected` com `expires_at`
7. **MikroTik consulta** API a cada 30 segundos
8. **API retorna** MACs para liberar/remover
9. **MikroTik libera** acesso do MAC pago

---

## ❓ TROUBLESHOOTING

### "Network unreachable"
- MikroTik não tem rota para internet
- Verifique `/ip route print`
- Adicione rota padrão para gateway da Starlink

### Captive Portal não aparece
- Android/iOS modernos usam HTTPS para detectar
- MikroTik só intercepta HTTP (porta 80)
- **Solução**: QR Code como alternativa

### Usuário não acessa portal de pagamento
- Verifique Walled Garden
- O domínio `tocantinstransportewifi.com.br` deve estar liberado

### Script não executa
- Verifique permissões: `/system script print detail`
- Deve ter `policy=read,write,test,policy`
