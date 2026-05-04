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
        $startDate   = Carbon::parse(self::SEED_START)->startOfDay();

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

            for ($dayOffset = 0; $dayOffset < self::SEED_DAYS; $dayOffset++) {
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
        $hasDiabetes = in_array('Diabète de type 2', $patientData['chronic_diseases'], true);
        $hasAsthme   = in_array('Asthme',            $patientData['chronic_diseases'], true);
        $hasHypo     = in_array('Hypothyroïdie',     $patientData['chronic_diseases'], true);
        $hasHyper    = in_array('Hypertension',      $patientData['chronic_diseases'], true);
        $isMale      = ($patientData['gender'] ?? 'male') === 'male';

        // ── Biologie sanguine : Glycémie — toutes les 4 jours ──────────────
        if ($dayOffset % 4 === 0) {
            $base  = $hasDiabetes ? 7.2 : 4.9;
            $value = round($base + (($metrics['stress'] - 5) * 0.10) + $this->faker->randomFloat(2, -0.3, 0.3), 2);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'Glycémie', max(3.5, $value), 'mmol/L', $date);
        }

        // ── Biologie sanguine : CRP — tous les 10 jours ────────────────────
        if ($dayOffset % 10 === 0) {
            $value = round(1.5 + ($metrics['stress'] >= 7 ? 2.5 : 0.8) + $this->faker->randomFloat(2, 0, 1.0), 2);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'CRP', max(0.3, $value), 'mg/L', $date);
        }

        // ── Biologie sanguine : Créatinine — toutes les 2 semaines ─────────
        if ($dayOffset % 14 === 0) {
            $base  = $isMale ? 10.0 : 8.5;
            $value = round($base + $this->faker->randomFloat(1, -1.5, 1.5), 1);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'Créatinine', max(5.0, $value), 'mg/L', $date);
        }

        // ── Biologie sanguine : TSH — Hypothyroïdie uniquement, toutes les 3 semaines
        if ($hasHypo && $dayOffset % 21 === 0) {
            $value = round(2.8 + $this->faker->randomFloat(2, -0.5, 0.5), 2);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'TSH', max(0.4, $value), 'mUI/L', $date);
        }

        // ── Biologie sanguine : HbA1c — Diabète, mensuel ───────────────────
        if ($hasDiabetes && $dayOffset % 31 === 0) {
            $value = round(6.9 + $this->faker->randomFloat(1, -0.3, 0.4), 1);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'HbA1c', max(5.5, $value), '%', $date);
        }

        // ── Biologie sanguine : Insuline — Diabète, tous les 10 jours décalé
        if ($hasDiabetes && $dayOffset % 10 === 5) {
            $value = round(14.0 + $this->faker->randomFloat(1, -3.0, 4.0), 1);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'Insuline', max(2.0, $value), 'µIU/mL', $date);
        }

        // ── Biologie sanguine : Ferritine — Ines (carence en fer), toutes les 3 semaines
        if ($patientData['name'] === 'Ines Gharsalli' && $dayOffset % 21 === 0) {
            $value = round(22.0 + $this->faker->randomFloat(1, -5.0, 5.0), 1);
            $this->addAnalysis($healthData, 'Biologie sanguine', 'Ferritine', max(8.0, $value), 'ng/mL', $date);
        }

        // ── Hématologie — toutes les 3 semaines ────────────────────────────
        if ($dayOffset % 21 === 7) {
            $hemoBase   = $isMale ? 14.8 : 13.0;
            $hemo       = round($hemoBase + ($metrics['energy'] >= 7 ? 0.6 : -0.3) + $this->faker->randomFloat(1, -0.6, 0.6), 1);
            $globules   = round(6.2 + $this->faker->randomFloat(1, -1.5, 1.5), 1);
            $plaquettes = (float) (230 + $this->faker->numberBetween(-40, 60));
            $vgm        = round(88.0 + $this->faker->randomFloat(1, -5.0, 5.0), 1);

            $this->addAnalysis($healthData, 'Hématologie', 'Hémoglobine',    max(10.0, $hemo),       'g/dL', $date);
            $this->addAnalysis($healthData, 'Hématologie', 'Globules blancs', max(3.0, $globules),    'G/L',  $date);
            $this->addAnalysis($healthData, 'Hématologie', 'Plaquettes',      max(150.0, $plaquettes),'G/L',  $date);
            $this->addAnalysis($healthData, 'Hématologie', 'VGM',             max(70.0, $vgm),        'fL',   $date);
        }

        // ── Bilan lipidique complet — toutes les 3 semaines ────────────────
        if ($dayOffset % 21 === 0) {
            $chol = round(4.9 + ($metrics['stress'] >= 7 ? 0.5 : 0.2) + $this->faker->randomFloat(2, -0.3, 0.3), 2);
            $hdl  = round(($isMale ? 1.2 : 1.5) + $this->faker->randomFloat(2, -0.1, 0.2), 2);
            $ldl  = round($chol * 0.60 + $this->faker->randomFloat(2, -0.2, 0.2), 2);
            $trig = round(1.3 + $this->faker->randomFloat(2, -0.3, 0.4), 2);

            $this->addAnalysis($healthData, 'Bilan lipidique', 'Cholestérol total', max(3.0, $chol),  'mmol/L', $date);
            $this->addAnalysis($healthData, 'Bilan lipidique', 'HDL',               max(0.7, $hdl),   'mmol/L', $date);
            $this->addAnalysis($healthData, 'Bilan lipidique', 'LDL',               max(1.0, $ldl),   'mmol/L', $date);
            $this->addAnalysis($healthData, 'Bilan lipidique', 'Triglycérides',     max(0.5, $trig),  'mmol/L', $date);
        }

        // ── Fonction rénale — toutes les 3 semaines décalées ───────────────
        if ($dayOffset % 21 === 14) {
            $uree = round(5.0 + $this->faker->randomFloat(1, -1.0, 1.5), 1);
            $dfg  = (float) (88 + $this->faker->numberBetween(-12, 8));
            $this->addAnalysis($healthData, 'Fonction rénale', 'Urée', max(2.0, $uree),  'mmol/L',          $date);
            $this->addAnalysis($healthData, 'Fonction rénale', 'DFG',  max(45.0, $dfg),  'mL/min/1.73m²',   $date);
        }

        // ── Fonction hépatique — mensuel ────────────────────────────────────
        if ($dayOffset % 31 === 0) {
            $asat = (float) (27 + $this->faker->numberBetween(-5, 12));
            $alat = (float) (24 + $this->faker->numberBetween(-4, 14));
            $this->addAnalysis($healthData, 'Fonction hépatique', 'ASAT', max(10.0, $asat), 'UI/L', $date);
            $this->addAnalysis($healthData, 'Fonction hépatique', 'ALAT', max(10.0, $alat), 'UI/L', $date);
        }

        // ── Urines — diabétiques uniquement, mensuel décalé ────────────────
        if ($hasDiabetes && $dayOffset % 31 === 15) {
            $proteinurie = round($this->faker->randomFloat(2, 0.02, 0.18), 2);
            $glucosurie  = round($this->faker->randomFloat(2, 0.0, 0.25), 2);
            $this->addAnalysis($healthData, 'Urines', 'Protéinurie', $proteinurie, 'g/L', $date);
            $this->addAnalysis($healthData, 'Urines', 'Glucosurie',  $glucosurie,  'g/L', $date);
        }

        // ── Immunologie — asthme uniquement, mensuel décalé ────────────────
        if ($hasAsthme && $dayOffset % 31 === 10) {
            $igg = round(11.5 + $this->faker->randomFloat(1, -1.5, 2.0), 1);
            $iga = round(2.1 + $this->faker->randomFloat(1, -0.5, 0.8), 1);
            $this->addAnalysis($healthData, 'Immunologie', 'IgG', max(6.0, $igg), 'g/L', $date);
            $this->addAnalysis($healthData, 'Immunologie', 'IgA', max(0.5, $iga), 'g/L', $date);
        }

        // ── Cardiologie — patients hypertendus, mensuel décalé ─────────────
        if ($hasHyper && $dayOffset % 31 === 10) {
            $bnp = (float) (75 + $this->faker->numberBetween(-20, 30));
            $this->addAnalysis($healthData, 'Cardiologie', 'BNP', max(10.0, $bnp), 'pg/mL', $date);
        }

        // ── Hormonologie — Sara (traitement hormonal), mensuel décalé ──────
        if ($patientData['name'] === 'Sara Haddad' && $dayOffset % 31 === 15) {
            $cortisol = (float) (380 + $this->faker->numberBetween(-50, 80));
            $this->addAnalysis($healthData, 'Hormonologie', 'Cortisol', max(100.0, $cortisol), 'nmol/L', $date);
        }
    }

    private function addAnalysis(HealthData $healthData, string $type, string $name, float $value, string $unit, Carbon $date): void
    {
        AnalysisResult::create([
            'health_data_id' => $healthData->id,
            'analysis_type'  => $type,
            'result_name'    => $name,
            'value'          => $value,
            'unit'           => $unit,
            'analysis_date'  => $date->toDateString(),
        ]);
    }
}
