<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('taches_bien_etre')) {
            Schema::create('taches_bien_etre', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('titre', 180);
                $table->enum('categorie', ['bien-etre', 'sante', 'fitness', 'nutrition'])->default('bien-etre');
                $table->date('date_echeance')->nullable();
                $table->boolean('est_complete')->default(false);
                $table->timestamp('terminee_le')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'categorie']);
                $table->index(['user_id', 'est_complete']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('taches_bien_etre');
    }
};
