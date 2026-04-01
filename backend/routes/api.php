<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorInvitationController;
use App\Http\Controllers\Api\DonneesSanteController;
use App\Http\Controllers\Api\JournalQuotidienController;
use App\Http\Controllers\Api\MotDePasseOubliController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfilSanteController;
use App\Http\Controllers\Api\ProfilUtilisateurController;
use App\Http\Controllers\Api\TreatmentCatalogController;
use App\Http\Controllers\Api\UtilisateurAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (sans Sanctum)
|--------------------------------------------------------------------------
*/
Route::post('/doctor-invitations/create', [DoctorInvitationController::class, 'createInvitation']);

/*
|--------------------------------------------------------------------------
| Authentification (publique)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/login',            [AuthController::class, 'login']);
    Route::post('/doctor/register',  [AuthController::class, 'registerDoctor']);
    Route::post('/doctor/login',     [AuthController::class, 'loginDoctor']);
    Route::post('/forgot-password',  [MotDePasseOubliController::class, 'requestReset']);
    Route::post('/reset-password',   [MotDePasseOubliController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Routes protégées (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Session ---
    Route::get('/auth/user',         [AuthController::class, 'getCurrentUser']);
    Route::get('/auth/me',           [AuthController::class, 'getCurrentUser']);
    Route::post('/auth/logout',      [AuthController::class, 'logout']);

    // --- Profil utilisateur ---
    Route::prefix('profil-utilisateur')->group(function () {
        Route::get('/',                      [ProfilUtilisateurController::class, 'getProfile']);
        Route::put('/nom',                   [ProfilUtilisateurController::class, 'updateName']);
        Route::put('/photo',                 [ProfilUtilisateurController::class, 'updatePhoto']);
        Route::delete('/photo',              [ProfilUtilisateurController::class, 'deletePhoto']);
        Route::post('/changer-mot-de-passe', [ProfilUtilisateurController::class, 'changePassword']);
    });

    // --- Profil santé ---
    Route::prefix('profil-sante')->group(function () {
        Route::post('/', [ProfilSanteController::class, 'store']);
        Route::get('/',  [ProfilSanteController::class, 'show']);
    });

    // --- Catalogue traitements ---
    Route::prefix('treatment-catalog')->group(function () {
        Route::get('/',  [TreatmentCatalogController::class, 'index']);
        Route::post('/', [TreatmentCatalogController::class, 'store']);
    });

    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/',                          [NotificationController::class, 'index']);
        Route::post('/{idNotification}/read',    [NotificationController::class, 'markAsRead']);
        Route::post('/read-all',                 [NotificationController::class, 'markAllAsRead']);
    });

    // --- Administration ---
    Route::prefix('admin/utilisateurs')->group(function () {
        Route::get('/',               [UtilisateurAdminController::class, 'index']);
        Route::put('/{user}/statut',  [UtilisateurAdminController::class, 'updateStatus']);
        Route::delete('/{user}',      [UtilisateurAdminController::class, 'destroy']);
    });

    // --- Journal Quotidien ---
    Route::prefix('journal')->group(function () {
        Route::get('/',                [JournalQuotidienController::class, 'index']);
        Route::post('/',               [JournalQuotidienController::class, 'store']);
        Route::get('/{journalQuotidien}',  [JournalQuotidienController::class, 'show']);
        Route::put('/{journalQuotidien}',  [JournalQuotidienController::class, 'update']);
        Route::delete('/{journalQuotidien}', [JournalQuotidienController::class, 'destroy']);
    });

    // --- Données de santé ---
    Route::prefix('donnees-sante')->group(function () {
        Route::get('/overview',      [DonneesSanteController::class, 'vueEnsemble']);
        Route::get('/vitals',        [DonneesSanteController::class, 'indexVitals']);
        Route::post('/vitals',       [DonneesSanteController::class, 'storeVital']);
        Route::get('/labs',          [DonneesSanteController::class, 'indexLabResults']);
        Route::post('/labs',         [DonneesSanteController::class, 'storeLabResult']);
        Route::put('/labs/{resultatAnalyse}',    [DonneesSanteController::class, 'updateLabResult']);
        Route::delete('/labs/{resultatAnalyse}', [DonneesSanteController::class, 'destroyLabResult']);
        Route::get('/treatment-checks',    [DonneesSanteController::class, 'indexTreatmentChecks']);
        Route::post('/treatment-checks/sync', [DonneesSanteController::class, 'syncTreatmentChecks']);
    });

    // --- Doctor Invitations ---
    Route::prefix('doctor-invitations')->group(function () {
        Route::get('/',    [DoctorInvitationController::class, 'index']);
        Route::post('/{doctorInvitation}/accept',   [DoctorInvitationController::class, 'accept']);
        Route::post('/{doctorInvitation}/reject',   [DoctorInvitationController::class, 'reject']);
        Route::get('/patients',                      [DoctorInvitationController::class, 'indexPatients']);
        Route::get('/patients/{patient}',            [DoctorInvitationController::class, 'showPatient']);
        Route::put('/patients/{patient}/observation', [DoctorInvitationController::class, 'storeObservation']);
    });
});
