<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_quotidien')) {
            Schema::create('journal_quotidien', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnDelete();
                $table->date('date_entree');

                // État général
                $table->unsignedTinyInteger('sommeil')->nullable();
                $table->unsignedTinyInteger('stress')->nullable();
                $table->unsignedTinyInteger('energie')->nullable();
                $table->unsignedTinyInteger('cafeine')->default(0);
                $table->decimal('hydratation', 4, 1)->default(0.0);

                // alcool
                $table->boolean('alcool')->default(false);
                $table->unsignedSmallInteger('nb_verres_alcool')->nullable();

                $table->timestamps();
                $table->unique(['id_utilisateur', 'date_entree']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_quotidien');
    }
};