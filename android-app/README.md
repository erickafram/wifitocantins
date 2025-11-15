# WiFi Tocantins - Aplicativo Android

Aplicativo Android nativo para o sistema WiFi Tocantins Transporte.

## 📱 Funcionalidades

- **Para Passageiros:**
  - Visualizar página inicial
  - Comprar acesso à internet via PIX
  - Acompanhar status de pagamento
  - Navegar com internet liberada

- **Para Motoristas:**
  - Ativar vouchers de internet
  - Verificar status de vouchers
  - Acesso rápido às funcionalidades

## 🛠️ Tecnologias

- **Linguagem:** Java
- **SDK Mínimo:** Android 7.0 (API 24)
- **SDK Target:** Android 14 (API 34)
- **WebView:** Aplicativo híbrido encapsulando o site

## 📋 Pré-requisitos

Para compilar o aplicativo, você precisa:

1. **Android Studio** (versão Arctic Fox ou superior)
   - Download: https://developer.android.com/studio

2. **JDK 11 ou superior**
   - Download: https://adoptium.net/

3. **Android SDK** com:
   - Android SDK Platform 34
   - Android SDK Build-Tools 34.0.0
   - Android Emulator (opcional, para testes)

## 🚀 Como Compilar

### Opção 1: Usando Android Studio (Recomendado)

1. **Abrir o projeto:**
   ```
   File > Open > Selecione a pasta "android-app"
   ```

2. **Aguardar sincronização:**
   - O Android Studio irá baixar dependências automaticamente
   - Aguarde a mensagem "Gradle sync finished"

3. **Conectar dispositivo ou emulador:**
   - Dispositivo físico: Ative "Depuração USB" nas opções de desenvolvedor
   - Emulador: Tools > AVD Manager > Create Virtual Device

4. **Compilar e instalar:**
   ```
   Build > Build Bundle(s) / APK(s) > Build APK(s)
   ```
   
   Ou clique no botão "Run" (▶️) para instalar diretamente

5. **Localizar APK:**
   ```
   android-app/app/build/outputs/apk/debug/app-debug.apk
   ```

### Opção 2: Linha de Comando

1. **Navegar até a pasta:**
   ```bash
   cd android-app
   ```

2. **Compilar APK Debug:**
   ```bash
   # Windows
   gradlew.bat assembleDebug
   
   # Linux/Mac
   ./gradlew assembleDebug
   ```

3. **Compilar APK Release (para produção):**
   ```bash
   # Windows
   gradlew.bat assembleRelease
   
   # Linux/Mac
   ./gradlew assembleRelease
   ```

4. **Localizar APK:**
   - Debug: `app/build/outputs/apk/debug/app-debug.apk`
   - Release: `app/build/outputs/apk/release/app-release.apk`

## 🔐 Gerar APK Assinado (Produção)

Para publicar na Google Play Store, você precisa assinar o APK:

### 1. Criar Keystore

```bash
keytool -genkey -v -keystore wifi-tocantins.keystore -alias wifi-tocantins -keyalg RSA -keysize 2048 -validity 10000
```

**Importante:** Guarde a senha e o arquivo `.keystore` em local seguro!

### 2. Configurar assinatura

Edite `app/build.gradle` e adicione:

```gradle
android {
    signingConfigs {
        release {
            storeFile file("../wifi-tocantins.keystore")
            storePassword "SUA_SENHA_AQUI"
            keyAlias "wifi-tocantins"
            keyPassword "SUA_SENHA_AQUI"
        }
    }
    
    buildTypes {
        release {
            signingConfig signingConfigs.release
            // ... resto da configuração
        }
    }
}
```

### 3. Compilar APK assinado

```bash
# Windows
gradlew.bat assembleRelease

# Linux/Mac
./gradlew assembleRelease
```

## 📦 Gerar AAB (Android App Bundle)

Para publicar na Google Play Store, é recomendado usar AAB:

```bash
# Windows
gradlew.bat bundleRelease

# Linux/Mac
./gradlew bundleRelease
```

O arquivo será gerado em: `app/build/outputs/bundle/release/app-release.aab`

## 🎨 Personalização

### Alterar ícone do aplicativo

1. Substitua os arquivos em `app/src/main/res/mipmap-*/ic_launcher.png`
2. Use o Image Asset Studio do Android Studio:
   - Clique com botão direito em `res` > New > Image Asset

### Alterar cores

Edite `app/src/main/res/values/styles.xml`:

```xml
<item name="android:colorPrimary">#10B981</item>
<item name="android:colorPrimaryDark">#059669</item>
<item name="android:colorAccent">#10B981</item>
```

### Alterar URL base

Edite `MainActivity.java`, linha 18:

```java
private static final String BASE_URL = "https://www.tocantinstransportewifi.com.br";
```

## 🧪 Testar o Aplicativo

### Instalar APK no dispositivo

1. **Via USB:**
   ```bash
   adb install app/build/outputs/apk/debug/app-debug.apk
   ```

2. **Via arquivo:**
   - Copie o APK para o dispositivo
   - Abra o arquivo no gerenciador de arquivos
   - Permita instalação de fontes desconhecidas

### Testar funcionalidades

- ✅ Navegação entre páginas
- ✅ Pagamento PIX (QR Code)
- ✅ Ativação de voucher
- ✅ Links externos (WhatsApp)
- ✅ Botão voltar
- ✅ Orientação de tela

## 📱 Requisitos do Dispositivo

- **Android:** 7.0 (Nougat) ou superior
- **RAM:** Mínimo 2GB
- **Armazenamento:** 50MB livres
- **Internet:** WiFi ou dados móveis

## 🐛 Solução de Problemas

### Erro: "SDK location not found"

Crie o arquivo `local.properties` na raiz do projeto:

```properties
sdk.dir=C\:\\Users\\SeuUsuario\\AppData\\Local\\Android\\Sdk
```

### Erro de sincronização Gradle

1. File > Invalidate Caches / Restart
2. Build > Clean Project
3. Build > Rebuild Project

### APK não instala no dispositivo

1. Verifique se "Fontes desconhecidas" está habilitado
2. Desinstale versões antigas do app
3. Verifique espaço disponível

## 📄 Estrutura do Projeto

```
android-app/
├── app/
│   ├── src/
│   │   └── main/
│   │       ├── java/com/tocantinstransporte/wifi/
│   │       │   ├── MainActivity.java          # Atividade principal
│   │       │   └── SplashActivity.java        # Tela de splash
│   │       ├── res/
│   │       │   ├── layout/                    # Layouts XML
│   │       │   ├── values/                    # Strings, cores, estilos
│   │       │   ├── drawable/                  # Imagens e gradientes
│   │       │   └── mipmap-*/                  # Ícones do app
│   │       └── AndroidManifest.xml            # Configurações do app
│   ├── build.gradle                           # Configurações de build
│   └── proguard-rules.pro                     # Regras de ofuscação
├── gradle/                                    # Wrapper do Gradle
├── build.gradle                               # Build raiz
├── settings.gradle                            # Configurações do projeto
└── README.md                                  # Este arquivo
```

## 🔄 Atualizações Futuras

Para atualizar o app:

1. Incremente `versionCode` e `versionName` em `app/build.gradle`
2. Compile novo APK/AAB
3. Publique na Play Store ou distribua diretamente

## 📞 Suporte

Para dúvidas ou problemas:
- Email: suporte@tocantinstransportewifi.com.br
- WhatsApp: (63) 8496-2118

## 📝 Licença

© 2025 Tocantins Transporte WiFi. Todos os direitos reservados.
