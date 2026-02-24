<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfilSanteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});

// Legacy alias to keep existing clients functional.
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profil-sante', [ProfilSanteController::class, 'store']);
    Route::get('/profil-sante', [ProfilSanteController::class, 'show']);
});
