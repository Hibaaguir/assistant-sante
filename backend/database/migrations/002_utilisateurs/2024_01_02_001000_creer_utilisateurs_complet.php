<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->date('date_of_birth')->nullable();
            $table->string('role', 50)->default('user');
            $table->string('specialite')->nullable();
            $table->string('statut_admin', 20)->default('Actif');
            $table->longText('profile_photo')->nullable();
            $table->timestamps();
            
            // Contrainte d'unicité partielle par rôle (email unique par rôle, pas globalement)
            $table->unique(['email', 'role'], 'users_email_role_unique');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};