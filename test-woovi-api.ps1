# 🧪 SCRIPT DE TESTE - WOOVI API
# Tocantins Transport WiFi

Write-Host "🚀 TESTANDO INTEGRAÇÃO WOOVI PIX" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Yellow

$baseUrl = "https://www.tocantinstransportewifi.com.br"

# Teste 1: Verificar se o site está online
Write-Host "`n1️⃣  TESTANDO CONECTIVIDADE DO SITE..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri $baseUrl -Method GET -TimeoutSec 10
    Write-Host "✅ Site online - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ Site offline ou inacessível: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Teste 2: Testar conexão com Woovi
Write-Host "`n2️⃣  TESTANDO CONEXÃO WOOVI API..." -ForegroundColor Cyan
try {
    $wooviTestUrl = "$baseUrl/api/payment/test-woovi"
    $response = Invoke-RestMethod -Uri $wooviTestUrl -Method GET -TimeoutSec 15
    
    if ($response.success) {
        Write-Host "✅ Conexão Woovi OK!" -ForegroundColor Green
        Write-Host "   Ambiente: $($response.environment)" -ForegroundColor White
        Write-Host "   Mensagem: $($response.message)" -ForegroundColor White
    } else {
        Write-Host "❌ Erro na conexão Woovi: $($response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Falha ao testar Woovi: $($_.Exception.Message)" -ForegroundColor Red
}

# Teste 3: Gerar QR Code PIX (Teste)
Write-Host "`n3️⃣  TESTANDO GERAÇÃO DE QR CODE PIX..." -ForegroundColor Cyan
try {
    $pixUrl = "$baseUrl/api/payment/pix"
    $body = @{
        amount = 5.99
        mac_address = "02:11:22:33:44:55"
    } | ConvertTo-Json
    
    $headers = @{
        'Content-Type' = 'application/json'
        'Accept' = 'application/json'
    }
    
    $response = Invoke-RestMethod -Uri $pixUrl -Method POST -Body $body -Headers $headers -TimeoutSec 20
    
    if ($response.success) {
        Write-Host "✅ QR Code gerado com sucesso!" -ForegroundColor Green
        Write-Host "   Gateway: $($response.gateway)" -ForegroundColor White
        Write-Host "   Payment ID: $($response.payment_id)" -ForegroundColor White
        Write-Host "   Valor: R$ $($response.qr_code.amount)" -ForegroundColor White
        Write-Host "   EMV: $($response.qr_code.emv_string.Substring(0, 50))..." -ForegroundColor Gray
        
        if ($response.qr_code.payment_link) {
            Write-Host "   Link de pagamento: $($response.qr_code.payment_link)" -ForegroundColor Blue
        }
    } else {
        Write-Host "❌ Erro ao gerar QR Code: $($response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Falha ao gerar QR Code: $($_.Exception.Message)" -ForegroundColor Red
}

# Teste 4: Verificar rotas disponíveis
Write-Host "`n4️⃣  VERIFICANDO ROTAS DISPONÍVEIS..." -ForegroundColor Cyan
$routes = @(
    "/api/payment/pix",
    "/api/payment/test-woovi", 
    "/api/payment/webhook/woovi",
    "/api/payment/pix/status"
)

foreach ($route in $routes) {
    $fullUrl = $baseUrl + $route
    try {
        if ($route -eq "/api/payment/pix/status") {
            $response = Invoke-WebRequest -Uri "$fullUrl?payment_id=1" -Method GET -TimeoutSec 5
        } else {
            $response = Invoke-WebRequest -Uri $fullUrl -Method GET -TimeoutSec 5
        }
        Write-Host "✅ $route - Status: $($response.StatusCode)" -ForegroundColor Green
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        if ($statusCode -eq 405) {
            Write-Host "⚠️  $route - Método não permitido (normal para POST)" -ForegroundColor Yellow
        } elseif ($statusCode -eq 422) {
            Write-Host "⚠️  $route - Dados inválidos (normal)" -ForegroundColor Yellow
        } else {
            Write-Host "❌ $route - Erro: $statusCode" -ForegroundColor Red
        }
    }
}

Write-Host "`n🎉 TESTE CONCLUÍDO!" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Yellow
