package com.tocantinstransporte.wifi;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.webkit.JavascriptInterface;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;
import androidx.core.app.ActivityCompat;
import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;

public class MainActivity extends Activity {
    
    private WebView webView;
    private ValueCallback<Uri[]> uploadMessage;
    private static final int REQUEST_SELECT_FILE = 100;
    private static final int REQUEST_NOTIFICATION_PERMISSION = 101;
    private static final String BASE_URL = "https://www.tocantinstransportewifi.com.br";
    private static final String CHANNEL_ID = "wifi_connection_channel";
    private NotificationManager notificationManager;
    
    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        
        webView = findViewById(R.id.webview);
        
        // Inicialmente invisível para evitar flash branco
        webView.setVisibility(android.view.View.INVISIBLE);
        
        // Inicializar notificações
        createNotificationChannel();
        
        // Solicitar permissão de notificação (Android 13+)
        requestNotificationPermission();
        
        // Configurações do WebView
        WebSettings webSettings = webView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webSettings.setDomStorageEnabled(true);
        webSettings.setDatabaseEnabled(true);
        webSettings.setAllowFileAccess(true);
        webSettings.setAllowContentAccess(true);
        webSettings.setLoadWithOverviewMode(true);
        webSettings.setUseWideViewPort(true);
        webSettings.setBuiltInZoomControls(false);
        webSettings.setDisplayZoomControls(false);
        webSettings.setSupportZoom(false);
        webSettings.setDefaultTextEncodingName("utf-8");
        webSettings.setCacheMode(WebSettings.LOAD_DEFAULT);
        webSettings.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        
        // Adicionar interface JavaScript para comunicação
        webView.addJavascriptInterface(new WebAppInterface(this), "AndroidApp");
        
        // WebViewClient para controlar navegação
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                // Abrir links externos no navegador
                if (url.startsWith("whatsapp://") || 
                    url.startsWith("tel:") || 
                    url.startsWith("mailto:") ||
                    url.contains("wa.me")) {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                    startActivity(intent);
                    return true;
                }
                
                // Manter navegação dentro do domínio no app
                if (url.contains("tocantinstransportewifi.com.br")) {
                    return false;
                }
                
