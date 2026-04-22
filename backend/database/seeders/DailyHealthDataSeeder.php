<?php

namespace Database\Seeders;

use App\Models\AnalysisResult;
use App\Models\DoctorInvitation;
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

            // Utiliser les maladies chroniques du profil en base (source de vérité).
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

    // ─── Résultats d'analyses ─────────────────────────────────────────────────

    private function seedAnalysisResults(HealthData $healthData, array $patientData, array $metrics, int $dayOffset, Carbon $date): void
    {
        $hasDiabetes  = in_array('Diabete de type 2', $patientData['chronic_diseases'], true);
        $glucoseBase  = $hasDiabetes ? 7.0 : 5.0;
        $glucoseValue = round($glucoseBase + (($metrics['stress'] - 5) * 0.18) + (($dayOffset % 5 === 0) ? 0.15 : 0.0), 2);

        AnalysisResult::create([
            'health_data_id' => $healthData->id,
            'analysis_type'  => 'Glucose',
            'result_name'    => 'Glycemie a jeun',
            'value'          => max(3.6, $glucoseValue),
            'unit'           => 'mmol/L',
            'analysis_date'  => $date->toDateString(),
        ]);

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

        if ($dayOffset % 10 === 0) {
            $cholesterol = round(172 + (($metrics['stress'] >= 7) ? 16 : 7) + (($dayOffset % 20 === 0) ? 8 : 0), 2);
            AnalysisResult::create([
                'health_data_id' => $healthData->id,
                'analysis_type'  => 'Bilan lipidique',
                'result_name'    => 'Cholesterol total',
                'value'          => $cholesterol,
                'unit'           => 'mg/dL',
                'analysis_date'  => $date->toDateString(),
            ]);
        }
    }
}
