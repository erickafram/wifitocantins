# Troubleshooting - MikroTik WiFi Hotspot

## Problema: "Paguei mas não tenho acesso à internet"

### Causas Comuns

1. **MAC não foi registrado corretamente**
   - O dispositivo pode estar usando MAC randomizado
   - O MAC não foi capturado no momento do pagamento

2. **Sincronização ainda não rodou**
   - O MikroTik sincroniza a cada 30 segundos
   - Aguarde até 1 minuto após o pagamento

3. **Status do usuário incorreto**
   - O pagamento foi confirmado mas o status não foi atualizado

4. **Problema de rede entre MikroTik e API**
   - MikroTik não consegue acessar a API
   - Certificado SSL com problema

### Como Diagnosticar

#### 1. Verificar status de um MAC específico

```
GET /api/mikrotik/check-mac?token=mikrotik-sync-2024&mac=XX:XX:XX:XX:XX:XX
```

Resposta mostra:
- Se o MAC está no sistema
- Status do usuário (connected, active, expired, etc)
- Quando expira o acesso
- Últimos pagamentos

#### 2. Forçar liberação de um MAC

```
GET /api/mikrotik/force-liberate?token=mikrotik-sync-2024&mac=XX:XX:XX:XX:XX:XX
```

Isso força o status para "connected" e define novo tempo de expiração.

#### 3. Ver diagnóstico geral

```
GET /api/mikrotik/diagnostics?token=mikrotik-sync-2024
```

Mostra estatísticas do sistema, últimos usuários liberados, etc.

### Comandos no MikroTik

#### Ver logs de sincronização
```
/log print where message~"SYNC"
```

#### Ver MACs liberados
```
/ip hotspot ip-binding print where comment="PAGO-AUTO"
```

#### Testar script manualmente
```
/system script run syncPagos
```

#### Ver usuários ativos no hotspot
```
/ip hotspot active print
```

#### Forçar reconexão de um dispositivo
```
/ip hotspot active remove [find mac-address="XX:XX:XX:XX:XX:XX"]
```

#### Liberar MAC manualmente
```
/ip hotspot ip-binding add mac-address=XX:XX:XX:XX:XX:XX type=bypassed comment="MANUAL"
```

### Problema: Usuário paga na rede 2.4GHz mas não acessa na 5GHz

**Isso NÃO deveria acontecer!**

O MAC address é do DISPOSITIVO, não da rede WiFi. O mesmo celular tem o mesmo MAC em ambas as redes (2.4GHz e 5GHz).

Se isso está acontecendo, verifique:

1. **MAC Randomizado**: Alguns dispositivos usam MAC diferente para cada rede
   - iPhone: Configurações > Wi-Fi > (i) > Endereço Wi-Fi Privado = DESLIGADO
   - Android: Configurações > Wi-Fi > Rede > Privacidade > Usar MAC do dispositivo

2. **Bridges separadas**: Verifique se ambas as redes estão na mesma bridge
   ```
   /interface bridge port print
   ```
   Ambas wlan1 e wlan2 devem estar na bridge `wifi-hotspot`

3. **Hotspot em ambas interfaces**: O hotspot deve estar configurado na bridge, não nas interfaces individuais

### Configuração Correta do MikroTik

```
# Bridge única para hotspot
/interface bridge add name=wifi-hotspot

# Adicionar ambas interfaces WiFi à bridge
/interface bridge port add bridge=wifi-hotspot interface=wlan1
/interface bridge port add bridge=wifi-hotspot interface=wlan2

# Hotspot na bridge (não nas interfaces individuais)
/ip hotspot add interface=wifi-hotspot name=tocantins-hotspot
```

### Fluxo de Liberação

1. Usuário conecta no WiFi → Recebe IP do DHCP
2. Usuário tenta acessar internet → Redirecionado para portal
3. Usuário paga via PIX → Webhook confirma pagamento
4. Sistema atualiza status do usuário para "connected"
5. MikroTik consulta API (a cada 30s) → Recebe lista de MACs para liberar
6. MikroTik cria ip-binding type=bypassed para o MAC
7. Usuário tem acesso direto à internet (bypass do hotspot)

### Logs Importantes

No Laravel (storage/logs/laravel.log):
```
📡 MikroTik Lite sync - Mostra MACs sendo sincronizados
✅ Pagamento confirmado - Mostra quando pagamento é aprovado
🔓 Liberação forçada - Mostra liberações manuais
```

No MikroTik (/log print):
```
SYNC: Liberado XX:XX:XX:XX:XX:XX - MAC foi liberado
SYNC: Removido XX:XX:XX:XX:XX:XX - MAC foi removido (expirou)
SYNC: Erro - Problemas de conexão com API
```
