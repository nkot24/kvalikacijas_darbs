<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client; // Use the model instead of raw DB
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'nosaukums'             => 'ACME SIA',
                'registracijas_numurs'  => '40103456789',
                'pvn_maksataja_numurs'  => 'LV40103456789',
                'juridiska_adrese'      => 'Brīvības iela 1, Rīga, LV-1001',
            ],
            [
                'nosaukums'             => 'Baltic Metals',
                'registracijas_numurs'  => '40201234567',
                'pvn_maksataja_numurs'  => 'LV40201234567',
                'juridiska_adrese'      => 'Daugavgrīvas iela 45, Rīga',
            ],
            [
                'nosaukums'             => 'Nordic Fabrication',
                'registracijas_numurs'  => '40307654321',
                'pvn_maksataja_numurs'  => 'LV40307654321',
                'juridiska_adrese'      => 'Kārļa iela 10, Jelgava',
            ],
        ];

        foreach ($clients as $client) {
            // Avoid duplicates based on unique registracijas_numurs
            \App\Models\Client::firstOrCreate(
                ['registracijas_numurs' => $client['registracijas_numurs']],
                [
                    'nosaukums' => $client['nosaukums'],
                    'pvn_maksataja_numurs' => $client['pvn_maksataja_numurs'],
                    'juridiska_adrese' => $client['juridiska_adrese'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        echo "✅ ClientSeeder completed successfully.\n";
    }
}
