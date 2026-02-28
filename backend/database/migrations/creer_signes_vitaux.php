<?php
/*
| Cette migration cree la table des signes vitaux utilisateur.
| Elle stocke les mesures principales (rythme cardiaque, tension, saturation).
| Les index sont poses pour accelerer l'historique par utilisateur et date.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Creation de la table des signes vitaux.
    public function up(): void
    {
        Schema::create('health_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('measured_at')->index();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('systolic_pressure')->nullable();
            $table->unsignedSmallInteger('diastolic_pressure')->nullable();
            $table->decimal('oxygen_saturation', 4, 1)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'measured_at']);
        });
    }

    // Rollback standard.
    public function down(): void
    {
        Schema::dropIfExists('health_vitals');
    }
};
