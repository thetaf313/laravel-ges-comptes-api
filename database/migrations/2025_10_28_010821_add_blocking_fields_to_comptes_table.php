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
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('motifBlocage')->nullable();
            $table->timestamp('dateBlocage')->nullable();
            $table->timestamp('dateDeblocagePrevue')->nullable();
            $table->string('motifDeblocage')->nullable();
            $table->timestamp('dateDeblocage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['motifBlocage', 'dateBlocage', 'dateDeblocagePrevue', 'motifDeblocage', 'dateDeblocage']);
        });
    }
};
