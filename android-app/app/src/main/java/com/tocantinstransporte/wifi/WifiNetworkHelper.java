package com.tocantinstransporte.wifi;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkRequest;
import android.net.wifi.WifiInfo;
import android.net.wifi.WifiManager;
import android.os.Build;
import android.util.Log;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.InetAddress;
import java.net.URL;

/**
 * Helper para forçar requisições pelo WiFi mesmo com dados móveis ativos.
 * Captura MAC e IP do dispositivo na rede WiFi do MikroTik.
 */
public class WifiNetworkHelper {

    private static final String TAG = "WifiNetworkHelper";
    private static final String HOTSPOT_SSID = "TocantinsTransporteWiFi";
    private static final String MIKROTIK_GATEWAY = "10.5.50.1";
    
    private Context context;
    private ConnectivityManager connectivityManager;
    private WifiManager wifiManager;
    private Network wifiNetwork;
    private WifiConnectionCallback callback;

    public interface WifiConnectionCallback {
        void onWifiConnected(String macAddress, String ipAddress);
        void onWifiNotConnected(String reason);
        void onError(String error);
    }

    public WifiNetworkHelper(Context context) {
        this.context = context;
        this.connectivityManager = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
        this.wifiManager = (WifiManager) context.getApplicationContext().getSystemService(Context.WIFI_SERVICE);
    }

    /**
     * Verifica se está conectado ao WiFi do ônibus e captura MAC/IP
     */
    public void checkWifiAndGetInfo(WifiConnectionCallback callback) {
        this.callback = callback;

        if (!isWifiEnabled()) {
            callback.onWifiNotConnected("WiFi está desativado");
            return;
        }

        if (!isConnectedToHotspot()) {
            callback.onWifiNotConnected("Não está conectado ao WiFi " + HOTSPOT_SSID);
            return;
        }

        // Forçar uso do WiFi para requisições
        bindToWifiNetwork();
    }

    /**
     * Verifica se o WiFi está ativado
     */
    private boolean isWifiEnabled() {
        return wifiManager != null && wifiManager.isWifiEnabled();
    }

    /**
     * Verifica se está conectado ao hotspot do ônibus
     */
    private boolean isConnectedToHotspot() {
        if (wifiManager == null) return false;

        WifiInfo wifiInfo = wifiManager.getConnectionInfo();
        if (wifiInfo == null) return false;

        String ssid = wifiInfo.getSSID();
        if (ssid == null) return false;

        // Remove aspas do SSID
        ssid = ssid.replace("\"", "");
        
        Log.d(TAG, "SSID conectado: " + ssid);
        
        // Verifica se é o WiFi do ônibus (2.4GHz ou 5GHz)
        return ssid.contains("TocantinsTransporte") || 
               ssid.equals(HOTSPOT_SSID) ||
               ssid.equals(HOTSPOT_SSID + "-5G");
    }

