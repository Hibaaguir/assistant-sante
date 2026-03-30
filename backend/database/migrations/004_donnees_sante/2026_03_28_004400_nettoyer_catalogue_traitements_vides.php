<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_catalog_items')) {
            return;
        }

        DB::table('treatment_catalog_items')
            ->whereNull('name')
            ->orWhereNull('type')
            ->orWhere('name', '')
            ->orWhere('type', '')
            ->delete();
    }

    public function down(): void
    {
        // No-op: cleanup migration is intentionally irreversible.
    }
};
