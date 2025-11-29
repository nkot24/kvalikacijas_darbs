<?php

namespace Database\Factories;

use App\Models\MaterialScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialScanFactory extends Factory
{
    protected $model = MaterialScan::class;

    public function definition(): array
    {
        return [
            'svitr_kods'   => $this->faker->ean13(),
            'qty'          => $this->faker->numberBetween(1, 10),
            'created_by'   => User::factory(),
            'accounted'    => false,
            'accounted_at' => null,
        ];
    }
}
