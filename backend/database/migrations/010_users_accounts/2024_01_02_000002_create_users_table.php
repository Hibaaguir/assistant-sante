<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('name', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->longText('profile_photo')->nullable();
            $table->integer('age')->nullable();
            $table->string('role', 50)->default('user');
            $table->string('specialty', 255)->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
