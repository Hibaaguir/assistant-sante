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
        // Use raw SQL to populate treatment_id for treatment_checks
        // Find the treatment that was active on the check_date for each user
        DB::statement(
            'UPDATE treatment_checks tc
            SET treatment_id = (
                SELECT t.id FROM treatments t
                WHERE t.user_id = tc.user_id
                AND t.start_date <= tc.check_date
                AND (t.end_date IS NULL OR t.end_date >= tc.check_date)
                ORDER BY t.start_date DESC
                LIMIT 1
            )
            WHERE tc.treatment_id IS NULL
            AND EXISTS (
                SELECT 1 FROM treatments t
                WHERE t.user_id = tc.user_id
                AND t.start_date <= tc.check_date
                AND (t.end_date IS NULL OR t.end_date >= tc.check_date)
            )'
        );
        
        // For any remaining NULL treatment_id, link to any treatment for that user
        DB::statement(
            'UPDATE treatment_checks tc
            SET treatment_id = (
                SELECT t.id FROM treatments t
                WHERE t.user_id = tc.user_id
                LIMIT 1
            )
            WHERE tc.treatment_id IS NULL
            AND tc.user_id IN (
                SELECT user_id FROM treatments GROUP BY user_id
            )'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set treatment_id back to NULL
        DB::table('treatment_checks')->update(['treatment_id' => null]);
    }
};
