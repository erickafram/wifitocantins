package com.tocantinstransporte.wifi;

import android.animation.ObjectAnimator;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.view.animation.AccelerateDecelerateInterpolator;
import android.widget.TextView;

public class SplashActivity extends Activity {
    
    private static final int SPLASH_DISPLAY_LENGTH = 2000; // 2 segundos
    
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);
        
        // Animar o ícone
        View iconText = findViewById(R.id.icon_text);
        if (iconText != null) {
            // Animação de escala (zoom in)
            ObjectAnimator scaleX = ObjectAnimator.ofFloat(iconText, "scaleX", 0.5f, 1f);
            ObjectAnimator scaleY = ObjectAnimator.ofFloat(iconText, "scaleY", 0.5f, 1f);
            scaleX.setDuration(800);
            scaleY.setDuration(800);
            scaleX.setInterpolator(new AccelerateDecelerateInterpolator());
            scaleY.setInterpolator(new AccelerateDecelerateInterpolator());
            scaleX.start();
            scaleY.start();
            
            // Animação de rotação
            ObjectAnimator rotation = ObjectAnimator.ofFloat(iconText, "rotation", 0f, 360f);
            rotation.setDuration(1000);
            rotation.setStartDelay(200);
            rotation.start();
        }
        
        // Animar texto de boas-vindas
        TextView welcomeText = findViewById(R.id.welcome_text);
        if (welcomeText != null) {
            ObjectAnimator alpha = ObjectAnimator.ofFloat(welcomeText, "alpha", 0f, 1f);
            alpha.setDuration(1000);
            alpha.setStartDelay(500);
            alpha.start();
        }
        
        // Aguardar 3 segundos antes de abrir a MainActivity
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                Intent mainIntent = new Intent(SplashActivity.this, MainActivity.class);
                startActivity(mainIntent);
                overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
                finish();
            }
        }, SPLASH_DISPLAY_LENGTH);
    }
}
