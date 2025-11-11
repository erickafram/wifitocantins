<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'wifi_price', 'value' => '5.99'],
            ['key' => 'pix_gateway', 'value' => 'pagbank'],
            ['key' => 'session_duration', 'value' => '24'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        $this->command->info('✅ Configurações do sistema inseridas/atualizadas com sucesso!');
    }
}
