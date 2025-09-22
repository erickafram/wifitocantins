<?php

echo "🌐 TESTANDO CONECTIVIDADE DA PÁGINA\n";
echo "===================================\n\n";

$urls = [
    'http://tocantinstransportewifi.com.br',
    'https://tocantinstransportewifi.com.br', 
    'http://www.tocantinstransportewifi.com.br',
    'https://www.tocantinstransportewifi.com.br',
    'http://206.189.217.189',
    'https://206.189.217.189'
];

foreach ($urls as $url) {
    echo "🔗 Testando: $url\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        echo "   ❌ Erro: $error\n";
    } else {
        echo "   ✅ HTTP $httpCode\n";
        if ($httpCode >= 300 && $httpCode < 400) {
            // Procurar Location header
            if (preg_match('/Location:\s*(.+)/i', $response, $matches)) {
                echo "   🔀 Redirect para: " . trim($matches[1]) . "\n";
            }
        }
    }
    echo "\n";
}

echo "📋 VERIFICANDO DNS:\n";
$domains = ['tocantinstransportewifi.com.br', 'www.tocantinstransportewifi.com.br'];
foreach ($domains as $domain) {
    $ip = gethostbyname($domain);
    echo "   $domain -> $ip\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
