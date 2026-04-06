<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Delete any treatments with user_id that don't exist in users table
        $invalidUserIds = DB::table('treatments')
            ->select('treatments.id')
            ->leftJoin('users', 'treatments.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->pluck('treatments.id')
            ->toArray();
        
        if (!empty($invalidUserIds)) {
            DB::table('treatments')->whereIn('id', $invalidUserIds)->delete();
        }

        // Make sure user_id is NOT NULL and add foreign key
        Schema::table('treatments', function (Blueprint $table) {
            // Change user_id to NOT NULL if it's still nullable
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // Add foreign key for user_id
        Schema::table('treatments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Add composite index for user_id and treatment_catalog_id
        Schema::table('treatments', function (Blueprint $table) {
            $table->index(['user_id', 'treatment_catalog_id']);
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'treatment_catalog_id']);
        });

        // This down is incomplete as restoring the old structure would be complex
        // The previous migration should be the one to rollback
    }
};
