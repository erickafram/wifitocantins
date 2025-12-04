# WiFi Network Binding - Captura de MAC/IP via WiFi

## O que foi implementado

O app agora força requisições HTTP a usar a rede WiFi mesmo quando os dados móveis estão ativos. Isso permite capturar o MAC e IP corretos do dispositivo na rede do MikroTik.

## Como funciona

1. **SplashActivity**: Ao iniciar, o app tenta se conectar à rede WiFi do ônibus
2. **WifiNetworkHelper**: Usa `bindProcessToNetwork()` para forçar todo tráfego pelo WiFi
3. **WebAppInterface**: Expõe métodos JavaScript para o site Laravel obter MAC/IP

## Uso no Laravel (JavaScript)

```javascript
// Verificar se está rodando no app Android
if (typeof AndroidApp !== 'undefined') {
    
    // Verificar se tem informações de WiFi
    if (AndroidApp.hasWifiInfo()) {
        var mac = AndroidApp.getWifiMac();
        var ip = AndroidApp.getWifiIp();
        
        console.log('MAC do WiFi:', mac);
        console.log('IP do WiFi:', ip);
        
        // Enviar para o backend
        fetch('/api/register-device', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mac: mac, ip: ip })
        });
    }
    
    // Solicitar atualização das informações de WiFi
    AndroidApp.refreshWifiInfo();
    
    // Mostrar toast no app
    AndroidApp.showToast('Conectado ao WiFi!');
}

// Callback quando WiFi info é atualizado
function onWifiInfoUpdated(mac, ip) {
    console.log('WiFi atualizado - MAC:', mac, 'IP:', ip);
    // Processar os dados...
}
```

## Parâmetros na URL

O app também envia MAC/IP como parâmetros na URL inicial:

```
https://www.tocantinstransportewifi.com.br?from_app=1&app_mac=XX:XX:XX:XX:XX:XX&app_ip=10.5.50.123
```

No Laravel, você pode capturar assim:

```php
// No Controller
public function index(Request $request)
{
    $fromApp = $request->has('from_app');
    $appMac = $request->get('app_mac');
    $appIp = $request->get('app_ip');
    
    if ($fromApp && $appIp) {
        // Usar MAC/IP do app (veio via WiFi)
        $this->registerDevice($appMac, $appIp);
    }
}
```

## Limitações

1. **MAC Address**: Android 6+ retorna MAC randomizado por privacidade. O MAC real é obtido pelo MikroTik quando o dispositivo conecta.

2. **IP Address**: O IP é capturado corretamente e deve estar na faixa `10.5.50.x` do hotspot.

3. **Permissões**: O app precisa das permissões:
   - `ACCESS_WIFI_STATE`
   - `CHANGE_NETWORK_STATE`
   - `ACCESS_NETWORK_STATE`

## Fluxo completo

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Usuário abre o app                                       │
├─────────────────────────────────────────────────────────────┤
│ 2. SplashActivity inicia WifiNetworkHelper                  │
│    - Verifica se está conectado ao WiFi do ônibus           │
│    - Faz binding da rede WiFi (bindProcessToNetwork)        │
│    - Captura IP do dispositivo na rede WiFi                 │
├─────────────────────────────────────────────────────────────┤
│ 3. Carrega site com parâmetros                              │
│    URL: tocantinstransportewifi.com.br?from_app=1&app_ip=X  │
├─────────────────────────────────────────────────────────────┤
│ 4. Laravel recebe requisição                                │
│    - Detecta que veio do app (from_app=1)                   │
│    - Usa app_ip para identificar dispositivo                │
│    - Consulta MikroTik para obter MAC real pelo IP          │
├─────────────────────────────────────────────────────────────┤
│ 5. MikroTik já tem o MAC registrado                         │
│    - Script registrarMacsAutomatico já enviou MAC/IP        │
│    - Laravel cruza IP do app com MAC do MikroTik            │
└─────────────────────────────────────────────────────────────┘
```

## Compilar o APK

```bash
cd android-app
gradlew assembleDebug
```

O APK estará em: `app/build/outputs/apk/debug/app-debug.apk`
