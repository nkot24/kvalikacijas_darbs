<?php

namespace Database\Factories;

use App\Models\OrderList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderListFactory extends Factory
{
    protected $model = OrderList::class;

    public function definition(): array
    {
        $orderedAt  = $this->faker->optional()->dateTimeBetween('-1 month', 'now');
        $expectedAt = $orderedAt
            ? $this->faker->optional()->dateTimeBetween($orderedAt, '+1 month')
            : null;

        return [
            'name'          => $this->faker->words(3, true),
            'quantity'      => $this->faker->numberBetween(1, 100),
            'supplier_name' => $this->faker->optional()->company(),
            'ordered_at'    => $orderedAt,
            'expected_at'   => $expectedAt,
            'arrived_at'    => null,
            'photo_path'    => null,
            'created_by'    => User::factory(),
        ];
    }

    /**
     * Completed / received order.
     */
    public function completed(): self
    {
        return $this->state(function () {
            return [
                'status'     => 'saņemts', // matches your controller check
                'arrived_at' => now(),
            ];
        });
    }
}