    /**
     * Força o binding da rede WiFi para todas as requisições
     */
    private void bindToWifiNetwork() {
        NetworkRequest.Builder builder = new NetworkRequest.Builder();
        builder.addTransportType(NetworkCapabilities.TRANSPORT_WIFI);

        connectivityManager.requestNetwork(builder.build(), new ConnectivityManager.NetworkCallback() {
            @Override
            public void onAvailable(Network network) {
                Log.d(TAG, "Rede WiFi disponível, fazendo binding...");
                wifiNetwork = network;
                
                // Bind do processo para usar WiFi
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    connectivityManager.bindProcessToNetwork(network);
                } else {
                    ConnectivityManager.setProcessDefaultNetwork(network);
                }

                // Agora captura MAC e IP via WiFi
                captureNetworkInfo();
            }

            @Override
            public void onLost(Network network) {
                Log.d(TAG, "Conexão WiFi perdida");
                wifiNetwork = null;
                if (callback != null) {
                    callback.onWifiNotConnected("Conexão WiFi perdida");
                }
            }

            @Override
            public void onUnavailable() {
                Log.d(TAG, "Rede WiFi indisponível");
                if (callback != null) {
                    callback.onWifiNotConnected("Rede WiFi indisponível");
                }
            }
        });
    }

    /**
     * Captura informações de rede (MAC e IP) do dispositivo
     */
    private void captureNetworkInfo() {
        new Thread(() -> {
            try {
                String macAddress = getMacAddress();
                String ipAddress = getWifiIpAddress();

                Log.d(TAG, "MAC capturado: " + macAddress);
                Log.d(TAG, "IP capturado: " + ipAddress);

                // Verificar se o IP está na faixa do hotspot (10.5.50.x)
                if (ipAddress != null && ipAddress.startsWith("10.5.50.")) {
                    if (callback != null) {
                        callback.onWifiConnected(macAddress, ipAddress);
                    }
                } else {
                    // Tentar obter do MikroTik diretamente
                    getInfoFromMikrotik();
                }

            } catch (Exception e) {
                Log.e(TAG, "Erro ao capturar info de rede: " + e.getMessage());
                if (callback != null) {
                    callback.onError("Erro ao capturar informações: " + e.getMessage());
                }
            }
        }).start();
    }

    /**
     * Obtém o endereço MAC do dispositivo
     * Android 6+ não permite obter MAC real, então tentamos alternativas
     */
    private String getMacAddress() {
        try {
            // Método 1: WifiInfo (funciona em Android < 6)
            WifiInfo wifiInfo = wifiManager.getConnectionInfo();
            if (wifiInfo != null) {
                String mac = wifiInfo.getMacAddress();
                
                // Android 6+ retorna "02:00:00:00:00:00" por privacidade
                if (mac != null && !mac.equals("02:00:00:00:00:00")) {
                    Log.d(TAG, "MAC obtido via WifiInfo: " + mac);
                    return mac.toUpperCase();
                }
            }
            
            // Método 2: Tentar via NetworkInterface (pode funcionar em alguns dispositivos)
            try {
                java.util.Enumeration<java.net.NetworkInterface> interfaces = java.net.NetworkInterface.getNetworkInterfaces();
                while (interfaces.hasMoreElements()) {
                    java.net.NetworkInterface networkInterface = interfaces.nextElement();
                    
                    // Procurar interface WiFi (wlan0, wlan1, etc)
                    String name = networkInterface.getName().toLowerCase();
                    if (name.contains("wlan") || name.contains("wifi")) {
                        byte[] macBytes = networkInterface.getHardwareAddress();
                        if (macBytes != null && macBytes.length == 6) {
                            StringBuilder macBuilder = new StringBuilder();
                            for (int i = 0; i < macBytes.length; i++) {
                                macBuilder.append(String.format("%02X", macBytes[i]));
                                if (i < macBytes.length - 1) {
                                    macBuilder.append(":");
                                }
                            }
                            String mac2 = macBuilder.toString();
                            
                            // Verificar se não é MAC fake
                            if (!mac2.equals("02:00:00:00:00:00") && !mac2.startsWith("02:")) {
                                Log.d(TAG, "MAC obtido via NetworkInterface: " + mac2);
                                return mac2;
                            }
                        }
                    }
                }
            } catch (Exception e) {
                Log.e(TAG, "Erro ao obter MAC via NetworkInterface: " + e.getMessage());
            }
            
        } catch (Exception e) {
            Log.e(TAG, "Erro ao obter MAC: " + e.getMessage());
        }
        
        // MAC não disponível - será obtido do MikroTik pelo IP
        Log.w(TAG, "MAC não disponível (Android 6+ privacidade) - usando IP para identificação");
        return null;
    }

    /**
     * Obtém o IP do dispositivo na rede WiFi
     */
    private String getWifiIpAddress() {
        try {
            if (wifiManager != null) {
                WifiInfo wifiInfo = wifiManager.getConnectionInfo();
                int ipInt = wifiInfo.getIpAddress();
                
                if (ipInt != 0) {
                    return String.format("%d.%d.%d.%d",
                            (ipInt & 0xff),
                            (ipInt >> 8 & 0xff),
                            (ipInt >> 16 & 0xff),
                            (ipInt >> 24 & 0xff));
                }
            }
        } catch (Exception e) {
            Log.e(TAG, "Erro ao obter IP: " + e.getMessage());
        }
        return null;
    }

    /**
     * Obtém MAC e IP diretamente do MikroTik via requisição HTTP
     * O MikroTik sabe o MAC real do dispositivo conectado
     */
    private void getInfoFromMikrotik() {
        try {
            // Fazer requisição para o gateway do MikroTik
            // O MikroTik pode retornar o MAC/IP via página de status
            URL url = new URL("http://" + MIKROTIK_GATEWAY + "/status");
            
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setConnectTimeout(5000);
            conn.setReadTimeout(5000);
            conn.setRequestMethod("GET");

            int responseCode = conn.getResponseCode();
            
            if (responseCode == 200) {
                BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();

                // Parse da resposta para extrair MAC/IP
                String responseStr = response.toString();
                Log.d(TAG, "Resposta MikroTik: " + responseStr);
                
                // O MikroTik hotspot geralmente inclui variáveis como $(mac) e $(ip)
                // Vamos usar o IP que já temos e confiar no registro do MikroTik
                String ipAddress = getWifiIpAddress();
                
                if (callback != null && ipAddress != null) {
                    // MAC será registrado pelo MikroTik quando acessar o portal
                    callback.onWifiConnected("PENDING", ipAddress);
                }
            } else {
                // Mesmo sem resposta do status, usar IP local
                String ipAddress = getWifiIpAddress();
                if (callback != null && ipAddress != null) {
                    callback.onWifiConnected("PENDING", ipAddress);
                }
            }

            conn.disconnect();

        } catch (Exception e) {
            Log.e(TAG, "Erro ao consultar MikroTik: " + e.getMessage());
            
            // Fallback: usar IP local
            String ipAddress = getWifiIpAddress();
            if (callback != null && ipAddress != null) {
                callback.onWifiConnected("PENDING", ipAddress);
            } else if (callback != null) {
                callback.onError("Não foi possível obter informações de rede");
            }
        }
    }

    /**
     * Faz uma requisição HTTP forçando uso do WiFi
     */
    public void makeRequestViaWifi(String urlString, HttpCallback httpCallback) {
        if (wifiNetwork == null) {
            httpCallback.onError("WiFi não está vinculado");
            return;
        }

        new Thread(() -> {
            try {
                URL url = new URL(urlString);
                HttpURLConnection conn = (HttpURLConnection) wifiNetwork.openConnection(url);
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);
                conn.setRequestMethod("GET");

                int responseCode = conn.getResponseCode();
                
                if (responseCode == 200) {
                    BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = reader.readLine()) != null) {
                        response.append(line);
                    }
                    reader.close();
                    httpCallback.onSuccess(response.toString());
                } else {
                    httpCallback.onError("HTTP " + responseCode);
                }

                conn.disconnect();

            } catch (Exception e) {
                httpCallback.onError(e.getMessage());
            }
        }).start();
    }

    /**
     * Libera o binding da rede WiFi
     */
    public void unbindWifiNetwork() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            connectivityManager.bindProcessToNetwork(null);
        } else {
            ConnectivityManager.setProcessDefaultNetwork(null);
        }
        wifiNetwork = null;
    }

    public interface HttpCallback {
        void onSuccess(String response);
        void onError(String error);
    }
}
