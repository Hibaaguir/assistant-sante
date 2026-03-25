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
        // Charger les migrations organisées par priorité (001-008).
        $this->loadMigrationsFrom([
            database_path('migrations/001_authentification'),
            database_path('migrations/002_utilisateurs'),
            database_path('migrations/003_profil_sante'),
            database_path('migrations/004_donnees_sante'),
            database_path('migrations/005_medecins'),
            database_path('migrations/006_journal_sante'),
            database_path('migrations/007_notifications'),
            database_path('migrations/008_utilitaires'),
        ]);
    }
}
