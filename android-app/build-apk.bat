@echo off
echo ========================================
echo  WiFi Tocantins - Compilador de APK
echo ========================================
echo.

REM Definir JAVA_HOME para o JDK do Android Studio
set "JAVA_HOME=C:\Program Files\Android\Android Studio\jbr"
set "PATH=%JAVA_HOME%\bin;%PATH%"

echo [1/4] Verificando Java...
java -version
if %ERRORLEVEL% neq 0 (
    echo ERRO: Java nao encontrado!
    pause
    exit /b 1
)

echo.
echo [2/4] Limpando build anterior...
if exist "app\build" rmdir /s /q "app\build"

echo.
echo [3/4] Compilando APK Debug...
echo Isso pode demorar alguns minutos na primeira vez...
echo.

call gradlew.bat assembleDebug

if %ERRORLEVEL% neq 0 (
    echo.
    echo ERRO na compilacao!
    pause
    exit /b 1
)

echo.
echo [4/4] APK gerado com sucesso!
echo.
echo ========================================
echo  APK LOCALIZADO EM:
echo ========================================
echo %CD%\app\build\outputs\apk\debug\app-debug.apk
echo.

REM Abrir pasta do APK
start "" "%CD%\app\build\outputs\apk\debug"

echo.
echo Pressione qualquer tecla para sair...
pause >nul
