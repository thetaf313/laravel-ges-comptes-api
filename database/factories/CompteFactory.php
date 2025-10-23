<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'numero_compte' => 'CPT-' . strtoupper($this->faker->unique()->lexify('????????')),
            'titulaire' => $this->faker->name,
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 1000000),
            'devise' => $this->faker->randomElement(['EUR', 'XOF', 'USD']),
            'date_creation' => $this->faker->date(),
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'version' => 1,
        ];
    }
}
