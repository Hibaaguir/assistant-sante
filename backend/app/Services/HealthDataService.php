<?php

namespace App\Services;

use App\Models\HealthVital;
use App\Models\ProfilSante;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
    /**
     * Construit les séries de données de signes vitaux pour l'affichage en graphique.
     * Regroupe les valeurs par date et retourne la dernière mesure de chaque journée.
     */
    public function construireSeriesGraphiqueSignesVitaux(Collection $vitals, int $days): array
    {
        $dates = collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset)->toDateString())
            ->values();

        $grouped = $vitals
            ->groupBy(fn (HealthVital $vital) => $vital->measured_at?->toDateString())
            ->map(function (Collection $items): array {
                $sorted = $items->sortByDesc(function (HealthVital $vital): string {
                    $timestamp = $vital->measured_at?->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00';
                    return $timestamp.'#'.str_pad((string) $vital->id, 10, '0', STR_PAD_LEFT);
                });

                $latestMeasuredValue = function (string $field) use ($sorted): ?float {
                    $row = $sorted->first(fn (HealthVital $vital) => $vital->{$field} !== null);
                    if (! $row) {
                        return null;
                    }
                    return round((float) $row->{$field}, 1);
                };

                return [
                    'heart_rate'          => $latestMeasuredValue('heart_rate'),
                    'systolic_pressure'   => $latestMeasuredValue('systolic_pressure'),
                    'diastolic_pressure'  => $latestMeasuredValue('diastolic_pressure'),
                    'oxygen_saturation'   => $latestMeasuredValue('oxygen_saturation'),
                ];
            });

        return [
            'labels'             => $dates,
            'heart_rate'         => $dates->map(fn (string $date) => $grouped[$date]['heart_rate'] ?? null)->all(),
            'systolic_pressure'  => $dates->map(fn (string $date) => $grouped[$date]['systolic_pressure'] ?? null)->all(),
            'diastolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['diastolic_pressure'] ?? null)->all(),
            'oxygen_saturation'  => $dates->map(fn (string $date) => $grouped[$date]['oxygen_saturation'] ?? null)->all(),
        ];
    }

    /**
     * Résout la liste des médicaments/traitements d'un utilisateur à partir de son profil santé.
     * Normalise les entrées brutes du champ JSON "traitements" et gère le cas nominatif simple.
     */
    public function resoudreMedicamentsTraitement(int $userId): array
    {
        $profil = ProfilSante::query()
            ->where('user_id', $userId)
            ->first();

        $rawTreatments = is_array($profil?->traitements) ? $profil->traitements : [];
        $medicines = [];

        foreach ($rawTreatments as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            $type = trim((string) ($item['type'] ?? ''));
            if ($name === '' && $type === '') {
                continue;
            }

            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name !== '' ? $name : $type));
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'traitement';
            }

            $frequencyCount = (int) ($item['frequency_count'] ?? 0);
            $frequencyUnit = trim((string) ($item['frequency_unit'] ?? ''));
            $dosesPerDay = ($frequencyCount > 0 && $frequencyUnit === 'jour') ? $frequencyCount : 1;
            $frequency = $frequencyCount > 0 && $frequencyUnit !== ''
                ? $frequencyCount.' fois / '.$frequencyUnit
                : 'Non precise';

            $medicines[] = [
                'id'           => $base.'-'.($index + 1),
                'name'         => $name !== '' ? $name : ucfirst($type),
                'dose'         => trim((string) ($item['dose'] ?? '')) ?: 'Dose non precisee',
                'freq'         => $frequency,
                'doses_per_day' => max(1, min($dosesPerDay, 12)),
                'note'         => trim((string) ($item['duration'] ?? '')) ?: ($type !== '' ? ucfirst($type) : ''),
            ];
        }

        if (empty($medicines) && ($profil?->prend_medicament) && ! empty($profil?->nom_medicament)) {
            $nom = trim((string) $profil->nom_medicament);
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nom));
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'medicament';
            }

            $medicines[] = [
                'id'            => $base.'-1',
                'name'          => $nom,
                'dose'          => 'Dose non precisee',
                'freq'          => 'Non precise',
                'doses_per_day' => 1,
                'note'          => '',
            ];
        }

        return $medicines;
    }
}
