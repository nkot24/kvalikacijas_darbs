<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'svitr_kods'             => 200000000001,
                'nosaukums'              => 'Steel Bracket 50x50',
                'pardosanas_cena'        => 12.50,
                'vairumtirdzniecibas_cena'=> 9.80,
                'daudzums_noliktava'     => 120,
                'svars_neto'             => 0.35,
                'nomGr_kods'             => 'Test',
                'garums'                 => 50.00,
                'platums'                => 50.00,
                'augstums'               => 3.00,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'svitr_kods'             => 200000000002,
                'nosaukums'              => 'Aluminum Plate 200x300x8',
                'pardosanas_cena'        => 28.90,
                'vairumtirdzniecibas_cena'=> 22.40,
                'daudzums_noliktava'     => 35,
                'svars_neto'             => 2.40,
                'nomGr_kods'             => 'Test',
                'garums'                 => 300.00,
                'platums'                => 200.00,
                'augstums'               => 8.00,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'svitr_kods'             => 200000000003,
                'nosaukums'              => 'Welded Frame M',
                'pardosanas_cena'        => 95.00,
                'vairumtirdzniecibas_cena'=> 79.00,
                'daudzums_noliktava'     => 10,
                'svars_neto'             => 6.80,
                'nomGr_kods'             => 'Test',
                'garums'                 => 600.00,
                'platums'                => 400.00,
                'augstums'               => 50.00,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);
    }
}
