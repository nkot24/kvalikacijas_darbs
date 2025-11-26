<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'svitr_kods' => $this->faker->unique()->numerify('##########'),
            'nosaukums' => $this->faker->words(3, true),
            'pardosanas_cena' => $this->faker->randomFloat(2, 1, 1000),
            'vairumtirdzniecibas_cena' => $this->faker->randomFloat(2, 1, 900),
            'daudzums_noliktava' => $this->faker->numberBetween(0, 1000),
            'svars_neto' => $this->faker->randomFloat(2, 0.1, 100),
            'nomGr_kods' => $this->faker->bothify('NOM-###'),
            'garums' => $this->faker->randomFloat(2, 1, 200),
            'platums' => $this->faker->randomFloat(2, 1, 200),
            'augstums' => $this->faker->randomFloat(2, 1, 200),
        ];
    }
    
}
