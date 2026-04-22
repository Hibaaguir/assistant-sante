<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use App\Models\User;
use Carbon\Carbon;

/**
 * Génère 30 jours d'entrées de journal par patient :
 * JournalEntry, repas (Meals), activité physique, tabac.
 */
class DailyJournalSeeder extends MedicalSeeder
{
    public function run(): void
    {
        $this->faker = fake('fr_FR');
        $startDate   = Carbon::today()->subDays(29)->startOfDay();

        foreach ($this->patients() as $patientIndex => $patientData) {
            $user = User::query()
                ->whereHas('account', fn ($q) => $q->where('email', strtolower($patientData['email'])))
                ->first();

            if (! $user) {
                continue;
            }

            for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
                $date    = $startDate->copy()->addDays($dayOffset);
                $metrics = $this->buildDailyMetrics($patientData, $patientIndex, $dayOffset, $date);

                $journal = JournalEntry::create([
                    'user_id'        => $user->id,
                    'entry_date'     => $date->toDateString(),
                    'sleep'          => $metrics['sleep'],
                    'stress'         => $metrics['stress'],
                    'energy'         => $metrics['energy'],
                    'caffeine'       => $metrics['caffeine'],
                    'hydration'      => $metrics['hydration'],
                    'sugar_intake'   => $metrics['sugar_intake'],
                    'alcohol'        => $metrics['alcohol'],
                    'alcohol_glasses'=> $metrics['alcohol_glasses'],
                ]);

                $this->seedMeals($journal, $metrics);
                $this->seedPhysicalActivity($journal, $patientIndex, $dayOffset, $metrics);
                $this->seedTobacco($journal, $patientData, $metrics);
            }
        }
    }

    // ─── Repas ────────────────────────────────────────────────────────────────

    private function seedMeals(JournalEntry $journal, array $metrics): void
    {
        $breakfasts = [
            ['Porridge a la banane et amandes', 390],
            ['Omelette aux fines herbes et pain complet', 420],
            ['Yaourt nature, fruits rouges et noix', 360],
            ['Tartines completes, avocat et fromage frais', 410],
        ];
        $lunches = [
            ['Poulet grille, quinoa et legumes de saison', 620],
            ['Soupe de lentilles et salade composee', 540],
            ['Poisson au four, riz complet et brocolis', 600],
            ['Couscous legumes et pois chiches', 640],
        ];
        $dinners = [
            ['Poelee de legumes et dinde', 530],
            ['Veloute de courgettes et tartine proteinee', 500],
            ['Salade composee, oeufs et pommes de terre', 560],
            ['Ratatouille maison et filet de poisson', 520],
        ];
        $snacks = [
            ['Pomme et amandes', 180],
            ['Fromage blanc et graines de chia', 160],
            ['Carottes croquantes et houmous', 190],
            ['Poire et noix', 170],
        ];

        [$bDesc, $bCal] = $breakfasts[$this->faker->numberBetween(0, count($breakfasts) - 1)];
        [$lDesc, $lCal] = $lunches[$this->faker->numberBetween(0, count($lunches) - 1)];
        [$dDesc, $dCal] = $dinners[$this->faker->numberBetween(0, count($dinners) - 1)];

        $journal->meals()->create(['meal_type' => 'breakfast', 'description' => $bDesc, 'calories' => $bCal]);
        $journal->meals()->create(['meal_type' => 'lunch',     'description' => $lDesc, 'calories' => $lCal]);
        $journal->meals()->create(['meal_type' => 'dinner',    'description' => $dDesc, 'calories' => $dCal]);

        if ($metrics['stress'] >= 7 || $this->faker->boolean(35)) {
            [$sDesc, $sCal] = $snacks[$this->faker->numberBetween(0, count($snacks) - 1)];
            $journal->meals()->create(['meal_type' => 'snack', 'description' => $sDesc, 'calories' => $sCal]);
        }
    }

    // ─── Activité physique ────────────────────────────────────────────────────

    private function seedPhysicalActivity(JournalEntry $journal, int $patientIndex, int $dayOffset, array $metrics): void
    {
        if ((($dayOffset + $patientIndex) % 3) === 0 && $metrics['energy'] <= 4) {
            return;
        }

        $activities  = ['marche rapide', 'velo', 'yoga', 'renforcement musculaire', 'natation'];
        $activityType = $activities[($dayOffset + $patientIndex) % count($activities)];
        $duration     = 25 + ((($dayOffset + $patientIndex) % 5) * 10);

        $intensity = 'low';
        if ($duration >= 45) {
            $intensity = 'medium';
        }
        if ($duration >= 60) {
            $intensity = 'high';
        }

        $journal->physicalActivities()->create([
            'activity_type'    => $activityType,
            'duration_minutes' => $duration,
            'intensity'        => $intensity,
        ]);
    }

    // ─── Tabac ────────────────────────────────────────────────────────────────

    private function seedTobacco(JournalEntry $journal, array $patientData, array $metrics): void
    {
        if (! $patientData['smoker']) {
            return;
        }

        $journal->tobacco()->create([
            'tobacco_type'      => 'cigarette',
            'cigarettes_per_day'=> max(2, min(20, 5 + ($metrics['stress'] - 5) + $this->faker->numberBetween(-1, 3))),
            'puffs_per_day'     => null,
        ]);
    }
}
