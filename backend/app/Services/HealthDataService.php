<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthDataService
{
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
