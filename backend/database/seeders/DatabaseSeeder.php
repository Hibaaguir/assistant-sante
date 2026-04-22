<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // ── Comptes système ──────────────────────────────────────────────
            AdminAccountSeeder::class,

            // ── Référentiel médicaments ──────────────────────────────────────
            TreatmentCatalogSeeder::class,

            // ── Utilisateurs ─────────────────────────────────────────────────
            DoctorsSeeder::class,
            PatientsSeeder::class,          // efface + recrée les données médicales

            // ── Traitements patients ──────────────────────────────────────────
            PatientTreatmentsSeeder::class,

            // ── Données de santé (30 jours) ───────────────────────────────────
            DailyHealthDataSeeder::class,   // HealthData, VitalSigns, AnalysisResults

            // ── Journal quotidien (30 jours) ──────────────────────────────────
            DailyJournalSeeder::class,      // JournalEntry, Meals, Activité, Tabac

            // ── Observance des traitements (30 jours) ─────────────────────────
            TreatmentChecksSeeder::class,   // TreatmentCheck, Notifications
        ]);
    }
}
