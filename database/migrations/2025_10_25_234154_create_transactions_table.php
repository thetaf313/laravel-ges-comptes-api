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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->decimal('montant', 15, 2);
            $table->enum('type', ['depot', 'retrait', 'virement', 'frais']);
            $table->date('date_transaction');
            $table->string('devise', 3)->default('XOF');
            $table->string('description')->nullable();
            $table->enum('statut', ['en_attente', 'validee', 'annulee'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
