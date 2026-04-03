<?php

namespace Database\Seeders;

use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $buses = [
            // Preencha com os seriais reais de cada MikroTik
            // O serial aparece no log do MikroTik como "mid" e no export como "serial number"
            ['mikrotik_serial' => 'HH60A2NSBE7', 'name' => 'Ônibus 01', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_02', 'name' => 'Ônibus 02', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_03', 'name' => 'Ônibus 03', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_04', 'name' => 'Ônibus 04', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_05', 'name' => 'Ônibus 05', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_06', 'name' => 'Ônibus 06', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_07', 'name' => 'Ônibus 07', 'plate' => null, 'route_description' => null],
            ['mikrotik_serial' => 'SERIAL_BUS_08', 'name' => 'Ônibus 08', 'plate' => null, 'route_description' => null],
        ];

        foreach ($buses as $bus) {
            Bus::updateOrCreate(
                ['mikrotik_serial' => $bus['mikrotik_serial']],
                $bus
            );
        }
    }
}