                // Links externos abrem no navegador
                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                startActivity(intent);
                return true;
            }
            
            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                
                // Tornar WebView visível após carregamento
                webView.setVisibility(android.view.View.VISIBLE);
                
                // Injetar CSS e JavaScript para melhorar experiência mobile
                injectCustomCSS();
                injectNotificationScript();
                
                android.util.Log.d("MainActivity", "Página carregada: " + url);
            }
        });
        
        // WebChromeClient para upload de arquivos, câmera e console
        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public boolean onShowFileChooser(WebView webView, ValueCallback<Uri[]> filePathCallback,
                                           FileChooserParams fileChooserParams) {
                if (uploadMessage != null) {
                    uploadMessage.onReceiveValue(null);
                    uploadMessage = null;
                }
                
                uploadMessage = filePathCallback;
                
                Intent intent = fileChooserParams.createIntent();
                try {
                    startActivityForResult(intent, REQUEST_SELECT_FILE);
                } catch (Exception e) {
                    uploadMessage = null;
                    Toast.makeText(MainActivity.this, "Não foi possível abrir o seletor de arquivos", 
                                 Toast.LENGTH_LONG).show();
                    return false;
                }
                return true;
            }
            
            @Override
            public boolean onConsoleMessage(android.webkit.ConsoleMessage consoleMessage) {
                android.util.Log.d("WebView", consoleMessage.message() + " -- From line " +
                        consoleMessage.lineNumber() + " of " + consoleMessage.sourceId());
                return true;
            }
        });
        
        // Carregar URL inicial
        String initialUrl = BASE_URL;
        Intent intent = getIntent();
        if (intent != null && intent.getData() != null) {
            initialUrl = intent.getData().toString();
        }
        
        webView.loadUrl(initialUrl);
    }
    
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_SELECT_FILE) {
            if (uploadMessage == null) return;
            
            Uri[] results = null;
            if (resultCode == Activity.RESULT_OK && data != null) {
                String dataString = data.getDataString();
                if (dataString != null) {
                    results = new Uri[]{Uri.parse(dataString)};
                }
            }
            
            uploadMessage.onReceiveValue(results);
            uploadMessage = null;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }
    
    @Override
    public void onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }
    
    private void injectCustomCSS() {
        // CSS customizado para melhorar experiência mobile
        String css = "javascript:(function() {" +
                "var style = document.createElement('style');" +
                "style.innerHTML = '" +
                "* { -webkit-tap-highlight-color: transparent; }" +
                "body { -webkit-touch-callout: none; -webkit-user-select: none; }" +
                "';" +
                "document.head.appendChild(style);" +
                "})()";
        webView.loadUrl(css);
    }
    
    private void injectNotificationScript() {
        // Script para detectar pagamento/voucher bem-sucedido
        String script = "javascript:(function() {" +
                "if (window.AndroidApp) {" +
                "  console.log('AndroidApp interface detectada!');" +
                "  " +
                "  window.notifyConnection = function(time) {" +
                "    console.log('notifyConnection chamada com tempo:', time);" +
                "    AndroidApp.showConnectionNotification(time || '');" +
                "  };" +
                "  " +
                "  var observer = new MutationObserver(function(mutations) {" +
                "    mutations.forEach(function(mutation) {" +
                "      var text = mutation.target.textContent || '';" +
                "      var lowerText = text.toLowerCase();" +
                "      if (lowerText.includes('conectado') || " +
                "          lowerText.includes('ativado com sucesso') || " +
                "          lowerText.includes('pagamento confirmado') || " +
                "          lowerText.includes('internet liberada') || " +
                "          lowerText.includes('voucher ativado')) {" +
                "        console.log('Texto detectado:', text);" +
                "        var timeMatch = text.match(/(\\d+)\\s*(hora|horas|minuto|minutos|dia|dias)/i);" +
                "        var timeText = '';" +
                "        if (timeMatch) {" +
                "          timeText = timeMatch[0];" +
                "        }" +
                "        AndroidApp.showConnectionNotification(timeText);" +
                "      }" +
                "    });" +
                "  });" +
                "  observer.observe(document.body, { childList: true, subtree: true, characterData: true });" +
                "  " +
                "  console.log('Observer de notificações ativado!');" +
                "}" +
                "})()";
        webView.loadUrl(script);
    }
    
    private void requestNotificationPermission() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) 
                    != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, 
                    new String[]{Manifest.permission.POST_NOTIFICATIONS}, 
                    REQUEST_NOTIFICATION_PERMISSION);
            }
        }
    }
    
    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == REQUEST_NOTIFICATION_PERMISSION) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                Toast.makeText(this, "✅ Notificações ativadas!", Toast.LENGTH_SHORT).show();
            } else {
                Toast.makeText(this, "⚠️ Ative as notificações para receber alertas de conexão", Toast.LENGTH_LONG).show();
            }
        }
    }
    
    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            CharSequence name = "Conexão WiFi";
            String description = "Notificações de conexão WiFi";
            int importance = NotificationManager.IMPORTANCE_HIGH;
            NotificationChannel channel = new NotificationChannel(CHANNEL_ID, name, importance);
            channel.setDescription(description);
            channel.enableLights(true);
            channel.setLightColor(Color.GREEN);
            channel.enableVibration(true);
            
            notificationManager = getSystemService(NotificationManager.class);
            notificationManager.createNotificationChannel(channel);
        }
    }
    
    // Interface JavaScript para comunicação com o WebView
    public class WebAppInterface {
        Context mContext;
        
        WebAppInterface(Context c) {
            mContext = c;
        }
        
        @JavascriptInterface
        public void showConnectionNotification() {
            showConnectionNotification("");
        }
        
        @JavascriptInterface
        public void showConnectionNotification(String timeRemaining) {
            final String time = timeRemaining;
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    showNotification(time);
                    String message = "✅ Conectado! Internet liberada";
                    if (time != null && !time.isEmpty()) {
                        message += "\n⏱️ Tempo: " + time;
                    }
                    Toast.makeText(MainActivity.this, message, Toast.LENGTH_LONG).show();
                }
            });
        }
    }
    
    private void showNotification(String timeRemaining) {
        // Log para debug
        android.util.Log.d("MainActivity", "showNotification chamada com tempo: " + timeRemaining);
        
        String contentText = "✅ Conectado! Sua internet está liberada";
        String bigText = contentText;
        
        if (timeRemaining != null && !timeRemaining.isEmpty()) {
            contentText = "✅ Conectado! Tempo: " + timeRemaining;
            bigText = "✅ Você está conectado!\n\n⏱️ Tempo disponível: " + timeRemaining + "\n\n🌐 Aproveite sua navegação!";
        } else {
            // Tentar obter tempo da sessão se não foi passado
            bigText = "✅ Você está conectado!\n\n🌐 Sua internet está liberada!\n\nAproveite sua navegação!";
        }
        
        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(android.R.drawable.stat_sys_upload_done)
                .setContentTitle("WiFi Tocantins Transporte")
                .setContentText(contentText)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(bigText))
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setAutoCancel(true)
                .setVibrate(new long[]{0, 500, 200, 500})
                .setLights(Color.GREEN, 1000, 1000)
                .setDefaults(NotificationCompat.DEFAULT_SOUND);
        
        if (notificationManager == null) {
            notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        }
        
        if (notificationManager != null) {
            notificationManager.notify(1, builder.build());
            android.util.Log.d("MainActivity", "Notificação enviada!");
        }
    }
    
    @Override
    protected void onResume() {
        super.onResume();
        webView.onResume();
    }
    
    @Override
    protected void onPause() {
        super.onPause();
        webView.onPause();
    }
    
    @Override
    protected void onDestroy() {
        if (webView != null) {
            webView.destroy();
        }
        super.onDestroy();
    }
}
