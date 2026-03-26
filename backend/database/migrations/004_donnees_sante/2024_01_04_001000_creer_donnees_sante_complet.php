<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('measured_at');
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('systolic_pressure')->nullable();
            $table->unsignedSmallInteger('diastolic_pressure')->nullable();
            $table->decimal('oxygen_saturation', 4, 1)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'measured_at']);
        });

        Schema::create('health_lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('analysis_type', 120);
            $table->string('analysis_result', 120)->nullable();
            $table->decimal('value', 10, 2);
            $table->string('unit', 30)->nullable();
            $table->date('analysis_date');
            $table->timestamps();

            $table->index(['user_id', 'analysis_date']);
        });

        Schema::create('health_treatment_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('check_date');
            $table->string('medication_key', 120);
            $table->string('medication_name', 255);
            $table->string('dose', 120)->nullable();
            $table->boolean('taken')->default(false);
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'check_date', 'medication_key'], 'htc_user_date_medication_unique');
            $table->index(['user_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_treatment_checks');
        Schema::dropIfExists('health_lab_results');
        Schema::dropIfExists('health_vitals');
    }
};