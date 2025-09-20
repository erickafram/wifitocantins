#!/bin/bash
# =====================================================
# 🚀 CONFIGURAÇÃO WIREGUARD NO DIGITALOCEAN
# Execute no servidor Ubuntu/Debian
# =====================================================

echo "🔧 Configurando WireGuard no DigitalOcean..."

# 1. INSTALAR WIREGUARD
sudo apt update
sudo apt install -y wireguard wireguard-tools

# 2. GERAR CHAVES
cd /etc/wireguard
sudo wg genkey | sudo tee privatekey | wg pubkey | sudo tee publickey

echo "✅ Chaves geradas:"
echo "Chave privada servidor:"
sudo cat privatekey
echo ""
echo "Chave pública servidor:"
sudo cat publickey
echo ""

# 3. CRIAR CONFIGURAÇÃO
sudo tee /etc/wireguard/wg0.conf > /dev/null <<EOF
[Interface]
PrivateKey = $(sudo cat privatekey)
Address = 10.0.0.2/30
ListenPort = 51820
SaveConfig = true

# Permitir forward
PostUp = iptables -A FORWARD -i wg0 -j ACCEPT; iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
PostDown = iptables -D FORWARD -i wg0 -j ACCEPT; iptables -t nat -D POSTROUTING -o eth0 -j MASQUERADE

[Peer]
PublicKey = kpJIigXHg/rb/3dF3A8/hlCLifiIEhVVxe7mc3yKsG4=
AllowedIPs = 10.0.0.1/32, 192.168.10.0/24
Endpoint = IP_PUBLICO_MIKROTIK:51820
PersistentKeepalive = 25
EOF

echo "✅ Configuração WireGuard criada"

# 4. HABILITAR IP FORWARDING
echo 'net.ipv4.ip_forward=1' | sudo tee -a /etc/sysctl.conf
sudo sysctl -p

# 5. INICIAR WIREGUARD
sudo systemctl enable wg-quick@wg0
sudo systemctl start wg-quick@wg0

echo "🎉 WireGuard configurado no DigitalOcean!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Copie a chave pública do servidor (mostrada acima)"
echo "2. Configure o peer no MikroTik com essa chave"
echo "3. Substitua IP_PUBLICO_MIKROTIK pelo IP real do MikroTik"
echo ""
echo "🧪 TESTAR CONEXÃO:"
echo "sudo wg show"
echo "ping 10.0.0.1"
