<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link inválido</title>
    <meta name="robots" content="noindex,nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-400 to-gray-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-center">
        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3 text-4xl">
            {{ $reason === 'expired' ? '⏰' : '🔗' }}
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $reason === 'expired' ? 'Link expirou' : 'Link inválido' }}
        </h1>
        <p class="text-sm text-gray-500 mt-2">
            @if($reason === 'expired')
                Este teste de conexão expirou após 30 minutos. Peça um novo link ao atendente no chat.
            @else
                Este link não existe ou já foi usado. Volte ao chat e peça um novo.
            @endif
        </p>
    </div>
</body>
</html>
