<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\ProfilSanteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Legacy alias to keep existing clients functional.
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/profil-sante', [ProfilSanteController::class, 'store']);
    Route::get('/profil-sante', [ProfilSanteController::class, 'show']);

    Route::prefix('journal')->group(function () {
        Route::get('/', [JournalEntryController::class, 'index']);
        Route::post('/', [JournalEntryController::class, 'store']);
        Route::get('/{journalEntry}', [JournalEntryController::class, 'show']);
        Route::put('/{journalEntry}', [JournalEntryController::class, 'update']);
        Route::delete('/{journalEntry}', [JournalEntryController::class, 'destroy']);
    });
});
