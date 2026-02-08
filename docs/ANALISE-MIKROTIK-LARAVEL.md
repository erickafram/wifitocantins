# Análise Completa: MikroTik ↔ Laravel - WiFi Tocantins

## Resumo Executivo

Foram identificados **3 bugs críticos** que explicam os 3 problemas reportados pelos passageiros:

| Problema Reportado | Causa Raiz | Correção |
|---|---|---|
| **"Paguei mas não liberou"** | Filtragem de MACs randomizados em TODA a cadeia | Removida filtragem em 4 arquivos |
| **"Internet muito lenta"** | Queue types definidos mas NUNCA aplicados | Queue tree criado no MikroTik |
| **"Bancos não abrem"** | CDNs bancários (AWS, Akamai, Azure) desabilitados no walled-garden | CDNs habilitados |

---

## Fluxo Completo: Pagamento → Liberação

```
PASSAGEIRO                    PORTAL LARAVEL             MIKROTIK
    │                              │                        │
    ├── Conecta ao WiFi ──────────────────────────────────► │
    │                              │                        │
    │◄─── Redireciona para portal ──────────────────────────┤
    │                              │                        │
    ├── Abre página portal ──────► │                        │
    │   (portal.js detecta MAC)    │                        │
    │                              │                        │
    │   MikroTik registra MAC ─────────────────────────────►│
    │   via registrarMacs (1min)   │◄── POST /register-mac ─┤
    │                              │                        │
    ├── Cadastra telefone ───────► │                        │
    │   POST /register-for-payment │ (salva MAC + telefone) │
    │                              │                        │
    ├── Gera PIX ────────────────► │                        │
    │   POST /payment/pix/generate │ (cria Payment record)  │
    │                              │                        │
    ├── Paga via app banco ──────► │ (webhook Woovi/etc)    │
    │                              │                        │
    │                    ┌─────────┤                        │
    │                    │ activateUserAccess():             │
    │                    │ - status = 'connected'           │
    │                    │ - expires_at = now + 24h         │
    │                    │ - Tenta liberação imediata ──────►│
    │                    └─────────┤                        │
    │                              │                        │
    │                              │   syncPagos (15s) ─────┤
    │                              │◄── GET /check-paid-users-lite
    │                              │   Retorna "L:MAC"      │
    │                              │──────────────────────► │
    │                              │   Cria ip-binding      │
    │                              │   type=bypassed        │
    │◄─── Internet liberada! ──────────────────────────────►│
```

---

## Bug Crítico #1: Filtragem de MACs Randomizados

### O Que São MACs Randomizados?

Desde iOS 14 (2020) e Android 10 (2019), **todos os smartphones modernos** usam MACs randomizados por padrão. Um MAC randomizado:
- Tem o bit 1 do primeiro byte setado (flag "locally administered")
- Prefixos comuns: `02:`, `06:`, `0A:`, `0E:`, `12:`, `16:`, etc.
- É **consistente por rede** (mesmo MAC para mesmo SSID WiFi)
- É um identificador **perfeitamente válido** para autenticação hotspot

### Onde o Bug Existia (TODOS corrigidos)

| Arquivo | Linha | Código Antigo | Efeito |
|---|---|---|---|
| `portal.js` | 162 | `!macFromUrl.startsWith('02:')` | Rejeitava MAC da URL |
| `portal.js` | 213 | `!mac.startsWith('02:')` | Rejeitava MAC da API |
| `mac-detector.js` | 19,52,63,72,86 | `!mac.startsWith('02:')` | Rejeitava em TODOS os métodos |
| `HotspotIdentity.php` | `isMockMac()` | `Str::startsWith('02:')` | Marcava como "mock" |
| `HotspotIdentity.php` | `resolveRealMac()` | Usava `isMockMac()` | Descartava MAC válido |
| MikroTik `registrarMacs` | script | `[:pick $mac 0 3] != "02:"` | Não registrava no servidor |

### Cadeia de Falha Completa

```
1. Passageiro conecta → iPhone envia MAC 02:A1:B2:C3:D4:E5 (randomizado)
2. portal.js REJEITA esse MAC → gera MAC "mock" aleatório
3. registrarMacs MikroTik IGNORA esse MAC → não registra na API
4. Passageiro paga → Payment é criado com MAC mock/errado
5. activateUserAccess() seta status='connected' para MAC mock
6. syncPagos retorna "L:02:XX:YY:..." (o mock) no MikroTik
7. MikroTik cria ip-binding para MAC mock → NÃO corresponde ao dispositivo real
8. Dispositivo real com MAC 02:A1:B2:C3:D4:E5 continua BLOQUEADO
9. ❌ PASSAGEIRO PAGOU MAS NÃO TEM INTERNET
```

### Correção Aplicada

- **`HotspotIdentity::isMockMac()`**: Agora só rejeita MACs verdadeiramente inválidos (`00:00:00:00:00:00`, `FF:FF:FF:FF:FF:FF`, vazio)
- **`portal.js`**: Aceita todos os MACs com formato válido, apenas loga se é randomizado
- **`mac-detector.js`**: Valida formato em vez de filtrar por prefixo
- **MikroTik `registrarMacs`**: Aceita todos os MACs com 17 caracteres

