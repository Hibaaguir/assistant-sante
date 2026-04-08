<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAnalysisResultRequest;
use App\Http\Requests\Api\StoreVitalSignsRequest;
use App\Http\Requests\Api\SyncTreatmentCheckRequest;
use App\Http\Requests\Api\UpdateAnalysisResultRequest;
use App\Models\AnalysisResult;
use App\Models\TreatmentCheck;
use App\Models\VitalSigns;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HealthDataController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Display health data overview
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        
        $days = max(1, min((int) $request->query('days', 7), 30));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $vitals = VitalSigns::query()
            ->where('user_id', $userId)
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get();

        $latestVitals = VitalSigns::query()
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

        $labResults = AnalysisResult::query()
            ->where('user_id', $userId)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $treatmentChecks = TreatmentCheck::query()
            ->where('user_id', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        $treatmentMedicines = $this->healthDataService->resolveTreatmentMedicines($userId);

        return response()->json([
            'message' => 'Health data retrieved successfully.',
            'data' => [
                'latest_vitals'       => $latestVitals,
                'vitals'              => $vitals,
                'vitals_chart'        => $this->healthDataService->buildVitalSignsChartSeries($vitals, $days),
                'lab_results'         => $labResults,
                'treatment_medicines' => $treatmentMedicines,
                'treatment_checks'    => $treatmentChecks,
            ],
        ]);
    }

    // List all vital signs
    public function indexVitals(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $days = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1);

        $rows = VitalSigns::query()
            ->where('user_id', $userId)
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'message' => 'Vital signs retrieved successfully.',
            'data' => $rows,
        ]);
    }

    // Record a new vital sign
    public function storeVital(StoreVitalSignsRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $payload = $request->validated();
        $measuredAt = isset($payload['measured_at']) ? Carbon::parse($payload['measured_at']) : now();
        $measuredDate = $measuredAt->toDateString();

        $existing = VitalSigns::query()
            ->where('user_id', $userId)
            ->whereDate('measured_at', $measuredDate)
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();

        // Check if vital signs already exist for this date
        if ($existing) {
            // Merge by date: keep existing measurements if new entry is null
            $existing->update([
                'heart_rate' => $payload['heart_rate'] ?? $existing->heart_rate,
                'systolic_pressure' => $payload['systolic_pressure'] ?? $existing->systolic_pressure,
                'diastolic_pressure' => $payload['diastolic_pressure'] ?? $existing->diastolic_pressure,
                'oxygen_saturation' => $payload['oxygen_saturation'] ?? $existing->oxygen_saturation,
                'measured_at' => $measuredAt,
            ]);

            return response()->json([
                'message' => 'Vital sign updated successfully.',
                'data' => $existing->fresh(),
            ]);
        }

        $vital = VitalSigns::create([
            'user_id' => $userId,
            'heart_rate' => $payload['heart_rate'] ?? null,
            'systolic_pressure' => $payload['systolic_pressure'] ?? null,
            'diastolic_pressure' => $payload['diastolic_pressure'] ?? null,
            'oxygen_saturation' => $payload['oxygen_saturation'] ?? null,
            'measured_at' => $measuredAt,
        ]);

        return response()->json([
            'message' => 'Vital sign recorded successfully.',
            'data' => $vital,
        ], 201);
    }

    // List all lab results
    public function indexLabResults(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $rows = AnalysisResult::query()
            ->where('user_id', $userId)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Lab results retrieved successfully.',
            'data' => $rows,
        ]);
    }

    // Record a new lab result
    public function storeLabResult(StoreAnalysisResultRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $payload = $request->validated();
        $payload['user_id'] = $userId;

        $row = AnalysisResult::create($payload);

        return response()->json([
            'message' => 'Lab result recorded successfully.',
            'data' => $row,
        ], 201);
    }

    // Update a lab result
    public function updateLabResult(UpdateAnalysisResultRequest $request, AnalysisResult $analysisResult): JsonResponse
    {
        // Check that user owns the result
        if ($error = $this->authorizeLabResult($analysisResult, $request)) return $error;

        $analysisResult->update($request->validated());

        return response()->json([
            'message' => 'Lab result updated successfully.',
            'data' => $analysisResult->fresh(),
        ]);
    }

    // Delete a lab result
    public function destroyLabResult(Request $request, AnalysisResult $analysisResult): JsonResponse
    {
        // Check that user owns the result
        if ($error = $this->authorizeLabResult($analysisResult, $request)) return $error;

        $analysisResult->delete();

        return response()->json([
            'message' => 'Lab result deleted successfully.',
        ]);
    }

    // List all treatment checks
    public function indexTreatmentChecks(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $days = max(1, min((int) $request->query('days', 14), 90));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $rows = TreatmentCheck::query()
            ->where('user_id', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        return response()->json([
            'message' => 'Treatment checks retrieved successfully.',
            'data' => $rows,
        ]);
    }

    // Synchronize treatment checks
    public function syncTreatmentChecks(SyncTreatmentCheckRequest $request): JsonResponse
    {
        $user   = $request->user();
        $userId = $user->id;

        foreach ($request->validated('checks') as $check) {
            // medication_key format: "{treatmentId}__dose_{n}"
            // Parse the treatment ID directly from the key
            if (!preg_match('/^(\d+)__dose_\d+$/', $check['medication_key'], $matches)) {
                continue; // skip invalid keys (old format)
            }

            $treatmentId = (int) $matches[1];

            // Security: verify the treatment belongs to this user
            $treatmentExists = $user->treatments()->where('id', $treatmentId)->exists();
            if (!$treatmentExists) {
                continue;
            }

            TreatmentCheck::updateOrCreate(
                [
                    'treatment_id'   => $treatmentId,
                    'user_id'        => $userId,
                    'check_date'     => $check['check_date'],
                    'medication_key' => $check['medication_key'],
                ],
                [
                    'medication_name' => $check['medication_name'],
                    'dose'            => $check['dose'] ?? null,
                    'taken'           => $check['taken'],
                    'checked_at'      => $check['taken'] ? ($check['checked_at'] ?? now()) : null,
                ]
            );
        }

        return response()->json([
            'message' => 'Treatment checks synchronized successfully.',
        ]);
    }

    // Check access to a lab result
    private function authorizeLabResult(AnalysisResult $labResult, Request $request): ?JsonResponse
    {
        // Check that the result belongs to the user
        $user = $request->user();
        $userId = $user->id;
        if ($labResult->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized access to this lab result.'], Response::HTTP_FORBIDDEN);
        }
        return null;
    }
}
