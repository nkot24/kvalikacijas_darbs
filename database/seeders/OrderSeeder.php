<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // assume first 3 clients/products from the other seeders
        $now = Carbon::now();

        DB::table('orders')->insert([
            [
                'pasutijuma_numurs' => 'ORD-0001',
                'datums'            => $now,
                'client_id'         => 1,
                'klients'           => 'ACME SIA',
                'products_id'       => 1,
                'produkts'          => 'Steel Bracket 50x50',
                'daudzums'          => 100,
                'izpildes_datums'   => $now->copy()->addDays(7)->toDateString(),
                'prioritāte'        => 'augsta',
                'statuss'           => 'nav nodots ražošanai',
                'piezimes'          => 'Steidzams pasūtījums.',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'pasutijuma_numurs' => 'ORD-0002',
                'datums'            => $now->copy()->subDay(),
                'client_id'         => 2,
                'klients'           => 'Baltic Metals',
                'products_id'       => 2,
                'produkts'          => 'Aluminum Plate 200x300x8',
                'daudzums'          => 12,
                'izpildes_datums'   => $now->copy()->addDays(14)->toDateString(),
                'prioritāte'        => 'normāla',
                'statuss'           => 'procesā',
                'piezimes'          => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'pasutijuma_numurs' => 'ORD-0003',
                'datums'            => $now->copy()->subDays(2),
                'client_id'         => 3,
                'klients'           => 'Nordic Fabrication',
                'products_id'       => 3,
                'produkts'          => 'Welded Frame M',
                'daudzums'          => 4,
                'izpildes_datums'   => $now->copy()->addDays(21)->toDateString(),
                'prioritāte'        => 'zema',
                'statuss'           => 'nav nodots ražošanai',
                'piezimes'          => 'Pēc krāsošanas piegāde.',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
