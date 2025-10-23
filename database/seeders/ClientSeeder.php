<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()->count(10)->create()->each(function ($client) {
            \App\Models\Compte::factory()->count(rand(1, 3))->create([
                'client_id' => $client->id,
            ]);
        });
    }
}
