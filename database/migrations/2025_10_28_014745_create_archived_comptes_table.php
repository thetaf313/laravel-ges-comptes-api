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
        if (!Schema::connection('pgsql_archive')->hasTable('archived_comptes')) {
            Schema::connection('pgsql_archive')->create('archived_comptes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('client_id');
                $table->string('numero_compte');
                $table->string('titulaire');
                $table->enum('type', ['epargne', 'cheque']);
                $table->decimal('solde_initial', 15, 2);
                $table->string('devise', 10)->default('XOF');
                $table->date('date_creation');
                $table->enum('statut', ['actif', 'bloque', 'ferme']);
                $table->json('metadonnees')->nullable();
                $table->timestamp('date_fermeture')->nullable();
                $table->string('motifBlocage')->nullable();
                $table->timestamp('dateBlocage')->nullable();
                $table->timestamp('dateDeblocagePrevue')->nullable();
                $table->string('motifDeblocage')->nullable();
                $table->timestamp('dateDeblocage')->nullable();
                $table->timestamp('archived_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_comptes');
    }
};
