<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AnalysisResult;
use App\Models\DoctorInvitation;
use App\Models\HealthData;
use App\Models\HealthProfile;
use App\Models\JournalEntry;
use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCatalog;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealisticMedicalDatasetSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password123';

    private Generator $faker;

    public function run(): void
    {
        $this->faker = fake('fr_FR');

        $startDate = Carbon::today()->subDays(29)->startOfDay();
        $doctors   = $this->seedDoctors();

        foreach ($this->patients() as $patientIndex => $patientData) {
            $account = Account::updateOrCreate(
                ['email' => strtolower((string) $patientData['email'])],
                [
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'account_status' => $patientData['account_status'] ?? 'active',
                ]
            );

            $dob = Carbon::parse($patientData['date_of_birth']);

            $user = User::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'name' => $patientData['name'],
                    'date_of_birth' => $dob->toDateString(),
                    'profile_photo' => null,
                    'age' => $dob->age,
                    'role' => 'user',
                    'specialty' => null,
                ]
            );

            // Nettoyer les donnees medicales pour garantir un seed deterministe et coherent.
            $this->clearPatientMedicalData($user);

            $doctorEmail = isset($patientData['doctor_email']) && $patientData['doctor_email']
                ? strtolower(trim((string) $patientData['doctor_email']))
                : null;

            $profile = HealthProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender' => $patientData['gender'],
                    'height' => $patientData['height'],
                    'initial_weight' => $patientData['initial_weight'],
                    'current_weight' => $patientData['initial_weight'],
                    'blood_type' => $patientData['blood_type'],
                    'goals' => $patientData['goals'],
                    'allergies' => $patientData['allergies'],
                    'chronic_diseases' => $patientData['chronic_diseases'],
                    'smoker' => $patientData['smoker'],
                    'alcoholic' => $patientData['alcoholic'],
                    'doctor_invited' => $doctorEmail !== null,
                    'doctor_email' => $doctorEmail,
                ]
            );

            $acceptedDoctor = $this->createOrUpdateDoctorInvitation($user, $patientData, $doctors, $startDate);

            $seedHealthData = HealthData::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $startDate->toDateString(),
                ],
                ['doctor_observation' => null]
            );

            $treatments = $this->createTreatments($seedHealthData, $patientData['treatments'], $startDate);

            $lastWeight = (float) $patientData['initial_weight'];

            for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
                $date = $startDate->copy()->addDays($dayOffset);

                $metrics = $this->buildDailyMetrics($patientData, $patientIndex, $dayOffset, $date);
                $lastWeight = (float) $metrics['weight'];

                $journal = JournalEntry::create([
                    'user_id' => $user->id,
                    'entry_date' => $date->toDateString(),
                    'sleep' => $metrics['sleep'],
                    'stress' => $metrics['stress'],
                    'energy' => $metrics['energy'],
                    'caffeine' => $metrics['caffeine'],
                    'hydration' => $metrics['hydration'],
                    'sugar_intake' => $metrics['sugar_intake'],
                    'alcohol' => $metrics['alcohol'],
                    'alcohol_glasses' => $metrics['alcohol_glasses'],
                ]);

                $this->seedMeals($journal, $metrics);
                $this->seedPhysicalActivity($journal, $patientIndex, $dayOffset, $metrics);
                $this->seedTobacco($journal, $patientData, $metrics);

                $observation = $this->buildDoctorObservation($patientData, $metrics, $dayOffset);
                $healthData = HealthData::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'doctor_observation' => $observation,
                        'doctor_user_id'     => $observation !== null ? $acceptedDoctor?->id : null,
                    ]
                );

                $measuredAt = $date->copy()->setTime(
                    7 + (($patientIndex + $dayOffset) % 3),
                    10 + (($dayOffset * 7) % 45)
                );

                VitalSigns::create([
                    'health_data_id' => $healthData->id,
                    'measured_at' => $measuredAt,
                    'heart_rate' => $metrics['heart_rate'],
                    'systolic_pressure' => $metrics['systolic_pressure'],
                    'diastolic_pressure' => $metrics['diastolic_pressure'],
                    'oxygen_saturation' => $metrics['oxygen_saturation'],
                ]);

                $this->seedAnalysisResults($healthData, $patientData, $metrics, $dayOffset, $date);
                $this->seedTreatmentChecksAndNotifications(
                    $user,
                    $treatments,
                    $metrics,
                    (float) $patientData['adherence'],
                    $patientIndex,
                    $dayOffset,
                    $date
                );
            }

            $profile->update(['current_weight' => round($lastWeight, 1)]);
        }
    }

    private function seedDoctors(): Collection
    {
        $doctors = collect($this->doctors())->map(function (array $doctor) {
            $account = Account::updateOrCreate(
                ['email' => strtolower($doctor['email'])],
                [
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'account_status' => 'active',
                ]
            );

            $dob = Carbon::parse($doctor['date_of_birth']);

            $user = User::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'name' => $doctor['name'],
                    'date_of_birth' => $dob->toDateString(),
                    'profile_photo' => null,
                    'age' => $dob->age,
                    'role' => 'doctor',
                    'specialty' => $doctor['specialty'],
                ]
            );

            return ['email' => strtolower($doctor['email']), 'user' => $user];
        });

        return $doctors->keyBy('email')->map(fn (array $row) => $row['user']);
    }

    private function createOrUpdateDoctorInvitation(User $patient, array $patientData, Collection $doctors, Carbon $startDate): ?User
    {
        $doctorEmail = isset($patientData['doctor_email']) && $patientData['doctor_email']
            ? strtolower(trim((string) $patientData['doctor_email']))
            : null;

        if (!$doctorEmail) {
            return null;
        }

        $status = strtolower((string) ($patientData['invitation_status'] ?? 'pending'));
        if (!in_array($status, ['pending', 'accepted', 'rejected'], true)) {
            $status = 'pending';
        }

        $doctorUser = $doctors->get($doctorEmail);

        DoctorInvitation::updateOrCreate(
            [
                'patient_user_id' => $patient->id,
                'doctor_email' => $doctorEmail,
            ],
            [
                'doctor_user_id' => ($status === 'accepted' || $status === 'rejected') ? $doctorUser?->id : null,
                'status' => $status,
                'token' => Str::random(64),
                'accepted_at' => $status === 'accepted' ? $startDate->copy()->addDays($this->faker->numberBetween(2, 12)) : null,
                'rejected_at' => $status === 'rejected' ? $startDate->copy()->addDays($this->faker->numberBetween(1, 8)) : null,
                'revoked_at' => null,
            ]
        );

        return ($status === 'accepted') ? $doctorUser : null;
    }

    private function clearPatientMedicalData(User $user): void
    {
        $treatmentIds = Treatment::query()
            ->whereHas('healthData', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        if ($treatmentIds->isNotEmpty()) {
            Notification::query()->whereIn('treatment_id', $treatmentIds)->delete();
            TreatmentCheck::query()->whereIn('treatment_id', $treatmentIds)->delete();
            Treatment::query()->whereIn('id', $treatmentIds)->delete();
        }

        $healthDataIds = HealthData::query()->where('user_id', $user->id)->pluck('id');
        if ($healthDataIds->isNotEmpty()) {
            VitalSigns::query()->whereIn('health_data_id', $healthDataIds)->delete();
            AnalysisResult::query()->whereIn('health_data_id', $healthDataIds)->delete();
            HealthData::query()->whereIn('id', $healthDataIds)->delete();
        }

        JournalEntry::query()->where('user_id', $user->id)->delete();
        DoctorInvitation::query()->where('patient_user_id', $user->id)->delete();
    }

    private function createTreatments(HealthData $healthData, array $treatmentRows, Carbon $monthStart): Collection
    {
        $created = collect();

        foreach ($treatmentRows as $row) {
            $catalog = TreatmentCatalog::firstOrCreate([
                'treatment_type' => trim((string) $row['type']),
                'treatment_name' => trim((string) $row['name']),
            ]);

            $startDate = $monthStart->copy()->addDays((int) ($row['start_offset'] ?? 0));
            $endDate = null;

            if (array_key_exists('end_offset', $row) && $row['end_offset'] !== null) {
                $endDate = $monthStart->copy()->addDays((int) $row['end_offset']);
                if ($endDate->lt($startDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }
            }

            $created->push(Treatment::create([
                'health_data_id' => $healthData->id,
                'treatment_catalog_id' => $catalog->id,
                'dose' => $row['dose'],
                'frequency' => $row['frequency'] ?? 'day',
                'daily_doses' => max(1, min((int) ($row['daily_doses'] ?? 1), 4)),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate?->toDateString(),
            ]));
        }

        return $created;
    }

    private function buildDailyMetrics(array $patientData, int $patientIndex, int $dayOffset, Carbon $date): array
    {
        $wave = sin(($dayOffset + ($patientIndex * 2)) / 5);
        $isWeekend = $date->isWeekend();

        $sleep = $this->clampInt(
            (int) round($patientData['baseline']['sleep'] + ($isWeekend ? 1 : 0) - ($wave > 0.6 ? 1 : 0)),
            5,
            9
        );

        $stress = $this->clampInt(
            (int) round(
                $patientData['baseline']['stress']
                + ($isWeekend ? -1 : 1)
                + ($wave > 0.7 ? 1 : 0)
                - ($sleep >= 8 ? 1 : 0)
            ),
            1,
            10
        );

        $energy = $this->clampInt(
            (int) round(
                $patientData['baseline']['energy']
                + ($sleep >= 8 ? 1 : 0)
                - ($stress >= 7 ? 1 : 0)
                + ($wave < -0.5 ? -1 : 0)
            ),
            1,
            10
        );

        $caffeine = $this->clampInt(1 + (($dayOffset + $patientIndex) % 4) + ($stress >= 7 ? 1 : 0), 0, 8);
        $hydration = $this->clampFloat(
            (float) ($patientData['baseline']['hydration'] + ($isWeekend ? 0.3 : 0.0) - ($caffeine >= 4 ? 0.2 : 0.0)),
            1.4,
            4.0
        );

        $alcohol = (bool) ($patientData['alcoholic'] && $isWeekend && (($dayOffset + $patientIndex) % 2 === 0));
        $alcoholGlasses = $alcohol ? (1 + (($dayOffset + $patientIndex) % 3)) : null;

        $heartRate = $this->clampInt(
            (int) round($patientData['baseline']['heart_rate'] + ($wave * 4) + ($stress >= 8 ? 3 : 0)),
            55,
            120
        );

        $systolic = $this->clampInt(
            (int) round($patientData['baseline']['systolic_pressure'] + ($wave * 5) + ($stress >= 8 ? 4 : 0)),
            95,
            170
        );

        $diastolic = $this->clampInt(
            (int) round($patientData['baseline']['diastolic_pressure'] + ($wave * 3) + ($stress >= 8 ? 2 : 0)),
            55,
            110
        );

        $oxygen = $this->clampInt(
            (int) round($patientData['baseline']['oxygen_saturation'] - ($patientData['smoker'] ? 1 : 0) + ($wave < -0.7 ? -1 : 0)),
            90,
            100
        );

        $weightTrend = (float) ($patientData['weight_trend'] ?? 0.0);
        $initialWeight = (float) $patientData['initial_weight'];
        $weight = $initialWeight + ($dayOffset * ($weightTrend / 30)) + ($wave * 0.6) + $this->faker->randomFloat(1, -0.3, 0.3);

        return [
            'sleep' => $sleep,
            'stress' => $stress,
            'energy' => $energy,
            'caffeine' => $caffeine,
            'hydration' => round($hydration, 1),
            'sugar_intake' => $this->buildSugarIntakeLabel($stress, $caffeine),
            'alcohol' => $alcohol,
            'alcohol_glasses' => $alcoholGlasses,
            'heart_rate' => $heartRate,
            'systolic_pressure' => $systolic,
            'diastolic_pressure' => $diastolic,
            'oxygen_saturation' => $oxygen,
            'weight' => round($this->clampFloat($weight, 45.0, 130.0), 1),
        ];
    }

    private function seedMeals(JournalEntry $journal, array $metrics): void
    {
        $petitsDejeuners = [
            ['Porridge a la banane et amandes', 390],
            ['Omelette aux fines herbes et pain complet', 420],
            ['Yaourt nature, fruits rouges et noix', 360],
            ['Tartines completes, avocat et fromage frais', 410],
        ];

        $dejeuners = [
            ['Poulet grille, quinoa et legumes de saison', 620],
            ['Soupe de lentilles et salade composee', 540],
            ['Poisson au four, riz complet et brocolis', 600],
            ['Couscous legumes et pois chiches', 640],
        ];

        $diners = [
            ['Poelee de legumes et dinde', 530],
            ['Veloute de courgettes et tartine proteinee', 500],
            ['Salade composee, oeufs et pommes de terre', 560],
            ['Ratatouille maison et filet de poisson', 520],
        ];

        $collations = [
            ['Pomme et amandes', 180],
            ['Fromage blanc et graines de chia', 160],
            ['Carottes croquantes et houmous', 190],
            ['Poire et noix', 170],
        ];

        [$bDesc, $bCal] = $petitsDejeuners[$this->faker->numberBetween(0, count($petitsDejeuners) - 1)];
        [$dDesc, $dCal] = $dejeuners[$this->faker->numberBetween(0, count($dejeuners) - 1)];
        [$sDesc, $sCal] = $diners[$this->faker->numberBetween(0, count($diners) - 1)];

        $journal->meals()->create([
            'meal_type' => 'breakfast',
            'description' => $bDesc,
            'calories' => $bCal,
        ]);

        $journal->meals()->create([
            'meal_type' => 'lunch',
            'description' => $dDesc,
            'calories' => $dCal,
        ]);

        $journal->meals()->create([
            'meal_type' => 'dinner',
            'description' => $sDesc,
            'calories' => $sCal,
        ]);

        if ($metrics['stress'] >= 7 || $this->faker->boolean(35)) {
            [$cDesc, $cCal] = $collations[$this->faker->numberBetween(0, count($collations) - 1)];
            $journal->meals()->create([
                'meal_type' => 'snack',
                'description' => $cDesc,
                'calories' => $cCal,
            ]);
        }
    }

    private function seedPhysicalActivity(JournalEntry $journal, int $patientIndex, int $dayOffset, array $metrics): void
    {
        if ((($dayOffset + $patientIndex) % 3) === 0 && $metrics['energy'] <= 4) {
            return;
        }

        $activities = ['marche rapide', 'velo', 'yoga', 'renforcement musculaire', 'natation'];
        $activityType = $activities[($dayOffset + $patientIndex) % count($activities)];

        $duration = 25 + ((($dayOffset + $patientIndex) % 5) * 10);

        $intensity = 'low';
        if ($duration >= 45) {
            $intensity = 'medium';
        }
        if ($duration >= 60) {
            $intensity = 'high';
        }

        $journal->physicalActivities()->create([
            'activity_type' => $activityType,
            'duration_minutes' => $duration,
            'intensity' => $intensity,
        ]);
    }

    private function seedTobacco(JournalEntry $journal, array $patientData, array $metrics): void
    {
        if (!$patientData['smoker']) {
            return;
        }

        $journal->tobacco()->create([
            'tobacco_type' => 'cigarette',
            'cigarettes_per_day' => max(2, min(20, 5 + ($metrics['stress'] - 5) + $this->faker->numberBetween(-1, 3))),
            'puffs_per_day' => null,
        ]);
    }

    private function buildDoctorObservation(array $patientData, array $metrics, int $dayOffset): ?string
    {
        if ($dayOffset % 6 !== 0 && $metrics['systolic_pressure'] < 140 && $metrics['stress'] < 8) {
            return null;
        }

        if ($metrics['systolic_pressure'] >= 145) {
            return 'Tension arterielle encore elevee. Revoir l adherence therapeutique et reduire le sel alimentaire.';
        }

        if ($metrics['stress'] >= 8) {
            return 'Niveau de stress important cette semaine. Renforcer les routines de sommeil et la respiration guidee.';
        }

        if (in_array('Diabete de type 2', $patientData['chronic_diseases'], true)) {
            return 'Equilibre glycemique globalement acceptable. Poursuivre les traitements et regulariser les repas.';
        }

        return 'Etat clinique stable. Maintenir le plan actuel et programmer le prochain suivi.';
    }

    private function seedAnalysisResults(HealthData $healthData, array $patientData, array $metrics, int $dayOffset, Carbon $date): void
    {
        $hasDiabetes = in_array('Diabete de type 2', $patientData['chronic_diseases'], true);
        $glucoseBase = $hasDiabetes ? 7.0 : 5.0;
        $glucoseValue = round($glucoseBase + (($metrics['stress'] - 5) * 0.18) + (($dayOffset % 5 === 0) ? 0.15 : 0.0), 2);

        AnalysisResult::create([
            'health_data_id' => $healthData->id,
            'analysis_type' => 'Glucose',
            'result_name' => 'Glycemie a jeun',
            'value' => max(3.6, $glucoseValue),
            'unit' => 'mmol/L',
            'analysis_date' => $date->toDateString(),
        ]);

        if ($dayOffset % 7 === 0) {
            $crp = round(1.2 + (($metrics['stress'] >= 7) ? 1.3 : 0.4) + (($dayOffset % 14 === 0) ? 0.6 : 0.0), 2);

            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type' => 'Inflammation',
                'result_name' => 'CRP',
                'value' => $crp,
                'unit' => 'mg/L',
                'analysis_date' => $date->toDateString(),
            ]);
        }

        if ($dayOffset % 10 === 0) {
            $cholesterol = round(172 + (($metrics['stress'] >= 7) ? 16 : 7) + (($dayOffset % 20 === 0) ? 8 : 0), 2);

            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type' => 'Bilan lipidique',
                'result_name' => 'Cholesterol total',
                'value' => $cholesterol,
                'unit' => 'mg/dL',
                'analysis_date' => $date->toDateString(),
            ]);
        }
    }

    private function seedTreatmentChecksAndNotifications(
        User $user,
        Collection $treatments,
        array $metrics,
        float $adherence,
        int $patientIndex,
        int $dayOffset,
        Carbon $date
    ): void {
        $baseHours = [8, 14, 20, 22];

        foreach ($treatments as $treatment) {
            if (!$this->isTreatmentDueOnDate($treatment, $date)) {
                continue;
            }

            Notification::create([
                'treatment_id' => $treatment->id,
                'kind' => 'reminder',
                'target_date' => $date->toDateString(),
                'message' => 'N oubliez pas de prendre ' . ($treatment->treatmentCatalog?->treatment_name ?? 'votre traitement') . ' aujourd hui.',
                'read_at' => $date->lt(Carbon::today()->subDays(2)) && $this->faker->boolean(60)
                    ? $date->copy()->setTime(21, $this->faker->numberBetween(0, 45))
                    : null,
            ]);

            $doses = strtolower((string) $treatment->frequency) === 'day'
                ? max(1, min((int) ($treatment->daily_doses ?? 1), 4))
                : 1;

            $hasMissedDose = false;

            for ($doseIndex = 1; $doseIndex <= $doses; $doseIndex++) {
                $penalty = ($date->isWeekend() ? 8 : 0) + ($metrics['stress'] >= 8 ? 10 : 0);
                $target = max(45, (int) round(($adherence * 100) - $penalty));
                $score = (($dayOffset + $doseIndex + ($patientIndex * 5)) * 13) % 100;
                $taken = $score < $target;

                if (!$taken) {
                    $hasMissedDose = true;
                }

                $hour = $baseHours[min($doseIndex - 1, count($baseHours) - 1)];
                $checkedAt = $taken
                    ? $date->copy()->setTime($hour, (($dayOffset + $doseIndex + $user->id) * 7) % 60)
                    : null;

                TreatmentCheck::create([
                    'treatment_id' => $treatment->id,
                    'user_id' => $user->id,
                    'check_date' => $date->toDateString(),
                    'medication_key' => $treatment->id . '__dose_' . $doseIndex,
                    'taken' => $taken,
                    'checked_at' => $checkedAt,
                ]);
            }

            if ($hasMissedDose) {
                Notification::create([
                    'treatment_id' => $treatment->id,
                    'kind' => 'missed',
                    'target_date' => $date->toDateString(),
                    'message' => 'Vous avez manque au moins une prise de ' . ($treatment->treatmentCatalog?->treatment_name ?? 'votre traitement') . ' aujourd hui.',
                    'read_at' => null,
                ]);
            }
        }
    }

    private function isTreatmentDueOnDate(Treatment $treatment, Carbon $date): bool
    {
        if ($treatment->start_date && $date->lt($treatment->start_date)) {
            return false;
        }

        if ($treatment->end_date && $date->gt($treatment->end_date)) {
            return false;
        }

        $frequency = strtolower((string) ($treatment->frequency ?? 'day'));

        if ($frequency === 'week') {
            return $treatment->start_date
                ? $date->dayOfWeek === $treatment->start_date->dayOfWeek
                : $date->dayOfWeek === Carbon::MONDAY;
        }

        if ($frequency === 'month') {
            return $treatment->start_date
                ? $date->day === $treatment->start_date->day
                : $date->day === 1;
        }

        return true;
    }

    private function buildSugarIntakeLabel(int $stress, int $caffeine): string
    {
        if ($stress >= 8 || $caffeine >= 5) {
            return 'Consommation elevee';
        }

        if ($stress <= 3 && $caffeine <= 2) {
            return 'Consommation faible';
        }

        return 'Consommation moderee';
    }

    private function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($value, $max));
    }

    private function clampFloat(float $value, float $min, float $max): float
    {
        return max($min, min($value, $max));
    }

    private function doctors(): array
    {
        return [
            [
                'name' => 'Dr Nadia Mansouri',
                'email' => 'dr.nadia@example.test',
                'specialty' => 'Cardiologie',
                'date_of_birth' => '1979-02-11',
            ],
            [
                'name' => 'Dr Samir Khadraoui',
                'email' => 'dr.samir@example.test',
                'specialty' => 'Endocrinologie',
                'date_of_birth' => '1982-09-20',
            ],
            [
                'name' => 'Dr Leila Ferjani',
                'email' => 'dr.leila@example.test',
                'specialty' => 'Pneumologie',
                'date_of_birth' => '1985-06-08',
            ],
        ];
    }

    private function patients(): array
    {
        $objectifOptions = [
            'Reduire la tension arterielle',
            'Mieux controler la glycemie',
            'Ameliorer la qualite du sommeil',
            'Augmenter l activite physique quotidienne',
            'Stabiliser le poids',
            'Diminuer les episodes de migraine',
            'Renforcer l adherence aux traitements',
            'Reduire le stress quotidien',
        ];

        $allergieOptions = ['Pollen', 'Acarien', 'Penicilline', 'Fruits de mer', 'Aucune'];
        $maladieOptions = ['Hypertension', 'Diabete de type 2', 'Asthme', 'Hypothyroidie', 'Aucune'];

        $base = [
            [
                'name' => 'Amina Benali',
                'email' => 'amina.benali@example.test',
                'date_of_birth' => '1989-04-18',
                'gender' => 'female',
                'height' => 165.0,
                'initial_weight' => 74.0,
                'blood_type' => 'A+',
                'smoker' => false,
                'alcoholic' => false,
                'doctor_email' => 'dr.nadia@example.test',
                'invitation_status' => 'accepted',
                'adherence' => 0.93,
                'weight_trend' => -1.4,
                'baseline' => [
                    'sleep' => 7,
                    'stress' => 5,
                    'energy' => 6,
                    'hydration' => 2.2,
                    'heart_rate' => 74,
                    'systolic_pressure' => 136,
                    'diastolic_pressure' => 86,
                    'oxygen_saturation' => 97,
                ],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Amlodipine', 'dose' => '5 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -20],
                    ['type' => 'Antidiabetique', 'name' => 'Metformine', 'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 2, 'start_offset' => -14],
                ],
            ],
            [
                'name' => 'Youssef Karim',
                'email' => 'youssef.karim@example.test',
                'date_of_birth' => '1978-09-11',
                'gender' => 'male',
                'height' => 178.0,
                'initial_weight' => 88.0,
                'blood_type' => 'O+',
                'smoker' => true,
                'alcoholic' => false,
                'doctor_email' => 'dr.leila@example.test',
                'invitation_status' => 'pending',
                'adherence' => 0.86,
                'weight_trend' => -0.8,
                'baseline' => [
                    'sleep' => 6,
                    'stress' => 6,
                    'energy' => 5,
                    'hydration' => 1.9,
                    'heart_rate' => 80,
                    'systolic_pressure' => 130,
                    'diastolic_pressure' => 84,
                    'oxygen_saturation' => 95,
                ],
                'treatments' => [
                    ['type' => 'Inhalateur respiratoire', 'name' => 'Budesonide/Formoterol', 'dose' => '2 inhalations', 'frequency' => 'day', 'daily_doses' => 2, 'start_offset' => -18],
                    ['type' => 'Antihistaminique', 'name' => 'Cetirizine', 'dose' => '10 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -10],
                ],
            ],
            [
                'name' => 'Sara Haddad',
                'email' => 'sara.haddad@example.test',
                'date_of_birth' => '1994-01-27',
                'gender' => 'female',
                'height' => 162.0,
                'initial_weight' => 62.0,
                'blood_type' => 'B+',
                'smoker' => false,
                'alcoholic' => true,
                'doctor_email' => 'dr.samir@example.test',
                'invitation_status' => 'accepted',
                'adherence' => 0.95,
                'weight_trend' => -0.4,
                'baseline' => [
                    'sleep' => 7,
                    'stress' => 4,
                    'energy' => 7,
                    'hydration' => 2.4,
                    'heart_rate' => 70,
                    'systolic_pressure' => 118,
                    'diastolic_pressure' => 76,
                    'oxygen_saturation' => 98,
                ],
                'treatments' => [
                    ['type' => 'Therapie hormonale', 'name' => 'Levothyroxine', 'dose' => '50 mcg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -30],
                    ['type' => 'Supplement vitamine', 'name' => 'Vitamine D', 'dose' => '1000 UI', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -12],
                ],
            ],
            [
                'name' => 'Mehdi Boussaid',
                'email' => 'mehdi.boussaid@example.test',
                'date_of_birth' => '1968-06-03',
                'gender' => 'male',
                'height' => 173.0,
                'initial_weight' => 91.0,
                'blood_type' => 'AB+',
                'smoker' => false,
                'alcoholic' => false,
                'doctor_email' => 'dr.nadia@example.test',
                'invitation_status' => 'rejected',
                'adherence' => 0.90,
                'weight_trend' => -0.6,
                'baseline' => [
                    'sleep' => 6,
                    'stress' => 5,
                    'energy' => 5,
                    'hydration' => 2.0,
                    'heart_rate' => 76,
                    'systolic_pressure' => 138,
                    'diastolic_pressure' => 88,
                    'oxygen_saturation' => 96,
                ],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Losartan', 'dose' => '50 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -25],
                    ['type' => 'Anticoagulant', 'name' => 'Warfarine', 'dose' => '3 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -20],
                ],
            ],
            [
                'name' => 'Lina Trabelsi',
                'email' => 'lina.trabelsi@example.test',
                'date_of_birth' => '1992-12-15',
                'gender' => 'female',
                'height' => 169.0,
                'initial_weight' => 68.0,
                'blood_type' => 'O-',
                'smoker' => false,
                'alcoholic' => true,
                'doctor_email' => null,
                'invitation_status' => null,
                'adherence' => 0.88,
                'weight_trend' => -0.3,
                'baseline' => [
                    'sleep' => 7,
                    'stress' => 6,
                    'energy' => 6,
                    'hydration' => 2.1,
                    'heart_rate' => 72,
                    'systolic_pressure' => 121,
                    'diastolic_pressure' => 79,
                    'oxygen_saturation' => 98,
                ],
                'treatments' => [
                    ['type' => 'Antidouleur', 'name' => 'Paracetamol', 'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -7],
                    ['type' => 'Supplement vitamine', 'name' => 'Vitamine C', 'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -7],
                ],
            ],
            [
                'name' => 'Rachid Mansour',
                'email' => 'rachid.mansour@example.test',
                'date_of_birth' => '1984-03-02',
                'gender' => 'male',
                'height' => 176.0,
                'initial_weight' => 82.0,
                'blood_type' => 'A-',
                'smoker' => true,
                'alcoholic' => false,
                'doctor_email' => 'dr.leila@example.test',
                'invitation_status' => 'accepted',
                'adherence' => 0.84,
                'weight_trend' => -0.9,
                'baseline' => [
                    'sleep' => 6,
                    'stress' => 7,
                    'energy' => 5,
                    'hydration' => 1.8,
                    'heart_rate' => 82,
                    'systolic_pressure' => 134,
                    'diastolic_pressure' => 85,
                    'oxygen_saturation' => 95,
                ],
                'treatments' => [
                    ['type' => 'Inhalateur respiratoire', 'name' => 'Albuterol', 'dose' => '1 inhalation', 'frequency' => 'day', 'daily_doses' => 2, 'start_offset' => -16],
                    ['type' => 'Antidouleur', 'name' => 'Ibuprofene', 'dose' => '400 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -4],
                ],
            ],
            [
                'name' => 'Ines Gharsalli',
                'email' => 'ines.gharsalli@example.test',
                'date_of_birth' => '1997-08-24',
                'gender' => 'female',
                'height' => 160.0,
                'initial_weight' => 59.0,
                'blood_type' => 'B-',
                'smoker' => false,
                'alcoholic' => false,
                'doctor_email' => 'dr.samir@example.test',
                'invitation_status' => 'pending',
                'adherence' => 0.96,
                'weight_trend' => 0.2,
                'baseline' => [
                    'sleep' => 8,
                    'stress' => 4,
                    'energy' => 7,
                    'hydration' => 2.5,
                    'heart_rate' => 68,
                    'systolic_pressure' => 112,
                    'diastolic_pressure' => 72,
                    'oxygen_saturation' => 99,
                ],
                'treatments' => [
                    ['type' => 'Antihistaminique', 'name' => 'Loratadine', 'dose' => '10 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -11],
                    ['type' => 'Supplement vitamine', 'name' => 'Fer', 'dose' => '1 comprime', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -9],
                ],
            ],
            [
                'name' => 'Nabil Ouali',
                'email' => 'nabil.ouali@example.test',
                'date_of_birth' => '1971-11-19',
                'gender' => 'male',
                'height' => 171.0,
                'initial_weight' => 85.0,
                'blood_type' => 'O+',
                'smoker' => false,
                'alcoholic' => true,
                'doctor_email' => null,
                'invitation_status' => null,
                'adherence' => 0.87,
                'weight_trend' => -0.5,
                'baseline' => [
                    'sleep' => 6,
                    'stress' => 6,
                    'energy' => 5,
                    'hydration' => 2.0,
                    'heart_rate' => 78,
                    'systolic_pressure' => 132,
                    'diastolic_pressure' => 83,
                    'oxygen_saturation' => 96,
                ],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Ramipril', 'dose' => '5 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -21],
                    ['type' => 'Anticoagulant', 'name' => 'Rivaroxaban', 'dose' => '20 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -19],
                ],
            ],
        ];

        return collect($base)->map(function (array $patient) use ($objectifOptions, $allergieOptions, $maladieOptions) {
            $goals = $this->faker->randomElements($objectifOptions, $this->faker->numberBetween(2, 3));

            $allergies = $this->faker->randomElements($allergieOptions, $this->faker->numberBetween(0, 2));
            $allergies = array_values(array_filter($allergies, fn (string $item) => $item !== 'Aucune'));

            $diseases = $this->faker->randomElements($maladieOptions, $this->faker->numberBetween(0, 2));
            $diseases = array_values(array_filter($diseases, fn (string $item) => $item !== 'Aucune'));

            // Renforcer les scenarios cliniques deja definis pour conserver des cas metiers stables.
            if ($patient['name'] === 'Amina Benali') {
                $diseases = ['Hypertension', 'Diabete de type 2'];
            }
            if ($patient['name'] === 'Youssef Karim') {
                $diseases = ['Asthme'];
                $allergies = ['Acarien'];
            }

            $patient['goals'] = $goals;
            $patient['allergies'] = $allergies;
            $patient['chronic_diseases'] = $diseases;

            return $patient;
        })->all();
    }
}
