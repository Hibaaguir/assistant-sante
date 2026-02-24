<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/profil-sante', 'profil-sante');

Route::view('/register', 'register');
