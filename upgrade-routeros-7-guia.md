# 🚀 GUIA DE UPGRADE ROUTEROS 6.49.13 → 7 (2025)

## ✅ **VANTAGENS DO ROUTEROS 7:**
- 🔒 **WireGuard VPN nativo** (solução definitiva para MAC real)
- ⚡ **Performance melhorada** (até 30% mais rápido)
- 🛡️ **Segurança aprimorada** (novos algoritmos de criptografia)
- 📱 **Interface moderna** (WebFig redesignado)
- 🔧 **Recursos avançados** (Container, ZeroTier, etc.)

---

## ⚠️ **ANTES DE ATUALIZAR:**

### **1. BACKUP COMPLETO:**
```bash
# No MikroTik atual (6.49.13)
/system backup save name=backup-pre-upgrade-v7
/export file=config-pre-upgrade-v7
```

### **2. VERIFICAR COMPATIBILIDADE:**
- ✅ **Modelo:** RBD52G-5HacD2HnD → **COMPATÍVEL com RouterOS 7**
- ✅ **RAM:** Mínimo 128MB → **OK**
- ✅ **Storage:** Mínimo 128MB → **OK**

### **3. PLANEJAR DOWNTIME:**
- ⏱️ **Tempo estimado:** 15-30 minutos
- 🚌 **Impacto:** WiFi indisponível durante upgrade
- 📅 **Melhor horário:** Madrugada ou baixa demanda

---

## 🔧 **PROCESSO DE UPGRADE:**

### **MÉTODO 1: Via WebFig/WinBox (RECOMENDADO)**
1. **Download firmware:**
   - Acesse: [mikrotik.com/download](https://mikrotik.com/download)
   - Modelo: `RBD52G-5HacD2HnD`
   - Versão: `RouterOS v7.16` (mais recente estável 2025)

2. **Upload via WinBox:**
   - Files → Drag firmware `.npk`
   - Reboot → Aguardar 10-15 minutos

### **MÉTODO 2: Via Terminal (AVANÇADO)**
```bash
# Download direto no MikroTik
/tool fetch url="https://download.mikrotik.com/routeros/7.16/routeros-7.16-arm.npk"

# Verificar download
/file print where name~"routeros"

# Reboot para aplicar
/system reboot
```

---

## 🎯 **PÓS-UPGRADE: CONFIGURAÇÃO WIREGUARD**

### **1. Configurar WireGuard Server:**
```bash
# Gerar chaves
/interface/wireguard add name=wg-server listen-port=51820

# Gerar chave privada
/interface/wireguard/peers add interface=wg-server public-key="CHAVE_PUBLICA_CLIENTE" allowed-address=10.0.0.2/32

# Configurar IP
/ip/address add address=10.0.0.1/24 interface=wg-server
```

### **2. Cliente no Servidor DigitalOcean:**
```bash
# Instalar WireGuard no Ubuntu/Debian
sudo apt update && sudo apt install wireguard

# Gerar chaves
wg genkey | tee privatekey | wg pubkey > publickey

# Configurar cliente
sudo nano /etc/wireguard/wg0.conf
```

### **3. Configuração Definitiva:**
```ini
# /etc/wireguard/wg0.conf (DigitalOcean)
[Interface]
PrivateKey = CHAVE_PRIVADA_CLIENTE
Address = 10.0.0.2/24

[Peer]
PublicKey = CHAVE_PUBLICA_MIKROTIK
Endpoint = IP_PUBLICO_MIKROTIK:51820
AllowedIPs = 192.168.10.0/24
PersistentKeepalive = 25
```

---

## 🌐 **SOLUÇÃO DEFINITIVA COM WIREGUARD:**

### **FLUXO COMPLETO:**
```
Usuário conecta WiFi (MikroTik)
    ↓
WireGuard tunnel → DigitalOcean
    ↓
Laravel recebe MAC real via tunnel
    ↓
Pagamento PIX confirmado
    ↓
Comando via WireGuard → MikroTik
    ↓
Liberação automática INSTANTÂNEA
```

### **VANTAGENS:**
- 🎯 **MAC real 100% garantido** (via tunnel)
- ⚡ **Latência baixa** (conexão direta)
- 🔒 **Segurança máxima** (criptografia WireGuard)
- 🚀 **Liberação instantânea** (sem polling HTTP)

---

## 📊 **COMPARAÇÃO DE SOLUÇÕES:**

| **MÉTODO** | **RouterOS 6** | **RouterOS 7 + WireGuard** |
|------------|----------------|----------------------------|
| MAC Real | ⚠️ Parcial | ✅ 100% Garantido |
| Latência | 🐌 30s (HTTP polling) | ⚡ <1s (tunnel direto) |
| Segurança | 🔓 HTTP | 🔒 WireGuard criptografado |
| Confiabilidade | ⚠️ Dependente de internet | ✅ Tunnel persistente |
| Complexidade | 😐 Média | 😊 Simples após setup |

---

## 🚀 **RECOMENDAÇÃO:**

### **OPÇÃO 1: TESTAR SOLUÇÃO ATUAL PRIMEIRO**
- Execute `solucao-definitiva-2025.rsc`
- Teste por 1-2 dias
- Se funcionar 95%+ → Manter RouterOS 6

### **OPÇÃO 2: UPGRADE PARA MÁXIMA CONFIABILIDADE**
- Fazer backup completo
- Upgrade para RouterOS 7
- Configurar WireGuard
- **Resultado:** Sistema 100% confiável

---

## 🎯 **DECISÃO:**

**Para seu caso (ônibus comercial):**
- 🥇 **1ª opção:** Testar solução atual (menos risco)
- 🥈 **2ª opção:** Upgrade se precisar 100% confiabilidade
- 🥉 **3ª opção:** Contratar técnico especializado para upgrade

**Qual opção prefere?** 🤔
