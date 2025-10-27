<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('numero_compte')->unique();
            $table->string('titulaire');
            $table->enum('type', ['epargne', 'cheque']);
            $table->decimal('solde_initial', 15, 2)->min(10000);
            $table->string('devise', 10)->default('XOF');
            $table->date('date_creation')->default(now());
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif');
            $table->json('metadonnees')->nullable();
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index('numero_compte');
            $table->index('titulaire');
            $table->index('type');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
