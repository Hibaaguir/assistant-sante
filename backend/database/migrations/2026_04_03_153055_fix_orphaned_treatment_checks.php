<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete all orphaned treatment_checks (those with NULL or non-existent treatment_id)
        DB::table('treatment_checks')
            ->where(function ($query) {
                $query->whereNull('treatment_id')
                    ->orWhereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('treatments')
                            ->whereColumn('treatments.id', 'treatment_checks.treatment_id');
                    });
            })
            ->delete();

        // Make treatment_id NOT NULL
        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->unsignedBigInteger('treatment_id')->nullable(false)->change();
        });

        // Ensure foreign key constraint exists
        Schema::table('treatment_checks', function (Blueprint $table) {
            // Drop old FK if exists and recreate it
            try {
                $table->dropForeign(['treatment_id']);
            } catch (\Exception $e) {
                // FK might not exist
            }
            
            $table->foreign('treatment_id')
                ->references('id')
                ->on('treatments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make treatment_id nullable again
        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->unsignedBigInteger('treatment_id')->nullable()->change();
        });
    }
};
