<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Administrativo - WiFi Tocantins</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tocantins-gold': '#FFD700',
                        'tocantins-green': '#228B22',
                        'tocantins-dark-green': '#006400',
                        'tocantins-gray-green': '#2F4F2F'
                    }
                }
            }
        }
    </script>
</head>
<body class="font-inter bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center">
    
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-tocantins-green rounded-full mx-auto mb-4 flex items-center justify-center">
                <span class="text-white text-2xl">🔧</span>
            </div>
            <h1 class="text-2xl font-bold text-tocantins-gray-green mb-2">Painel Administrativo</h1>
            <p class="text-gray-600">WiFi Tocantins Express</p>
        </div>

        <div class="space-y-4">
            <a href="/admin" class="block w-full bg-tocantins-green text-white font-bold py-4 px-6 rounded-xl text-center hover:bg-tocantins-dark-green transition-colors shadow-lg">
                📊 Acessar Dashboard
            </a>
            
            <a href="/" class="block w-full border-2 border-tocantins-green text-tocantins-green font-bold py-4 px-6 rounded-xl text-center hover:bg-tocantins-green hover:text-white transition-colors">
                🌐 Portal do Usuário
            </a>
        </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Funcionalidades do Painel:</p>
            <ul class="mt-2 space-y-1">
                <li>• Dashboard em tempo real</li>
                <li>• Gestão de usuários conectados</li>
                <li>• Relatórios de receita</li>
                <li>• Controle de vouchers</li>
                <li>• Logs de conexão</li>
            </ul>
        </div>
    </div>

</body>
</html>

