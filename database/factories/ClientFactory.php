<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail,
            'telephone' => '7' . $this->faker->randomElement(['0', '6', '7', '8']) . $this->faker->numerify('#######'),
            'date_naissance' => $this->faker->date('Y-m-d', '2000-01-01'),
            'adresse' => $this->faker->address,
            'cni' => $this->faker->randomElement(['1', '2']) . $this->faker->numerify('############'),
            'code' => $this->faker->optional()->word,
            'password' => bcrypt('password'),
        ];
    }
}
