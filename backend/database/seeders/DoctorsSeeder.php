<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/** Crée les 3 comptes médecins de démonstration. */
class DoctorsSeeder extends MedicalSeeder
{
    public function run(): void
    {
        foreach ($this->doctors() as $doctor) {
            $account = Account::updateOrCreate(
                ['email' => strtolower($doctor['email'])],
                [
                    'password'       => Hash::make(self::DEFAULT_PASSWORD),
                    'account_status' => 'active',
                ]
            );

            $dob = Carbon::parse($doctor['date_of_birth']);

            User::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'name'          => $doctor['name'],
                    'date_of_birth' => $dob->toDateString(),
                    'profile_photo' => null,
                    'age'           => $dob->age,
                    'role'          => 'doctor',
                    'specialty'     => $doctor['specialty'],
                ]
            );
        }
    }
}