---

## Bug Crítico #2: Banda Sem Controle

### O Problema

```
Queue Type pcq-download-hotspot (5M) ──── DEFINIDO ✓ mas NÃO APLICADO ✗
Queue Type pcq-upload-hotspot (2M)   ──── DEFINIDO ✓ mas NÃO APLICADO ✗
Queue Tree                           ──── NÃO EXISTE ✗
Simple Queue                         ──── NÃO EXISTE ✗
```

Resultado: **ZERO controle de banda**. Se 1 passageiro assiste Netflix em 4K (~25 Mbps), todos os outros ficam sem internet.

### Correção no MikroTik

```routeros
# Mangle marks para identificar tráfego do hotspot
/ip firewall mangle add chain=forward src-address=10.5.50.0/24 action=mark-connection new-connection-mark=hotspot-conn
/ip firewall mangle add chain=forward connection-mark=hotspot-conn in-interface=ether1 action=mark-packet new-packet-mark=hotspot-download
/ip firewall mangle add chain=forward connection-mark=hotspot-conn out-interface=ether1 action=mark-packet new-packet-mark=hotspot-upload

# Queue tree usando os PCQ types (5M download, 2M upload POR USUÁRIO)
/queue tree add name=hotspot-download-tree parent=global packet-mark=hotspot-download queue=pcq-download-hotspot max-limit=50M
/queue tree add name=hotspot-upload-tree parent=global packet-mark=hotspot-upload queue=pcq-upload-hotspot max-limit=20M
```

O PCQ distribui a banda **igualmente** entre todos os usuários ativos, com máximo de 5 Mbps download / 2 Mbps upload por dispositivo.

---

## Bug Crítico #3: Bancos Não Carregam

### O Problema

Os apps bancários dependem de CDNs para funcionar. Esses CDNs estavam completamente ausentes do walled-garden.

**IMPORTANTE**: O walled-garden libera acesso APENAS para quem **NÃO pagou**. A abordagem deve ser **restritiva**:
- Liberar APENAS o portal, bancos e gateways de pagamento
- NÃO liberar domínios amplos como `*.google.com`, `*.amazonaws.com`, `*.microsoft.com`
- Esses domínios dariam internet livre para quem não pagou!

### Entradas CDN Corretas (APENAS CDN puro)

| Domínio | Tipo | O que serve | Seguro? |
|---|---|---|---|
| `*.cloudfront.net` | CDN AWS | Assets dos bancos (Nubank, Inter, C6) | Sim - só CDN |
| `*.akamai.net` | CDN Akamai | Assets BB, Caixa, Bradesco, Itaú | Sim - só CDN |
| `*.akamaiedge.net` | CDN Akamai | Edge delivery | Sim - só CDN |
| `*.azureedge.net` | CDN Azure | Assets Santander, PagBank | Sim - só CDN |
| `*.fastly.net` | CDN Fastly | Assets fintechs | Sim - só CDN |

### Entradas que NÃO devem estar no walled-garden

| Domínio | Por que NÃO | O que libera indevidamente |
|---|---|---|
| `*.google.com` | Internet livre | YouTube, Gmail, Search, etc. |
| `*.amazonaws.com` | Internet livre | Qualquer site na AWS |
| `*.microsoft.com` | Internet livre | Bing, Office, etc. |
| `*.apple.com` | Internet livre | iCloud, App Store, etc. |
| `*.azure.com` | Internet livre | Portal Azure, serviços |
| `*.windows.net` | Internet livre | Azure Storage, muitos sites |
| `*.gov.br` | Muito amplo | TODOS os sites do governo |
| `104.0.0.0/8` | 16M IPs! | Milhares de serviços |

### Correção

Adicionados ao walled-garden **APENAS domínios de CDN puro** (não dão acesso a sites):
- `*.cloudfront.net` (AWS CDN - não é `*.amazonaws.com`!)
- `*.akamai.net`, `*.akamaiedge.net`, `*.akamaitechnologies.com` (Akamai CDN)
- `*.azureedge.net`, `*.msecnd.net` (Azure CDN - não é `*.microsoft.com`!)
- `*.fastly.net` (Fastly CDN)
- `*.firebaseio.com`, `*.firebaseapp.com` (Firebase para apps bancários)
- Certificados CA específicos (ocsp.digicert.com, etc.) para validação HTTPS
- Captive portal detection específico (captive.apple.com, connectivitycheck.gstatic.com)

**Removidos/Não incluídos** (davam internet livre):
- `*.google.com` → substituído por endpoints específicos como `fonts.googleapis.com`
- `*.amazonaws.com` → substituído por `*.cloudfront.net` (só CDN)
- `*.microsoft.com` → substituído por `*.azureedge.net` (só CDN)
- `*.apple.com` → substituído por `captive.apple.com` (só captive portal)
- `*.gov.br` → removido (muito amplo)
- `104.0.0.0/8` → removido (16 milhões de IPs!)

---

## Outros Problemas Corrigidos

