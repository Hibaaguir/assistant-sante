<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) env('ADMIN_SEED_EMAIL', 'admin@gmail.com')));
        $password = (string) env('ADMIN_SEED_PASSWORD', 'admin1234');
        $name = trim((string) env('ADMIN_SEED_NAME', 'Administrator'));

        $account = Account::updateOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make($password),
                'account_status' => 'active',
            ],
        );

        User::updateOrCreate(
            ['account_id' => $account->id],
            [
                'name' => $name,
                'date_of_birth' => null,
                'profile_photo' => null,
                'age' => null,
                'role' => 'admin',
                'specialty' => null,
            ],
        );
    }
}