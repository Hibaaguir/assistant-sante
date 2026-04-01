<?php
require 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app->boot();

echo "=== Checking Journal Tables ===\n";
echo "journal_quotidien: " . (Schema::hasTable('journal_quotidien') ? "EXISTS" : "MISSING") . "\n";
echo "repas: " . (Schema::hasTable('repas') ? "EXISTS" : "MISSING") . "\n";
echo "activites_physiques: " . (Schema::hasTable('activites_physiques') ? "EXISTS" : "MISSING") . "\n";
echo "tabacs: " . (Schema::hasTable('tabacs') ? "EXISTS" : "MISSING") . "\n";

// List all migrations
echo "\n=== All Tables ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    foreach ((array)$table as $name) {
        echo "- $name\n";
    }
}
