<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('treatment_id')->constrained('users')->cascadeOnDelete();
            $table->index(['user_id', 'check_date']);
        });

        // Backfill user_id from the treatment owner (treatments -> health_data -> user_id)
        DB::table('treatment_checks as tc')
            ->join('treatments as t', 't.id', '=', 'tc.treatment_id')
            ->join('health_data as hd', 'hd.id', '=', 't.health_data_id')
            ->select('tc.id as id', 'hd.user_id as user_id')
            ->orderBy('tc.id')
            ->chunk(1000, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('treatment_checks')
                        ->where('id', $row->id)
                        ->update(['user_id' => $row->user_id]);
                }
            });

        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('health_data_id');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->foreignId('health_data_id')->nullable()->after('treatment_id')->constrained('health_data')->nullOnDelete();
            $table->index(['health_data_id', 'check_date']);
        });

        // Restore health_data_id from treatment ownership link
        DB::table('treatment_checks as tc')
            ->join('treatments as t', 't.id', '=', 'tc.treatment_id')
            ->select('tc.id as id', 't.health_data_id as health_data_id')
            ->orderBy('tc.id')
            ->chunk(1000, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('treatment_checks')
                        ->where('id', $row->id)
                        ->update(['health_data_id' => $row->health_data_id]);
                }
            });

        Schema::table('treatment_checks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'check_date']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
