<?php
// Migration: creer la table des traitements
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            // FK added in a later migration because health_data is created in domain 040.
            $table->unsignedBigInteger('health_data_id');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
