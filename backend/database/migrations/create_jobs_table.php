<?php

// On importe les classes nécessaires pour créer des migrations
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour créer les tables liées aux jobs (files d’attente)
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute avec : php artisan migrate
     * Elle crée les tables jobs, job_batches et failed_jobs
     */
    public function up(): void
    {
        // Table "jobs"
        // Elle stocke les tâches envoyées en file d’attente (queue)
        Schema::create('jobs', function (Blueprint $table) {

            // Clé primaire
            $table->id();

            // Nom de la file d’attente (ex: default)
            $table->string('queue')->index();

            // Données de la tâche (stockées en texte long)
            $table->longText('payload');

            // Nombre de tentatives d’exécution
            $table->unsignedTinyInteger('attempts');

            // Date à laquelle la tâche est réservée
            $table->unsignedInteger('reserved_at')->nullable();

            // Date à laquelle la tâche devient disponible
            $table->unsignedInteger('available_at');

            // Date de création de la tâche
            $table->unsignedInteger('created_at');
        });

        // Table "job_batches"
        // Sert à gérer un groupe de jobs exécutés ensemble
        Schema::create('job_batches', function (Blueprint $table) {

            // ID unique du batch
            $table->string('id')->primary();

            // Nom du batch
            $table->string('name');

            // Nombre total de jobs dans le batch
            $table->integer('total_jobs');

            // Nombre de jobs en attente
            $table->integer('pending_jobs');

            // Nombre de jobs échoués
            $table->integer('failed_jobs');

            // Liste des IDs des jobs échoués
            $table->longText('failed_job_ids');

            // Options supplémentaires (facultatif)
            $table->mediumText('options')->nullable();

            // Date d’annulation (si annulé)
            $table->integer('cancelled_at')->nullable();

            // Date de création
            $table->integer('created_at');

            // Date de fin (si terminé)
            $table->integer('finished_at')->nullable();
        });

        // Table "failed_jobs"
        // Elle stocke les jobs qui ont échoué
        Schema::create('failed_jobs', function (Blueprint $table) {

            // Clé primaire
            $table->id();

            // Identifiant unique du job
            $table->string('uuid')->unique();

            // Connexion utilisée (ex: database, redis)
            $table->text('connection');

            // Nom de la file d’attente
            $table->text('queue');

            // Données du job
            $table->longText('payload');

            // Détails de l’erreur
            $table->longText('exception');

            // Date de l’échec (automatiquement définie)
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute avec : php artisan migrate:rollback
     * Elle supprime les tables
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
