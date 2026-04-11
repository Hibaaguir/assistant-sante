<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorInvitationController;
use App\Http\Controllers\Api\HealthDataController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\HealthProfileController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\TreatmentCatalogController;
use App\Http\Controllers\Api\UserAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (without Sanctum)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Authentication (public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/login',            [AuthController::class, 'login']);
    Route::post('/doctor/register',  [AuthController::class, 'registerDoctor']);
    Route::post('/doctor/login',     [AuthController::class, 'loginDoctor']);
    Route::post('/forgot-password',  [ForgotPasswordController::class, 'requestReset']);
    Route::post('/reset-password',   [ForgotPasswordController::class, 'resetPassword']);
});

// Routes treatment catalogs (public, used during profile creation)
Route::get('/treatment-catalogs/medication-types', [TreatmentCatalogController::class, 'medicationTypes']);
Route::get('/treatment-catalogs/medication-names',  [TreatmentCatalogController::class, 'medicationNames']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Session ---
    Route::get('/auth/user',         [AuthController::class, 'getCurrentUser']);
    Route::get('/auth/me',           [AuthController::class, 'getCurrentUser']);
    Route::post('/auth/logout',      [AuthController::class, 'logout']);

    // --- User Profile ---
    Route::prefix('user-profile')->group(function () {
        Route::get('/',                      [UserProfileController::class, 'show']);
        Route::put('/name',                   [UserProfileController::class, 'updateName']);
        Route::put('/photo',                 [UserProfileController::class, 'updatePhoto']);
        Route::delete('/photo',              [UserProfileController::class, 'deletePhoto']);
        Route::post('/change-password', [UserProfileController::class, 'changePassword']);
    });

    // --- Health Profile ---
    Route::prefix('health-profile')->group(function () {
        Route::post('/', [HealthProfileController::class, 'store']);
        Route::get('/',  [HealthProfileController::class, 'show']);
    });

    // --- Treatment Catalog ---
    Route::prefix('treatment-catalog')->group(function () {
        Route::get('/',  [TreatmentCatalogController::class, 'index']);
        Route::post('/', [TreatmentCatalogController::class, 'store']);
    });

    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/',          [NotificationController::class, 'index']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // --- Administration ---
    Route::prefix('admin/users')->group(function () {
        Route::get('/',               [UserAdminController::class, 'index']);
        Route::put('/{user}/status',  [UserAdminController::class, 'updateStatus']);
        Route::delete('/{user}',      [UserAdminController::class, 'destroy']);
    });

    // --- Journal Entries ---
    Route::prefix('journal')->group(function () {
        Route::get('/',                [JournalEntryController::class, 'index']);
        Route::post('/',               [JournalEntryController::class, 'store']);
        Route::get('/{journalEntry}',  [JournalEntryController::class, 'show']);
        Route::put('/{journalEntry}',  [JournalEntryController::class, 'update']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'destroy']);
    });

    // --- Health Data ---
    Route::prefix('health-data')->group(function () {
        Route::get('/overview',      [HealthDataController::class, 'overview']);
        Route::get('/vitals',        [HealthDataController::class, 'indexVitals']);
        Route::post('/vitals',       [HealthDataController::class, 'storeVital']);
        Route::get('/labs',          [HealthDataController::class, 'indexLabResults']);
        Route::post('/labs',         [HealthDataController::class, 'storeLabResult']);
        Route::put('/labs/{analysisResult}',    [HealthDataController::class, 'updateLabResult']);
        Route::delete('/labs/{analysisResult}', [HealthDataController::class, 'destroyLabResult']);
        Route::get('/treatment-checks',    [HealthDataController::class, 'indexTreatmentChecks']);
        Route::post('/treatment-checks/sync', [HealthDataController::class, 'syncTreatmentChecks']);
    });

    // --- Doctor Invitations ---
    Route::prefix('doctor-invitations')->group(function () {
        Route::get('/',    [DoctorInvitationController::class, 'index']);
        Route::post('/{invitation}/accept',   [DoctorInvitationController::class, 'accept']);
        Route::post('/{invitation}/reject',   [DoctorInvitationController::class, 'reject']);
        Route::get('/patients',                [DoctorInvitationController::class, 'indexPatients']);
        Route::get('/patients/{patient}',      [DoctorInvitationController::class, 'showPatient']);
        Route::post('/patients/{patient}/observations', [DoctorInvitationController::class, 'storeObservation']);
    });
});
