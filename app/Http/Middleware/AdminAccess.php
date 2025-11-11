<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
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

        // Verificar se o usuário tem role de admin ou manager
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'manager'])) {
            auth()->logout();
            return redirect()->route('login')
                           ->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
