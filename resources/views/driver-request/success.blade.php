<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Enviado - WiFi Tocantins</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md text-center">
    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-xl font-bold text-gray-800 mb-2">Cadastro enviado</h1>
    <p class="text-sm text-gray-500 mb-6">Seus dados foram recebidos. O administrador ira analisar e ativar seu voucher de acesso ao WiFi. Voce sera notificado quando estiver pronto.</p>
    <a href="{{ route('driver-request.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300 transition-colors">Voltar ao inicio</a>
</div>
</body>
</html>
