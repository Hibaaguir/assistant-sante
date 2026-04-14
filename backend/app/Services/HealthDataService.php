<?php

namespace App\Services;

use App\Models\AnalysisResult;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
    // Construire la serie de graphique des signes vitaux
    public function buildVitalSignsChartSeries(Collection $vitals, int $days): array
    {
        $dates = collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset)->toDateString())
            ->values();

        $grouped = $vitals
            ->groupBy(fn (VitalSigns $v) => $v->measured_at?->toDateString())
            ->map(fn (Collection $items) => $this->extractVitalSignsByDate($items));

        return [
            'labels'             => $dates,
            'heart_rate'         => $dates->map(fn (string $date) => $grouped[$date]['heart_rate'] ?? null)->all(),
            'systolic_pressure'  => $dates->map(fn (string $date) => $grouped[$date]['systolic_pressure'] ?? null)->all(),
            'diastolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['diastolic_pressure'] ?? null)->all(),
            'oxygen_saturation'  => $dates->map(fn (string $date) => $grouped[$date]['oxygen_saturation'] ?? null)->all(),
        ];
    }

    // Resoudre la liste des traitements actifs pour aujourd'hui
    public function resolveTreatmentMedicines(int $userId): array
    {
        $today = Carbon::today()->toDateString();

        $treatments = Treatment::query()
            ->with('treatmentCatalog')
            ->where('user_id', $userId)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        return $treatments
            ->map(fn (Treatment $t) => $this->normalizeMedicine($t))
            ->filter()
            ->values()
            ->all();
    }

    // Recuperer le dernier enregistrement de signes vitaux pour l'utilisateur
    public function latestVitals(int $userId): ?VitalSigns
    {
        return VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where(fn ($q) => $q
                ->whereNotNull('heart_rate')
                ->orWhereNotNull('systolic_pressure')
                ->orWhereNotNull('diastolic_pressure')
                ->orWhereNotNull('oxygen_saturation')
            )
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();
    }

    // Serialiser les verifications de traitement en tableau standard
    public function serializeTreatmentChecks(Collection $rows): array
    {
        return $rows->map(function (TreatmentCheck $check) {
            $treatment = $check->treatment;
            return [
                'id'             => $check->id,
                'treatment_id'   => $check->treatment_id,
                'health_data_id' => $check->health_data_id,
                'check_date'     => $check->check_date?->toDateString(),
                'medication_key' => $check->medication_key,
                'treatment_name'=> $treatment?->treatmentCatalog?->treatment_name,
                'dose'           => $treatment?->dose,
                'taken'          => (bool) $check->taken,
                'checked_at'     => $check->checked_at?->toDateTimeString(),
                'created_at'     => $check->created_at?->toISOString(),
                'updated_at'     => $check->updated_at?->toISOString(),
            ];
        })->values()->all();
    }

    // Construire des alertes cliniques a partir des donnees du patient
    public function buildPatientAlerts(?VitalSigns $latestVitals, Collection $labResults): array
    {
        $alerts = [];

        if ($latestVitals?->systolic_pressure >= 140) {
            $alerts[] = [
                'severity'       => 'warning',
                'title'          => 'Alert',
                'message'        => 'Elevated blood pressure: ' . (int) $latestVitals->systolic_pressure . '/' . (int) ($latestVitals->diastolic_pressure ?? 0) . ' mmHg',
                'recommendation' => 'Monitor blood pressure and contact patient if elevation persists.',
                'measured_at'    => $latestVitals->measured_at?->toISOString(),
            ];
        }

        $glucose = $labResults->first(fn (AnalysisResult $r) => str_contains(strtolower((string) $r->analysis_type), 'glucose'));

        if ($glucose && is_numeric($glucose->analysis_result) && $glucose->analysis_result < 3.9) {
            $alerts[] = [
                'severity'       => 'critical',
                'title'          => 'Critical Alert',
                'message'        => 'Very low blood glucose detected: ' . rtrim(rtrim((string) $glucose->analysis_result, '0'), '.') . ' ' . ($glucose->normal_range ?: 'mmol/L'),
                'recommendation' => 'Contact patient immediately. Emergency sugar intake recommended.',
                'measured_at'    => $glucose->analysis_date?->toISOString(),
            ];
        }

        return $alerts;
    }

    // Extraire les signes vitaux par date
    private function extractVitalSignsByDate(Collection $items): array
    {
        $sorted = $items->sortByDesc(fn (VitalSigns $v) =>
            ($v->measured_at?->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00') . '#' .
            str_pad((string) $v->id, 10, '0', STR_PAD_LEFT)
        );

        return [
            'heart_rate'         => $this->getLatestValue($sorted, 'heart_rate'),
            'systolic_pressure'  => $this->getLatestValue($sorted, 'systolic_pressure'),
            'diastolic_pressure' => $this->getLatestValue($sorted, 'diastolic_pressure'),
            'oxygen_saturation'  => $this->getLatestValue($sorted, 'oxygen_saturation'),
        ];
    }

    // Obtenir la derniere valeur pour un champ donne
    private function getLatestValue(Collection $items, string $field): ?float
    {
        $row = $items->first(fn (VitalSigns $v) => $v->{$field} !== null);
        return $row ? round((float) $row->{$field}, 1) : null;
    }

    // Normaliser un medicament pour l'affichage
    private function normalizeMedicine(Treatment $treatment): ?array
    {
        $name = trim((string) ($treatment->treatmentCatalog?->treatment_name ?? ''));
        $type = trim((string) ($treatment->treatmentCatalog?->treatment_type ?? ''));

        if ($name === '' && $type === '') return null;

        $frequencyCount = (int) ($treatment->daily_doses ?? 0);
        $frequencyUnit  = trim((string) ($treatment->frequency ?? ''));
        $dosesPerDay    = ($frequencyCount > 0 && $frequencyUnit === 'day') ? $frequencyCount : 1;

        return [
            // Use the real database ID so the frontend builds medication_key as "{id}__dose_{n}"
            'id'           => $treatment->id,
            'name'         => $name ?: ucfirst($type),
            'dose'         => trim((string) ($treatment->dose ?? '')) ?: 'Dose non spécifiée',
            'freq'         => $frequencyCount > 0 && $frequencyUnit
                                ? "$frequencyCount fois / $frequencyUnit"
                                : 'Non spécifiée',
            'doses_per_day' => max(1, min($dosesPerDay, 12)),
            'note'          => $type ? ucfirst($type) : '',
            'start_date'    => $treatment->start_date?->toDateString(),
            'end_date'      => $treatment->end_date?->toDateString(),
        ];
    }
}
