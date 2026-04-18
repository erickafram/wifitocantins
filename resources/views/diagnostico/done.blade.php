<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste já concluído</title>
    <meta name="robots" content="noindex,nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-emerald-500 to-teal-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-center">
        <div class="w-20 h-20 mx-auto bg-emerald-100 rounded-full flex items-center justify-center mb-3 text-4xl">✅</div>
        <h1 class="text-2xl font-bold text-gray-900">Teste já foi feito</h1>
        <p class="text-sm text-gray-500 mt-2">
            Seu atendente já recebeu o resultado. Se precisar de um novo teste, peça no chat.
        </p>
        @if($probe->completed_at)
            <p class="text-xs text-gray-400 mt-3">Concluído {{ $probe->completed_at->diffForHumans() }}</p>
        @endif
    </div>
</body>
</html>
