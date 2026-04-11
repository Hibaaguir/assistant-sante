<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAnalysisResultRequest;
use App\Http\Requests\Api\StoreVitalSignsRequest;
use App\Http\Requests\Api\SyncTreatmentCheckRequest;
use App\Http\Requests\Api\UpdateAnalysisResultRequest;
use App\Models\AnalysisResult;
use App\Models\HealthData;
use App\Models\TreatmentCheck;
use App\Models\VitalSigns;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthDataController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Return a summary of all health data for the current user (dashboard)
    public function overview(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = max(1, min((int) $request->query('days', 7), 30));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $vitals = VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get();

        $labResults = AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $treatmentChecks = TreatmentCheck::with('treatment.treatmentCatalog')
            ->whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        $doctorObservations = HealthData::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->whereNotNull('doctor_observation')
            ->orderByDesc('date')
            ->get(['id', 'date', 'doctor_observation', 'updated_at']);

        return response()->json([
            'message' => 'Health data retrieved successfully.',
            'data' => [
                'latest_vitals'       => $this->healthDataService->latestVitals($userId),
                'vitals'              => $vitals,
                'vitals_chart'        => $this->healthDataService->buildVitalSignsChartSeries($vitals, $days),
                'lab_results'         => $labResults,
                'treatment_medicines' => $this->healthDataService->resolveTreatmentMedicines($userId),
                'treatment_checks'    => $this->healthDataService->serializeTreatmentChecks($treatmentChecks),
                'doctor_observations' => $doctorObservations,
            ],
        ]);
    }

    // Return a list of vital signs for the current user
    public function indexVitals(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1);

        $vitals = VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'message' => 'Vital signs retrieved successfully.',
            'data'    => $vitals,
        ]);
    }

    // Save a new vital signs entry for the current user.
    // One record per user per day — if one already exists, merge the new values into it.
    public function storeVital(StoreVitalSignsRequest $request): JsonResponse
    {
        $userId     = $request->user()->id;
        $data       = $request->validated();
        $measuredAt = Carbon::parse($data['measured_at']);

        $healthData = HealthData::firstOrCreate([
            'user_id' => $userId,
            'date'    => $measuredAt->toDateString(),
        ]);

        $existing = VitalSigns::where('health_data_id', $healthData->id)->first();

        if ($existing) {
            $existing->update([
                'heart_rate'         => $data['heart_rate']         ?? $existing->heart_rate,
                'systolic_pressure'  => $data['systolic_pressure']  ?? $existing->systolic_pressure,
                'diastolic_pressure' => $data['diastolic_pressure'] ?? $existing->diastolic_pressure,
                'oxygen_saturation'  => $data['oxygen_saturation']  ?? $existing->oxygen_saturation,
                'measured_at'        => $measuredAt,
            ]);

            return response()->json([
                'message' => 'Vital sign updated successfully.',
                'data'    => $existing->fresh(),
            ]);
        }

        $vital = VitalSigns::create([
            'health_data_id'     => $healthData->id,
            'heart_rate'         => $data['heart_rate']         ?? null,
            'systolic_pressure'  => $data['systolic_pressure']  ?? null,
            'diastolic_pressure' => $data['diastolic_pressure'] ?? null,
            'oxygen_saturation'  => $data['oxygen_saturation']  ?? null,
            'measured_at'        => $measuredAt,
        ]);

        return response()->json([
            'message' => 'Vital sign recorded successfully.',
            'data'    => $vital,
        ], 201);
    }

    // Return all lab results for the current user
    public function indexLabResults(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $labResults = AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Lab results retrieved successfully.',
            'data'    => $labResults,
        ]);
    }

    // Save a new lab result for the current user
    public function storeLabResult(StoreAnalysisResultRequest $request): JsonResponse
    {
        $userId     = $request->user()->id;
        $data       = $request->validated();
        $healthData = HealthData::firstOrCreate([
            'user_id' => $userId,
            'date'    => $data['analysis_date'],
        ]);

        $labResult = AnalysisResult::create([
            ...$data,
            'health_data_id' => $healthData->id,
        ]);

        return response()->json([
            'message' => 'Lab result recorded successfully.',
            'data'    => $labResult,
        ], 201);
    }

    // Update an existing lab result
    public function updateLabResult(UpdateAnalysisResultRequest $request, AnalysisResult $analysisResult): JsonResponse
    {
        if ($analysisResult->healthData?->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access to this lab result.'], 403);
        }

        $analysisResult->update($request->validated());

        return response()->json([
            'message' => 'Lab result updated successfully.',
            'data'    => $analysisResult->fresh(),
        ]);
    }

    // Delete a lab result
    public function destroyLabResult(Request $request, AnalysisResult $analysisResult): JsonResponse
    {
        if ($analysisResult->healthData?->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access to this lab result.'], 403);
        }

        $analysisResult->delete();

        return response()->json(['message' => 'Lab result deleted successfully.']);
    }

    // Return treatment checks for the current user
    public function indexTreatmentChecks(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = max(1, min((int) $request->query('days', 14), 90));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $checks = TreatmentCheck::with('treatment.treatmentCatalog')
            ->whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        return response()->json([
            'message' => 'Treatment checks retrieved successfully.',
            'data'    => $this->healthDataService->serializeTreatmentChecks($checks),
        ]);
    }

    // Save or update a batch of treatment checks sent from the frontend.
    // Each check has a medication_key like "12__dose_1" — we extract the treatment ID from it.
    public function syncTreatmentChecks(SyncTreatmentCheckRequest $request): JsonResponse
    {
        $user   = $request->user();
        $userId = $user->id;

        foreach ($request->validated('checks') as $check) {
            // medication_key format is already validated by SyncTreatmentCheckRequest
            $parts = explode('__dose_', $check['medication_key'], 2);
            $treatmentId = (int) $parts[0];

            if (!$user->treatments()->where('id', $treatmentId)->exists()) {
                continue;
            }

            $healthData = HealthData::firstOrCreate([
                'user_id' => $userId,
                'date'    => $check['check_date'],
            ]);

            TreatmentCheck::updateOrCreate(
                [
                    'treatment_id'   => $treatmentId,
                    'check_date'     => $check['check_date'],
                    'medication_key' => $check['medication_key'],
                ],
                [
                    'health_data_id' => $healthData->id,
                    'taken'          => $check['taken'],
                    'checked_at'     => $check['taken'] ? ($check['checked_at'] ?? now()) : null,
                ]
            );
        }

        return response()->json(['message' => 'Treatment checks synchronized successfully.']);
    }
}
