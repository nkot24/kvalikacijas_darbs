<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessFactory extends Factory
{
    protected $model = Process::class;

    public function definition(): array
    {
        return [
            'processa_nosaukums' => $this->faker->words(2, true), // e.g. "Griešana metāls"
        ];
    }
}
