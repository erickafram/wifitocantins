package com.tocantinstransporte.wifi;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class MainActivity extends AppCompatActivity {

    private WebView webView;
    private ValueCallback<Uri[]> uploadMessage;
    private static final int REQUEST_SELECT_FILE = 100;
    private static final String BASE_URL = "https://www.tocantinstransportewifi.com.br";

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);

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
                // Injetar CSS para melhorar experiência mobile
                injectCustomCSS();
            }
        });

        // WebChromeClient para upload de arquivos e câmera
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
            if (resultCode == AppCompatActivity.RESULT_OK && data != null) {
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
