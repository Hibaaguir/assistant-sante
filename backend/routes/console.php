<?php
// Routes de commandes console de l'application
use App\Mail\DoctorInvitationMail;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test-doctor-invitation {email : Recipient email address}', function (string $email) {
    // Creer un compte et un utilisateur temporaire pour le patient de test
    $account = new Account([
        'email' => 'patient-test@example.com',
        'password' => 'test',
        'account_status' => 'active',
    ]);

    $patient = new User([
        'name' => 'Test Patient',
        'account_id' => null, // Non persiste
    ]);

    // Definir la relation manuellement
    $patient->setRelation('account', $account);

    Mail::to($email)->send(new DoctorInvitationMail($patient, $email));

    $this->info("Invitation email sent to {$email}.");
})->purpose('Send a doctor invitation via the configured SMTP service');



$tz = config('app.timezone');

Schedule::command('treatments:notify --mode=reminder')->timezone($tz)->dailyAt('08:00');
Schedule::command('treatments:notify --mode=missed')->timezone($tz)->dailyAt('21:00');