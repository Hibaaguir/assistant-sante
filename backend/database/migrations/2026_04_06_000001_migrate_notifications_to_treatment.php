<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clear existing notifications — they were linked to User.
        // After this migration, notifications are linked to Treatment.
        DB::table('notifications')->truncate();
    }

    public function down(): void
    {
        // Nothing to reverse — data is gone.
    }
};
