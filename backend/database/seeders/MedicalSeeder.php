<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;

/**
 * Classe de base pour les seeders du domaine médical.
 * Contient les données statiques (médecins, patients) et les helpers de calcul partagés.
 * Chaque seeder concret doit appeler $this->faker = fake('fr_FR') avant d'utiliser patients().
 */
abstract class MedicalSeeder extends Seeder
{
    protected const SEED_START       = '2026-03-03';
    protected const SEED_DAYS        = 63;
    protected const DEFAULT_PASSWORD = 'password123';

    protected Generator $faker;

    // ─── Données de référence ──────────────────────────────────────────────────

    protected function doctors(): array
    {
        return [
            ['name' => 'Dr Nadia Mansouri',   'email' => 'dr.nadia@example.test',  'specialty' => 'Cardiologie',    'date_of_birth' => '1979-02-11'],
            ['name' => 'Dr Samir Khadraoui',  'email' => 'dr.samir@example.test',  'specialty' => 'Endocrinologie', 'date_of_birth' => '1982-09-20'],
            ['name' => 'Dr Leila Ferjani',    'email' => 'dr.leila@example.test',  'specialty' => 'Pneumologie',    'date_of_birth' => '1985-06-08'],
        ];
    }

    /** Retourne les 8 patients avec goals/allergies/maladies générés via Faker. */
    protected function patients(): array
    {
        $objectifs = [
            'Réduire la tension artérielle',
            'Mieux contrôler la glycémie',
            'Améliorer la qualité du sommeil',
            'Augmenter l\'activité physique quotidienne',
            'Stabiliser le poids',
            'Diminuer les épisodes de migraine',
            'Renforcer l\'adhérence aux traitements',
            'Réduire le stress quotidien',
        ];

        $allergieOptions = ['Pollen', 'Acarien', 'Pénicilline', 'Fruits de mer', 'Aucune'];
        $maladieOptions  = ['Hypertension', 'Diabète de type 2', 'Asthme', 'Hypothyroïdie', 'Aucune'];

        $base = [
            [
                'name' => 'Amina Benali', 'email' => 'amina.benali@example.test',
                'date_of_birth' => '1989-04-18', 'gender' => 'female',
                'height' => 165.0, 'initial_weight' => 74.0, 'blood_type' => 'A+',
                'smoker' => false, 'alcoholic' => false,
                'doctor_email' => 'dr.nadia@example.test', 'invitation_status' => 'accepted',
                'adherence' => 0.93, 'weight_trend' => -1.4,
                'baseline' => ['sleep' => 7, 'stress' => 5, 'energy' => 6, 'hydration' => 2.2,
                               'heart_rate' => 74, 'systolic_pressure' => 136, 'diastolic_pressure' => 86, 'oxygen_saturation' => 97],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Amlodipine',  'dose' => '5 mg',   'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Antidiabétique',   'name' => 'Metformine',  'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 2, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                ],
            ],
            [
                'name' => 'Youssef Karim', 'email' => 'youssef.karim@example.test',
                'date_of_birth' => '1978-09-11', 'gender' => 'male',
                'height' => 178.0, 'initial_weight' => 88.0, 'blood_type' => 'O+',
                'smoker' => true, 'alcoholic' => false,
                'doctor_email' => 'dr.leila@example.test', 'invitation_status' => 'pending',
                'adherence' => 0.86, 'weight_trend' => -0.8,
                'baseline' => ['sleep' => 6, 'stress' => 6, 'energy' => 5, 'hydration' => 1.9,
                               'heart_rate' => 80, 'systolic_pressure' => 130, 'diastolic_pressure' => 84, 'oxygen_saturation' => 95],
                'treatments' => [
                    ['type' => 'Inhalateur respiratoire', 'name' => 'Budésonide/Formotérol', 'dose' => '2 inhalations', 'frequency' => 'day', 'daily_doses' => 2, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Antihistaminique',        'name' => 'Cétirizine',             'dose' => '10 mg',         'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-10', 'end_date' => '2026-07-03'],
                ],
            ],
            [
                'name' => 'Sara Haddad', 'email' => 'sara.haddad@example.test',
                'date_of_birth' => '1994-01-27', 'gender' => 'female',
                'height' => 162.0, 'initial_weight' => 62.0, 'blood_type' => 'B+',
                'smoker' => false, 'alcoholic' => true,
                'doctor_email' => 'dr.samir@example.test', 'invitation_status' => 'accepted',
                'adherence' => 0.95, 'weight_trend' => -0.4,
                'baseline' => ['sleep' => 7, 'stress' => 4, 'energy' => 7, 'hydration' => 2.4,
                               'heart_rate' => 70, 'systolic_pressure' => 118, 'diastolic_pressure' => 76, 'oxygen_saturation' => 98],
                'treatments' => [
                    ['type' => 'Thérapie hormonale',   'name' => 'Lévothyroxine', 'dose' => '50 mcg',  'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Supplément vitaminé',  'name' => 'Vitamine D',    'dose' => '1000 UI', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                ],
            ],
            [
                'name' => 'Mehdi Boussaid', 'email' => 'mehdi.boussaid@example.test',
                'date_of_birth' => '1968-06-03', 'gender' => 'male',
                'height' => 173.0, 'initial_weight' => 91.0, 'blood_type' => 'AB+',
                'smoker' => false, 'alcoholic' => false,
                'doctor_email' => 'dr.nadia@example.test', 'invitation_status' => 'rejected',
                'adherence' => 0.90, 'weight_trend' => -0.6,
                'baseline' => ['sleep' => 6, 'stress' => 5, 'energy' => 5, 'hydration' => 2.0,
                               'heart_rate' => 76, 'systolic_pressure' => 138, 'diastolic_pressure' => 88, 'oxygen_saturation' => 96],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Losartan',  'dose' => '50 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Anticoagulant',    'name' => 'Warfarine', 'dose' => '3 mg',  'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-05', 'end_date' => '2026-07-03'],
                ],
            ],
            [
                'name' => 'Lina Trabelsi', 'email' => 'lina.trabelsi@example.test',
                'date_of_birth' => '1992-12-15', 'gender' => 'female',
                'height' => 169.0, 'initial_weight' => 68.0, 'blood_type' => 'O-',
                'smoker' => false, 'alcoholic' => true,
                'doctor_email' => null, 'invitation_status' => null,
                'adherence' => 0.88, 'weight_trend' => -0.3,
                'baseline' => ['sleep' => 7, 'stress' => 6, 'energy' => 6, 'hydration' => 2.1,
                               'heart_rate' => 72, 'systolic_pressure' => 121, 'diastolic_pressure' => 79, 'oxygen_saturation' => 98],
                'treatments' => [
                    ['type' => 'Antidouleur',         'name' => 'Paracétamol', 'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-05-03'],
                    ['type' => 'Supplément vitaminé', 'name' => 'Vitamine C',  'dose' => '500 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-06-03'],
                ],
            ],
            [
                'name' => 'Rachid Mansour', 'email' => 'rachid.mansour@example.test',
                'date_of_birth' => '1984-03-02', 'gender' => 'male',
                'height' => 176.0, 'initial_weight' => 82.0, 'blood_type' => 'A-',
                'smoker' => true, 'alcoholic' => false,
                'doctor_email' => 'dr.leila@example.test', 'invitation_status' => 'accepted',
                'adherence' => 0.84, 'weight_trend' => -0.9,
                'baseline' => ['sleep' => 6, 'stress' => 7, 'energy' => 5, 'hydration' => 1.8,
                               'heart_rate' => 82, 'systolic_pressure' => 134, 'diastolic_pressure' => 85, 'oxygen_saturation' => 95],
                'treatments' => [
                    ['type' => 'Inhalateur respiratoire', 'name' => 'Albutérol',  'dose' => '1 inhalation', 'frequency' => 'day', 'daily_doses' => 2, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Anti-inflammatoire',      'name' => 'Ibuprofène', 'dose' => '400 mg',       'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-20', 'end_date' => '2026-04-20'],
                ],
            ],
            [
                'name' => 'Ines Gharsalli', 'email' => 'ines.gharsalli@example.test',
                'date_of_birth' => '1997-08-24', 'gender' => 'female',
                'height' => 160.0, 'initial_weight' => 59.0, 'blood_type' => 'B-',
                'smoker' => false, 'alcoholic' => false,
                'doctor_email' => 'dr.samir@example.test', 'invitation_status' => 'pending',
                'adherence' => 0.96, 'weight_trend' => 0.2,
                'baseline' => ['sleep' => 8, 'stress' => 4, 'energy' => 7, 'hydration' => 2.5,
                               'heart_rate' => 68, 'systolic_pressure' => 112, 'diastolic_pressure' => 72, 'oxygen_saturation' => 99],
                'treatments' => [
                    ['type' => 'Antihistaminique',    'name' => 'Loratadine', 'dose' => '10 mg',      'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Supplément vitaminé', 'name' => 'Fer',        'dose' => '1 comprimé', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-10', 'end_date' => '2026-07-03'],
                ],
            ],
            [
                'name' => 'Nabil Ouali', 'email' => 'nabil.ouali@example.test',
                'date_of_birth' => '1971-11-19', 'gender' => 'male',
                'height' => 171.0, 'initial_weight' => 85.0, 'blood_type' => 'O+',
                'smoker' => false, 'alcoholic' => true,
                'doctor_email' => null, 'invitation_status' => null,
                'adherence' => 0.87, 'weight_trend' => -0.5,
                'baseline' => ['sleep' => 6, 'stress' => 6, 'energy' => 5, 'hydration' => 2.0,
                               'heart_rate' => 78, 'systolic_pressure' => 132, 'diastolic_pressure' => 83, 'oxygen_saturation' => 96],
                'treatments' => [
                    ['type' => 'Antihypertenseur', 'name' => 'Ramipril',    'dose' => '5 mg',  'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                    ['type' => 'Anticoagulant',    'name' => 'Rivaroxaban', 'dose' => '20 mg', 'frequency' => 'day', 'daily_doses' => 1, 'start_date' => '2026-03-03', 'end_date' => '2026-07-03'],
                ],
            ],
        ];

        return collect($base)->map(function (array $patient) use ($objectifs, $allergieOptions, $maladieOptions) {
            $goals    = $this->faker->randomElements($objectifs, $this->faker->numberBetween(2, 3));
            $allergies = array_values(array_filter(
                $this->faker->randomElements($allergieOptions, $this->faker->numberBetween(0, 2)),
                fn (string $v) => $v !== 'Aucune'
            ));
            $diseases = array_values(array_filter(
                $this->faker->randomElements($maladieOptions, $this->faker->numberBetween(0, 2)),
                fn (string $v) => $v !== 'Aucune'
            ));

            // Scénarios cliniques stables pour les cas métier importants.
            if ($patient['name'] === 'Amina Benali') {
                $diseases = ['Hypertension', 'Diabète de type 2'];
            }
            if ($patient['name'] === 'Youssef Karim') {
                $diseases  = ['Asthme'];
                $allergies = ['Acarien'];
            }

            return array_merge($patient, [
                'goals'            => $goals,
                'allergies'        => $allergies,
                'chronic_diseases' => $diseases,
            ]);
        })->all();
    }

    // ─── Calcul des métriques journalières ────────────────────────────────────

    protected function buildDailyMetrics(array $patientData, int $patientIndex, int $dayOffset, Carbon $date): array
    {
        $wave      = sin(($dayOffset + ($patientIndex * 2)) / 5);
        $isWeekend = $date->isWeekend();

        $sleep = $this->clampInt(
            (int) round($patientData['baseline']['sleep'] + ($isWeekend ? 1 : 0) - ($wave > 0.6 ? 1 : 0)),
            5, 9
        );

        $stress = $this->clampInt(
            (int) round(
                $patientData['baseline']['stress']
                + ($isWeekend ? -1 : 1)
                + ($wave > 0.7 ? 1 : 0)
                - ($sleep >= 8 ? 1 : 0)
            ),
            1, 10
        );

        $energy = $this->clampInt(
            (int) round(
                $patientData['baseline']['energy']
                + ($sleep >= 8 ? 1 : 0)
                - ($stress >= 7 ? 1 : 0)
                + ($wave < -0.5 ? -1 : 0)
            ),
            1, 10
        );

        $caffeine  = $this->clampInt(1 + (($dayOffset + $patientIndex) % 4) + ($stress >= 7 ? 1 : 0), 0, 8);
        $hydration = $this->clampFloat(
            (float) ($patientData['baseline']['hydration'] + ($isWeekend ? 0.3 : 0.0) - ($caffeine >= 4 ? 0.2 : 0.0)),
            1.4, 4.0
        );

        $alcohol        = (bool) ($patientData['alcoholic'] && $isWeekend && (($dayOffset + $patientIndex) % 2 === 0));
        $alcoholGlasses = $alcohol ? (1 + (($dayOffset + $patientIndex) % 3)) : null;

        $heartRate = $this->clampInt(
            (int) round($patientData['baseline']['heart_rate'] + ($wave * 4) + ($stress >= 8 ? 3 : 0)),
            55, 120
        );

        $systolic = $this->clampInt(
            (int) round($patientData['baseline']['systolic_pressure'] + ($wave * 5) + ($stress >= 8 ? 4 : 0)),
            95, 170
        );

        $diastolic = $this->clampInt(
            (int) round($patientData['baseline']['diastolic_pressure'] + ($wave * 3) + ($stress >= 8 ? 2 : 0)),
            55, 110
        );

        $oxygen = $this->clampInt(
            (int) round($patientData['baseline']['oxygen_saturation'] - ($patientData['smoker'] ? 1 : 0) + ($wave < -0.7 ? -1 : 0)),
            90, 100
        );

        $weight = (float) $patientData['initial_weight']
            + ($dayOffset * ((float) ($patientData['weight_trend'] ?? 0.0) / 30))
            + ($wave * 0.6)
            + $this->faker->randomFloat(1, -0.3, 0.3);

        return [
            'sleep'             => $sleep,
            'stress'            => $stress,
            'energy'            => $energy,
            'caffeine'          => $caffeine,
            'hydration'         => round($hydration, 1),
            'sugar_intake'      => $this->buildSugarIntakeLabel($stress, $caffeine),
            'alcohol'           => $alcohol,
            'alcohol_glasses'   => $alcoholGlasses,
            'heart_rate'        => $heartRate,
            'systolic_pressure' => $systolic,
            'diastolic_pressure'=> $diastolic,
            'oxygen_saturation' => $oxygen,
            'weight'            => round($this->clampFloat($weight, 45.0, 130.0), 1),
        ];
    }

    // Retourne 'low'/'medium'/'high' pour correspondre au mapping du frontend
    protected function buildSugarIntakeLabel(int $stress, int $caffeine): string
    {
        if ($stress >= 8 || $caffeine >= 5) {
            return 'high';
        }
        if ($stress <= 3 && $caffeine <= 2) {
            return 'low';
        }
        return 'medium';
    }

    protected function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($value, $max));
    }

    protected function clampFloat(float $value, float $min, float $max): float
    {
        return max($min, min($value, $max));
    }
}
