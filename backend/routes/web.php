<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/profil-sante', 'profil-sante');

// /inscription et /register pointent vers la même vue
Route::view('/inscription', 'register');
Route::view('/register',    'register');