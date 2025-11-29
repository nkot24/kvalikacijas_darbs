<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Production;
use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'production_id' => Production::factory(),
            'process_id'    => Process::factory(),
            'user_id'       => null, // you are not using user_id anymore (assignedUsers pivot instead)
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ];
    }
}
