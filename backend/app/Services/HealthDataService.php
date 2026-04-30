<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
    // Construire la serie de graphique des signes vitaux
    public function buildVitalSignsChartSeries(Collection $vitals, int $days): array
    {
        // Créer la liste des dates des N derniers jours
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = Carbon::today()->subDays($i)->toDateString();
        }

        // Grouper les signes vitaux par date
        $groupedByDate = [];
        foreach ($vitals as $vital) {
            $date = $vital->measured_at?->toDateString();
            if ($date !== null) {
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = collect();
                }
                $groupedByDate[$date]->push($vital);
            }
        }

        // Extraire les dernières valeurs pour chaque date
        $extractedByDate = [];
        foreach ($groupedByDate as $date => $items) {
            $extractedByDate[$date] = $this->extractVitalSignsByDate($items);
        }

        // Construire les tableaux de données pour le graphique
        $heartRate         = [];
        $systolicPressure  = [];
        $diastolicPressure = [];
        $oxygenSaturation  = [];

        foreach ($dates as $date) {
            $heartRate[]         = $extractedByDate[$date]['heart_rate'] ?? null;
            $systolicPressure[]  = $extractedByDate[$date]['systolic_pressure'] ?? null;
            $diastolicPressure[] = $extractedByDate[$date]['diastolic_pressure'] ?? null;
            $oxygenSaturation[]  = $extractedByDate[$date]['oxygen_saturation'] ?? null;
        }

        return [
            'labels'             => $dates,
            'heart_rate'         => $heartRate,
            'systolic_pressure'  => $systolicPressure,
            'diastolic_pressure' => $diastolicPressure,
            'oxygen_saturation'  => $oxygenSaturation,
        ];
    }

    // Resoudre la liste des traitements actifs pour aujourd'hui
    public function resolveTreatmentMedicines(int $userId): array
    {
        $today = Carbon::today()->toDateString();

        $treatments = Treatment::query()
            ->with('treatmentCatalog')
            ->whereHas('healthData', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        $medicines = [];
        foreach ($treatments as $treatment) {
            $medicine = $this->normalizeMedicine($treatment);
            if ($medicine !== null) {
                $medicines[] = $medicine;
            }
        }

        return $medicines;
    }

    // Recuperer le dernier enregistrement de signes vitaux pour l'utilisateur
    public function latestVitals(int $userId): ?VitalSigns
    {
        return VitalSigns::whereHas('healthData', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where(function ($q) {
                $q->whereNotNull('heart_rate')
                  ->orWhereNotNull('systolic_pressure')
                  ->orWhereNotNull('diastolic_pressure')
                  ->orWhereNotNull('oxygen_saturation');
            })
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();
    }

    // Serialiser les verifications de traitement en tableau standard
    public function serializeTreatmentChecks(Collection $rows): array
    {
        $result = [];

        foreach ($rows as $check) {
            $treatment = $check->treatment;
            $result[] = [
                'id'             => $check->id,
                'user_id'        => $check->user_id,
                'treatment_id'   => $check->treatment_id,
                'check_date'     => $check->check_date?->toDateString(),
                'medication_key' => $check->medication_key,
                'treatment_name' => $treatment?->treatmentCatalog?->treatment_name,
                'dose'           => $treatment?->dose,
                'taken'          => (bool) $check->taken,
                'checked_at'     => $check->checked_at?->toDateTimeString(),
                'created_at'     => $check->created_at?->toISOString(),
                'updated_at'     => $check->updated_at?->toISOString(),
            ];
        }

        return $result;
    }

    // Construire des alertes cliniques a partir des donnees du patient
    public function buildPatientAlerts(?VitalSigns $latestVitals, Collection $labResults): array
    {
        $alerts = [];

        // Vérifier si la pression artérielle est élevée
        if ($latestVitals?->systolic_pressure >= 140) {
            $systolic  = (int) $latestVitals->systolic_pressure;
            $diastolic = (int) ($latestVitals->diastolic_pressure ?? 0);

            $alerts[] = [
                'severity'       => 'warning',
                'title'          => 'Alert',
                'message'        => 'Elevated blood pressure: ' . $systolic . '/' . $diastolic . ' mmHg',
                'recommendation' => 'Monitor blood pressure and contact patient if elevation persists.',
                'measured_at'    => $latestVitals->measured_at?->toISOString(),
            ];
        }

        // Chercher un résultat de glucose parmi les analyses
        $glucose = null;
        foreach ($labResults as $labResult) {
            if (str_contains(strtolower((string) $labResult->analysis_type), 'glucose')) {
                $glucose = $labResult;
                break;
            }
        }

        // Vérifier si le glucose est trop bas
        if ($glucose !== null && is_numeric($glucose->analysis_result) && $glucose->analysis_result < 3.9) {
            $glucoseValue = rtrim(rtrim((string) $glucose->analysis_result, '0'), '.');
            $glucoseUnit  = $glucose->normal_range ?: 'mmol/L';

            $alerts[] = [
                'severity'       => 'critical',
                'title'          => 'Critical Alert',
                'message'        => 'Very low blood glucose detected: ' . $glucoseValue . ' ' . $glucoseUnit,
                'recommendation' => 'Contact patient immediately. Emergency sugar intake recommended.',
                'measured_at'    => $glucose->analysis_date?->toISOString(),
            ];
        }

        return $alerts;
    }

    // Extraire les signes vitaux par date (le plus récent en premier)
    private function extractVitalSignsByDate(Collection $items): array
    {
        $sorted = $items->sortByDesc(function (VitalSigns $v) {
            $date = $v->measured_at ? $v->measured_at->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
            $id   = str_pad((string) $v->id, 10, '0', STR_PAD_LEFT);
            return $date . '#' . $id;
        });

        return [
            'heart_rate'         => $this->getLatestValue($sorted, 'heart_rate'),
            'systolic_pressure'  => $this->getLatestValue($sorted, 'systolic_pressure'),
            'diastolic_pressure' => $this->getLatestValue($sorted, 'diastolic_pressure'),
            'oxygen_saturation'  => $this->getLatestValue($sorted, 'oxygen_saturation'),
        ];
    }

    // Obtenir la derniere valeur non-nulle pour un champ donne
    private function getLatestValue(Collection $items, string $field): ?float
    {
        foreach ($items as $vital) {
            if ($vital->{$field} !== null) {
                return round((float) $vital->{$field}, 1);
            }
        }
        return null;
    }

    // Normaliser un medicament pour l'affichage
    private function normalizeMedicine(Treatment $treatment): ?array
    {
        $name = trim((string) ($treatment->treatmentCatalog?->treatment_name ?? ''));
        $type = trim((string) ($treatment->treatmentCatalog?->treatment_type ?? ''));

        // Ignorer si le nom et le type sont tous les deux vides
        if ($name === '' && $type === '') {
            return null;
        }

        $frequencyCount = (int) ($treatment->daily_doses ?? 0);
        $frequencyUnit  = trim((string) ($treatment->frequency ?? ''));

        // Calculer le nombre de doses par jour
        if ($frequencyCount > 0 && $frequencyUnit === 'day') {
            $dosesPerDay = $frequencyCount;
        } else {
            $dosesPerDay = 1;
        }

        // Construire le texte de fréquence
        if ($frequencyCount > 0 && $frequencyUnit !== '') {
            $freq = "$frequencyCount fois / $frequencyUnit";
        } else {
            $freq = 'Non spécifiée';
        }

        // Choisir le nom d'affichage
        $displayName = ($name !== '') ? $name : ucfirst($type);

        // Construire le texte de la dose
        $dose = trim((string) ($treatment->dose ?? ''));
        if ($dose === '') {
            $dose = 'Dose non spécifiée';
        }

        return [
            'id'            => $treatment->id,
            'name'          => $displayName,
            'dose'          => $dose,
            'freq'          => $freq,
            'doses_per_day' => max(1, min($dosesPerDay, 12)),
            'note'          => ($type !== '') ? ucfirst($type) : '',
            'start_date'    => $treatment->start_date?->toDateString(),
            'end_date'      => $treatment->end_date?->toDateString(),
        ];
    }
}
