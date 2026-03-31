<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Drop tables
try {
    DB::statement('DROP TABLE IF EXISTS utilisateurs');
    echo "utilisateurs dropped\n";
} catch (Exception $e) {
    echo "utilisateurs drop failed: " . $e->getMessage() . "\n";
}

try {
    DB::statement('DROP TABLE IF EXISTS users');
    echo "users dropped\n";
} catch (Exception $e) {
    echo "users drop failed: " . $e->getMessage() . "\n";
}

try {
    DB::statement('DROP TABLE IF EXISTS comptes');
    echo "comptes dropped\n";
} catch (Exception $e) {
    echo "comptes drop failed: " . $e->getMessage() . "\n";
}

// Clear migration records
try {
    DB::table('migrations')
        ->whereIn('migration', [
            '2026_03_30_000000_creer_table_comptes_et_modifier_users',
            '2026_03_30_000001_renommer_colonnes_en_francais'
        ])
        ->delete();
    echo "Migration records cleared\n";
} catch (Exception $e) {
    echo "Migration clear failed: " . $e->getMessage() . "\n";
}
