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
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealisticMedicalDatasetSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password123';

    public function run(): void
    {
        $startDate = Carbon::today()->subDays(29)->startOfDay();

        foreach ($this->patients() as $patientIndex => $patientData) {
            $account = Account::updateOrCreate(
                ['email' => $patientData['email']],
                [
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'account_status' => 'active',
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

            $this->clearPatientMedicalData($user);

            $profile = HealthProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender'         => $patientData['gender'],
                    'height'         => $patientData['height'],
                    'initial_weight' => $patientData['initial_weight'],
                    'current_weight' => $patientData['initial_weight'],
                    'blood_type' => $patientData['blood_type'],
                    'goals' => $patientData['goals'],
                    'allergies' => $patientData['allergies'],
                    'chronic_diseases' => $patientData['chronic_diseases'],
                    'smoker' => $patientData['smoker'],
                    'alcoholic' => $patientData['alcoholic'],
                    'doctor_invited' => !empty($patientData['doctor_email']),
                    'doctor_email' => $patientData['doctor_email'],
                ]
            );

            if (!empty($patientData['doctor_email'])) {
                DoctorInvitation::create([
                    'patient_user_id' => $user->id,
                    'doctor_user_id' => null,
                    'doctor_email' => $patientData['doctor_email'],
                    'status' => 'pending',
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            }

            $seedHealthData = HealthData::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $startDate->toDateString(),
                ],
                ['doctor_observation' => null]
            );

            $treatments = $this->createTreatments($seedHealthData, $patientData['treatments'], $startDate);

            for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
                $date = $startDate->copy()->addDays($dayOffset);
                $metrics = $this->buildDailyMetrics($patientData, $patientIndex, $dayOffset, $date);

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

                $this->seedMeals($journal, $patientIndex, $dayOffset, $metrics);
                $this->seedPhysicalActivity($journal, $patientIndex, $dayOffset, $metrics);
                $this->seedTobacco($journal, $patientData, $dayOffset);

                $healthData = HealthData::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                    ],
                    ['doctor_observation' => $this->buildDoctorObservation($patientData, $metrics, $dayOffset)]
                );

                $measuredAt = $date->copy()->setTime(7 + (($patientIndex + $dayOffset) % 3), 10 + (($dayOffset * 7) % 45));

                VitalSigns::create([
                    'health_data_id' => $healthData->id,
                    'measured_at' => $measuredAt,
                    'heart_rate' => $metrics['heart_rate'],
                    'systolic_pressure' => $metrics['systolic_pressure'],
                    'diastolic_pressure' => $metrics['diastolic_pressure'],
                    'oxygen_saturation' => $metrics['oxygen_saturation'],
                ]);

                $this->seedAnalysisResults($healthData, $patientData, $metrics, $dayOffset, $date);
                $this->seedTreatmentChecksAndNotifications($user, $treatments, $healthData, $metrics, $patientData['adherence'], $patientIndex, $dayOffset, $date);
            }

            $profile->refresh();
        }
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
            TreatmentCheck::query()->whereIn('health_data_id', $healthDataIds)->delete();
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
                'treatment_type' => $row['type'],
                'treatment_name' => $row['name'],
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

        $sleep = $this->clampInt((int) round($patientData['baseline']['sleep'] + ($isWeekend ? 1 : 0) - ($wave > 0.6 ? 1 : 0)), 5, 9);
        $stress = $this->clampInt((int) round($patientData['baseline']['stress'] + ($isWeekend ? -1 : 1) + ($wave > 0.7 ? 1 : 0) - ($sleep >= 8 ? 1 : 0)), 1, 10);
        $energy = $this->clampInt((int) round($patientData['baseline']['energy'] + ($sleep >= 8 ? 1 : 0) - ($stress >= 7 ? 1 : 0) + ($wave < -0.5 ? -1 : 0)), 1, 10);

        $caffeine = $this->clampInt(1 + (int) (($dayOffset + $patientIndex) % 4) + ($stress >= 7 ? 1 : 0), 0, 8);
        $hydration = $this->clampFloat((float) ($patientData['baseline']['hydration'] + ($isWeekend ? 0.3 : 0.0) - ($caffeine >= 4 ? 0.2 : 0.0)), 1.4, 4.0);

        $alcohol = (bool) ($patientData['alcoholic'] && $isWeekend && (($dayOffset + $patientIndex) % 2 === 0));
        $alcoholGlasses = $alcohol ? (1 + (($dayOffset + $patientIndex) % 3)) : null;

        $heartRate = $this->clampInt((int) round($patientData['baseline']['heart_rate'] + ($wave * 4) + ($stress >= 8 ? 3 : 0)), 55, 120);
        $systolic = $this->clampInt((int) round($patientData['baseline']['systolic_pressure'] + ($wave * 5) + ($stress >= 8 ? 4 : 0)), 95, 170);
        $diastolic = $this->clampInt((int) round($patientData['baseline']['diastolic_pressure'] + ($wave * 3) + ($stress >= 8 ? 2 : 0)), 55, 110);
        $oxygen = $this->clampInt((int) round($patientData['baseline']['oxygen_saturation'] - ($patientData['smoker'] ? 1 : 0) + ($wave < -0.7 ? -1 : 0)), 90, 100);

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
        ];
    }

    private function seedMeals(JournalEntry $journal, int $patientIndex, int $dayOffset, array $metrics): void
    {
        $breakfasts = [
            ['Oatmeal with banana and yogurt', 380],
            ['Boiled eggs with whole grain bread', 410],
            ['Greek yogurt, nuts and berries', 360],
        ];
        $lunches = [
            ['Grilled chicken with quinoa and vegetables', 620],
            ['Lentil soup with mixed salad', 540],
            ['Baked fish with brown rice and greens', 600],
        ];
        $dinners = [
            ['Vegetable omelet and tomato salad', 500],
            ['Turkey stew with steamed vegetables', 560],
            ['Chickpea bowl with olive oil and herbs', 520],
        ];
        $snacks = [
            ['Apple with almonds', 180],
            ['Low-fat yogurt', 140],
            ['Whole grain crackers and cheese', 210],
        ];

        [$bDesc, $bCal] = $breakfasts[($dayOffset + $patientIndex) % count($breakfasts)];
        [$lDesc, $lCal] = $lunches[($dayOffset + $patientIndex + 1) % count($lunches)];
        [$dDesc, $dCal] = $dinners[($dayOffset + $patientIndex + 2) % count($dinners)];

        $journal->meals()->create([
            'meal_type' => 'breakfast',
            'description' => $bDesc,
            'calories' => $bCal,
        ]);

        $journal->meals()->create([
            'meal_type' => 'lunch',
            'description' => $lDesc,
            'calories' => $lCal,
        ]);

        $journal->meals()->create([
            'meal_type' => 'dinner',
            'description' => $dDesc,
            'calories' => $dCal,
        ]);

        if ($metrics['stress'] >= 7 || (($dayOffset + $patientIndex) % 3 === 0)) {
            [$sDesc, $sCal] = $snacks[($dayOffset + $patientIndex) % count($snacks)];
            $journal->meals()->create([
                'meal_type' => 'snack',
                'description' => $sDesc,
                'calories' => $sCal,
            ]);
        }
    }

    private function seedPhysicalActivity(JournalEntry $journal, int $patientIndex, int $dayOffset, array $metrics): void
    {
        if ((($dayOffset + $patientIndex) % 3) === 0 && $metrics['energy'] <= 4) {
            return;
        }

        $activities = ['walking', 'cycling', 'yoga', 'light strength', 'swimming'];
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

    private function seedTobacco(JournalEntry $journal, array $patientData, int $dayOffset): void
    {
        if (!$patientData['smoker']) {
            return;
        }

        $journal->tobacco()->create([
            'tobacco_type' => 'cigarette',
            'cigarettes_per_day' => 4 + (($dayOffset + strlen($patientData['name'])) % 8),
            'puffs_per_day' => null,
        ]);
    }

    private function buildDoctorObservation(array $patientData, array $metrics, int $dayOffset): ?string
    {
        if ($dayOffset % 6 !== 0 && $metrics['systolic_pressure'] < 140 && $metrics['stress'] < 8) {
            return null;
        }

        if ($metrics['systolic_pressure'] >= 140) {
            return 'Blood pressure remains elevated. Reinforce treatment adherence and reduce dietary salt.';
        }

        if ($metrics['stress'] >= 8) {
            return 'Stress level is high this week. Encourage rest routines and regular hydration.';
        }

        if (in_array('Type 2 Diabetes', $patientData['chronic_diseases'], true)) {
            return 'Glycemic trend is stable overall. Continue medication and regular meal timing.';
        }

        return 'Clinical follow-up is stable. Continue current treatment and lifestyle plan.';
    }

    private function seedAnalysisResults(HealthData $healthData, array $patientData, array $metrics, int $dayOffset, Carbon $date): void
    {
        $hasDiabetes = in_array('Type 2 Diabetes', $patientData['chronic_diseases'], true);
        $glucoseBase = $hasDiabetes ? 7.0 : 5.0;
        $glucoseValue = round($glucoseBase + (($metrics['stress'] - 5) * 0.18) + (($dayOffset % 5 === 0) ? 0.15 : 0.0), 2);

        AnalysisResult::create([
            'health_data_id' => $healthData->id,
            'analysis_type' => 'Glucose',
            'result_name' => 'Fasting blood glucose',
            'value' => max(3.6, $glucoseValue),
            'unit' => 'mmol/L',
            'analysis_date' => $date->toDateString(),
        ]);

        if ($dayOffset % 7 === 0) {
            $crp = round(1.2 + (($metrics['stress'] >= 7) ? 1.3 : 0.4) + (($dayOffset % 14 === 0) ? 0.6 : 0.0), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type' => 'Inflammation',
                'result_name' => 'C-reactive protein',
                'value' => $crp,
                'unit' => 'mg/L',
                'analysis_date' => $date->toDateString(),
            ]);
        }

        if ($dayOffset % 10 === 0) {
            $cholesterol = round(172 + (($metrics['stress'] >= 7) ? 16 : 7) + (($dayOffset % 20 === 0) ? 8 : 0), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type' => 'Lipid panel',
                'result_name' => 'Total cholesterol',
                'value' => $cholesterol,
                'unit' => 'mg/dL',
                'analysis_date' => $date->toDateString(),
            ]);
        }
    }

    private function seedTreatmentChecksAndNotifications(
        User $user,
        Collection $treatments,
        HealthData $healthData,
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
                'message' => "Do not forget to take " . ($treatment->treatmentCatalog?->treatment_name ?? 'your treatment') . ' today.',
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
                    'health_data_id' => $healthData->id,
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
                    'message' => "You missed at least one dose of " . ($treatment->treatmentCatalog?->treatment_name ?? 'your treatment') . ' today.',
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
            return 'High sugar intake';
        }

        if ($stress <= 3 && $caffeine <= 2) {
            return 'Low sugar intake';
        }

        return 'Moderate sugar intake';
    }

    private function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($value, $max));
    }

    private function clampFloat(float $value, float $min, float $max): float
    {
        return max($min, min($value, $max));
    }

    private function patients(): array
    {
        return [
            [
                'name' => 'Amina Benali',
                'email' => 'amina.benali@example.test',
                'date_of_birth' => '1989-04-18',
                'gender' => 'female',
                'height'         => 165.0,
                'initial_weight' => 74.0,
                'blood_type' => 'A+',
                'goals' => ['Lower blood pressure', 'Better glucose control', 'Improve sleep quality'],
                'allergies' => ['Pollen'],
                'chronic_diseases' => ['Hypertension', 'Type 2 Diabetes'],
                'smoker' => false,
                'alcoholic' => false,
                'doctor_email' => 'dr.nadia@example.test',
                'adherence' => 0.93,
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
                'height'         => 178.0,
                'initial_weight' => 88.0,
                'blood_type' => 'O+',
                'goals' => ['Improve breathing', 'Quit smoking', 'Increase daily activity'],
                'allergies' => ['Dust mites'],
                'chronic_diseases' => ['Asthma'],
                'smoker' => true,
                'alcoholic' => false,
                'doctor_email' => null,
                'adherence' => 0.86,
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
                'height'         => 162.0,
                'initial_weight' => 62.0,
                'blood_type' => 'B+',
                'goals' => ['Maintain stable energy', 'Improve concentration'],
                'allergies' => [],
                'chronic_diseases' => ['Hypothyroidism'],
                'smoker' => false,
                'alcoholic' => true,
                'doctor_email' => 'dr.samir@example.test',
                'adherence' => 0.95,
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
                    ['type' => 'Supplement vitamine', 'name' => 'Vitamine D', 'dose' => '1000 IU', 'frequency' => 'day', 'daily_doses' => 1, 'start_offset' => -12],
                ],
            ],
            [
                'name' => 'Mehdi Boussaid',
                'email' => 'mehdi.boussaid@example.test',
                'date_of_birth' => '1968-06-03',
                'gender' => 'male',
                'height'         => 173.0,
                'initial_weight' => 91.0,
                'blood_type' => 'AB+',
                'goals' => ['Stabilize blood pressure', 'Improve medication adherence'],
                'allergies' => ['Penicillin'],
                'chronic_diseases' => ['Hypertension'],
                'smoker' => false,
                'alcoholic' => false,
                'doctor_email' => null,
                'adherence' => 0.9,
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
                'height'         => 169.0,
                'initial_weight' => 68.0,
                'blood_type' => 'O-',
                'goals' => ['Reduce migraine episodes', 'Keep stress under control'],
                'allergies' => ['Seafood'],
                'chronic_diseases' => [],
                'smoker' => false,
                'alcoholic' => true,
                'doctor_email' => null,
                'adherence' => 0.88,
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
        ];
    }
}
