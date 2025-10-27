<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),  // Mot de passe par défaut
            'authenticatable_type' => null,  // Sera défini lors de la création (ex: App\Models\Client)
            'authenticatable_id' => null,    // Sera défini lors de la création
            'verification_code' => $this->faker->numerify('######'),
            'code_expires_at' => now()->addHours(24),
            'is_active' => $this->faker->boolean(50),  // 50% de chance d'être actif
        ];
    }
}
