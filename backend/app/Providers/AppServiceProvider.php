<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Charger les migrations organisées par sous-dossiers.
        $this->loadMigrationsFrom([
            database_path('migrations/authentification'),
            database_path('migrations/utilisateurs'),
            database_path('migrations/medecins'),
            database_path('migrations/profil_sante'),
            database_path('migrations/journal_sante'),
            database_path('migrations/donnees_sante'),
            database_path('migrations/notifications'),
            database_path('migrations/utilitaires'),
        ]);
    }
}
