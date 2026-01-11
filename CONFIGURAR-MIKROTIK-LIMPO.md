# 🚀 Configuração Limpa do MikroTik - Passo a Passo

## Após o Reset de Fábrica

### 1. Acessar o MikroTik
- IP: `192.168.88.1`
- Usuário: `admin`
- Senha: (vazia)

### 2. Verificar Espaço em Disco
```
/system resource print
```
Deve mostrar `free-hdd-space` com alguns MB livres.

### 3. Configurar Passo a Passo

Execute cada bloco separadamente:

---

## BLOCO 1: BRIDGES
```
/interface bridge
add admin-mac=D4:01:C3:C6:29:4A auto-mac=no comment=defconf name=bridgeLocal
add fast-forward=no name=wifi-hotspot
```

## BLOCO 2: BRIDGE PORTS
```
/interface bridge port
add bridge=bridgeLocal interface=ether1
add bridge=bridgeLocal interface=ether2
add bridge=bridgeLocal interface=ether3
add bridge=bridgeLocal interface=ether4
add bridge=bridgeLocal interface=ether5
```

## BLOCO 3: WIFI
```
/interface wifi datapath
add bridge=wifi-hotspot name=capdp

/interface wifi security
add authentication-types="" name=open-security

/interface wifi configuration
add country=Brazil name=tocantins-2g security=open-security ssid=TocantinsTransporteWiFi
add country=Brazil name=tocantins-5g security=open-security ssid=TocantinsTransporteWiFi-5G

/interface wifi
set wifi1 configuration=tocantins-2g configuration.mode=ap datapath=capdp disabled=no
set wifi2 configuration=tocantins-5g configuration.mode=ap datapath=capdp disabled=no
```

## BLOCO 4: IP E DHCP
```
/ip address
add address=10.5.50.1/24 interface=wifi-hotspot network=10.5.50.0

/ip pool
add name=hs-pool ranges=10.5.50.10-10.5.50.250

/ip dhcp-server
add address-pool=hs-pool interface=wifi-hotspot name=hotspot-dhcp

/ip dhcp-server network
add address=10.5.50.0/24 dns-server=1.1.1.1,8.8.8.8 gateway=10.5.50.1

/ip dhcp-client
add interface=bridgeLocal
```

## BLOCO 5: DNS
```
/ip dns
set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8

/ip dns static
add address=138.68.255.122 name=tocantinstransportewifi.com.br
add address=138.68.255.122 name=www.tocantinstransportewifi.com.br
add address=10.5.50.1 name=hotspot.wifi
```

## BLOCO 6: NAT
```
/ip firewall nat
add action=masquerade chain=srcnat out-interface=bridgeLocal
```

## BLOCO 7: HOTSPOT
```
/ip hotspot profile
add dns-name=hotspot.wifi hotspot-address=10.5.50.1 html-directory=flash/hotspot http-cookie-lifetime=1d login-by=cookie,http-chap,http-pap name=hsprof-tocantins

/ip hotspot
add address-pool=hs-pool disabled=no interface=wifi-hotspot name=tocantins-hotspot profile=hsprof-tocantins
```

## BLOCO 8: WALLED GARDEN (MÍNIMO!)
```
/ip hotspot walled-garden
add dst-host=tocantinstransportewifi.com.br server=tocantins-hotspot
add dst-host=*.tocantinstransportewifi.com.br server=tocantins-hotspot
add dst-host=www.tocantinstransportewifi.com.br server=tocantins-hotspot
add dst-host=10.5.50.1 server=tocantins-hotspot
add dst-host=api.woovi.com server=tocantins-hotspot
add dst-host=*.woovi.com server=tocantins-hotspot
add dst-host=api.openpix.com.br server=tocantins-hotspot
add dst-host=*.openpix.com.br server=tocantins-hotspot
add dst-host=fonts.googleapis.com server=tocantins-hotspot
add dst-host=fonts.gstatic.com server=tocantins-hotspot
add dst-host=cdn.tailwindcss.com server=tocantins-hotspot

/ip hotspot walled-garden ip
add action=accept dst-address=138.68.255.122 server=tocantins-hotspot
add action=accept dst-address=104.16.0.0/12
add action=accept dst-address=10.5.50.1 server=tocantins-hotspot
```

## BLOCO 9: SCRIPT DE SYNC (OTIMIZADO)
```
/system script
add name=syncPagos owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:do {
:local r [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
:if (($r->"status")="finished") do={
:local d ($r->"data")
:local p 0
:while ([:find $d "\"mac_address\":\"" $p]>=0) do={
:set p [:find $d "\"mac_address\":\"" $p]
:if ($p>=0) do={
:set p ($p+15)
:local e [:find $d "\"" $p]
:if ($e>=0) do={
:local m [:pick $d $p $e]
:set p ($e+1)
:if ([:len $m]=17) do={
:if ([:len [/ip hotspot ip-binding find mac-address=$m]]=0) do={
:do {/ip hotspot ip-binding add mac-address=$m type=bypassed comment="PAGO"} on-error={}
}
}
}
}
}
}
} on-error={}
}

/system scheduler
add name=syncScheduler interval=2m on-event=syncPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon
```

## BLOCO 10: CONFIGURAÇÕES FINAIS
```
/system clock
set time-zone-name=America/Araguaina

/system logging action set memory memory-lines=100
```

---

## ⚠️ O QUE FOI REMOVIDO (para economizar espaço):

1. **Bancos no walled-garden** - Não precisa! O usuário paga pelo portal e depois é liberado
2. **Scripts grandes** - Substituído por script mínimo
3. **Múltiplos schedulers** - Apenas 1 a cada 2 minutos
4. **Logging em disco** - Só em memória
5. **DNS estáticos extras** - Apenas os essenciais

---

## 📊 Verificar se Funcionou

```
# Ver espaço
/system resource print

# Ver WiFi
/interface wifi print

# Ver hotspot
/ip hotspot print

# Testar internet
/ping 8.8.8.8 count=3
```

---

## 🔧 Se Precisar Adicionar Bancos Depois

Adicione APENAS os bancos mais usados:
```
/ip hotspot walled-garden
add dst-host=*.nubank.com.br server=tocantins-hotspot
add dst-host=*.bb.com.br server=tocantins-hotspot
add dst-host=*.caixa.gov.br server=tocantins-hotspot
add dst-host=*.itau.com.br server=tocantins-hotspot
add dst-host=*.bradesco.com.br server=tocantins-hotspot
add dst-host=*.santander.com.br server=tocantins-hotspot
add dst-host=*.picpay.com server=tocantins-hotspot
add dst-host=*.mercadopago.com.br server=tocantins-hotspot
```

NÃO adicione todos os 100+ bancos como antes!
