<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client OAuth2 pour l'application (éviter les doublons)
        DB::table('oauth_clients')->updateOrInsert(
            ['id' => 1],
            [
                'user_id' => null,
                'name' => 'Ges-Comptes API Client',
                'secret' => Str::random(40),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Créer un client pour les accès personnels
        DB::table('oauth_clients')->updateOrInsert(
            ['id' => 2],
            [
                'user_id' => null,
                'name' => 'Ges-Comptes Personal Access Client',
                'secret' => Str::random(40),
                'provider' => null,
                'redirect' => 'http://localhost',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Créer un client pour les codes d'autorisation
        DB::table('oauth_clients')->updateOrInsert(
            ['id' => 3],
            [
                'user_id' => null,
                'name' => 'Ges-Comptes Authorization Code Client',
                'secret' => Str::random(40),
                'provider' => 'users',
                'redirect' => 'http://localhost/callback',
                'personal_access_client' => false,
                'password_client' => false,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('OAuth2 clients created successfully!');
        $this->command->info('Password Grant Client ID: 1');
        $this->command->info('Personal Access Client ID: 2');
        $this->command->info('Authorization Code Client ID: 3');
    }
}
