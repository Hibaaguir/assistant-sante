<?php use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\ProfilSanteController; 

Route::post('/register', [AuthController::class, 'register']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profil-sante', [ProfilSanteController::class, 'store']); 
    Route::get('/profil-sante', [ProfilSanteController::class, 'show']); 
});