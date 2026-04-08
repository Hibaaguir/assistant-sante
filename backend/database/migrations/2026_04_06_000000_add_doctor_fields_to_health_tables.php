<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->text('doctor_observation')->nullable()->after('oxygen_saturation');
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            $table->text('doctor_note')->nullable()->after('analysis_date');
        });

        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->text('doctor_report')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropColumn('doctor_observation');
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            $table->dropColumn('doctor_note');
        });

        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->dropColumn('doctor_report');
        });
    }
};
