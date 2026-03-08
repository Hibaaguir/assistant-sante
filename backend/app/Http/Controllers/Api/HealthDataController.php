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
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthDataController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService) {}

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
            ->where(function ($query) {
                $query
                    ->whereNotNull('heart_rate')
                    ->orWhereNotNull('systolic_pressure')
                    ->orWhereNotNull('diastolic_pressure')
                    ->orWhereNotNull('oxygen_saturation');
            })
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
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

        $treatmentMedicines = $this->healthDataService->resolveTreatmentMedicines($userId);

        return response()->json([
            'message' => 'Health data fetched successfully.',
            'data' => [
                'latest_vitals' => $latestVitals,
                'vitals' => $vitals,
                'vitals_chart' => $this->healthDataService->buildVitalsChartSeries($vitals, $days),
                'lab_results' => $labResults,
                'treatment_medicines' => $treatmentMedicines,
                'treatment_checks' => $treatmentChecks,
            ],
        ]);
    }

    public function listVitals(Request $request): JsonResponse
    {
        $days = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1);

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
        $userId = $request->user()->id;
        $measuredAt = isset($payload['measured_at']) ? Carbon::parse($payload['measured_at']) : now();
        $measuredDate = $measuredAt->toDateString();

        $existing = HealthVital::query()
            ->where('user_id', $userId)
            ->whereDate('measured_at', $measuredDate)
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            // Merge par date: on conserve les mesures deja presentes si la nouvelle saisie est null.
            $existing->update([
                'heart_rate' => $payload['heart_rate'] ?? $existing->heart_rate,
                'systolic_pressure' => $payload['systolic_pressure'] ?? $existing->systolic_pressure,
                'diastolic_pressure' => $payload['diastolic_pressure'] ?? $existing->diastolic_pressure,
                'oxygen_saturation' => $payload['oxygen_saturation'] ?? $existing->oxygen_saturation,
                'measured_at' => $measuredAt,
            ]);

            return response()->json([
                'message' => 'Vital updated successfully.',
                'data' => $existing->fresh(),
            ]);
        }

        $vital = HealthVital::create([
            'user_id' => $userId,
            'heart_rate' => $payload['heart_rate'] ?? null,
            'systolic_pressure' => $payload['systolic_pressure'] ?? null,
            'diastolic_pressure' => $payload['diastolic_pressure'] ?? null,
            'oxygen_saturation' => $payload['oxygen_saturation'] ?? null,
            'measured_at' => $measuredAt,
        ]);

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
        if ($error = $this->authorizeLabResult($healthLabResult, $request)) return $error;

        $healthLabResult->update($request->validated());

        return response()->json([
            'message' => 'Lab result updated successfully.',
            'data' => $healthLabResult->fresh(),
        ]);
    }

    public function destroyLabResult(Request $request, HealthLabResult $healthLabResult): JsonResponse
    {
        if ($error = $this->authorizeLabResult($healthLabResult, $request)) return $error;

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
                    'taken' => $check['taken'],
                    'checked_at' => $check['taken']
                        ? ($check['checked_at'] ?? now())
                        : null,
                ]
            );
        }

        return response()->json([
            'message' => 'Treatment checks synchronized successfully.',
        ]);
    }

    private function authorizeLabResult(HealthLabResult $labResult, Request $request): ?JsonResponse
    {
        if ($labResult->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access to this lab result.'], 403);
        }
        return null;
    }
}