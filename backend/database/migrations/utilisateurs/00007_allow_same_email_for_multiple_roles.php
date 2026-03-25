<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
        } catch (\Throwable $e) {
            // Index may already be absent.
        }

        try {
            DB::statement('ALTER TABLE users ADD UNIQUE users_email_role_unique (email, role)');
        } catch (\Throwable $e) {
            // Index may already exist.
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE users DROP INDEX users_email_role_unique');
        } catch (\Throwable $e) {
            // Index may already be absent.
        }

        try {
            DB::statement('ALTER TABLE users ADD UNIQUE users_email_unique (email)');
        } catch (\Throwable $e) {
            // Restoring the old unique index can fail if duplicates now exist.
        }
    }
};
