<?php
// Migration: creer les tables de suivi de sante (signes vitaux, resultats d'analyse, traitements, verifications de traitement)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->text('doctor_observation')->nullable();
            $table->foreignId('doctor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('user_id');
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_data_id')->nullable()->constrained('health_data')->nullOnDelete();
            $table->dateTime('measured_at');
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('systolic_pressure')->nullable();
            $table->unsignedSmallInteger('diastolic_pressure')->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->timestamps();

            $table->index(['health_data_id', 'measured_at']);
        });

        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_data_id')->nullable()->constrained('health_data')->nullOnDelete();
            $table->string('analysis_type', 120);
            $table->string('result_name', 120)->nullable();
            $table->decimal('value', 10, 2);
            $table->string('unit', 30)->nullable();
            $table->date('analysis_date');
            $table->timestamps();

            $table->index(['health_data_id', 'analysis_date']);
        });

        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_data_id')->constrained('health_data')->cascadeOnDelete();
            $table->foreignId('treatment_catalog_id')->nullable()->constrained('treatment_catalogs')->nullOnDelete();

            $table->string('dose', 120)->nullable();
            $table->string('frequency', 120)->nullable();
            $table->unsignedTinyInteger('daily_doses')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->index('health_data_id');
            $table->index('treatment_catalog_id');
            $table->index('start_date');
            $table->index(['health_data_id', 'treatment_catalog_id']);
        });

        Schema::create('treatment_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained('treatments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_date');
            $table->string('medication_key', 120)->nullable();
            $table->boolean('taken')->default(false);
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['treatment_id', 'check_date', 'medication_key'], 'treatment_checks_unique');
            $table->index(['treatment_id', 'check_date']);
            $table->index(['user_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_checks');
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('analysis_results');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('health_data');
    }
};
