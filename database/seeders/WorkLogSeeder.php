<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;

class WorkLogSeeder extends Seeder
{
    public function run(): void
{
    $user = User::firstOrCreate(
        ['name' => 'Test'],
        [
            'role' => 'admin',
            'password' => bcrypt('password'),
            'visible_password' => 'password',
        ]
    );

    for ($i = 0; $i < 5; $i++) {
        $date = Carbon::now('Europe/Riga')->subDays($i)->toDateString();

        $start = '08:00:00';
        $end = '17:00:00';
        $hours = 9.00; // 9 raw hours, before lunch

        WorkLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date,
            ],
            [
                'start_time' => $start,
                'end_time' => $end,
                'hours_worked' => $hours,
            ]
        );
    }

    echo "✅ Added 5 work days for user: {$user->name}\n";
}
}
