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

    // Récupérer un résumé de toutes les données de santé 
    public function overview(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = max(1, min((int) $request->query('days', 7), 30));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $labResults = AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        $doctorObservations = HealthData::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->whereNotNull('doctor_observation')
            ->orderByDesc('date')
            ->get(['id', 'date', 'doctor_observation', 'updated_at']);

        return response()->json([
            'message' => 'Données de santé récupérées avec succès.',
            'data' => [
                'latest_vitals'       => $this->healthDataService->latestVitals($userId),
'lab_results'         => $labResults,
                'treatment_medicines' => $this->healthDataService->resolveTreatmentMedicines($userId),
                'doctor_observations' => $doctorObservations,
            ],
        ]);
    }

    // Récupérer la liste des signes vitaux de l'utilisateur
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
            'message' => 'Signes vitaux récupérés avec succès.',
            'data'    => $vitals,
        ]);
    }

    // Enregistrer ou mettre à jour les signes vitaux du jour
    public function storeVital(StoreVitalSignsRequest $request): JsonResponse
    {
        $data       = $request->validated();
        $measuredAt = Carbon::parse($data['measured_at']);

        $healthData = HealthData::firstOrCreate([
            'user_id' => $request->user()->id,
            'date'    => $measuredAt->toDateString(),
        ]);

        $existing = VitalSigns::where('health_data_id', $healthData->id)->first();

        $fields = [
            'heart_rate'         => $data['heart_rate']         ?? $existing?->heart_rate,
            'systolic_pressure'  => $data['systolic_pressure']  ?? $existing?->systolic_pressure,
            'diastolic_pressure' => $data['diastolic_pressure'] ?? $existing?->diastolic_pressure,
            'oxygen_saturation'  => $data['oxygen_saturation']  ?? $existing?->oxygen_saturation,
            'measured_at'        => $measuredAt,
        ];

        if ($existing) {
            $existing->update($fields);
            return response()->json([
                'message' => 'Signe vital mis à jour avec succès.',
                'data'    => $existing->fresh(),
            ]);
        }

        $vital = VitalSigns::create(['health_data_id' => $healthData->id, ...$fields]);

        return response()->json([
            'message' => 'Signe vital enregistré avec succès.',
            'data'    => $vital,
        ], 201);
    }

    // Enregistrer un nouveau résultat d'analyse
    public function storeLabResult(StoreAnalysisResultRequest $request): JsonResponse
    {
        $data       = $request->validated();
        $healthData = HealthData::firstOrCreate([
            'user_id' => $request->user()->id,
            'date'    => $data['analysis_date'],
        ]);

        $labResult = AnalysisResult::create([...$data, 'health_data_id' => $healthData->id]);

        return response()->json([
            'message' => 'Résultat d\'analyse enregistré avec succès.',
            'data'    => $labResult,
        ], 201);
    }

    // Mettre à jour un résultat d'analyse existant
    public function updateLabResult(UpdateAnalysisResultRequest $request, AnalysisResult $analysisResult): JsonResponse
    {
        if ($analysisResult->healthData?->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé à ce résultat d\'analyse.'], 403);
        }

        $analysisResult->update($request->validated());

        return response()->json([
            'message' => 'Résultat d\'analyse mis à jour avec succès.',
            'data'    => $analysisResult->fresh(),
        ]);
    }

    // Supprimer un résultat d'analyse
    public function destroyLabResult(Request $request, AnalysisResult $analysisResult): JsonResponse
    {
        if ($analysisResult->healthData?->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé à ce résultat d\'analyse.'], 403);
        }

        $analysisResult->delete();

        return response()->json(['message' => 'Résultat d\'analyse supprimé avec succès.']);
    }

    // Récupérer les vérifications de traitement pour l'utilisateur
    public function indexTreatmentChecks(Request $request): JsonResponse
    {
        $days = (int) $request->query('days');

        if ($days < 1) {
            return response()->json(['message' => 'Le paramètre days doit être supérieur ou égal à 1.'], 422);
        }

        $checks = TreatmentCheck::with('treatment.treatmentCatalog')
            ->where('user_id', $request->user()->id)
            ->where('check_date', '>=', Carbon::today()->subDays($days - 1)->toDateString())
            ->orderBy('check_date')
            ->get();

        return response()->json([
            'message' => 'Vérifications de traitement récupérées avec succès.',
            'data'    => $this->healthDataService->serializeTreatmentChecks($checks),
        ]);
    }

    // Enregistrer ou mettre à jour un lot de vérifications de traitement
    public function syncTreatmentChecks(SyncTreatmentCheckRequest $request): JsonResponse
    {
        $user = $request->user();

        foreach ($request->validated('checks') as $check) {
            $treatmentId = (int) explode('__dose_', $check['medication_key'], 2)[0];

            if (!$user->treatments()->where('treatments.id', $treatmentId)->exists()) {
                continue;
            }

            TreatmentCheck::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'treatment_id'   => $treatmentId,
                    'check_date'     => $check['check_date'],
                    'medication_key' => $check['medication_key'],
                ],
                [
                    'taken'      => $check['taken'],
                    'checked_at' => $check['taken'] ? ($check['checked_at'] ?? now()) : null,
                ]
            );
        }

        return response()->json(['message' => 'Vérifications de traitement synchronisées avec succès.']);
    }
}
