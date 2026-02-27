<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHealthLabResultRequest;
use App\Http\Requests\Api\StoreHealthVitalRequest;
use App\Http\Requests\Api\SyncHealthTreatmentChecksRequest;
use App\Http\Requests\Api\UpdateHealthLabResultRequest;
use App\Models\HealthLabResult;
use App\Models\HealthTreatmentCheck;
use App\Models\HealthVital;
use App\Models\ProfilSante;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HealthDataController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $days = max(1, min((int) $request->query('days', 7), 30));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $vitals = HealthVital::query()
            ->where('user_id', $userId)
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get();

        $latestVitals = HealthVital::query()
            ->where('user_id', $userId)
            ->latest('measured_at')
            ->first();

        $labResults = HealthLabResult::query()
            ->where('user_id', $userId)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $treatmentChecks = HealthTreatmentCheck::query()
            ->where('user_id', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->orderBy('medication_name')
            ->get();

        $treatmentMedicines = $this->resolveTreatmentMedicines($userId);

        return response()->json([
            'message' => 'Health data fetched successfully.',
            'data' => [
                'latest_vitals' => $latestVitals,
                'vitals' => $vitals,
                'vitals_chart' => $this->buildVitalsChartSeries($vitals, $days),
                'lab_results' => $labResults,
                'treatment_medicines' => $treatmentMedicines,
                'treatment_checks' => $treatmentChecks,
            ],
        ]);
    }

    public function listVitals(Request $request): JsonResponse
    {
        $days = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1)->startOfDay();

        $rows = HealthVital::query()
            ->where('user_id', $request->user()->id)
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'message' => 'Vitals fetched successfully.',
            'data' => $rows,
        ]);
    }

    public function storeVital(StoreHealthVitalRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;
        $payload['measured_at'] = $payload['measured_at'] ?? now();

        $vital = HealthVital::create($payload);

        return response()->json([
            'message' => 'Vital saved successfully.',
            'data' => $vital,
        ], 201);
    }

    public function listLabResults(Request $request): JsonResponse
    {
        $rows = HealthLabResult::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Lab results fetched successfully.',
            'data' => $rows,
        ]);
    }

    public function storeLabResult(StoreHealthLabResultRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $row = HealthLabResult::create($payload);

        return response()->json([
            'message' => 'Lab result saved successfully.',
            'data' => $row,
        ], 201);
    }

    public function updateLabResult(UpdateHealthLabResultRequest $request, HealthLabResult $healthLabResult): JsonResponse
    {
        if ($healthLabResult->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this lab result.',
            ], 403);
        }

        $healthLabResult->update($request->validated());

        return response()->json([
            'message' => 'Lab result updated successfully.',
            'data' => $healthLabResult->fresh(),
        ]);
    }

    public function destroyLabResult(Request $request, HealthLabResult $healthLabResult): JsonResponse
    {
        if ($healthLabResult->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this lab result.',
            ], 403);
        }

        $healthLabResult->delete();

        return response()->json([
            'message' => 'Lab result deleted successfully.',
        ]);
    }

    public function listTreatmentChecks(Request $request): JsonResponse
    {
        $days = max(1, min((int) $request->query('days', 14), 90));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $rows = HealthTreatmentCheck::query()
            ->where('user_id', $request->user()->id)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->orderBy('medication_name')
            ->get();

        return response()->json([
            'message' => 'Treatment checks fetched successfully.',
            'data' => $rows,
        ]);
    }

    public function syncTreatmentChecks(SyncHealthTreatmentChecksRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        foreach ($request->validated('checks') as $check) {
            HealthTreatmentCheck::updateOrCreate(
                [
                    'user_id' => $userId,
                    'check_date' => $check['check_date'],
                    'medication_key' => $check['medication_key'],
                ],
                [
                    'medication_name' => $check['medication_name'],
                    'dose' => $check['dose'] ?? null,
                    'taken' => (bool) $check['taken'],
                    'checked_at' => ($check['taken'] ?? false)
                        ? ($check['checked_at'] ?? now())
                        : null,
                ]
            );
        }

        return response()->json([
            'message' => 'Treatment checks synchronized successfully.',
        ]);
    }

    private function buildVitalsChartSeries(Collection $vitals, int $days): array
    {
        $dates = collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset)->toDateString())
            ->values();

        $grouped = $vitals
            ->groupBy(fn (HealthVital $vital) => optional($vital->measured_at)->toDateString())
            ->map(function (Collection $items): array {
                $avg = fn (string $field) => round((float) $items->avg($field), 1);

                return [
                    'heart_rate' => $avg('heart_rate'),
                    'systolic_pressure' => $avg('systolic_pressure'),
                    'oxygen_saturation' => $avg('oxygen_saturation'),
                ];
            });

        return [
            'labels' => $dates,
            'heart_rate' => $dates->map(fn (string $date) => $grouped[$date]['heart_rate'] ?? null)->all(),
            'systolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['systolic_pressure'] ?? null)->all(),
            'oxygen_saturation' => $dates->map(fn (string $date) => $grouped[$date]['oxygen_saturation'] ?? null)->all(),
        ];
    }

    private function resolveTreatmentMedicines(int $userId): array
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

            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name !== '' ? $name : $type) ?? 'traitement');
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'traitement';
            }

            $frequencyCount = (int) ($item['frequency_count'] ?? 0);
            $frequencyUnit = trim((string) ($item['frequency_unit'] ?? ''));
            $dosesPerDay = 1;
            if ($frequencyCount > 0) {
                $dosesPerDay = $frequencyUnit === 'jour' ? $frequencyCount : 1;
            }
            $frequency = $frequencyCount > 0 && $frequencyUnit !== ''
                ? $frequencyCount.' fois / '.$frequencyUnit
                : 'Non precise';

            $medicines[] = [
                'id' => $base.'-'.($index + 1),
                'name' => $name !== '' ? $name : ucfirst($type),
                'dose' => trim((string) ($item['dose'] ?? '')) ?: 'Dose non precisee',
                'freq' => $frequency,
                'doses_per_day' => max(1, min($dosesPerDay, 12)),
                'note' => trim((string) ($item['duration'] ?? '')) ?: ($type !== '' ? ucfirst($type) : ''),
            ];
        }

        if (empty($medicines) && ($profil?->prend_medicament) && ! empty($profil?->nom_medicament)) {
            $nom = trim((string) $profil->nom_medicament);
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nom) ?? 'medicament');
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'medicament';
            }

            $medicines[] = [
                'id' => $base.'-1',
                'name' => $nom,
                'dose' => 'Dose non precisee',
                'freq' => 'Non precise',
                'doses_per_day' => 1,
                'note' => '',
            ];
        }

        return $medicines;
    }
}
