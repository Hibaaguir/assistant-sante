<?php
use Illuminate\Support\Facades\Schedule;

$tz = config('app.timezone');

Schedule::command('treatments:notify --mode=reminder')->timezone($tz)->dailyAt('08:00');
Schedule::command('treatments:notify --mode=missed')->timezone($tz)->dailyAt('21:00');