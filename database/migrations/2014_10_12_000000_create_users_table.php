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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('authenticatable_type')->nullable();
            $table->string('authenticatable_id')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->index(['authenticatable_type', 'authenticatable_id']);
            $table->unique(['authenticatable_type', 'authenticatable_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
