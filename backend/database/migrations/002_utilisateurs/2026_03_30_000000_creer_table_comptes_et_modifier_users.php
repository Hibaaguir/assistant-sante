<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
        });

        // Adapter la table users
        Schema::table('users', function (Blueprint $table) {
            // Ajouter seulement la colonne compte_id si elle n'existe pas
            if (!Schema::hasColumn('users', 'compte_id')) {
                $table->unsignedBigInteger('compte_id')->nullable()->after('id');
            }
            // Ajouter la colonne age si elle n'existe pas
            if (!Schema::hasColumn('users', 'age')) {
                $table->integer('age')->nullable()->after('profile_photo');
            }
            // Supprimer les anciennes colonnes inutiles si elles existent
            $colonnesASupprimer = array_filter([
                'email', 'password', 'email_verified_at', 'remember_token', 'role', 'specialite', 'statut_admin'
            ], fn($col) => Schema::hasColumn('users', $col));
            if (!empty($colonnesASupprimer)) {
                $table->dropColumn($colonnesASupprimer);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['compte_id', 'date_naissance', 'profile_photo', 'age']);
            // Les anciennes colonnes ne sont pas restaurées ici pour éviter les conflits
        });
        Schema::dropIfExists('comptes');
    }
};
