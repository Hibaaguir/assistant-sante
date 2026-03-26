<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorInvitationController;
use App\Http\Controllers\Api\HealthDataController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\MotDePasseOubliController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfilSanteController;
use App\Http\Controllers\Api\ProfilUtilisateurController;
use App\Http\Controllers\Api\UtilisateurAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentification (publique)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/inscription',          [AuthController::class, 'inscrire']);
    Route::post('/connexion',            [AuthController::class, 'connecter']);
    Route::post('/medecin/inscription',  [AuthController::class, 'inscrireMedecin']);
    Route::post('/medecin/connexion',    [AuthController::class, 'connecterMedecin']);

    Route::post('/oublier-mot-de-passe',    [MotDePasseOubliController::class, 'demanderReinit']);
    Route::post('/reinitialiser-mot-de-passe', [MotDePasseOubliController::class, 'reinitialiserMotDePasse']);

    // Alias anglais (compatibilité anciens clients)
    Route::post('/register',         [AuthController::class, 'inscrire']);
    Route::post('/login',            [AuthController::class, 'connecter']);
    Route::post('/doctor/register',  [AuthController::class, 'inscrireMedecin']);
    Route::post('/doctor/login',     [AuthController::class, 'connecterMedecin']);
});

// Alias racine (compatibilité anciens clients)
Route::post('/inscription', [AuthController::class, 'inscrire']);
Route::post('/register',    [AuthController::class, 'inscrire']);

/*
|--------------------------------------------------------------------------
| Routes protégées (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Session ---
    Route::get('/auth/profil',       [AuthController::class, 'utilisateurConnecte']);
    Route::post('/auth/deconnexion', [AuthController::class, 'deconnexion']);
    Route::get('/auth/me',           [AuthController::class, 'utilisateurConnecte']);   // alias
    Route::post('/auth/logout',      [AuthController::class, 'deconnexion']);           // alias

    // --- Profil utilisateur ---
    Route::prefix('profil-utilisateur')->group(function () {
        Route::get('/',                      [ProfilUtilisateurController::class, 'obtenirProfil']);
        Route::put('/nom',                   [ProfilUtilisateurController::class, 'mettreAJourNom']);
        Route::put('/photo',                 [ProfilUtilisateurController::class, 'mettreAJourPhoto']);
        Route::delete('/photo',              [ProfilUtilisateurController::class, 'supprimerPhoto']);
        Route::post('/changer-mot-de-passe', [ProfilUtilisateurController::class, 'changerMotDePasse']);
    });

    // --- Profil santé ---
    Route::post('/profil-sante', [ProfilSanteController::class, 'enregistrer']);
    Route::get('/profil-sante',  [ProfilSanteController::class, 'afficher']);

    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/',                          [NotificationController::class, 'lister']);
        Route::post('/{idNotification}/read',    [NotificationController::class, 'marquerLue']);
        Route::post('/read-all',                 [NotificationController::class, 'toutMarquerLu']);
    });

    // --- Administration ---
    Route::prefix('admin/utilisateurs')->group(function () {
        Route::get('/',               [UtilisateurAdminController::class, 'lister']);
        Route::put('/{user}/statut',  [UtilisateurAdminController::class, 'mettreAJourStatut']);
        Route::delete('/{user}',      [UtilisateurAdminController::class, 'supprimer']);
    });

    // --- Journal ---
    Route::prefix('journal')->group(function () {
        Route::get('/',                [JournalEntryController::class, 'lister']);
        Route::post('/',               [JournalEntryController::class, 'enregistrer']);
        Route::get('/{journalEntry}',  [JournalEntryController::class, 'afficher']);
        Route::put('/{journalEntry}',  [JournalEntryController::class, 'mettreAJour']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'supprimer']);
    });

    // --- Données de santé (FR + EN) ---
    foreach (['donnees-sante', 'health-data'] as $prefix) {
        Route::prefix($prefix)->group(function () {
            Route::get('/vue-ensemble',  [HealthDataController::class, 'vueEnsemble']);
            Route::get('/overview',      [HealthDataController::class, 'vueEnsemble']);    // alias

            Route::get('/signes-vitaux', [HealthDataController::class, 'listerSignesVitaux']);
            Route::post('/signes-vitaux', [HealthDataController::class, 'enregistrerSigneVital']);
            Route::get('/vitals',        [HealthDataController::class, 'listerSignesVitaux']);   // alias
            Route::post('/vitals',       [HealthDataController::class, 'enregistrerSigneVital']); // alias

            Route::get('/resultats-laboratoire',              [HealthDataController::class, 'listerResultatsLaboratoire']);
            Route::post('/resultats-laboratoire',             [HealthDataController::class, 'enregistrerResultatLaboratoire']);
            Route::put('/resultats-laboratoire/{healthLabResult}',    [HealthDataController::class, 'mettreAJourResultatLaboratoire']);
            Route::delete('/resultats-laboratoire/{healthLabResult}', [HealthDataController::class, 'supprimerResultatLaboratoire']);
            Route::get('/labs',          [HealthDataController::class, 'listerResultatsLaboratoire']);    // alias
            Route::post('/labs',         [HealthDataController::class, 'enregistrerResultatLaboratoire']); // alias
            Route::put('/labs/{healthLabResult}',    [HealthDataController::class, 'mettreAJourResultatLaboratoire']);    // alias
            Route::delete('/labs/{healthLabResult}', [HealthDataController::class, 'supprimerResultatLaboratoire']);      // alias

            Route::get('/controles-traitement',               [HealthDataController::class, 'listerControlesTraitement']);
            Route::post('/controles-traitement/synchroniser', [HealthDataController::class, 'synchroniserControlesTraitement']);
            Route::get('/treatment-checks',    [HealthDataController::class, 'listerControlesTraitement']);         // alias
            Route::post('/treatment-checks/sync', [HealthDataController::class, 'synchroniserControlesTraitement']); // alias
        });
    }

    // --- Invitations médecins (FR + EN) ---
    foreach (['invitations-medecins', 'doctor-invitations'] as $prefix) {
        Route::prefix($prefix)->group(function () {
            Route::get('/',    [DoctorInvitationController::class, 'lister']);
            Route::post('/{doctorInvitation}/accepter', [DoctorInvitationController::class, 'accepter']);
            Route::post('/{doctorInvitation}/refuser',  [DoctorInvitationController::class, 'refuser']);
            Route::post('/{doctorInvitation}/accept',   [DoctorInvitationController::class, 'accepter']); // alias
            Route::post('/{doctorInvitation}/reject',   [DoctorInvitationController::class, 'refuser']);  // alias

            Route::get('/patients',                                        [DoctorInvitationController::class, 'listerPatients']);
            Route::get('/patients/{patient}',                              [DoctorInvitationController::class, 'detailPatient']);
            Route::put('/patients/{patient}/observation-generale',         [DoctorInvitationController::class, 'enregistrerObservationGenerale']);
        });
    }
});