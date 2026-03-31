<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "=== Comptes Table ===\n";
$cols = DB::select('SHOW COLUMNS FROM comptes');
echo "Columns: " . implode(', ', array_map(fn($c) => $c->Field, $cols)) . "\n";
echo "Count: " . DB::table('comptes')->count() . " records\n";

echo "\n=== Utilisateurs Table ===\n";
$cols = DB::select('SHOW COLUMNS FROM utilisateurs');
echo "Total columns: " . count($cols) . "\n";
echo "First 8 columns: " . implode(', ', array_slice(array_map(fn($c) => $c->Field, $cols), 0, 8)) . "\n";
echo "Count: " . DB::table('utilisateurs')->count() . " records\n";

echo "\n=== API Token Permission ===\n";
echo "API tokens table exists: " . (DB::table('personal_access_tokens')->count() > 0 ? 'Yes with ' . DB::table('personal_access_tokens')->count() . ' tokens' : 'No') . "\n";
