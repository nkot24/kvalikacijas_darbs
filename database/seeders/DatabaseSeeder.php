<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClientSeeder::class,
            ProductSeeder::class,
            ProcessSeeder::class,
            OrderSeeder::class,
        ]);

        $user = User::firstOrCreate(
            ['name' => 'Test'],
            ['role' => 'admin', 'password' => bcrypt('password')]
        );

        if (Schema::hasColumn('users', 'visible_password')) {
            User::where('id', $user->id)->update(['visible_password' => 'password']);
        }

        $pids = DB::table('processes')->pluck('id');

        DB::table('process_user')->insertOrIgnore(
            $pids->map(fn ($id) => [
                'process_id' => $id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );
    }
}
