<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin account (create if not exists)
        $adminAccount = Account::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['password' => Hash::make('admin1234'), 'account_status' => 'active']
        );

        if (!User::where('account_id', $adminAccount->id)->exists()) {
            User::create([
                'account_id'    => $adminAccount->id,
                'name'          => 'Administrator',
                'date_of_birth' => null,
                'profile_photo' => null,
                'age'           => null,
                'role'          => 'admin',
                'specialty'     => null,
            ]);
        }

        // Test user account (create if not exists)
        $testAccount = Account::firstOrCreate(
            ['email' => 'test@example.com'],
            ['password' => Hash::make('password'), 'account_status' => 'active']
        );

        if (!User::where('account_id', $testAccount->id)->exists()) {
            User::create([
                'account_id'    => $testAccount->id,
                'name'          => 'Test User',
                'date_of_birth' => null,
                'profile_photo' => null,
                'age'           => null,
                'role'          => 'user',
                'specialty'     => null,
            ]);
        }

        $this->call([
            TreatmentCatalogSeeder::class,
        ]);
    }
}
