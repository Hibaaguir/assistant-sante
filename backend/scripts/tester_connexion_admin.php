<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

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

// Générer un token de test
$token = $admin->createToken('admin_test_token')->plainTextToken;
echo "\n✓ Token de test généré:\n";
echo "  $token\n";
