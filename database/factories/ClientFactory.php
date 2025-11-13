<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'nosaukums' => $this->faker->company,
            'registracijas_numurs' => $this->faker->unique()->numerify('#########'),
            'pvn_maksataja_numurs' => $this->faker->optional()->numerify('LV########'),
            'juridiska_adrese' => $this->faker->address,
        ];
    }
}
