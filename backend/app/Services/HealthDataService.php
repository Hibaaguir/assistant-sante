<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
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

    /**
     * Returns the list of active treatments for a user today.
     * Uses the treatment's real database ID so the frontend can generate
     * medication_key as "{treatmentId}__dose_{n}" for sync.
     */
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

    private function getLatestValue(Collection $items, string $field): ?float
    {
        $row = $items->first(fn (VitalSigns $v) => $v->{$field} !== null);
        return $row ? round((float) $row->{$field}, 1) : null;
    }

    private function normalizeMedicine(Treatment $treatment): ?array
    {
        $name = trim((string) ($treatment->treatmentCatalog?->medication_name ?? ''));
        $type = trim((string) ($treatment->treatmentCatalog?->medication_type ?? ''));

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
