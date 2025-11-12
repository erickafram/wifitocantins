<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionando - WiFi Tocantins</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            margin: 20px auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            📶
        </div>
        
        <h1>Conectando ao WiFi Tocantins</h1>
        <p>Aguarde enquanto preparamos sua conexão...</p>
        
        <div class="spinner"></div>
        
        <div class="info">
            <strong>🚌 WiFi a bordo</strong><br>
            Você está sendo redirecionado para o portal de acesso
        </div>
        
        <a href="{{ $portal_url }}" class="btn">
            Ir para o Portal
        </a>
    </div>
    
    <script>
        // Redirecionar automaticamente após 2 segundos
        setTimeout(function() {
            const portalUrl = "{{ $portal_url }}";
            const params = new URLSearchParams({
                @if(isset($mac))
                mac: "{{ $mac }}",
                @endif
                @if(isset($dst))
                dst: "{{ $dst }}",
                @endif
                from_mikrotik: 1,
                captive: 1
            });
            
            window.location.href = portalUrl + '?' + params.toString();
        }, 2000);
    </script>
</body>
</html>
