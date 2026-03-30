<?php

namespace Database\Seeders;

use App\Models\AllergyCatalogItem;
use App\Models\ChronicDiseaseCatalogItem;
use App\Models\DoctorInvitation;
use App\Models\HealthLabResult;
use App\Models\HealthTreatmentCheck;
use App\Models\HealthVital;
use App\Models\JournalEntry;
use App\Models\ProfilSante;
use App\Models\TreatmentCatalogItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HealthcareAnalyticsSeeder extends Seeder
{
    private const DOCTOR_COUNT = 12;
    private const PATIENT_COUNT = 180;
    private const JOURNAL_DAYS = 90;
    private const VITAL_DAYS = 70;
    private const TREATMENT_CHECK_DAYS = 35;

    private const DOCTOR_SPECIALTIES = [
        'Cardiologie',
        'Endocrinologie',
        'Medecine interne',
        'Pneumologie',
        'Nutrition',
        'Medecine generale',
    ];

    private const OBJECTIFS = [
        'Perdre du poids',
        'Ameliorer mon sommeil',
        'Augmenter mon energie',
        'Reduire mon stress',
        'Suivre ma sante regulierement',
    ];

    private const ACTIVITIES = [
        'Marche',
        'Course',
        'Velo',
        'Natation',
        'Yoga',
        'Musculation',
        'Sport collectif',
    ];

    private const MEAL_LIBRARY = [
        'breakfast' => [
            'Oeufs et pain complet',
            'Porridge fruits rouges',
            'Yaourt grec et granola',
            'Smoothie banane avoine',
        ],
        'lunch' => [
            'Poulet legumes vapeur',
            'Saumon riz complet',
            'Salade composee quinoa',
            'Pates completes au thon',
        ],
        'dinner' => [
            'Soupe legumes et dinde',
            'Poisson blanc et brocolis',
            'Omelette champignons',
            'Buddha bowl maison',
        ],
        'snack' => [
            'Amandes et pomme',
            'Fromage blanc et fruits',
            'Barre cereales maison',
            'Banane et beurre de cacahuete',
        ],
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $contexts = [];

    /**
     * @var array<string, int>
     */
    private array $coverageOffsets = [];

    private int $treatmentTypeOffset = 0;

    /**
     * @var array<string, int>
     */
    private array $treatmentNameOffsets = [];

    public function run(): void
    {
        $doctors = $this->createDoctors();
        $patients = $this->createPatients();

        $this->createProfiles($patients, $doctors);
        $this->createJournalEntries($patients);
        $this->createVitals($patients);
        $this->createLabResults($patients);
        $this->createTreatmentChecks($patients);
        $this->createDoctorInvitations($patients);
    }

    private function createDoctors(): Collection
    {
        return User::factory()
            ->count(self::DOCTOR_COUNT)
            ->state(function (): array {
                $emailLocalPart = 'doctor+' . Str::lower(str_replace('-', '', Str::uuid()->toString()));

                return [
                    'role' => 'medecin',
                    'email' => $emailLocalPart . '@seed.local',
                    'specialite' => fake()->randomElement(self::DOCTOR_SPECIALTIES),
                    'date_of_birth' => Carbon::today()
                        ->subYears(fake()->numberBetween(28, 69))
                        ->subDays(fake()->numberBetween(0, 360))
                        ->toDateString(),
                ];
            })
            ->create();
    }

    private function createPatients(): Collection
    {
        return User::factory()
            ->count(self::PATIENT_COUNT)
            ->state(function (): array {
                $emailLocalPart = 'patient+' . Str::lower(str_replace('-', '', Str::uuid()->toString()));

                return [
                    'role' => 'user',
                    'email' => $emailLocalPart . '@seed.local',
                    'specialite' => null,
                    'date_of_birth' => Carbon::today()
                        ->subYears(fake()->numberBetween(18, 85))
                        ->subDays(fake()->numberBetween(0, 360))
                        ->toDateString(),
                ];
            })
            ->create();
    }

    private function createProfiles(Collection $patients, Collection $doctors): void
    {
        $allergyPool = AllergyCatalogItem::query()->pluck('name')->filter()->values()->all();
        $diseasePool = ChronicDiseaseCatalogItem::query()->pluck('name')->filter()->values()->all();
        $treatmentCatalog = TreatmentCatalogItem::query()
            ->get(['type', 'name'])
            ->groupBy('type')
            ->map(fn (Collection $rows) => $rows->pluck('name')->filter()->values()->all())
            ->all();

        foreach ($patients as $patient) {
            $age = Carbon::parse($patient->date_of_birth)->age;
            $sexe = fake()->randomElement(['homme', 'femme']);
            $taille = $sexe === 'homme' ? fake()->numberBetween(166, 194) : fake()->numberBetween(152, 180);
            $bmi = fake()->randomFloat(1, 19, 33);
            $poids = round((($taille / 100) ** 2) * $bmi, 1);

            $hasChronic = fake()->boolean($age >= 50 ? 58 : 38);
            $maladies = $hasChronic
                ? $this->pickDiverseSubset($diseasePool, 1, 3, 'diseases', 88)
                : (fake()->boolean(16) ? $this->pickDiverseSubset($diseasePool, 1, 1, 'diseases', 72) : []);
            $allergies = $this->pickDiverseSubset($allergyPool, 0, 3, 'allergies', 74);
            $objectifs = $this->pickSubset(self::OBJECTIFS, 1, 3);

            $traitements = $this->buildTreatments($maladies, $treatmentCatalog);
            if ($traitements === [] && fake()->boolean(20)) {
                $traitements = $this->buildTreatments([], $treatmentCatalog);
            }

            $fumeur = fake()->boolean($age > 45 ? 30 : 18);
            $alcool = fake()->boolean(58);
            $consulteMedecin = ($maladies !== [] && fake()->boolean(82)) || fake()->boolean($age > 60 ? 45 : 22);
            $medecinPeutConsulter = $consulteMedecin ? fake()->boolean(75) : false;
            $assignedDoctor = $medecinPeutConsulter ? $doctors->random() : null;

            ProfilSante::factory()
                ->for($patient)
                ->state([
                    'sexe' => $sexe,
                    'taille' => $taille,
                    'poids' => $poids,
                    'groupe_sanguin' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                    'objectifs' => $objectifs,
                    'allergies' => $allergies,
                    'maladies_chroniques' => $maladies,
                    'traitements' => $traitements,
                    'fumeur' => $fumeur,
                    'alcool' => $alcool,
                    'consulte_medecin' => $consulteMedecin,
                    'medecin_peut_consulter' => $medecinPeutConsulter,
                    'medecin_email' => $assignedDoctor?->email,
                ])
                ->create();

            $this->contexts[$patient->id] = [
                'age' => $age,
                'fumeur' => $fumeur,
                'alcool' => $alcool,
                'maladies' => $maladies,
                'traitements' => $traitements,
                'assigned_doctor_id' => $assignedDoctor?->id,
                'assigned_doctor_email' => $assignedDoctor?->email,
                'medecin_peut_consulter' => $medecinPeutConsulter,
            ];
        }
    }

    private function createJournalEntries(Collection $patients): void
    {
        $today = Carbon::today();

        foreach ($patients as $patient) {
            $context = $this->contexts[$patient->id] ?? null;
            if ($context === null) {
                continue;
            }

            for ($offset = self::JOURNAL_DAYS - 1; $offset >= 0; $offset--) {
                if (fake()->boolean(12)) {
                    continue;
                }

                $entryDate = $today->copy()->subDays($offset);
                [$meals, $calories] = $this->buildMealsAndCalories();

                $activityType = fake()->boolean(72) ? fake()->randomElement(self::ACTIVITIES) : null;
                $intensity = $activityType === null
                    ? fake()->randomElement(['light', 'medium'])
                    : fake()->randomElement(['light', 'medium', 'high']);

                $activityDuration = $activityType === null
                    ? null
                    : match ($intensity) {
                        'light' => fake()->numberBetween(15, 55),
                        'medium' => fake()->numberBetween(25, 80),
                        default => fake()->numberBetween(35, 105),
                    };

                $sleep = $this->clampInt((int) round(fake()->randomFloat(1, 5.2, 8.7) - ($context['fumeur'] ? 0.4 : 0.0)), 3, 11);
                $stress = $this->clampInt((int) round(fake()->randomFloat(1, 2, 8) + ($context['fumeur'] ? 1 : 0) + (($context['maladies'] !== []) ? 1 : 0)), 0, 10);
                $energy = $this->clampInt((int) round(8 - ($stress * 0.5) + (($sleep - 7) * 0.8) + fake()->randomFloat(1, -1.2, 1.2)), 0, 10);

                $tobacco = $context['fumeur'] ? fake()->boolean(76) : fake()->boolean(4);
                $alcohol = $context['alcool'] ? fake()->boolean($entryDate->isWeekend() ? 38 : 20) : false;

                $tobaccoTypes = [
                    'cigarette' => false,
                    'vape' => false,
                ];

                $cigarettesPerDay = null;
                $puffsPerDay = null;

                if ($tobacco) {
                    $tobaccoTypes['cigarette'] = fake()->boolean(80);
                    $tobaccoTypes['vape'] = fake()->boolean(35);

                    if (! $tobaccoTypes['cigarette'] && ! $tobaccoTypes['vape']) {
                        $tobaccoTypes['cigarette'] = true;
                    }

                    if ($tobaccoTypes['cigarette']) {
                        $cigarettesPerDay = fake()->numberBetween(2, 18);
                    }

                    if ($tobaccoTypes['vape']) {
                        $puffsPerDay = fake()->numberBetween(20, 450);
                    }
                }

                $hydration = $this->clampFloat(
                    fake()->randomFloat(1, 1.0, 3.8) + (($activityDuration ?? 0) / 120),
                    0.5,
                    8.0,
                    1
                );

                $caffeine = $this->clampInt((int) round(fake()->randomFloat(1, 0, 4) + ($stress / 5)), 0, 20);

                JournalEntry::factory()
                    ->for($patient)
                    ->state([
                        'entry_date' => $entryDate->toDateString(),
                        'sleep' => $sleep,
                        'stress' => $stress,
                        'energy' => $energy,
                        'sugar' => $calories >= 2800 ? 'high' : ($calories >= 2100 ? 'medium' : 'low'),
                        'caffeine' => $caffeine,
                        'hydration' => $hydration,
                        'meals' => $meals,
                        'calories' => $calories,
                        'activity_type' => $activityType,
                        'activity_duration' => $activityDuration,
                        'intensity' => $intensity,
                        'tobacco' => $tobacco,
                        'alcohol' => $alcohol,
                        'tobacco_types' => $tobaccoTypes,
                        'cigarettes_per_day' => $cigarettesPerDay,
                        'puffs_per_day' => $puffsPerDay,
                        'alcohol_drinks' => $alcohol ? fake()->numberBetween(1, 4) : 0,
                    ])
                    ->create();
            }
        }
    }

    private function createVitals(Collection $patients): void
    {
        $start = Carbon::today()->subDays(self::VITAL_DAYS);

        foreach ($patients as $patient) {
            $context = $this->contexts[$patient->id] ?? null;
            if ($context === null) {
                continue;
            }

            $entries = JournalEntry::query()
                ->where('user_id', $patient->id)
                ->whereDate('entry_date', '>=', $start->toDateString())
                ->orderBy('entry_date')
                ->get(['entry_date', 'stress', 'intensity']);

            foreach ($entries as $entry) {
                if (fake()->boolean(18)) {
                    continue;
                }

                $isHighIntensity = $entry->intensity === 'high';
                $hasHypertension = in_array('Hypertension arterielle', $context['maladies'], true);

                $heartRate = $this->clampInt(
                    (int) round(60 + ($entry->stress * 2.1) + ($isHighIntensity ? 9 : 2) + ($context['fumeur'] ? 6 : 0) + fake()->numberBetween(-7, 8)),
                    45,
                    170
                );

                $systolic = $this->clampInt(
                    (int) round(102 + ($context['age'] * 0.42) + ($entry->stress * 1.3) + ($context['fumeur'] ? 7 : 0) + ($hasHypertension ? 14 : 0) + fake()->numberBetween(-10, 12)),
                    88,
                    200
                );

                $diastolic = $this->clampInt(
                    (int) round(64 + ($context['age'] * 0.22) + ($entry->stress * 0.9) + ($context['fumeur'] ? 4 : 0) + ($hasHypertension ? 10 : 0) + fake()->numberBetween(-8, 9)),
                    52,
                    120
                );

                $oxygen = $this->clampFloat(
                    98.2 - ($context['fumeur'] ? 2.1 : 0.3) + fake()->randomFloat(1, -1.5, 1.2),
                    88,
                    100,
                    1
                );

                $measuredAt = Carbon::parse($entry->entry_date)
                    ->setHour(fake()->numberBetween(6, 22))
                    ->setMinute(fake()->randomElement([0, 10, 20, 30, 40, 50]));

                HealthVital::factory()
                    ->for($patient)
                    ->state([
                        'measured_at' => $measuredAt,
                        'heart_rate' => $heartRate,
                        'systolic_pressure' => $systolic,
                        'diastolic_pressure' => $diastolic,
                        'oxygen_saturation' => $oxygen,
                    ])
                    ->create();
            }
        }
    }

    private function createLabResults(Collection $patients): void
    {
        $panels = [
            ['indicator' => 'glycemie', 'category' => 'Biologie sanguine', 'result' => 'Glycémie', 'unit' => 'mmol/L', 'base' => 5.1, 'spread' => 0.8],
            ['indicator' => 'hba1c', 'category' => 'Biologie sanguine', 'result' => 'HbA1c', 'unit' => '%', 'base' => 5.4, 'spread' => 0.5],
            ['indicator' => 'creatinine', 'category' => 'Biologie sanguine', 'result' => 'Créatinine', 'unit' => 'mg/L', 'base' => 10.5, 'spread' => 2.1],
            ['indicator' => 'crp', 'category' => 'Biologie sanguine', 'result' => 'CRP', 'unit' => 'mg/L', 'base' => 4.2, 'spread' => 2.4],
            ['indicator' => 'ferritine', 'category' => 'Biologie sanguine', 'result' => 'Ferritine', 'unit' => 'ng/mL', 'base' => 110, 'spread' => 48],
            ['indicator' => 'hemoglobine', 'category' => 'Hématologie', 'result' => 'Hémoglobine', 'unit' => 'g/dL', 'base' => 14.0, 'spread' => 1.2],
            ['indicator' => 'cholesterol_total', 'category' => 'Bilan lipidique', 'result' => 'Cholestérol total', 'unit' => 'mmol/L', 'base' => 4.8, 'spread' => 0.9],
            ['indicator' => 'ldl', 'category' => 'Bilan lipidique', 'result' => 'LDL', 'unit' => 'mmol/L', 'base' => 2.6, 'spread' => 0.8],
            ['indicator' => 'hdl', 'category' => 'Bilan lipidique', 'result' => 'HDL', 'unit' => 'mmol/L', 'base' => 1.3, 'spread' => 0.35],
            ['indicator' => 'triglycerides', 'category' => 'Bilan lipidique', 'result' => 'Triglycérides', 'unit' => 'mmol/L', 'base' => 1.4, 'spread' => 0.6],
        ];

        foreach ($patients as $patient) {
            $context = $this->contexts[$patient->id] ?? null;
            if ($context === null) {
                continue;
            }

            for ($monthOffset = 0; $monthOffset < 8; $monthOffset++) {
                $testsForMonth = fake()->randomElements($panels, fake()->numberBetween(2, 4));
                foreach ($testsForMonth as $panel) {
                    $value = $this->labValueForContext($panel['indicator'], $panel['base'], $panel['spread'], $context);

                    HealthLabResult::factory()
                        ->for($patient)
                        ->state([
                            'analysis_type' => $panel['category'],
                            'analysis_result' => $panel['result'],
                            'value' => $value,
                            'unit' => $panel['unit'],
                            'analysis_date' => Carbon::today()
                                ->subMonths($monthOffset)
                                ->subDays(fake()->numberBetween(0, 25))
                                ->toDateString(),
                        ])
                        ->create();
                }
            }
        }
    }

    private function createTreatmentChecks(Collection $patients): void
    {
        $today = Carbon::today();

        foreach ($patients as $patient) {
            $context = $this->contexts[$patient->id] ?? null;
            if ($context === null || $context['traitements'] === []) {
                continue;
            }

            for ($offset = self::TREATMENT_CHECK_DAYS - 1; $offset >= 0; $offset--) {
                $date = $today->copy()->subDays($offset);

                foreach ($context['traitements'] as $index => $treatment) {
                    $slots = $this->slotsForDate($date, $treatment);
                    if ($slots <= 0) {
                        continue;
                    }

                    $name = (string) ($treatment['name'] ?? $treatment['type'] ?? 'traitement');
                    $slug = Str::slug($name);
                    if ($slug === '') {
                        $slug = 'traitement';
                    }
                    $keyBase = $slug . '-' . ($index + 1);

                    for ($slot = 1; $slot <= $slots; $slot++) {
                        $taken = fake()->boolean($context['fumeur'] ? 78 : 86);
                        $checkedAt = $taken
                            ? $date->copy()->setHour(fake()->numberBetween(6, 22))->setMinute(fake()->randomElement([0, 15, 30, 45]))
                            : null;

                        HealthTreatmentCheck::factory()
                            ->for($patient)
                            ->state([
                                'check_date' => $date->toDateString(),
                                'treatment_type' => (string) ($treatment['type'] ?? ''),
                                'medication_key' => $keyBase . '-' . $slot,
                                'treatment_name' => $name,
                                'dose' => (string) ($treatment['dose'] ?? '1 comprime'),
                                'taken' => $taken,
                                'checked_at' => $checkedAt,
                            ])
                            ->create();
                    }
                }
            }
        }
    }

    private function createDoctorInvitations(Collection $patients): void
    {
        foreach ($patients as $patient) {
            $context = $this->contexts[$patient->id] ?? null;
            if ($context === null) {
                continue;
            }

            if (! $context['medecin_peut_consulter'] || empty($context['assigned_doctor_email'])) {
                continue;
            }

            $status = fake()->boolean(80) ? 'accepted' : 'pending';

            DoctorInvitation::query()->updateOrCreate(
                [
                    'patient_user_id' => $patient->id,
                    'doctor_email' => $context['assigned_doctor_email'],
                ],
                [
                    'doctor_user_id' => $context['assigned_doctor_id'],
                    'status' => $status,
                    'token' => hash('sha256', Str::uuid()->toString()),
                    'accepted_at' => $status === 'accepted' ? Carbon::now()->subDays(fake()->numberBetween(3, 120)) : null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                    'general_observation' => null,
                    'general_observation_updated_at' => null,
                ]
            );
        }
    }

    /**
     * @param array<int, string> $maladies
     * @param array<string, array<int, string>> $catalog
     * @return array<int, array<string, mixed>>
     */
    private function buildTreatments(array $maladies, array $catalog): array
    {
        if ($catalog === []) {
            return [];
        }

        $catalog = collect($catalog)
            ->map(function (array $names): array {
                return array_values(array_filter(array_map(
                    static fn ($name) => is_string($name) ? trim($name) : '',
                    $names
                ), static fn (string $name) => $name !== '' && ! preg_match('/^Traitement\s+/i', $name)));
            })
            ->filter(static fn (array $names) => $names !== [])
            ->all();

        if ($catalog === []) {
            return [];
        }

        $catalogTypes = array_values(array_keys($catalog));

        $conditionToTypes = [
            'Diabete' => ['Antidiabetique'],
            'Hypertension arterielle' => ['Antihypertenseur'],
            'Asthme' => ['Inhalateur respiratoire'],
            'Depression' => ['Antidepresseur'],
            'Arthrite' => ['Anti-inflammatoire'],
            'Cholesterol eleve' => ['Anticoagulant'],
            'Maladie cardiaque' => ['Antihypertenseur', 'Anticoagulant'],
            'Maladie pulmonaire chronique' => ['Inhalateur respiratoire', 'Corticoide'],
            'Maladie thyroidienne' => ['Traitement hormonal'],
            'Migraine chronique' => ['Antidouleur'],
            'Anemie' => ['Supplement vitaminique'],
            'Epilepsie' => ['Antidouleur'],
        ];

        $selectedTypes = [];
        foreach ($maladies as $condition) {
            foreach ($conditionToTypes[$condition] ?? [] as $type) {
                if (isset($catalog[$type])) {
                    $selectedTypes[] = $type;
                }
            }
        }

        if ($selectedTypes === []) {
            $selectedTypes = $this->pickRotatingTypes($catalogTypes, fake()->boolean(42) ? 2 : 1);
        } elseif (count($selectedTypes) < 3 && fake()->boolean(48)) {
            $extras = $this->pickRotatingTypes($catalogTypes, 1);
            foreach ($extras as $extra) {
                if (! in_array($extra, $selectedTypes, true)) {
                    $selectedTypes[] = $extra;
                }
            }
        }

        $selectedTypes = array_values(array_unique($selectedTypes));
        $treatments = [];

        foreach ($selectedTypes as $type) {
            $names = $catalog[$type] ?? [];
            if ($names === []) {
                continue;
            }

            $name = $this->pickRotatingTreatmentName($type, $names);
            if ($name === '') {
                continue;
            }

            $frequencyUnit = fake()->randomElement(['jour', 'jour', 'jour', 'semaine', 'mois']);
            $frequencyCount = match ($frequencyUnit) {
                'jour' => fake()->numberBetween(1, 3),
                'semaine' => fake()->numberBetween(1, 4),
                default => fake()->numberBetween(1, 3),
            };

            $treatments[] = [
                'type' => $type,
                'name' => $name,
                'dose' => fake()->randomElement(['5 mg', '10 mg', '20 mg', '250 mg', '500 mg', '1 comprime']),
                'frequency_unit' => $frequencyUnit,
                'frequency_count' => $frequencyCount,
                'duration' => fake()->randomElement(['30 jours', '3 mois', '6 mois', 'traitement continu']),
            ];

            if (count($treatments) >= 3) {
                break;
            }
        }

        return $treatments;
    }

    /**
     * @param array<int, string> $pool
     * @return array<int, string>
     */
    private function pickSubset(array $pool, int $min, int $max): array
    {
        if ($pool === []) {
            return [];
        }

        $count = min(count($pool), fake()->numberBetween($min, $max));
        if ($count <= 0) {
            return [];
        }

        return fake()->randomElements($pool, $count);
    }

    /**
     * @param array<int, string> $pool
     * @return array<int, string>
     */
    private function pickDiverseSubset(array $pool, int $min, int $max, string $coverageKey, int $anchorChance): array
    {
        if ($pool === []) {
            return [];
        }

        $count = min(count($pool), fake()->numberBetween($min, $max));
        if ($count <= 0 && ! fake()->boolean($anchorChance)) {
            return [];
        }

        if ($count <= 0) {
            $count = 1;
        }

        $subset = fake()->randomElements($pool, $count);

        if (fake()->boolean($anchorChance)) {
            $anchor = $this->pickRotatingFromPool($coverageKey, $pool);
            if (! in_array($anchor, $subset, true)) {
                if ($subset === []) {
                    $subset[] = $anchor;
                } else {
                    $subset[0] = $anchor;
                }
            }
        }

        return array_values(array_unique($subset));
    }

    /**
     * @param array<int, string> $pool
     */
    private function pickRotatingFromPool(string $coverageKey, array $pool): string
    {
        $count = count($pool);
        if ($count === 0) {
            return '';
        }

        $offset = $this->coverageOffsets[$coverageKey] ?? 0;
        $value = $pool[$offset % $count];
        $this->coverageOffsets[$coverageKey] = $offset + 1;

        return (string) $value;
    }

    /**
     * @param array<int, string> $types
     * @return array<int, string>
     */
    private function pickRotatingTypes(array $types, int $count): array
    {
        $types = array_values(array_unique(array_filter($types, static fn ($value) => is_string($value) && trim($value) !== '')));
        if ($types === []) {
            return [];
        }

        $count = max(1, min($count, count($types)));
        $selected = [];

        for ($i = 0; $i < $count; $i++) {
            $selected[] = $types[($this->treatmentTypeOffset + $i) % count($types)];
        }

        $this->treatmentTypeOffset += $count;

        return array_values(array_unique($selected));
    }

    /**
     * @param array<int, string> $names
     */
    private function pickRotatingTreatmentName(string $type, array $names): string
    {
        $names = array_values(array_unique(array_filter(array_map(
            static fn ($value) => is_string($value) ? trim($value) : '',
            $names
        ))));

        if ($names === []) {
            return '';
        }

        $offset = $this->treatmentNameOffsets[$type] ?? 0;
        $name = $names[$offset % count($names)];
        $this->treatmentNameOffsets[$type] = $offset + 1;

        return $name;
    }

    /**
     * @return array{0: array<int, array<string, string>>, 1: int}
     */
    private function buildMealsAndCalories(): array
    {
        $meals = [];
        $calories = 0;

        foreach (['breakfast', 'lunch', 'dinner'] as $type) {
            $label = (string) fake()->randomElement(self::MEAL_LIBRARY[$type]);
            $meals[] = ['type' => $type, 'label' => $label];

            $calories += match ($type) {
                'breakfast' => fake()->numberBetween(280, 620),
                'lunch' => fake()->numberBetween(520, 980),
                default => fake()->numberBetween(420, 900),
            };
        }

        if (fake()->boolean(45)) {
            $label = (string) fake()->randomElement(self::MEAL_LIBRARY['snack']);
            $meals[] = ['type' => 'snack', 'label' => $label];
            $calories += fake()->numberBetween(120, 420);
        }

        return [$meals, $this->clampInt($calories, 900, 4200)];
    }

    private function labValueForContext(string $indicator, float $base, float $spread, array $context): float
    {
        $value = $base + fake()->randomFloat(2, -$spread, $spread);

        $hasDiabetes = in_array('Diabete', $context['maladies'], true);
        $hasHighChol = in_array('Cholesterol eleve', $context['maladies'], true);
        $hasRenal = in_array('Maladie renale chronique', $context['maladies'], true);
        $hasAnemia = in_array('Anemie', $context['maladies'], true);

        if ($indicator === 'glycemie' && $hasDiabetes) {
            $value += fake()->randomFloat(2, 0.8, 2.4);
        }

        if ($indicator === 'hba1c' && $hasDiabetes) {
            $value += fake()->randomFloat(2, 0.7, 2.2);
        }

        if (in_array($indicator, ['cholesterol_total', 'ldl', 'triglycerides'], true) && $hasHighChol) {
            $value += fake()->randomFloat(2, 0.25, 0.9);
        }

        if ($indicator === 'creatinine' && $hasRenal) {
            $value += fake()->randomFloat(2, 2.2, 6.0);
        }

        if ($indicator === 'hemoglobine' && $hasAnemia) {
            $value -= fake()->randomFloat(2, 1.5, 3.2);
        }

        return round(max(0.1, $value), 2);
    }

    private function slotsForDate(Carbon $date, array $treatment): int
    {
        $unit = (string) ($treatment['frequency_unit'] ?? 'jour');
        $count = max(1, (int) ($treatment['frequency_count'] ?? 1));

        return match ($unit) {
            'jour' => min($count, 4),
            'semaine' => in_array($date->dayOfWeekIso, range(1, min($count, 7)), true) ? 1 : 0,
            'mois' => $date->day <= min($count, 28) ? 1 : 0,
            default => 1,
        };
    }

    private function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($value, $max));
    }

    private function clampFloat(float $value, float $min, float $max, int $precision): float
    {
        return round(max($min, min($value, $max)), $precision);
    }
}
