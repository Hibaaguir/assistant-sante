<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Vérification de la Configuration Admin ===\n\n";

// Vérifier l'admin en BD
$admin = App\Models\User::where('email', 'admin@gmail.com')->first();
if ($admin) {
    echo "✓ Admin trouvé en base de données:\n";
    echo "  ID: {$admin->id}\n";
    echo "  Email: {$admin->email}\n";
    echo "  Nom: {$admin->name}\n";
    echo "  Rôle: {$admin->role}\n";
    echo "  Statut: {$admin->statut_admin}\n\n";
} else {
    echo "✗ ERREUR: Admin non trouvé en base de données\n\n";
}

// Compter les utilisateurs
$total = App\Models\User::count();
echo "Total d'utilisateurs en BD: $total\n";

// Lister tous les utilisateurs
$users = App\Models\User::all();
echo "\nListe de tous les utilisateurs:\n";
foreach ($users as $user) {
    echo "  - {$user->email} ({$user->role})\n";
}
