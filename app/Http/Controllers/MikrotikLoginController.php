<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MikrotikLoginController extends Controller
{
    /**
     * Rota especial para receber redirecionamentos do MikroTik
     * Aceita tanto HTTP quanto HTTPS
     */
    public function handleMikrotikLogin(Request $request)
    {
        Log::info('🔵 Requisição recebida do MikroTik', [
            'url' => $request->fullUrl(),
            'params' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'protocol' => $request->secure() ? 'HTTPS' : 'HTTP',
        ]);

        // Capturar parâmetros do MikroTik
        $dst = $request->get('dst');
        $mac = $request->get('mac');
        $ip = $request->get('ip');
        $username = $request->get('username');
        $linkLogin = $request->get('link-login');
        $linkOrig = $request->get('link-orig');

        // Marcar que veio do MikroTik
        $request->session()->put('mikrotik_context_verified', true);
        $request->session()->put('from_mikrotik', true);

        // Construir URL do portal com os parâmetros
        $portalUrl = config('app.url');
        
        // Se não tiver protocolo seguro, usar HTTP
        if (!$request->secure()) {
            $portalUrl = str_replace('https://', 'http://', $portalUrl);
        }

        $queryParams = array_filter([
            'mac' => $mac,
            'ip' => $ip,
            'dst' => $dst,
            'from_mikrotik' => 1,
            'captive' => 1,
        ]);

        Log::info('✅ Redirecionando para portal', [
            'portal_url' => $portalUrl,
            'params' => $queryParams,
        ]);

        // Redirecionar para a página principal do portal
        return redirect()->to($portalUrl . '?' . http_build_query($queryParams));
    }

    /**
     * Página de fallback caso o redirecionamento não funcione
     */
    public function showLoginPage(Request $request)
    {
        $dst = $request->get('dst');
        $mac = $request->get('mac');
        
        return view('mikrotik.login', [
            'dst' => $dst,
            'mac' => $mac,
            'portal_url' => config('app.url'),
        ]);
    }
}
