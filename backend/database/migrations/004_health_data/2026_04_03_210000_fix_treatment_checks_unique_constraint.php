<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            // Drop the old unique constraint that only covers (treatment_id, check_date)
            // which prevents multiple doses per day for the same treatment
            $table->dropUnique('treatment_checks_treatment_id_check_date_unique');

            // New unique constraint: one record per treatment + date + dose key
            $table->unique(['treatment_id', 'check_date', 'medication_key'], 'treatment_checks_unique');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->dropUnique('treatment_checks_unique');
            $table->unique(['treatment_id', 'check_date']);
        });
    }
};
