package com.tocantinstransporte.wifi;

import android.animation.ObjectAnimator;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.view.animation.AccelerateDecelerateInterpolator;
import android.view.animation.OvershootInterpolator;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import androidx.appcompat.app.AppCompatActivity;

public class SplashActivity extends AppCompatActivity {

    private static final int SPLASH_MIN_LENGTH = 12000; // 12 segundos mínimo (tempo para MikroTik + Laravel)
    private static final int SPLASH_MAX_LENGTH = 15000; // 15 segundos máximo
    private WebView hiddenWebView;
    private boolean webViewReady = false;
    private long startTime;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        startTime = System.currentTimeMillis();

        // Iniciar animações
        animateIcon();
        animateTexts();
        animateProgressBar();

        // Pré-carregar site em background
        preloadWebsite();

        // Verificar periodicamente se pode avançar
        checkAndProceed();
    }

    private void checkAndProceed() {
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                long elapsedTime = System.currentTimeMillis() - startTime;

                // Só avança se:
                // 1. Passou o tempo mínimo (12s) E site está pronto
                // OU
                // 2. Passou o tempo máximo (15s) independente do site
                if ((elapsedTime >= SPLASH_MIN_LENGTH && webViewReady) ||
                    elapsedTime >= SPLASH_MAX_LENGTH) {

                    android.util.Log.d("SplashActivity", "Avançando após " + elapsedTime + "ms, site pronto: " + webViewReady);

                    // Fazer fade out de todos os elementos antes de transição
                    fadeOutAndProceed();
                } else {
                    // Verificar novamente em 500ms
                    checkAndProceed();
                }
            }
        }, 500);
    }

    private void fadeOutAndProceed() {
        android.util.Log.d("SplashActivity", "Iniciando transição para MainActivity");

        // Passar para MainActivity que vai carregar em background
        Intent mainIntent = new Intent(SplashActivity.this, MainActivity.class);
        mainIntent.putExtra("preloaded", webViewReady);
        mainIntent.putExtra("keep_splash", true);
        startActivity(mainIntent);

        // Sem animação de transição
        overridePendingTransition(0, 0);

        // Finalizar splash após 3 segundos (tempo para MainActivity carregar)
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                finish();
            }
        }, 3000);
    }

    private void preloadWebsite() {
        // Criar WebView invisível para pré-carregar o site
        hiddenWebView = new WebView(this);
        hiddenWebView.getSettings().setJavaScriptEnabled(true);
        hiddenWebView.getSettings().setDomStorageEnabled(true);
        hiddenWebView.getSettings().setLoadWithOverviewMode(true);
        hiddenWebView.getSettings().setUseWideViewPort(true);

        hiddenWebView.setWebViewClient(new WebViewClient() {
            private int redirectCount = 0;
            private String lastUrl = "";
            private Handler stabilityHandler = new Handler();

            @Override
            public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                android.util.Log.d("SplashActivity", "[" + redirectCount + "] Carregando: " + url);

                // Contar redirecionamentos
                if (!url.equals(lastUrl)) {
                    redirectCount++;
                    lastUrl = url;
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                android.util.Log.d("SplashActivity", "[" + redirectCount + "] Página carregada: " + url);

                // Cancelar verificações anteriores
                stabilityHandler.removeCallbacksAndMessages(null);

                // Aguardar 2 segundos para garantir que não há mais redirecionamentos
                // Tempo maior para garantir que o MikroTik complete o processo
                stabilityHandler.postDelayed(new Runnable() {
                    @Override
                    public void run() {
                        String currentUrl = view.getUrl();
                        if (currentUrl != null && currentUrl.equals(lastUrl)) {
                            // URL estável, redirecionamentos completos
                            webViewReady = true;
                            android.util.Log.d("SplashActivity",
                                "✅ Site pronto após " + redirectCount + " redirecionamentos: " + currentUrl);
                        }
                    }
                }, 2000); // Aguardar 2s para confirmar estabilidade
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                android.util.Log.d("SplashActivity", "Redirecionando para: " + url);
                // Permitir TODOS os redirecionamentos, incluindo login.tocantinswifi.local
                return false;
            }

            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                super.onReceivedError(view, errorCode, description, failingUrl);
                android.util.Log.e("SplashActivity", "Erro ao carregar " + failingUrl + ": " + description);
            }
        });

        // Carregar site em background
        android.util.Log.d("SplashActivity", "Iniciando pré-carregamento do site...");
        hiddenWebView.loadUrl("https://www.tocantinstransportewifi.com.br");
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();

        if (hiddenWebView != null) {
            hiddenWebView.destroy();
        }
    }

    private void animateIcon() {
        View iconText = findViewById(R.id.icon_text);
        if (iconText == null) return;

        // Animação de escala com bounce
        ObjectAnimator scaleX = ObjectAnimator.ofFloat(iconText, "scaleX", 0f, 1.1f, 1f);
        ObjectAnimator scaleY = ObjectAnimator.ofFloat(iconText, "scaleY", 0f, 1.1f, 1f);
        scaleX.setDuration(1200);
        scaleY.setDuration(1200);
        scaleX.setInterpolator(new OvershootInterpolator());
        scaleY.setInterpolator(new OvershootInterpolator());

        // Animação de rotação suave
        ObjectAnimator rotation = ObjectAnimator.ofFloat(iconText, "rotation", -10f, 10f, -5f, 5f, 0f);
        rotation.setDuration(1500);
        rotation.setStartDelay(800);
        rotation.setInterpolator(new AccelerateDecelerateInterpolator());

        // Animação de elevação (pulsação)
        ObjectAnimator elevation = ObjectAnimator.ofFloat(iconText, "translationY", 0f, -15f, 0f);
        elevation.setDuration(1000);
        elevation.setStartDelay(1000);
        elevation.setRepeatCount(ObjectAnimator.INFINITE);
        elevation.setRepeatMode(ObjectAnimator.REVERSE);

        scaleX.start();
        scaleY.start();
        rotation.start();
        elevation.start();
    }

    private void animateTexts() {
        // Animar "Bem-vindo ao"
        View welcomeText = findViewById(R.id.welcome_text);
        if (welcomeText != null) {
            // Fade in inicial
            ObjectAnimator alpha1 = ObjectAnimator.ofFloat(welcomeText, "alpha", 0f, 1f);
            ObjectAnimator translateY1 = ObjectAnimator.ofFloat(welcomeText, "translationY", 30f, 0f);
            alpha1.setDuration(800);
            translateY1.setDuration(800);
            alpha1.setStartDelay(800);
            translateY1.setStartDelay(800);
            alpha1.start();
            translateY1.start();

            // Pulsação suave contínua
            ObjectAnimator pulse1 = ObjectAnimator.ofFloat(welcomeText, "alpha", 1f, 0.7f, 1f);
            pulse1.setDuration(2500);
            pulse1.setStartDelay(2000);
            pulse1.setRepeatCount(ObjectAnimator.INFINITE);
            pulse1.setRepeatMode(ObjectAnimator.RESTART);
            pulse1.start();
        }

        // Animar nome do app
        View appNameText = findViewById(R.id.app_name_text);
        if (appNameText != null) {
            // Fade in inicial
            ObjectAnimator alpha2 = ObjectAnimator.ofFloat(appNameText, "alpha", 0f, 1f);
            ObjectAnimator translateY2 = ObjectAnimator.ofFloat(appNameText, "translationY", 30f, 0f);
            alpha2.setDuration(800);
            translateY2.setDuration(800);
            alpha2.setStartDelay(1100);
            translateY2.setStartDelay(1100);
            alpha2.start();
            translateY2.start();

            // Pulsação suave contínua (levemente defasada)
            ObjectAnimator pulse2 = ObjectAnimator.ofFloat(appNameText, "alpha", 1f, 0.8f, 1f);
            pulse2.setDuration(2500);
            pulse2.setStartDelay(2300);
            pulse2.setRepeatCount(ObjectAnimator.INFINITE);
            pulse2.setRepeatMode(ObjectAnimator.RESTART);
            pulse2.start();
        }

        // Animar linha decorativa
        View decorativeLine = findViewById(R.id.decorative_line);
        if (decorativeLine != null) {
            // Fade in inicial
            ObjectAnimator alpha3 = ObjectAnimator.ofFloat(decorativeLine, "alpha", 0f, 1f);
            ObjectAnimator scaleX = ObjectAnimator.ofFloat(decorativeLine, "scaleX", 0f, 1f);
            alpha3.setDuration(600);
            scaleX.setDuration(600);
            alpha3.setStartDelay(1400);
            scaleX.setStartDelay(1400);
            alpha3.start();
            scaleX.start();

            // Pulsação suave contínua
            ObjectAnimator pulse3 = ObjectAnimator.ofFloat(decorativeLine, "alpha", 1f, 0.6f, 1f);
            pulse3.setDuration(2000);
            pulse3.setStartDelay(2500);
            pulse3.setRepeatCount(ObjectAnimator.INFINITE);
            pulse3.setRepeatMode(ObjectAnimator.RESTART);
            pulse3.start();
        }
    }

    private void animateProgressBar() {
        View progressBar = findViewById(R.id.progress_bar);
        if (progressBar != null) {
            ObjectAnimator alpha = ObjectAnimator.ofFloat(progressBar, "alpha", 0f, 1f);
            alpha.setDuration(500);
            alpha.setStartDelay(1800);
            alpha.start();
        }

        // Animar texto de carregamento com pulsação mais visível
        View loadingText = findViewById(R.id.loading_text);
        if (loadingText != null) {
            // Fade in inicial
            ObjectAnimator fadeIn = ObjectAnimator.ofFloat(loadingText, "alpha", 0f, 1f);
            fadeIn.setDuration(500);
            fadeIn.setStartDelay(2000);
            fadeIn.start();

            // Pulsação contínua
            ObjectAnimator pulse = ObjectAnimator.ofFloat(loadingText, "alpha", 1f, 0.4f, 1f);
            pulse.setDuration(1500);
            pulse.setStartDelay(2500);
            pulse.setRepeatCount(ObjectAnimator.INFINITE);
            pulse.setRepeatMode(ObjectAnimator.RESTART);
            pulse.start();
        }
    }
}
