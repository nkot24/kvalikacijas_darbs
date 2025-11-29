<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Production;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionFactory extends Factory
{
    protected $model = Production::class;

    public function definition(): array
    {
        return [
            // Adjust if your column names differ
            'order_id' => Order::factory(),
        ];
    }
}
