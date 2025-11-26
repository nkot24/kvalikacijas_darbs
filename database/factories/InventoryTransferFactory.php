<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\InventoryTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransferFactory extends Factory
{
    protected $model = InventoryTransfer::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'qty' => $this->faker->numberBetween(1, 20),
            'accounted' => false,
            'created_by' => User::factory(),
        ];
    }
}
