<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_profile_id')->constrained('health_profiles')->cascadeOnDelete();
            $table->foreignId('treatment_catalog_id')->nullable()->constrained('treatment_catalogs')->nullOnDelete();
            
            // Treatment details
            $table->string('dose', 120)->nullable();
            $table->string('frequency', 120)->nullable(); 
            $table->unsignedTinyInteger('daily_doses')->nullable();
            
            // Treatment period
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Metadata
            $table->timestamps();
            
            // Indexes
            $table->index('health_profile_id');
            $table->index('treatment_catalog_id');
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
