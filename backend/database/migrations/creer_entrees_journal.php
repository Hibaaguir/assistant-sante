<?php
/*
| Cette migration cree la table du journal quotidien utilisateur.
| Elle stocke sommeil, stress, energie, alimentation, activite et habitudes.
| La contrainte unique user/date impose une seule entree par jour.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Structure principale du journal et index de recherche.
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date')->index();

            $table->unsignedTinyInteger('sleep')->nullable();
            $table->unsignedTinyInteger('stress')->nullable();
            $table->unsignedTinyInteger('energy')->nullable();
            $table->enum('sugar', ['low', 'medium', 'high'])->default('low');
            $table->unsignedTinyInteger('caffeine')->default(0);
            $table->decimal('hydration', 4, 1)->default(0.0);

            $table->json('meals')->nullable();
            $table->string('activity_type', 120)->nullable();
            $table->unsignedSmallInteger('activity_duration')->nullable();
            $table->enum('intensity', ['light', 'medium', 'high'])->default('medium');

            $table->boolean('tobacco')->default(false);
            $table->boolean('alcohol')->default(false);
            $table->json('tobacco_types')->nullable();
            $table->unsignedSmallInteger('cigarettes_per_day')->nullable();
            $table->string('vape_frequency', 50)->nullable();
            $table->unsignedSmallInteger('vape_liquid_ml')->nullable();
            $table->unsignedSmallInteger('alcohol_drinks')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'entry_date']);
        });
    }

    // Rollback standard.
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
