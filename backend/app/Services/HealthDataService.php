<?php

namespace App\Services;

use App\Models\HealthVital;
use App\Models\ProfilSante;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
    public function construireSeriesGraphiqueSignesVitaux(Collection $vitals, int $days): array
    {
        $dates = collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset)->toDateString())
            ->values();

        $grouped = $vitals
            ->groupBy(fn (HealthVital $v) => $v->measured_at?->toDateString())
            ->map(fn (Collection $items) => $this->extraireSignesVitauxParDate($items));

        return [
            'labels'             => $dates,
            'heart_rate'         => $dates->map(fn (string $date) => $grouped[$date]['heart_rate'] ?? null)->all(),
            'systolic_pressure'  => $dates->map(fn (string $date) => $grouped[$date]['systolic_pressure'] ?? null)->all(),
            'diastolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['diastolic_pressure'] ?? null)->all(),
            'oxygen_saturation'  => $dates->map(fn (string $date) => $grouped[$date]['oxygen_saturation'] ?? null)->all(),
        ];
    }

    public function resoudreMedicamentsTraitement(int $userId): array
    {
        $profil = ProfilSante::query()->where('user_id', $userId)->first();
        $medicines = [];

        foreach ((array) $profil?->traitements as $index => $item) {
            if (!is_array($item)) continue;

            $medicines[] = $this->normaliseMedicament($item, $index);
        }

        return array_filter($medicines);
    }

    private function extraireSignesVitauxParDate(Collection $items): array
    {
        $sorted = $items->sortByDesc(fn (HealthVital $v) => 
            ($v->measured_at?->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00') . '#' . 
            str_pad((string) $v->id, 10, '0', STR_PAD_LEFT)
        );

        return [
            'heart_rate'         => $this->obtenirDerniereValeur($sorted, 'heart_rate'),
            'systolic_pressure'  => $this->obtenirDerniereValeur($sorted, 'systolic_pressure'),
            'diastolic_pressure' => $this->obtenirDerniereValeur($sorted, 'diastolic_pressure'),
            'oxygen_saturation'  => $this->obtenirDerniereValeur($sorted, 'oxygen_saturation'),
        ];
    }

    private function obtenirDerniereValeur(Collection $items, string $field): ?float
    {
        $row = $items->first(fn (HealthVital $v) => $v->{$field} !== null);
        return $row ? round((float) $row->{$field}, 1) : null;
    }

    private function normaliseMedicament(array $item, int $index): ?array
    {
        $name = trim((string) ($item['name'] ?? ''));
        $type = trim((string) ($item['type'] ?? ''));

        if ($name === '' && $type === '') return null;

        $id = $this->genererIdMedicament($name ?: $type, $index);
        $frequencyCount = (int) ($item['frequency_count'] ?? 0);
        $frequencyUnit = trim((string) ($item['frequency_unit'] ?? ''));
        $dosesPerDay = ($frequencyCount > 0 && $frequencyUnit === 'jour') ? $frequencyCount : 1;

        return [
            'id' => $id,
            'name' => $name ?: ucfirst($type),
            'dose' => trim((string) ($item['dose'] ?? '')) ?: 'Dose non precisee',
            'freq' => $frequencyCount > 0 && $frequencyUnit ? "$frequencyCount fois / $frequencyUnit" : 'Non precise',
            'doses_per_day' => max(1, min($dosesPerDay, 12)),
            'note' => trim((string) ($item['duration'] ?? '')) ?: ($type ? ucfirst($type) : ''),
        ];
    }

    private function genererIdMedicament(string $name, int $index): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $base = trim($base, '-') ?: 'medicament';
        return $base . '-' . ($index + 1);
    }
}
