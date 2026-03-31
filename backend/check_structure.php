<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$db = $app->make('db');

echo "=== COMPTES TABLE ===\n";
$comptes = $db->select('SHOW COLUMNS FROM comptes');
foreach($comptes as $col) {
    echo "{$col->Field}: {$col->Type} | Null:{$col->Null} | Default:{$col->Default}\n";
}

echo "\n=== UTILISATEURS TABLE ===\n";
$users = $db->select('SHOW COLUMNS FROM utilisateurs');
foreach($users as $col) {
    echo "{$col->Field}: {$col->Type} | Null:{$col->Null} | Default:{$col->Default}\n";
}

echo "\n=== FOREIGN KEYS ===\n";
$fks = $db->select("SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND (TABLE_NAME = 'utilisateurs' OR TABLE_NAME = 'comptes')");
foreach($fks as $fk) {
    if($fk->REFERENCED_TABLE_NAME) {
        echo "{$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
}
