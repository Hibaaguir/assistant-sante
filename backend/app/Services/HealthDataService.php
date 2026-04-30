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
        ->where('start_date', '<=', $today)
        ->where('end_date', '>=', $today)
        ->get();
    

    $medicines = [];

    foreach ($treatments as $treatment) {
        $medicine = $this->normalizeTreatment($treatment);

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

    // Normaliser un traitement pour l'affichage
    private function normalizeTreatment(Treatment $treatment): ?array
    {
        $catalog = $treatment->treatmentCatalog;
        if (!$catalog) return null;

        $name  = trim($catalog->treatment_name ?? '');
        $type  = trim($catalog->treatment_type ?? '');
        $doses = $treatment->daily_doses ?? 0;
        $unit  = trim($treatment->frequency ?? '');
        $dose  = trim($treatment->dose ?? '');

        return [
            'id'              => $treatment->id,
            'type'            => $type,
            'name'            => $name,
            'dose'            => $dose,
            'frequency_unit'  => $unit,
            'frequency_count' => $doses,
        ];
    }
}
