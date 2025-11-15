# 📱 Como Compilar o APK no Android Studio

## ✅ Passo a Passo Simples

### 1️⃣ Abrir o Projeto no Android Studio

1. Abra o **Android Studio**
2. Clique em **File** → **Open**
3. Navegue até: `C:\wamp64\www\wifitocantins\android-app`
4. Clique em **OK**

### 2️⃣ Aguardar Sincronização

- O Android Studio irá sincronizar o projeto automaticamente
- Você verá uma barra de progresso no canto inferior direito
- **Aguarde até aparecer "Gradle sync finished"** (pode demorar 5-10 minutos na primeira vez)
- Se aparecer algum erro de SDK, clique em "Install missing SDK" e aguarde

### 3️⃣ Gerar o APK

**Opção A - Menu Build:**
1. Clique em **Build** no menu superior
2. Selecione **Build Bundle(s) / APK(s)**
3. Clique em **Build APK(s)**
4. Aguarde a compilação (2-5 minutos)
5. Quando aparecer a notificação "APK(s) generated successfully", clique em **locate**

**Opção B - Atalho:**
1. Pressione `Ctrl + Shift + A`
2. Digite "Build APK"
3. Selecione "Build > Build Bundle(s) / APK(s) > Build APK(s)"
4. Aguarde

### 4️⃣ Localizar o APK

O APK estará em:
```
C:\wamp64\www\wifitocantins\android-app\app\build\outputs\apk\debug\app-debug.apk
```

---

## 🔧 Resolver Problemas Comuns

### Erro: "SDK location not found"

1. Abra o arquivo `local.properties` (se não existir, crie)
2. Adicione a linha:
   ```
   sdk.dir=C\:\\Users\\Erick\\AppData\\Local\\Android\\Sdk
   ```
3. Salve e clique em **File** → **Sync Project with Gradle Files**

### Erro: "Gradle sync failed"

1. Clique em **File** → **Invalidate Caches / Restart**
2. Selecione **Invalidate and Restart**
3. Aguarde o Android Studio reiniciar
4. Tente novamente

### Erro: "Android SDK is missing"

1. Clique em **Tools** → **SDK Manager**
2. Na aba **SDK Platforms**, marque:
   - Android 14.0 (API 34)
   - Android 7.0 (API 24)
3. Na aba **SDK Tools**, marque:
   - Android SDK Build-Tools 34
   - Android SDK Platform-Tools
4. Clique em **Apply** e aguarde o download

---

## 📦 Instalar o APK no Celular

### Via Cabo USB:

1. Conecte o celular no computador via USB
2. No celular, ative **Depuração USB**:
   - Configurações → Sobre o telefone
   - Toque 7 vezes em "Número da versão"
   - Volte e entre em "Opções do desenvolvedor"
   - Ative "Depuração USB"
3. No Android Studio, clique no botão ▶️ (Run)
4. Selecione seu dispositivo
5. O app será instalado automaticamente

### Via Arquivo APK:

1. Copie o arquivo `app-debug.apk` para o celular
2. No celular, abra o gerenciador de arquivos
3. Localize o arquivo `app-debug.apk`
4. Toque no arquivo
5. Se aparecer aviso de "Fonte desconhecida", permita a instalação
6. Toque em **Instalar**

---

## 🎯 Checklist

- [ ] Android Studio aberto
- [ ] Projeto sincronizado (sem erros)
- [ ] SDK instalado (API 34)
- [ ] Build executado com sucesso
- [ ] APK localizado
- [ ] APK instalado no celular
- [ ] App funcionando

---

## ⏱️ Tempo Estimado

- **Sincronização inicial:** 5-10 minutos
- **Compilação:** 2-5 minutos
- **Instalação:** 30 segundos
- **Total:** ~10-15 minutos

---

## 📞 Dúvidas?

Se tiver algum problema, verifique:
1. Java está instalado? (Android Studio já inclui)
2. SDK está instalado? (Tools → SDK Manager)
3. Internet está funcionando? (para baixar dependências)

**Pronto! Seu APK estará funcionando! 🎉**
