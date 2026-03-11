<?php

use App\Mail\DoctorInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('citation', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Artisan::command('mail:test-invitation-medecin {email : Adresse e-mail du destinataire}', function (string $email) {
    $patient = new User([
        'name' => 'Patient de test',
        'email' => 'patient-test@example.com',
    ]);

    \Illuminate\Support\Facades\Mail::to($email)->send(new DoctorInvitationMail($patient, $email));

    $this->info("Invitation e-mail envoyee a {$email}.");
})->purpose('Envoyer une vraie invitation medecin avec le service SMTP configure');

Artisan::command('mail:test-doctor-invitation {email : Recipient email address}', function (string $email) {
    $patient = new User([
        'name' => 'Patient de test',
        'email' => 'patient-test@example.com',
    ]);

    \Illuminate\Support\Facades\Mail::to($email)->send(new DoctorInvitationMail($patient, $email));

    $this->info("Invitation e-mail envoyee a {$email}.");
})->purpose('Envoyer une vraie invitation medecin avec le service SMTP configure');
