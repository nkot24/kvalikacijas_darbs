<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'pasutijuma_numurs' => 'ORD-0001',
                'klients' => 'ACME SIA',
                'produkts' => 'Steel Bracket 50x50',
                'daudzums' => 100,
                'prioritāte' => 'augsta',
                'statuss' => 'nav nodots ražošanai',
                'piezimes' => 'Steidzams pasūtījums.',
                'datums' => Carbon::now('Europe/Riga'),
                'izpildes_datums' => Carbon::now('Europe/Riga')->addDays(7),
                'client_id' => 1,
                'products_id' => 1,
            ],
            [
                'pasutijuma_numurs' => 'ORD-0002',
                'klients' => 'Baltic Metals',
                'produkts' => 'Aluminum Plate 200x300x8',
                'daudzums' => 12,
                'prioritāte' => 'normāla',
                'statuss' => 'procesā',
                'piezimes' => '?',
                'datums' => Carbon::now('Europe/Riga')->subDay(),
                'izpildes_datums' => Carbon::now('Europe/Riga')->addDays(14),
                'client_id' => 2,
                'products_id' => 2,
            ],
            [
                'pasutijuma_numurs' => 'ORD-0003',
                'klients' => 'Nordic Fabrication',
                'produkts' => 'Welded Frame M',
                'daudzums' => 4,
                'prioritāte' => 'zema',
                'statuss' => 'nav nodots ražošanai',
                'piezimes' => 'Pēc krāsošanas piegāde.',
                'datums' => Carbon::now('Europe/Riga')->subDays(2),
                'izpildes_datums' => Carbon::now('Europe/Riga')->addWeeks(3),
                'client_id' => 3,
                'products_id' => 3,
            ],
        ];

        foreach ($orders as $order) {
            Order::firstOrCreate(
                ['pasutijuma_numurs' => $order['pasutijuma_numurs']],
                array_merge($order, [
                    'created_at' => Carbon::now('Europe/Riga'),
                    'updated_at' => Carbon::now('Europe/Riga'),
                ])
            );
        }

        echo "✅ OrderSeeder completed successfully.\n";
    }
}
