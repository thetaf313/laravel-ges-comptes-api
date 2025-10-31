<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PassportClientSeeder::class,
            AdminSeeder::class,
            ClientSeeder::class,
        ]);

        // Créer un utilisateur de test
        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'verification_code' => '123456',
                'code_expires_at' => now()->addHours(24),
                'is_active' => true,
                'authenticatable_type' => \App\Models\Client::class,
                'authenticatable_id' => \App\Models\Client::factory()->create()->id,
            ]
        );
    }
}
