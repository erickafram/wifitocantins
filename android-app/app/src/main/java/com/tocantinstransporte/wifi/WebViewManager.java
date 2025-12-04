package com.tocantinstransporte.wifi;

import android.webkit.WebView;

/**
 * Gerenciador singleton para compartilhar WebView entre Activities
 * Também armazena informações de WiFi (MAC/IP) capturadas via Network Binding
 */
public class WebViewManager {
    private static WebViewManager instance;
    private WebView sharedWebView;
    private boolean isReady = false;
    private String finalUrl = null;
    
    // Informações de WiFi capturadas via Network Binding
    private String wifiMac = null;
    private String wifiIp = null;

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
        wifiMac = null;
        wifiIp = null;
    }
    
    // Métodos para WiFi MAC/IP
    public void setWifiMac(String mac) {
        this.wifiMac = mac;
    }
    
    public String getWifiMac() {
        return wifiMac;
    }
    
    public void setWifiIp(String ip) {
        this.wifiIp = ip;
    }
    
    public String getWifiIp() {
        return wifiIp;
    }
    
    public boolean hasWifiInfo() {
        return wifiIp != null && !wifiIp.isEmpty();
    }
}
