<?php
// Routes web de l'application
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/health-profile', 'health-profile');

// Les deux routes /registration et /register pointent vers la meme vue
Route::view('/registration', 'register');
Route::view('/register',    'register');