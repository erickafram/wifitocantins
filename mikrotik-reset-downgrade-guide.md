# Guia Completo: Reset e Downgrade MikroTik Access Point para RouterOS 6.x

## ⚠️ Problema Comum: "NetInstall não funciona"

Se o NetInstall64 não está funcionando, o problema geralmente é um dos seguintes:

### 1. **Firewall/Antivírus Bloqueando**
- **Windows Defender/Firewall**: DEVE ser desativado temporariamente
- **Antivírus**: Desative completamente durante o processo
- **Outras conexões de rede**: Desative Wi-Fi e outras interfaces

### 2. **Configuração de IP Incorreta**
- Seu computador DEVE ter IP estático na mesma rede
- O NetInstall precisa de permissões de administrador

### 3. **Procedimento de Reset Incorreto**
- Timing do botão reset é crítico
- Cada modelo tem procedimento específico

---

## 📋 Pré-requisitos

### Downloads Necessários:
1. **NetInstall**: https://mikrotik.com/download
2. **RouterOS 6.49.10** (última versão estável v6): https://mikrotik.com/download/archive

### Verificar Modelo do Access Point:
- Olhe na etiqueta do dispositivo
- Anote o modelo exato (ex: hAP ac², cAP ac, etc.)
- Baixe o RouterOS compatível com a arquitetura

---

## 🔧 Preparação do Ambiente (CRÍTICO)

### Passo 1: Configurar Computador
```
1. Desative TODAS as conexões:
   - Wi-Fi
   - VPN
   - Outras interfaces Ethernet

2. Configure IP estático na placa de rede:
   - IP: 192.168.88.10
   - Máscara: 255.255.255.0
   - Gateway: 192.168.88.1

3. Desative temporariamente:
   - Windows Defender
   - Antivírus
   - Firewall
```

### Passo 2: Preparar NetInstall
```
1. Execute NetInstall como ADMINISTRADOR
2. Clique em "Net booting"
3. Marque "Boot Server enabled"
4. Configure:
   - Client IP Address: 192.168.88.100
   - Netmask: 255.255.255.0
5. Deixe pronto para receber conexões
```

---

## 🔄 Procedimento de Reset e Instalação

### Método 1: Reset com Botão (Mais Comum)

```
1. DESLIGUE o access point
2. Conecte cabo Ethernet da Ether1 direto no PC
3. PRESSIONE E SEGURE o botão RESET
4. LIGUE o dispositivo (mantendo reset pressionado)
5. Segure o reset por 10-15 segundos até:
   - LED começar a piscar rapidamente, OU
   - Dispositivo aparecer no NetInstall
6. Solte o botão reset
```

### Método 2: Reset com Boot Loader (Se Método 1 falhar)

```
1. Conecte cabo serial (se disponível)
2. Use terminal 115200 baud
3. Desligue/ligue o dispositivo
4. Pressione qualquer tecla durante boot
5. Digite: /system reset
6. Confirme reset
```

---

## 📦 Instalação do RouterOS 6.x

### No NetInstall:

```
1. O dispositivo deve aparecer na lista
   - Se não aparecer, repita o reset
   - Verifique se firewall está desativado

2. Clique em "Browse"
3. Selecione o arquivo .npk do RouterOS 6.x baixado
4. Marque o pacote na lista
5. Clique em "Install"
6. Aguarde conclusão (5-15 minutos)
7. Dispositivo reiniciará automaticamente
```

---

## 🛠️ Solução de Problemas Comuns

### Problema: "Dispositivo não aparece no NetInstall"
**Soluções:**
```
1. Verificar cabo Ethernet (teste com outro cabo)
2. Tentar porta Ether2 ou PoE (alguns modelos)
3. Verificar se IP estático foi configurado
4. Desativar Windows Defender completamente
5. Usar outro computador (teste)
6. Tentar diferentes timings do botão reset
```

### Problema: "Install falha ou trava"
**Soluções:**
```
1. Verificar se RouterOS é compatível com o modelo
2. Baixar arquivo .npk novamente (pode estar corrompido)
3. Tentar versão diferente do RouterOS 6.x
4. Verificar espaço em disco do computador
```

### Problema: "Acesso após instalação"
**Configuração padrão após reset:**
```
- IP: 192.168.88.1
- Login: admin
- Senha: (vazio)
- Interface web: http://192.168.88.1
```

---

## 🎯 Versões Recomendadas RouterOS 6.x

### Para Access Points:
- **RouterOS 6.49.10** (mais estável)
- **RouterOS 6.49.7** (alternativa)
- **RouterOS 6.48.6** (para modelos mais antigos)

### Arquiteturas:
- **ARM**: Para hAP, cAP, wAP series
- **MIPSBE**: Para dispositivos mais antigos
- **x86**: Para dispositivos baseados em PC

---

## ✅ Verificação Final

Após instalação bem-sucedida:

```
1. Configure IP do PC para 192.168.88.2
2. Acesse: http://192.168.88.1
3. Login: admin (senha vazia)
4. Verifique em System > RouterBoard:
   - Current RouterOS: 6.x.x
   - Upgrade RouterOS: deve estar vazio
5. Configure conforme necessário
```

---

## 📞 Se Nada Funcionar

### Última tentativa:
1. Use computador diferente
2. Cabo Ethernet diferente
3. Fonte de energia diferente
4. Teste com outro NetInstall (versão anterior)

### Hardware pode estar danificado se:
- LED não acende
- Não aquece quando ligado
- Nenhuma resposta em múltiplos testes

---

## 💡 Dicas Importantes

1. **Paciência**: O processo pode levar até 30 minutos
2. **Não interrompa**: Nunca desligue durante a instalação
3. **Backup**: Se possível, faça backup da configuração antes
4. **Documentação**: Anote o processo que funcionou para futuros resets
5. **Teste**: Sempre teste a conectividade básica após instalação

---

*Guia criado em setembro 2024 com base nas melhores práticas da comunidade MikroTik*
