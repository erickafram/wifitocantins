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
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'wifi_price' => 'required|numeric|min:0.01|max:999.99',
            'pix_gateway' => 'required|in:woovi,pagbank,santander',
            'session_duration' => 'required|integer|min:1|max:168',
        ]);

        SystemSetting::setValue('wifi_price', $request->wifi_price);
        SystemSetting::setValue('pix_gateway', $request->pix_gateway);
        SystemSetting::setValue('session_duration', $request->session_duration);

        // Limpar cache de configurações
        \App\Helpers\SettingsHelper::clearCache();
        
        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
