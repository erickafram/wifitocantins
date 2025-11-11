<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\User;
use App\Models\Payment;
use App\Models\Device;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar vouchers de exemplo
        $this->createSampleVouchers();
        
        // Criar dados de exemplo para demonstração
        $this->createSampleData();
    }

    private function createSampleVouchers()
    {
        $vouchers = [
            [
                'code' => 'WIFI_DEMO01',
                'description' => 'Voucher de demonstração - Acesso gratuito',
                'discount_percent' => 100,
                'max_uses' => 1,
                'expires_at' => now()->addDays(30)
            ],
            [
                'code' => 'WIFI_TEST01',
                'description' => 'Voucher de teste - Válido por 7 dias',
                'discount_percent' => 100,
                'max_uses' => 5,
                'expires_at' => now()->addDays(7)
            ],
            [
                'code' => 'ADMIN_FREE',
                'description' => 'Voucher administrativo - Uso ilimitado',
                'discount_percent' => 100,
                'max_uses' => 999,
                'expires_at' => now()->addMonths(12)
            ],
            [
                'code' => 'PROMO_50',
                'description' => 'Promoção 50% de desconto',
                'discount_percent' => 50,
                'max_uses' => 100,
                'expires_at' => now()->addDays(15)
            ],
            [
                'code' => 'TOCANTINS2024',
                'description' => 'Voucher especial Tocantins',
                'discount_percent' => 100,
                'max_uses' => 50,
                'expires_at' => now()->addDays(60)
            ]
        ];

        foreach ($vouchers as $voucherData) {
            Voucher::create(array_merge($voucherData, [
                'discount' => null,
                'used_count' => 0,
                'is_active' => true
            ]));
        }

        $this->command->info('Criados 5 vouchers de exemplo');
    }

    private function createSampleData()
    {
        // Criar alguns usuários simulados conectados
        $macAddresses = [
            '02:AA:BB:CC:DD:01',
            '02:AA:BB:CC:DD:02', 
            '02:AA:BB:CC:DD:03',
            '02:AA:BB:CC:DD:04',
            '02:AA:BB:CC:DD:05'
        ];

        foreach ($macAddresses as $index => $mac) {
            $user = User::create([
                'mac_address' => $mac,
                'ip_address' => '192.168.1.' . (100 + $index),
                'device_name' => 'Device ' . ($index + 1),
                'status' => $index < 3 ? 'connected' : 'offline',
                'connected_at' => $index < 3 ? now()->subMinutes(rand(5, 60)) : null,
                'expires_at' => $index < 3 ? now()->addHours(24) : null,
                'data_used' => rand(10, 500)
            ]);

            // Criar alguns pagamentos
            if (rand(0, 1)) {
                Payment::create([
                    'user_id' => $user->id,
                    'amount' => 5.00,
                    'method' => rand(0, 1) ? 'pix' : 'card',
                    'status' => 'completed',
                    'transaction_id' => 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'payment_gateway' => 'mercadopago',
                    'paid_at' => now()->subMinutes(rand(5, 120))
                ]);
            }

            // Registrar dispositivos
            Device::create([
                'mac_address' => $mac,
                'device_name' => 'Smartphone ' . ($index + 1),
                'device_type' => 'mobile',
                'user_agent' => 'Mozilla/5.0 (Mobile; Android)',
                'first_seen' => now()->subDays(rand(1, 7)),
                'last_seen' => now()->subMinutes(rand(1, 30)),
                'total_connections' => rand(1, 10)
            ]);
        }

        // Criar alguns pagamentos do dia para estatísticas
        for ($i = 0; $i < 8; $i++) {
            $randomUser = User::inRandomOrder()->first();
            if ($randomUser) {
                Payment::create([
                    'user_id' => $randomUser->id,
                    'amount' => 5.00,
                    'method' => rand(0, 1) ? 'pix' : 'card',
                    'status' => 'completed',
                    'transaction_id' => 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'payment_gateway' => rand(0, 1) ? 'mercadopago' : 'stripe',
                    'paid_at' => today()->addHours(rand(6, 22)),
                    'created_at' => today()->addHours(rand(6, 22))
                ]);
            }
        }

        $this->command->info('Criados dados de exemplo: 5 usuários, dispositivos e pagamentos');
    }
}
