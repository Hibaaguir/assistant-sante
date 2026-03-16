<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Supprimer l'ancien admin s'il existe
\App\Models\User::where('email', 'admin@gmail.com')->delete();

// Créer le nouvel admin
$admin = \App\Models\User::create([
    'name' => 'Administrateur',
    'email' => 'admin@gmail.com',
    'password' => \Illuminate\Support\Facades\Hash::make('admin1234'),
    'role' => 'administrateur',
    'statut_admin' => 'Actif',
]);

echo "✓ Admin créé:\n";
echo "  ID: {$admin->id}\n";
echo "  Email: {$admin->email}\n";
echo "  Rôle: {$admin->role}\n";
echo "  Mot de passe: ✓ Hashé\n";
