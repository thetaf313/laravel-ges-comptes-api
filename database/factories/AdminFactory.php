<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'telephone' => '+221' . $this->faker->numerify('7########'),  // Téléphone sénégalais
        ];
    }
}
