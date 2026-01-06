<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'wifi_price' => SystemSetting::getValue('wifi_price', '5.99'),
            'pix_gateway' => SystemSetting::getValue('pix_gateway', 'pagbank'),
            'session_duration' => SystemSetting::getValue('session_duration', '24'),
            'pagbank_account' => SystemSetting::getValue('pagbank_account', 'junior'),
            'pagbank_email' => SystemSetting::getValue('pagbank_email', 'juniormoreiragloboplay@gmail.com'),
            'pagbank_token' => SystemSetting::getValue('pagbank_token', 'c75a2308-ec9d-4825-94fd-bacba8a7248344f58a634d1b857348dba39f6a5b6c957b2a-2890-4da4-9866-af24b6eee984'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'wifi_price' => 'required|numeric|min:0.01|max:999.99',
            'pix_gateway' => 'required|in:woovi,pagbank,santander',
            'session_duration' => 'required|integer|min:1|max:168',
            'pagbank_account' => 'nullable|in:junior,erick',
            'pagbank_email' => 'nullable|email|max:255',
            'pagbank_token' => 'nullable|string|max:500',
        ]);

        SystemSetting::setValue('wifi_price', $request->wifi_price);
        SystemSetting::setValue('pix_gateway', $request->pix_gateway);
        SystemSetting::setValue('session_duration', $request->session_duration);
        
        // Salvar conta e credenciais PagBank
        if ($request->filled('pagbank_account')) {
            SystemSetting::setValue('pagbank_account', $request->pagbank_account);
        }
        if ($request->filled('pagbank_email')) {
            SystemSetting::setValue('pagbank_email', $request->pagbank_email);
        }
        if ($request->filled('pagbank_token')) {
            SystemSetting::setValue('pagbank_token', $request->pagbank_token);
        }

        // Limpar cache de configurações (aplica imediatamente sem precisar de artisan)
        \App\Helpers\SettingsHelper::clearCache();
        
        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
