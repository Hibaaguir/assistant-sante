<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            return;
        }

        $isNullable = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', (string) config('database.connections.mysql.database'))
            ->where('TABLE_NAME', 'doctor_invitations')
            ->where('COLUMN_NAME', 'doctor_user_id')
            ->value('IS_NULLABLE');

        if ($isNullable === 'YES') {
            return;
        }

        DB::statement('ALTER TABLE doctor_invitations MODIFY doctor_user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // No-op: reverting to NOT NULL can fail if existing rows contain NULL.
    }
};
