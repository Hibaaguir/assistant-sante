<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/health-profile', 'health-profile');

// Both /registration and /register point to the same view
Route::view('/registration', 'register');
Route::view('/register',    'register');