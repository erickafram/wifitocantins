<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = auth()->user();

        if (!$user || !$user->hasModule($module)) {
            abort(403, 'Voce nao tem acesso a este modulo.');
        }

        return $next($request);
    }
}
