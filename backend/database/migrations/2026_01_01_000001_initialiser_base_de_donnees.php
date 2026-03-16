<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('user'); // user, medecin, administrateur
            $table->string('specialite')->nullable();
            $table->string('statut_admin')->default('Actif')->nullable();
            $table->timestamps();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Personal Access Tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 80)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Profils Santé
        Schema::create('profils_sante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('sexe', ['homme', 'femme'])->nullable();
            $table->float('taille')->nullable();
            $table->float('poids')->nullable();
            $table->string('groupe_sanguin')->nullable();
            $table->string('objectif')->nullable();
            $table->json('allergies')->nullable();
            $table->json('maladies_chroniques')->nullable();
            $table->json('traitements')->nullable();
            $table->boolean('prend_medicament')->default(false);
            $table->string('nom_medicament')->nullable();
            $table->boolean('fumeur')->default(false);
            $table->boolean('alcool')->default(false);
            $table->boolean('consulte_medecin')->default(false);
            $table->string('medecin_email')->nullable();
            $table->boolean('medecin_peut_consulter')->default(false);
            $table->json('objectifs')->nullable();
            $table->timestamps();
        });

        // Invitations Medecin
        Schema::create('invitations_medecin', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('profil_sante_id')->nullable()->constrained('profils_sante')->onDelete('cascade');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('accepted')->default(false);
            $table->timestamps();
        });

        // Signes Vitaux
        Schema::create('signes_vitaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_sante_id')->constrained('profils_sante')->onDelete('cascade');
            $table->float('tension_systolique')->nullable();
            $table->float('tension_diastolique')->nullable();
            $table->float('frequence_cardiaque')->nullable();
            $table->float('temperature')->nullable();
            $table->float('glycemie')->nullable();
            $table->string('unite_glycemie')->nullable();
            $table->timestamps();
        });

        // Résultats Laboratoire
        Schema::create('resultats_laboratoire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_sante_id')->constrained('profils_sante')->onDelete('cascade');
            $table->string('nom_analyse');
            $table->string('type_resultat');
            $table->text('description')->nullable();
            $table->text('fichier_resultat')->nullable();
            $table->timestamps();
        });

        // Entrées Journal
        Schema::create('entrees_journal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_sante_id')->constrained('profils_sante')->onDelete('cascade');
            $table->text('contenu');
            $table->string('sentiment')->nullable();
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrees_journal');
        Schema::dropIfExists('resultats_laboratoire');
        Schema::dropIfExists('signes_vitaux');
        Schema::dropIfExists('invitations_medecin');
        Schema::dropIfExists('profils_sante');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
