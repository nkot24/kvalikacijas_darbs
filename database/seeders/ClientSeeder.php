<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('clients')->insert([
            [
                'nosaukums'             => 'ACME SIA',
                'registracijas_numurs'  => '40103456789',
                'pvn_maksataja_numurs'  => 'LV40103456789',
                'juridiska_adrese'      => 'Brīvības iela 1, Rīga, LV-1001',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'nosaukums'             => 'Baltic Metals',
                'registracijas_numurs'  => '40201234567',
                'pvn_maksataja_numurs'  => 'LV40201234567',
                'juridiska_adrese'      => 'Daugavgrīvas iela 45, Rīga',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'nosaukums'             => 'Nordic Fabrication',
                'registracijas_numurs'  => '40307654321',
                'pvn_maksataja_numurs'  => 'LV40307654321',
                'juridiska_adrese'      => 'Kārļa iela 10, Jelgava',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
        ]);
    }
}
