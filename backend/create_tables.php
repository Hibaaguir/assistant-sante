<?php
putenv('APP_ENV=local');

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create comptes table
DB::statement("
    CREATE TABLE IF NOT EXISTS `comptes` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `email` varchar(255) NOT NULL UNIQUE,
        `motdepasse` varchar(255) NOT NULL,
        `statut_compte` enum('actif','inactif') NOT NULL DEFAULT 'actif',
        `cree_a` timestamp NULL,
        `modifie_a` timestamp NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Table created\n";

// Check users table for data
$usersExist = DB::table('users')->count();
echo "Users count: " . $usersExist . "\n";

// Add foreign key if needed
if ($usersExist > 0 && !DB::table('comptes')->count()) {
    // Generate dummy data for comptes if users exist but comptes is empty
    DB::statement("INSERT IGNORE INTO comptes (email, motdepasse, statut_compte, cree_a, modifie_a) VALUES ('dummy@example.com', 'hashed_password', 'actif', NOW(), NOW())");
    echo "Inserted dummy compte\n";
}

// Update users to reference comptes
if ($usersExist > 0) {
    DB::statement("
        UPDATE users u
        SET compte_id = (SELECT comptes.id FROM comptes WHERE comptes.email LIKE CONCAT(u.email, '%') LIMIT 1)
        WHERE compte_id IS NULL
    ");
    echo "Updated compte_ids\n";
}
