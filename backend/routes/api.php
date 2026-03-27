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

    // --- Journal ---
    Route::prefix('journal')->group(function () {
        Route::get('/',                [JournalEntryController::class, 'index']);
        Route::post('/',               [JournalEntryController::class, 'store']);
        Route::get('/{journalEntry}',  [JournalEntryController::class, 'show']);
        Route::put('/{journalEntry}',  [JournalEntryController::class, 'update']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'destroy']);
    });

    // --- Données de santé (Health Data) ---
    Route::prefix('health-data')->group(function () {
        Route::get('/overview',      [HealthDataController::class, 'vueEnsemble']);
        Route::get('/vitals',        [HealthDataController::class, 'indexVitals']);
        Route::post('/vitals',       [HealthDataController::class, 'storeVital']);
        Route::get('/labs',          [HealthDataController::class, 'indexLabResults']);
        Route::post('/labs',         [HealthDataController::class, 'storeLabResult']);
        Route::put('/labs/{healthLabResult}',    [HealthDataController::class, 'updateLabResult']);
        Route::delete('/labs/{healthLabResult}', [HealthDataController::class, 'destroyLabResult']);
        Route::get('/treatment-checks',    [HealthDataController::class, 'indexTreatmentChecks']);
        Route::post('/treatment-checks/sync', [HealthDataController::class, 'syncTreatmentChecks']);
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