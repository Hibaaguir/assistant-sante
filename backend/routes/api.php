<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorInvitationController;
use App\Http\Controllers\Api\HealthDataController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\ProfilSanteController;
use Illuminate\Support\Facades\Route;

$routesAuthentification = function () {
    Route::post('/inscription', [AuthController::class, 'inscrire']);
    Route::post('/connexion', [AuthController::class, 'connecter']);
    Route::post('/medecin/inscription', [AuthController::class, 'inscrireMedecin']);
    Route::post('/medecin/connexion', [AuthController::class, 'connecterMedecin']);

    // Alias anglais conserves pour les anciens clients.
    Route::post('/register', [AuthController::class, 'inscrire']);
    Route::post('/login', [AuthController::class, 'connecter']);
    Route::post('/doctor/register', [AuthController::class, 'inscrireMedecin']);
    Route::post('/doctor/login', [AuthController::class, 'connecterMedecin']);
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

$routesInvitationsMedecins = function () {
    Route::get('/', [DoctorInvitationController::class, 'lister']);
    Route::post('/{doctorInvitation}/accepter', [DoctorInvitationController::class, 'accepter']);
    Route::post('/{doctorInvitation}/refuser', [DoctorInvitationController::class, 'refuser']);
    Route::get('/patients', [DoctorInvitationController::class, 'listerPatients']);
    Route::get('/patients/{patient}', [DoctorInvitationController::class, 'detailPatient']);

    // Alias anglais conserves pour les anciens clients.
    Route::post('/{doctorInvitation}/accept', [DoctorInvitationController::class, 'accepter']);
    Route::post('/{doctorInvitation}/reject', [DoctorInvitationController::class, 'refuser']);
};

// Routes publiques liees a l'authentification.
Route::prefix('auth')->group(function () use ($routesAuthentification) {
    $routesAuthentification();
});

// Alias racine conserves pour compatibilite.
Route::post('/inscription', [AuthController::class, 'inscrire']);
Route::post('/register', [AuthController::class, 'inscrire']);

// Routes protegees necessitant Laravel Sanctum.
Route::middleware('auth:sanctum')->group(function () use ($routesDonneesSante, $routesInvitationsMedecins) {
    Route::get('/auth/profil', [AuthController::class, 'utilisateurConnecte']);
    Route::post('/auth/deconnexion', [AuthController::class, 'deconnexion']);

    // Alias anglais conserves pour les anciens clients.
    Route::get('/auth/me', [AuthController::class, 'utilisateurConnecte']);
    Route::post('/auth/logout', [AuthController::class, 'deconnexion']);

    Route::post('/profil-sante', [ProfilSanteController::class, 'enregistrer']);
    Route::get('/profil-sante', [ProfilSanteController::class, 'afficher']);

    Route::prefix('journal')->group(function () {
        Route::get('/', [JournalEntryController::class, 'lister']);
        Route::post('/', [JournalEntryController::class, 'enregistrer']);
        Route::get('/{journalEntry}', [JournalEntryController::class, 'afficher']);
        Route::put('/{journalEntry}', [JournalEntryController::class, 'mettreAJour']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'supprimer']);
    });

    Route::prefix('donnees-sante')->group($routesDonneesSante);
    Route::prefix('health-data')->group($routesDonneesSante);

    Route::prefix('invitations-medecins')->group($routesInvitationsMedecins);
    Route::prefix('doctor-invitations')->group($routesInvitationsMedecins);
});
