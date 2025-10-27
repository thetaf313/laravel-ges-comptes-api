<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()->count(3)->create()->each(function ($admin) {
            // Créer un utilisateur lié à l'admin
            $user = User::factory()->create([
                'authenticatable_type' => Admin::class,
                'authenticatable_id' => $admin->id,
                'is_active' => true,  // Admins actifs par défaut
            ]);
        });
    }
}
