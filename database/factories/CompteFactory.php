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
            'numero_compte' => 'C' . $this->faker->unique()->numerify('########'),
            'titulaire' => $this->faker->name, // Valeur par défaut si non spécifiée
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde_initial' => $this->faker->numberBetween(10000, 1000000),
            'devise' => 'XOF',
            'statut' => 'actif',
            'date_creation' => now(),
            'metadonnees' => ['derniere_modification' => now(), 'version' => 1],
        ];
    }

    /**
     * Créer un compte pour un client spécifique
     */
    public function forClient($clientName): static
    {
        return $this->state(fn(array $attributes) => [
            'titulaire' => $clientName,
        ]);
    }
}
