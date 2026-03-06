<?php

use App\Mail\DoctorInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test-doctor-invitation {email : Recipient email address}', function (string $email) {
    $patient = new User([
        'name' => 'Patient Test',
        'email' => 'patient-test@example.com',
    ]);

    \Illuminate\Support\Facades\Mail::to($email)->send(new DoctorInvitationMail($patient, $email));

    $this->info("Invitation email sent to {$email}.");
})->purpose('Send a real doctor invitation email using the configured SMTP mailer');
