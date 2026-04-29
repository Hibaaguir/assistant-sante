<?php

namespace Database\Seeders;

use App\Models\AnalysisResult;
use App\Models\HealthData;
use App\Models\User;
use App\Models\VitalSigns;
use Carbon\Carbon;

/**
 * Génère 30 jours de données de santé par patient :
 * HealthData, VitalSigns, AnalysisResults, observation médecin, poids courant.
 */
class DailyHealthDataSeeder extends MedicalSeeder
{
    public function run(): void
    {
        $this->faker = fake('fr_FR');
        $startDate   = Carbon::today()->subDays(29)->startOfDay();

        foreach ($this->patients() as $patientIndex => $patientData) {
            $user = User::query()
                ->whereHas('account', fn ($q) => $q->where('email', strtolower($patientData['email'])))
                ->with(['healthProfile', 'invitationsAsPatient' => fn ($q) => $q->where('status', 'accepted')])
                ->first();

            if (! $user) {
                continue;
            }

            $profile         = $user->healthProfile;
            $chronicDiseases = $profile?->chronic_diseases ?? $patientData['chronic_diseases'];
            $localData       = array_merge($patientData, ['chronic_diseases' => $chronicDiseases]);

            $acceptedDoctor = null;
            $invitation     = $user->invitationsAsPatient->first();
            if ($invitation?->doctor_user_id) {
                $acceptedDoctor = User::find($invitation->doctor_user_id);
            }

            $lastWeight = (float) $patientData['initial_weight'];

            for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
                $date    = $startDate->copy()->addDays($dayOffset);
                $metrics = $this->buildDailyMetrics($localData, $patientIndex, $dayOffset, $date);
                $lastWeight = (float) $metrics['weight'];

                $observation = $this->buildDoctorObservation($localData, $metrics, $dayOffset);

                $healthData = HealthData::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date->toDateString()],
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
                    'health_data_id'     => $healthData->id,
                    'measured_at'        => $measuredAt,
                    'heart_rate'         => $metrics['heart_rate'],
                    'systolic_pressure'  => $metrics['systolic_pressure'],
                    'diastolic_pressure' => $metrics['diastolic_pressure'],
                    'oxygen_saturation'  => $metrics['oxygen_saturation'],
                ]);

                $this->seedAnalysisResults($healthData, $localData, $metrics, $dayOffset, $date);
            }

            $profile?->update(['current_weight' => round($lastWeight, 1)]);
        }
    }

    // ─── Observation médicale ─────────────────────────────────────────────────

    private function buildDoctorObservation(array $patientData, array $metrics, int $dayOffset): ?string
    {
        if ($dayOffset % 6 !== 0 && $metrics['systolic_pressure'] < 140 && $metrics['stress'] < 8) {
            return null;
        }

        if ($metrics['systolic_pressure'] >= 145) {
            return 'Tension artérielle encore élevée. Revoir l\'adhérence thérapeutique et réduire le sel alimentaire.';
        }

        if ($metrics['stress'] >= 8) {
            return 'Niveau de stress important cette semaine. Renforcer les routines de sommeil et la respiration guidée.';
        }

        if (in_array('Diabète de type 2', $patientData['chronic_diseases'], true)) {
            return 'Équilibre glycémique globalement acceptable. Poursuivre les traitements et régulariser les repas.';
        }

        return 'État clinique stable. Maintenir le plan actuel et programmer le prochain suivi.';
    }

    // ─── Résultats d'analyses ─────────────────────────────────────────────────

    private function seedAnalysisResults(HealthData $healthData, array $patientData, array $metrics, int $dayOffset, Carbon $date): void
    {
        $hasDiabetes  = in_array('Diabète de type 2', $patientData['chronic_diseases'], true);
        $hasAsthme    = in_array('Asthme', $patientData['chronic_diseases'], true);
        $hasHypo      = in_array('Hypothyroïdie', $patientData['chronic_diseases'], true);

        // Glycémie — quotidienne
        $glucoseBase  = $hasDiabetes ? 7.0 : 5.0;
        $glucoseValue = round($glucoseBase + (($metrics['stress'] - 5) * 0.18) + (($dayOffset % 5 === 0) ? 0.15 : 0.0), 2);
        AnalysisResult::create([
            'health_data_id' => $healthData->id,
            'analysis_type'  => 'Glucose',
            'result_name'    => 'Glycémie à jeun',
            'value'          => max(3.6, $glucoseValue),
            'unit'           => 'mmol/L',
            'analysis_date'  => $date->toDateString(),
        ]);

        // CRP (inflammation) — hebdomadaire
        if ($dayOffset % 7 === 0) {
            $crp = round(1.2 + (($metrics['stress'] >= 7) ? 1.3 : 0.4) + (($dayOffset % 14 === 0) ? 0.6 : 0.0), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Inflammation',
                'result_name'    => 'CRP',
                'value'          => $crp,
                'unit'           => 'mg/L',
                'analysis_date'  => $date->toDateString(),
            ]);
        }

        // Bilan lipidique — tous les 10 jours
        if ($dayOffset % 10 === 0) {
            $cholesterol = round(172 + (($metrics['stress'] >= 7) ? 16 : 7) + (($dayOffset % 20 === 0) ? 8 : 0), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Bilan lipidique',
                'result_name'    => 'Cholestérol total',
                'value'          => $cholesterol,
                'unit'           => 'mg/dL',
                'analysis_date'  => $date->toDateString(),
            ]);
        }

        // Créatinine (fonction rénale) — tous les 14 jours
        if ($dayOffset % 14 === 0) {
            $creatinine = round(75 + (($metrics['stress'] >= 7) ? 8 : 2) + ($this->faker->numberBetween(-4, 4)), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Fonction rénale',
                'result_name'    => 'Créatinine',
                'value'          => max(50, $creatinine),
                'unit'           => 'µmol/L',
                'analysis_date'  => $date->toDateString(),
            ]);
        }

        // Hémoglobine — tous les 14 jours décalés
        if ($dayOffset % 14 === 7) {
            $hemo = round(13.5 + (($metrics['energy'] >= 7) ? 0.8 : -0.4) + ($this->faker->randomFloat(1, -0.5, 0.5)), 1);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Hémogramme',
                'result_name'    => 'Hémoglobine',
                'value'          => max(10.0, $hemo),
                'unit'           => 'g/dL',
                'analysis_date'  => $date->toDateString(),
            ]);
        }

        // TSH — uniquement pour Hypothyroïdie, tous les 15 jours
        if ($hasHypo && $dayOffset % 15 === 0) {
            $tsh = round(2.5 + (($dayOffset % 30 === 0) ? 1.2 : 0.3) + ($this->faker->randomFloat(2, -0.4, 0.4)), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Bilan thyroïdien',
                'result_name'    => 'TSH',
                'value'          => max(0.4, $tsh),
                'unit'           => 'mUI/L',
                'analysis_date'  => $date->toDateString(),
            ]);
        }

        // DEP (débit expiratoire de pointe) — uniquement pour Asthme, hebdomadaire
        if ($hasAsthme && $dayOffset % 7 === 3) {
            $dep = round(380 + (($metrics['oxygen_saturation'] >= 97) ? 30 : -20) + ($this->faker->numberBetween(-15, 15)));
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Fonction respiratoire',
                'result_name'    => 'DEP',
                'value'          => max(250, $dep),
                'unit'           => 'L/min',
                'analysis_date'  => $date->toDateString(),
            ]);
        }
    }
}
