<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHealthLabResultRequest;
use App\Http\Requests\Api\StoreHealthVitalRequest;
use App\Http\Requests\Api\SyncHealthTreatmentChecksRequest;
use App\Http\Requests\Api\UpdateHealthLabResultRequest;
use App\Models\DoctorInvitation;
use App\Models\HealthLabResult;
use App\Models\HealthTreatmentCheck;
use App\Models\HealthVital;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthDataController extends Controller
{
    public function __construct(private readonly HealthDataService $serviceDonneesSante) {}

    // Afficher vue d'ensemble des données de santé
    public function vueEnsemble(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $days = max(1, min((int) $request->query( 'days', 7), 30));
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

        $latestDoctorObservation = DoctorInvitation::query()
            ->with('doctor:id,name,email')
            ->where('patient_user_id', $userId)
            ->where('status', 'accepted')
            ->whereNotNull('general_observation')
            ->orderByDesc('general_observation_updated_at')
            ->orderByDesc('accepted_at')
            ->orderByDesc('id')
            ->first();

        $treatmentMedicines = $this->serviceDonneesSante->resoudreMedicamentsTraitement($userId);

        return response()->json([
            'message' => 'Donnees de sante recuperees avec succes.',
            'data' => [
                'latest_vitals' => $latestVitals,
                'vitals' => $vitals,
                'vitals_chart' => $this->serviceDonneesSante->construireSeriesGraphiqueSignesVitaux($vitals, $days),
                'lab_results' => $labResults,
                'treatment_medicines' => $treatmentMedicines,
                'treatment_checks' => $treatmentChecks,
                'doctor_observation' => [
                    'text' => $latestDoctorObservation?->general_observation,
                    'updated_at' => optional($latestDoctorObservation?->general_observation_updated_at)?->toISOString(),
                    'doctor_name' => $latestDoctorObservation?->doctor?->name,
                    'doctor_email' => $latestDoctorObservation?->doctor?->email,
                ],
            ],
        ]);
    }

    // Lister tous les signes vitaux
    public function indexVitals(Request $request): JsonResponse
    {
        $days = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1);

        $rows = HealthVital::query()
            ->where('user_id', $request->user()->id)
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'message' => 'Signes vitaux recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Enregistrer un nouveau signe vital
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

        // Vérifier si des signes vitaux existent déjà pour cette date
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
                'message' => 'Signe vital mis a jour avec succes.',
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
            'message' => 'Signe vital enregistre avec succes.',
            'data' => $vital,
        ], 201);
    }

    // Lister tous les résultats de laboratoire
    public function indexLabResults(Request $request): JsonResponse
    {
        $rows = HealthLabResult::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Resultats de laboratoire recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Enregistrer un nouveau résultat de laboratoire
    public function storeLabResult(StoreHealthLabResultRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $row = HealthLabResult::create($payload);

        return response()->json([
            'message' => 'Resultat de laboratoire enregistre avec succes.',
            'data' => $row,
        ], 201);
    }

    // Mettre à jour un résultat de laboratoire
    public function updateLabResult(UpdateHealthLabResultRequest $request, HealthLabResult $healthLabResult): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du résultat
        if ($error = $this->authorizeLabResult($healthLabResult, $request)) return $error;

        $healthLabResult->update($request->validated());

        return response()->json([
            'message' => 'Resultat de laboratoire mis a jour avec succes.',
            'data' => $healthLabResult->fresh(),
        ]);
    }

    // Supprimer un résultat de laboratoire
    public function destroyLabResult(Request $request, HealthLabResult $healthLabResult): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du résultat
        if ($error = $this->authorizeLabResult($healthLabResult, $request)) return $error;

        $healthLabResult->delete();

        return response()->json([
            'message' => 'Resultat de laboratoire supprime avec succes.',
        ]);
    }

    // Lister tous les contrôles de traitement
    public function indexTreatmentChecks(Request $request): JsonResponse
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
            'message' => 'Controles de traitement recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Synchroniser les contrôles de traitement
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
            'message' => 'Controles de traitement synchronises avec succes.',
        ]);
    }

    // Vérifier l'accès à un résultat de laboratoire
    private function authorizeLabResult(HealthLabResult $labResult, Request $request): ?JsonResponse
    {
        // Vérifier que le résultat appartient à l'utilisateur
        if ($labResult->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Acces non autorise a ce resultat de laboratoire.'], 403);
        }
        return null;
    }
}
