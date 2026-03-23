<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('citation', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Schedule::command('treatments:notify --mode=reminder')->dailyAt('08:00');
Schedule::command('treatments:notify --mode=missed')->dailyAt('21:00');
