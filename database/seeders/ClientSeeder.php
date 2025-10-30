<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()->count(10)->create()->each(function ($client) {
            // Créer un utilisateur lié au client
            $user = User::factory()->create([
                'authenticatable_type' => Client::class,
                'authenticatable_id' => $client->id,
            ]);

            // Créer 1 à 3 comptes par client avec le bon titulaire
            \App\Models\Compte::factory()->count(rand(1, 3))->create([
                'client_id' => $client->id,
                'titulaire' => $client->nom . ' ' . $client->prenom, // Utiliser le nom du client
            ])->each(function ($compte) {
                // Créer 2 à 5 transactions par compte
                Transaction::factory()->count(rand(2, 5))->create([
                    'compte_id' => $compte->id,
                ]);
            });
        });
    }
}
