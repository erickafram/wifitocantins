package com.tocantinstransporte.wifi;

import android.animation.AnimatorSet;
import android.animation.ObjectAnimator;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.view.animation.AccelerateDecelerateInterpolator;
import android.view.animation.BounceInterpolator;
import android.view.animation.OvershootInterpolator;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class SplashActivity extends Activity {
    
    private static final int SPLASH_DISPLAY_LENGTH = 6000; // 6 segundos - tempo para redirecionamento
    private WebView hiddenWebView;
    private boolean webViewReady = false;
    
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);
        
        // Iniciar animações
        animateIcon();
        animateTexts();
        animateProgressBar();
        
        // Pré-carregar site em background
        preloadWebsite();
        
        // Aguardar antes de abrir a MainActivity
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                Intent mainIntent = new Intent(SplashActivity.this, MainActivity.class);
                mainIntent.putExtra("preloaded", webViewReady);
                startActivity(mainIntent);
                overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
                finish();
            }
        }, SPLASH_DISPLAY_LENGTH);
    }
    
    private void preloadWebsite() {
        // Criar WebView invisível para pré-carregar o site
        hiddenWebView = new WebView(this);
        hiddenWebView.getSettings().setJavaScriptEnabled(true);
        hiddenWebView.getSettings().setDomStorageEnabled(true);
        
        hiddenWebView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                // Site carregado e redirecionamentos completos
                webViewReady = true;
                android.util.Log.d("SplashActivity", "Site pré-carregado: " + url);
            }
        });
        
        // Carregar site em background
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
            ObjectAnimator alpha1 = ObjectAnimator.ofFloat(welcomeText, "alpha", 0f, 1f);
            ObjectAnimator translateY1 = ObjectAnimator.ofFloat(welcomeText, "translationY", 30f, 0f);
            alpha1.setDuration(800);
            translateY1.setDuration(800);
            alpha1.setStartDelay(800);
            translateY1.setStartDelay(800);
            alpha1.start();
            translateY1.start();
        }
        
        // Animar nome do app
        View appNameText = findViewById(R.id.app_name_text);
        if (appNameText != null) {
            ObjectAnimator alpha2 = ObjectAnimator.ofFloat(appNameText, "alpha", 0f, 1f);
            ObjectAnimator translateY2 = ObjectAnimator.ofFloat(appNameText, "translationY", 30f, 0f);
            alpha2.setDuration(800);
            translateY2.setDuration(800);
            alpha2.setStartDelay(1100);
            translateY2.setStartDelay(1100);
            alpha2.start();
            translateY2.start();
        }
        
        // Animar linha decorativa
        View decorativeLine = findViewById(R.id.decorative_line);
        if (decorativeLine != null) {
            ObjectAnimator alpha3 = ObjectAnimator.ofFloat(decorativeLine, "alpha", 0f, 1f);
            ObjectAnimator scaleX = ObjectAnimator.ofFloat(decorativeLine, "scaleX", 0f, 1f);
            alpha3.setDuration(600);
            scaleX.setDuration(600);
            alpha3.setStartDelay(1400);
            scaleX.setStartDelay(1400);
            alpha3.start();
            scaleX.start();
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
        
        // Animar texto de carregamento
        View loadingText = findViewById(R.id.loading_text);
        if (loadingText != null) {
            ObjectAnimator alpha = ObjectAnimator.ofFloat(loadingText, "alpha", 0f, 0.8f, 0.5f, 0.8f);
            alpha.setDuration(2000);
            alpha.setStartDelay(2000);
            alpha.setRepeatCount(ObjectAnimator.INFINITE);
            alpha.setRepeatMode(ObjectAnimator.REVERSE);
            alpha.start();
        }
    }
}
