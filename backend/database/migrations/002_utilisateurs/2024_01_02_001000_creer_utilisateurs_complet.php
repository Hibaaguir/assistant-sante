<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Créer la table users avec toutes les colonnes finales
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                
                // Colonnes ajoutées progressivement
                $table->date('date_of_birth')->nullable();
                $table->string('role', 50)->default('user');
                $table->string('specialite')->nullable();
                $table->string('statut_admin', 20)->default('Actif');
                $table->longText('profile_photo')->nullable();
                
                $table->timestamps();
            });
        }

        // Créer la table password_reset_tokens
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Créer la table sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Initialiser les valeurs par défaut si la table vient d'être créée
        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'user']);

        DB::table('users')
            ->whereNull('statut_admin')
            ->update(['statut_admin' => 'Actif']);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