### 4. Script `liberarPagos` Usando JSON (Frágil)

O script antigo tentava fazer parse de JSON no MikroTik (que não tem suporte nativo a JSON). O parsing era frágil e falhava silenciosamente.

**Correção**: Substituído pelo script `syncPagos` que usa o endpoint `/check-paid-users-lite` com formato texto simples:
```
OK
L:AA:BB:CC:DD:EE:FF    ← Liberar
R:11:22:33:44:55:66    ← Remover
END
```

### 5. Regras de Firewall Duplicadas

Múltiplas regras de masquerade e DNS redirect duplicadas. Cada regra duplicada consome CPU no processamento de pacotes.

**Correção**: Script remove duplicatas automaticamente.

### 6. Bloqueio DoH/DoT Quebrando Serviços

Bloquear porta 443 para 8.8.8.8 e 1.1.1.1 também bloqueia HTTPS legítimo para esses IPs. Vários serviços dependem disso.

**Correção**: Regras removidas. O controle de DNS já é feito pelo DHCP + NAT redirect.

### 7. Intervalo de Sync Otimizado

| Script | Antes | Depois | Motivo |
|---|---|---|---|
| syncPagos | 30s | 15s | Passageiro espera menos após pagamento |
| registrarMacs | 2min | 1min | MACs registrados mais rápido |

---

## Arquivos Modificados (Laravel)

### `app/Support/HotspotIdentity.php`
- `isMockMac()`: Não rejeita mais MACs randomizados (02:xx). Apenas rejeita `00:00:00:00:00:00`, `FF:FF:FF:FF:FF:FF` e vazio
- Adicionado método `isRandomizedMac()` (informacional apenas)
- `resolveRealMac()`: Aceita MACs randomizados normalmente

### `public/js/portal.js`
- `detectDevice()`: Remove filtro `!macFromUrl.startsWith('02:')` - aceita todos os MACs válidos
- `waitForRealMac()`: Remove filtro `!mac.startsWith('02:')` - aceita todos os MACs válidos
- Adicionado método `isRandomizedMac()` para log informacional

### `public/js/mac-detector.js`
- Reescrito completamente para aceitar TODOS os MACs com formato válido
- Adicionado método `isValidMac()` que valida formato e rejeita apenas MACs placeholder
- Removidas TODAS as verificações `!mac.startsWith('02:')`

---

## Scripts MikroTik a Aplicar

**Arquivo**: `docs/CORRECOES-MIKROTIK.rsc`

Execute no terminal do MikroTik (WinBox → Terminal, ou SSH):

```
/system backup save name=backup-antes-correcoes
/import file-name=CORRECOES-MIKROTIK.rsc
```

Ou copie e cole o conteúdo seção por seção no terminal.

---

## Verificação Pós-Correção

### No MikroTik

```routeros
# Verificar scripts atualizados
/system script print

# Verificar schedulers
/system scheduler print

# Verificar queue tree ativo
/queue tree print

# Verificar walled-garden tem CDNs
/ip hotspot walled-garden print where comment~"CDN|Akamai|CloudFront|Azure"

# Verificar mangle marks funcionando
/ip firewall mangle print stats where comment~"hotspot"

# Monitorar logs de sync
/log print where topics~"script"
```

### Na API Laravel

```bash
# Testar endpoint lite
curl "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite?token=mikrotik-sync-2024"

# Testar diagnóstico
curl "https://www.tocantinstransportewifi.com.br/api/mikrotik/diagnostics?token=mikrotik-sync-2024"
```

### Teste Funcional Completo

1. Conectar smartphone ao WiFi `TocantinsTransporteWiFi`
2. Verificar se portal abre automaticamente
3. Verificar no console do navegador se MAC foi detectado (mesmo randomizado)
4. Registrar e gerar PIX
5. Verificar se app bancário abre e carrega corretamente
6. Pagar o PIX
7. Verificar no log do MikroTik se `syncPagos` liberou o MAC
8. Confirmar que internet foi liberada (máximo 15 segundos após pagamento)
9. Testar velocidade (deve ser ~5 Mbps download, ~2 Mbps upload)

---

## Arquitetura Recomendada para o Futuro

### Liberação Instantânea (sem polling)

O sistema atual depende de polling (MikroTik consulta API a cada 15s). Para liberação **instantânea** (0 segundos de espera):

1. **Webhook → REST API do MikroTik**: Após pagamento confirmado, Laravel faz POST direto na REST API do RouterOS 7 para criar o ip-binding. O `MikrotikWebhookService.php` já tenta isso, mas precisa que o MikroTik tenha a REST API habilitada e acessível.

2. **Configurar REST API no MikroTik**:
```routeros
/ip service set www-ssl disabled=no port=443
# Criar usuário API com permissão limitada
/user add name=api-laravel password=SENHA-SEGURA group=write
```

### Monitoramento

Implementar um endpoint de health check que o MikroTik chama periodicamente para reportar:
- Número de usuários conectados
- Uso de banda total
- Número de ip-bindings ativos
- Uptime do router

Isso permite criar dashboards e alertas no painel admin.
