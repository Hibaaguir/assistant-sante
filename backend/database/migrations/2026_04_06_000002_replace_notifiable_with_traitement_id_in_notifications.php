<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear existing data before changing structure
        DB::table('notifications')->truncate();

        Schema::table('notifications', function (Blueprint $table) {
            // Remove the polymorphic columns
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Add direct foreign key to treatments
            $table->unsignedBigInteger('traitement_id')->after('type');
            $table->foreign('traitement_id')->references('id')->on('treatments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        DB::table('notifications')->truncate();

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['traitement_id']);
            $table->dropColumn('traitement_id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notifiable_type')->after('type');
            $table->unsignedBigInteger('notifiable_id')->after('notifiable_type');
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }
};
