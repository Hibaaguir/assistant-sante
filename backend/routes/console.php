<?php

use App\Mail\DoctorInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Commandes Artisan
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Artisan::command('mail:test-invitation-medecin {email : Adresse e-mail du destinataire}', function (string $email) {
    $patient = new User([
        'name'  => 'Patient de test',
        'email' => 'patient-test@example.com',
    ]);

    Mail::to($email)->send(new DoctorInvitationMail($patient, $email));

    $this->info("Invitation e-mail envoyée à {$email}.");
})->purpose('Envoyer une invitation médecin via le service SMTP configuré');

// Alias anglais (compatibilité anciens clients)
Artisan::command('mail:test-doctor-invitation {email : Recipient email address}', function (string $email) {
    $this->call('mail:test-invitation-medecin', ['email' => $email]);
})->purpose('Alias of mail:test-invitation-medecin');

/*
|--------------------------------------------------------------------------
| Tâches planifiées
|--------------------------------------------------------------------------
*/

$tz = config('app.timezone');

Schedule::command('treatments:notify --mode=reminder')->timezone($tz)->dailyAt('08:00');
Schedule::command('treatments:notify --mode=missed')->timezone($tz)->dailyAt('21:00');