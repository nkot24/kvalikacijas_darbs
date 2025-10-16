<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'nosaukums' => 'Steel Bracket 50x50',
                'svitr_kods' => '200000000001',
                'nomGr_kods' => 'Test',
                'garums' => 50,
                'platums' => 50,
                'augstums' => 3,
                'svars_neto' => 0.35,
                'daudzums_noliktava' => 120,
                'vairumtirdzniecibas_cena' => 9.8,
                'pardosanas_cena' => 12.5,
            ],
            [
                'nosaukums' => 'Aluminum Plate 200x300x8',
                'svitr_kods' => '200000000002',
                'nomGr_kods' => 'Test',
                'garums' => 300,
                'platums' => 200,
                'augstums' => 8,
                'svars_neto' => 2.4,
                'daudzums_noliktava' => 35,
                'vairumtirdzniecibas_cena' => 22.4,
                'pardosanas_cena' => 28.9,
            ],
            [
                'nosaukums' => 'Welded Frame M',
                'svitr_kods' => '200000000003',
                'nomGr_kods' => 'Test',
                'garums' => 600,
                'platums' => 400,
                'augstums' => 50,
                'svars_neto' => 6.8,
                'daudzums_noliktava' => 10,
                'vairumtirdzniecibas_cena' => 79.0,
                'pardosanas_cena' => 95.0,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['svitr_kods' => $product['svitr_kods']],
                array_merge($product, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }

        echo "✅ ProductSeeder completed successfully.\n";
    }
}
