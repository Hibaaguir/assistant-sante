<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "=== Colonnes de la table utilisateurs ===\n";
$cols = DB::select('SHOW COLUMNS FROM utilisateurs');
foreach ($cols as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
