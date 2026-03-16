<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test de Connexion Admin ===\n\n";

$admin = App\Models\User::where('email', 'admin@gmail.com')->first();

if (!$admin) {
    echo "✗ Admin non trouvé\n";
    exit;
}

echo "Admin trouvé:\n";
echo "  Email: {$admin->email}\n";
echo "  Rôle: {$admin->role}\n";
echo "  Mot de passe hashé: {$admin->password}\n\n";

// Test le mot de passe
$password = 'admin1234';
$isCorrect = Illuminate\Support\Facades\Hash::check($password, $admin->password);

echo "Test du mot de passe 'admin1234':\n";
if ($isCorrect) {
    echo "  ✓ Mot de passe CORRECT\n";
} else {
    echo "  ✗ Mot de passe INCORRECT\n";
}

echo "\n=== Simulation de Connexion ===\n";

// Simuler la connexion
$email = 'admin@gmail.com';
$passwordTest = 'admin1234';

$user = App\Models\User::query()
    ->where('email', $email)
    ->where('role', 'administrateur')
    ->first();

if ($user && Illuminate\Support\Facades\Hash::check($passwordTest, $user->password)) {
    echo "✓ Connexion RÉUSSIE!\n";
    echo "  User: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Rôle: {$user->role}\n";
} else {
    echo "✗ Connexion ÉCHOUÉE\n";
    
    if (!$user) {
        echo "  Raison: Utilisateur 'administrateur' non trouvé\n";
    } else {
        echo "  Raison: Mot de passe incorrect\n";
    }
}
