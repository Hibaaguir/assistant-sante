<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->dropColumn(['general_observation', 'general_observation_updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->text('general_observation')->nullable();
            $table->timestamp('general_observation_updated_at')->nullable();
        });
    }
};
