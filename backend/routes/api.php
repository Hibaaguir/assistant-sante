<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthDataController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\MotDePasseOubliController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfilSanteController;
use App\Http\Controllers\Api\ProfilUtilisateurController;
use App\Http\Controllers\Api\TacheBienEtreController;
use App\Http\Controllers\Api\UtilisateurAdminController;
use Illuminate\Support\Facades\Route;

$routesAuthentification = function () {
    Route::post('/inscription', [AuthController::class, 'inscrire']);
    Route::post('/connexion', [AuthController::class, 'connecter']);
    Route::post('/oublier-mot-de-passe', [MotDePasseOubliController::class, 'demanderReinit']);
    Route::post('/reinitialiser-mot-de-passe', [MotDePasseOubliController::class, 'reinitialiserMotDePasse']);

    // Alias anglais conserves pour les anciens clients.
    Route::post('/register', [AuthController::class, 'inscrire']);
    Route::post('/login', [AuthController::class, 'connecter']);
};

$routesDonneesSante = function () {
    Route::get('/vue-ensemble', [HealthDataController::class, 'vueEnsemble']);

    Route::get('/signes-vitaux', [HealthDataController::class, 'listerSignesVitaux']);
    Route::post('/signes-vitaux', [HealthDataController::class, 'enregistrerSigneVital']);

    Route::get('/resultats-laboratoire', [HealthDataController::class, 'listerResultatsLaboratoire']);
    Route::post('/resultats-laboratoire', [HealthDataController::class, 'enregistrerResultatLaboratoire']);
    Route::put('/resultats-laboratoire/{healthLabResult}', [HealthDataController::class, 'mettreAJourResultatLaboratoire']);
    Route::delete('/resultats-laboratoire/{healthLabResult}', [HealthDataController::class, 'supprimerResultatLaboratoire']);

    Route::get('/controles-traitement', [HealthDataController::class, 'listerControlesTraitement']);
    Route::post('/controles-traitement/synchroniser', [HealthDataController::class, 'synchroniserControlesTraitement']);

    // Alias anglais conserves pour les anciens clients.
    Route::get('/overview', [HealthDataController::class, 'vueEnsemble']);
    Route::get('/vitals', [HealthDataController::class, 'listerSignesVitaux']);
    Route::post('/vitals', [HealthDataController::class, 'enregistrerSigneVital']);
    Route::get('/labs', [HealthDataController::class, 'listerResultatsLaboratoire']);
    Route::post('/labs', [HealthDataController::class, 'enregistrerResultatLaboratoire']);
    Route::put('/labs/{healthLabResult}', [HealthDataController::class, 'mettreAJourResultatLaboratoire']);
    Route::delete('/labs/{healthLabResult}', [HealthDataController::class, 'supprimerResultatLaboratoire']);
    Route::get('/treatment-checks', [HealthDataController::class, 'listerControlesTraitement']);
    Route::post('/treatment-checks/sync', [HealthDataController::class, 'synchroniserControlesTraitement']);
};



// Routes publiques liees a l'authentification.
Route::prefix('auth')->group(function () use ($routesAuthentification) {
    $routesAuthentification();
});

// Alias racine conserves pour compatibilite.
Route::post('/inscription', [AuthController::class, 'inscrire']);
Route::post('/register', [AuthController::class, 'inscrire']);

// Routes protegees necessitant Laravel Sanctum.
Route::middleware('auth:sanctum')->group(function () use ($routesDonneesSante) {
    Route::get('/auth/profil', [AuthController::class, 'utilisateurConnecte']);
    Route::post('/auth/deconnexion', [AuthController::class, 'deconnexion']);

    // Alias anglais conserves pour les anciens clients.
    Route::get('/auth/me', [AuthController::class, 'utilisateurConnecte']);
    Route::post('/auth/logout', [AuthController::class, 'deconnexion']);

    Route::prefix('profil-utilisateur')->group(function () {
        Route::get('/', [ProfilUtilisateurController::class, 'obtenirProfil']);
        Route::put('/nom', [ProfilUtilisateurController::class, 'mettreAJourNom']);
        Route::post('/changer-mot-de-passe', [ProfilUtilisateurController::class, 'changerMotDePasse']);
    });

    Route::post('/profil-sante', [ProfilSanteController::class, 'enregistrer']);
    Route::get('/profil-sante', [ProfilSanteController::class, 'afficher']);

    Route::get('/notifications', [NotificationController::class, 'lister']);
    Route::post('/notifications/{idNotification}/read', [NotificationController::class, 'marquerLue']);
    Route::post('/notifications/read-all', [NotificationController::class, 'toutMarquerLu']);

    Route::prefix('admin/utilisateurs')->group(function () {
        Route::get('/', [UtilisateurAdminController::class, 'lister']);
        Route::put('/{user}', [UtilisateurAdminController::class, 'mettreAJour']);
        Route::delete('/{user}', [UtilisateurAdminController::class, 'supprimer']);
    });

    Route::prefix('journal')->group(function () {
        Route::get('/', [JournalEntryController::class, 'lister']);
        Route::post('/', [JournalEntryController::class, 'enregistrer']);
        Route::get('/{journalEntry}', [JournalEntryController::class, 'afficher']);
        Route::put('/{journalEntry}', [JournalEntryController::class, 'mettreAJour']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'supprimer']);
    });

    Route::prefix('taches-bien-etre')->group(function () {
        Route::get('/', [TacheBienEtreController::class, 'lister']);
        Route::post('/', [TacheBienEtreController::class, 'creer']);
        Route::put('/{tacheBienEtre}', [TacheBienEtreController::class, 'mettreAJour']);
        Route::patch('/{tacheBienEtre}/statut', [TacheBienEtreController::class, 'basculerStatut']);
        Route::delete('/{tacheBienEtre}', [TacheBienEtreController::class, 'supprimer']);
    });

    Route::prefix('donnees-sante')->group($routesDonneesSante);
    Route::prefix('health-data')->group($routesDonneesSante);
});
