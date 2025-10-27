<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'compte_id' => null,  // Sera défini lors de la création (lié à un Compte)
            'type' => $this->faker->randomElement(['depot', 'retrait', 'frais']),
            'montant' => $this->faker->numberBetween(1000, 100000),
            'description' => $this->faker->sentence,
            'date_transaction' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'statut' => 'validee',  // Statut validé par défaut
            'devise' => 'XOF'
        ];
    }
}
