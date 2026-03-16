<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Créer l'utilisateur administrateur
$user = new \App\Models\User();
$user->name = 'Administrateur';
$user->email = 'admin@gmail.com';
$user->password = bcrypt('admin1234');
$user->role = 'administrateur';
$user->statut_admin = 'Actif';

try {
    $user->save();
    echo "✓ Utilisateur admin créé avec succès\n";
    echo "  Email: admin@gmail.com\n";
    echo "  Mot de passe: admin1234\n";
    echo "  Rôle: administrateur\n";
} catch (\Illuminate\Database\QueryException $e) {
    echo "✗ Erreur lors de la création: " . $e->getMessage() . "\n";
}
