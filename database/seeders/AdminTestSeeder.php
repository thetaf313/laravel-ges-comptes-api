<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin de test
        $admin = \App\Models\Admin::factory()->create([
            'nom' => 'Test',
            'prenom' => 'Admin',
            'telephone' => '+221777123456'
        ]);

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'verification_code' => '000000',
                'code_expires_at' => now()->addHours(24),
                'is_active' => true,
                'authenticatable_type' => \App\Models\Admin::class,
                'authenticatable_id' => $admin->id,
            ]
        );
    }
}
