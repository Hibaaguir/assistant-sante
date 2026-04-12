<?php
// Semoir principal pour initialiser les donnees de la base de donnees
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
        // Compte administrateur (creer s'il n'existe pas)
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

        // Compte utilisateur de test (creer s'il n'existe pas)
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
