<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkLogFactory extends Factory
{
    protected $model = WorkLog::class;

    public function definition()
    {
        return [
            'user_id'       => User::factory(),
            'date'          => $this->faker->date(),
            'start_time'    => '08:00:00',
            'end_time'      => '17:00:00',
            'hours_worked'  => 8.0,
            'lunch_minutes' => 0,
            'break_count'   => 0,
        ];
    }
}
