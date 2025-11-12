<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     * Permite acesso apenas para usuários com role 'admin'
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login')
                           ->with('error', 'Você precisa fazer login para acessar esta área.');
        }

        // Verificar se o usuário tem role de admin
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Acesso negado! Apenas administradores podem acessar esta página.');
        }

        return $next($request);
    }
}
