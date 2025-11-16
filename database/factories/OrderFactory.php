<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'pasutijuma_numurs' => 'P-' . $this->faker->unique()->numerify('######'),
            'datums'             => $this->faker->date(),

            // Relations – will automatically create Client/Product if needed
            'client_id'   => Client::factory(),
            'klients'     => null,
            'products_id' => Product::factory(),
            'produkts'    => null,

            'daudzums'         => $this->faker->numberBetween(1, 100),
            'izpildes_datums'  => $this->faker->dateTimeBetween('now', '+1 month'),
            'prioritāte'       => $this->faker->randomElement(['zema', 'normāla', 'augsta']),
            'statuss'          => $this->faker->randomElement([
                'nav nodots ražošanai',
                'ražošanā',
                'pabeigts',
            ]),
            'piezimes'         => $this->faker->sentence(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'statuss' => 'pabeigts',
        ]);
    }

    public function notCompleted(): self
    {
        return $this->state(fn () => [
            'statuss' => $this->faker->randomElement([
                'nav nodots ražošanai',
                'ražošanā',
            ]),
        ]);
    }
}
