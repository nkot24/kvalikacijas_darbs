<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('processes')->insert([
            [ 'processa_nosaukums' => 'Rasēšana',          'created_at' => now(), 'updated_at' => now() ],
            [ 'processa_nosaukums' => 'Lokšņu griešana',   'created_at' => now(), 'updated_at' => now() ],
            [ 'processa_nosaukums' => 'Metināšana',        'created_at' => now(), 'updated_at' => now() ],
            [ 'processa_nosaukums' => 'Slīpēšana',         'created_at' => now(), 'updated_at' => now() ],
            [ 'processa_nosaukums' => 'Krāsošana',         'created_at' => now(), 'updated_at' => now() ],
            [ 'processa_nosaukums' => 'Kvalitātes kontrole','created_at' => now(), 'updated_at' => now() ],
        ]);
    }
}
