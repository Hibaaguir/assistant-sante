<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chronic_disease_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('name');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronic_disease_catalog_items');
    }
};
