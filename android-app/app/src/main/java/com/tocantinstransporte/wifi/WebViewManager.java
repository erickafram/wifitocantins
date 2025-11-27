package com.tocantinstransporte.wifi;

import android.webkit.WebView;

/**
 * Gerenciador singleton para compartilhar WebView entre Activities
 */
public class WebViewManager {
    private static WebViewManager instance;
    private WebView sharedWebView;
    private boolean isReady = false;
    private String finalUrl = null;

    private WebViewManager() {
    }

    public static synchronized WebViewManager getInstance() {
        if (instance == null) {
            instance = new WebViewManager();
        }
        return instance;
    }

    public void setWebView(WebView webView) {
        this.sharedWebView = webView;
    }

    public WebView getWebView() {
        return sharedWebView;
    }

    public void setReady(boolean ready) {
        this.isReady = ready;
    }

    public boolean isReady() {
        return isReady;
    }

    public void setFinalUrl(String url) {
        this.finalUrl = url;
    }

    public String getFinalUrl() {
        return finalUrl;
    }

    public void clear() {
        if (sharedWebView != null) {
            sharedWebView.destroy();
            sharedWebView = null;
        }
        isReady = false;
        finalUrl = null;
    }
}
