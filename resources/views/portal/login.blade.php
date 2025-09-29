@extends('portal.layout')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-purple-50/30 to-cyan-50/30 px-4 py-12">
    <div class="w-full max-w-md">
        <div class="elegant-card rounded-3xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="WiFi Tocantins" class="w-20 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Entrar no WiFi Tocantins</h1>
                <p class="text-sm text-gray-500 mt-2">Acompanhe seus pagamentos e conexão.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    Telefone ou senha inválidos.
                </div>
            @endif

            <form method="POST" action="{{ route('portal.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-600 mb-2">Telefone (com DDD)</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Senha</label>
                    <input type="password" id="password" name="password" required class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                </div>

                <button type="submit" class="w-full connect-button text-white font-semibold py-3 rounded-xl">Entrar</button>
            </form>

            <p class="text-xs text-gray-500 text-center mt-6">Se ainda não tem acesso, conecte-se ao Wi-Fi a bordo e faça o cadastro.</p>
        </div>
    </div>
</div>
@endsection

