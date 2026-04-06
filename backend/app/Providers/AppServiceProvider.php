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
        // Load migrations organized by priority (001-008).
        $this->loadMigrationsFrom([
            database_path('migrations/001_authentication'),
            database_path('migrations/002_users'),
            database_path('migrations/003_health_profile'),
            database_path('migrations/004_health_data'),
            database_path('migrations/005_doctor_invitations'),
            database_path('migrations/006_journal_entries'),
            database_path('migrations/007_notifications'),
            database_path('migrations/008_utilities'),
        ]);
    }
}
