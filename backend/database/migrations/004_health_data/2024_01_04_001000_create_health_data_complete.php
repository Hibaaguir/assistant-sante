<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('measured_at');
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('systolic_pressure')->nullable();
            $table->unsignedSmallInteger('diastolic_pressure')->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'measured_at']);
        });

        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('analysis_type', 120);
            $table->string('result_name', 120)->nullable();
            $table->decimal('value', 10, 2);
            $table->string('unit', 30)->nullable();
            $table->date('analysis_date');
            $table->timestamps();

            $table->index(['user_id', 'analysis_date']);
        });

        Schema::create('treatment_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained('treatments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_date');
            $table->string('medication_key', 120)->nullable();
            $table->enum('status', ['taken', 'not_taken', 'pending'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['treatment_id', 'check_date']);
            $table->index(['treatment_id', 'check_date']);
            $table->index(['user_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_checks');
        Schema::dropIfExists('analysis_results');
        Schema::dropIfExists('vital_signs');
    }
};