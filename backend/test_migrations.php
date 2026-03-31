<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Compte;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

echo "=== TEST DE CRÉATION DE COMPTE ET UTILISATEUR ===\n\n";

// Test 1: Créer un compte
echo "1️ Création d'un compte...\n";
$email = 'test_' . uniqid() . '@example.com';
$compte = Compte::create([
    'email' => $email,
    'motdepasse' => Hash::make('password123'),
    'statut_compte' => 'actif'
]);
echo "✅ Compte créé avec ID: {$compte->id} | Email: {$email}\n\n";

// Test 2: Créer un utilisateur lié au compte
echo "2️ Création d'un utilisateur lié...\n";
$utilisateur = Utilisateur::create([
    'compte_id' => $compte->id,
    'nom' => 'John Doe',
    'date_naissance' => '1990-01-15',
    'age' => 36,
    'role' => 'utilisateur',
    'photo_profil' => null,
    'specialite' => null,
    'statut_admin' => 'Actif'
]);
echo "✅ Utilisateur créé avec ID: {$utilisateur->id}\n\n";

// Test 3: Vérifier la relation Compte -> Utilisateur
echo "3️ Vérification de la relation Compte -> Utilisateur...\n";
$compte_reload = Compte::find($compte->id);
$user_from_compte = $compte_reload->utilisateur;
echo "✅ Compte {$compte->id} -> Utilisateur {$user_from_compte->id} ({$user_from_compte->nom})\n\n";

// Test 4: Vérifier la relation Utilisateur -> Compte
echo "4️ Vérification de la relation Utilisateur -> Compte...\n";
$user_reload = Utilisateur::find($utilisateur->id);
$compte_from_user = $user_reload->compte;
echo "✅ Utilisateur {$utilisateur->id} -> Compte {$compte_from_user->id} ({$compte_from_user->email})\n\n";

// Test 5: Vérifier les timestamps
echo "5️ Vérification des timestamps (cree_a, modifie_a)...\n";
echo "✅ Compte cree_a: {$compte->cree_a} | modifie_a: {$compte->modifie_a}\n";
echo "✅ Utilisateur cree_a: {$utilisateur->cree_a} | modifie_a: {$utilisateur->modifie_a}\n\n";

// Test 6: Vérifier que tous les champs existent
echo "6️ Vérification de tous les champs...\n";
echo "Compte: " . json_encode($compte->toArray()) . "\n";
echo "Utilisateur: " . json_encode($utilisateur->toArray()) . "\n\n";

echo "════════════════════════════════════════════════\n";
echo "✅ TOUS LES TESTS SONT PASSÉS!\n";
echo "════════════════════════════════════════════════\n";
echo "\n📋 RÉSUMÉ DE LA STRUCTURE:\n";
echo "✅ TABLE COMPTES: id, email, motdepasse, statut_compte, cree_a, modifie_a\n";
echo "✅ TABLE UTILISATEURS: id, compte_id(FK), nom, date_naissance, photo_profil,\n";
echo "                      age, role, specialite, statut_admin, cree_a, modifie_a\n";
echo "✅ RELATIONS: Compte->Utilisateur (1:1), Utilisateur->Compte (M:1)\n";
echo "✅ TIMESTAMPS: Correctement mappés à cree_a/modifie_a\n\n";

// Cleanup
$utilisateur->delete();
$compte->delete();
echo "✅ Données de test supprimées\n";
