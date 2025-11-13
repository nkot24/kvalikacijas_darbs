<?php

namespace Database\Factories;

use App\Models\ContactPerson;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactPersonFactory extends Factory
{
    protected $model = ContactPerson::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'kontakt_personas_vards' => $this->faker->name,
            'e-pasts' => $this->faker->safeEmail,
            'telefons' => $this->faker->phoneNumber,
        ];
    }
}
