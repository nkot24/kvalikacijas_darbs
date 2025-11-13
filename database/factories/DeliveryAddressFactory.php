<?php

namespace Database\Factories;

use App\Models\DeliveryAddress;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryAddressFactory extends Factory
{
    protected $model = DeliveryAddress::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'piegades_adrese' => $this->faker->address,
        ];
    }
}
