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
        // Load migrations organized by domain and dependency order.
        $this->loadMigrationsFrom([
            database_path('migrations/001_core_access'),
            database_path('migrations/010_users_accounts'),
            database_path('migrations/020_health_profile'),
            database_path('migrations/030_treatments_catalog'),
            database_path('migrations/040_health_tracking'),
            database_path('migrations/050_doctor_collaboration'),
            database_path('migrations/060_journal_lifestyle'),
            database_path('migrations/070_notifications'),
            database_path('migrations/080_infrastructure_queue'),
        ]);
    }
}
