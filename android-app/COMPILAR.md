# 🚀 Guia Rápido de Compilação - WiFi Tocantins APK

## ⚡ Método Mais Rápido (Android Studio)

### 1️⃣ Instalar Android Studio
- Download: https://developer.android.com/studio
- Instale com as configurações padrão
- Aguarde download do SDK

### 2️⃣ Abrir Projeto
1. Abra o Android Studio
2. Clique em "Open"
3. Selecione a pasta `android-app`
4. Aguarde sincronização (pode demorar 5-10 minutos na primeira vez)

### 3️⃣ Gerar APK
1. Menu: **Build** → **Build Bundle(s) / APK(s)** → **Build APK(s)**
2. Aguarde compilação (2-5 minutos)
3. Clique em "locate" na notificação que aparecer
4. O APK estará em: `app/build/outputs/apk/debug/app-debug.apk`

### 4️⃣ Instalar no Celular
**Opção A - Via cabo USB:**
1. Ative "Depuração USB" no celular
2. Conecte o cabo USB
3. No Android Studio, clique no botão ▶️ (Run)

**Opção B - Via arquivo:**
1. Copie o arquivo `app-debug.apk` para o celular
2. Abra o arquivo no celular
3. Permita instalação de fontes desconhecidas
4. Instale

---

## 🖥️ Método Linha de Comando (Sem Android Studio)

### Requisitos
- JDK 11+: https://adoptium.net/
- Android SDK Command Line Tools

### Windows
```cmd
cd android-app
gradlew.bat assembleDebug
```

### Linux/Mac
```bash
cd android-app
chmod +x gradlew
./gradlew assembleDebug
```

### Localizar APK
```
android-app/app/build/outputs/apk/debug/app-debug.apk
```

---

## 📦 Gerar APK para Produção (Assinado)

### 1. Criar Keystore (apenas uma vez)
```bash
keytool -genkey -v -keystore wifi-tocantins.keystore -alias wifi-tocantins -keyalg RSA -keysize 2048 -validity 10000
```

**⚠️ IMPORTANTE:** Guarde a senha em local seguro!

### 2. Configurar Assinatura
Edite `app/build.gradle` e descomente as linhas de `signingConfig`

### 3. Compilar APK Release
```bash
# Windows
gradlew.bat assembleRelease

# Linux/Mac
./gradlew assembleRelease
```

APK estará em: `app/build/outputs/apk/release/app-release.apk`

---

## 🎯 Checklist Antes de Publicar

- [ ] Testado em dispositivo físico
- [ ] Versão incrementada em `build.gradle`
- [ ] Ícone personalizado adicionado
- [ ] URL de produção configurada
- [ ] APK assinado gerado
- [ ] Testado instalação limpa

---

## 🐛 Problemas Comuns

### "SDK location not found"
Crie arquivo `local.properties`:
```properties
sdk.dir=C\:\\Users\\SeuUsuario\\AppData\\Local\\Android\\Sdk
```

### "Gradle sync failed"
1. File → Invalidate Caches / Restart
2. Build → Clean Project
3. Tente novamente

### APK não instala
1. Desinstale versão antiga
2. Habilite "Fontes desconhecidas"
3. Verifique espaço disponível

---

## 📱 Tamanho do APK

- **Debug:** ~8-12 MB
- **Release (otimizado):** ~5-8 MB

---

## ⏱️ Tempo Estimado

- **Primeira compilação:** 15-20 minutos
- **Compilações seguintes:** 2-5 minutos
- **Instalação:** 30 segundos

---

## 🎉 Pronto!

Após compilar, você terá um APK funcional que pode ser:
- Instalado diretamente em dispositivos Android
- Distribuído via link de download
- Publicado na Google Play Store (após assinatura)

**Dúvidas?** Consulte o README.md completo.
