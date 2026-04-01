<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreResultatAnalyseRequest;
use App\Http\Requests\Api\StoreSignesVitauxRequest;
use App\Http\Requests\Api\SyncSuiviTraitementRequest;
use App\Http\Requests\Api\UpdateResultatAnalyseRequest;
use App\Models\InvitationMedecin;
use App\Models\ResultatAnalyse;
use App\Models\SuiviTraitement;
use App\Models\SignesVitaux;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DonneesSanteController extends Controller
{
    public function __construct(private readonly HealthDataService $serviceDonneesSante) {}

    // Afficher vue d'ensemble des données de santé
    public function vueEnsemble(Request $request): JsonResponse
    {
        $compte = $request->user();
        $utilisateur = $compte->utilisateur;
        $userId = $utilisateur->id;
        
        $days = max(1, min((int) $request->query( 'days', 7), 30));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $vitals = SignesVitaux::query()
            ->where('id_utilisateur', $userId)
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get();

        $latestVitals = SignesVitaux::query()
            ->where('id_utilisateur', $userId)
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

        $labResults = ResultatAnalyse::query()
            ->where('id_utilisateur', $userId)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $treatmentChecks = SuiviTraitement::query()
            ->where('id_utilisateur', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        $latestDoctorObservation = InvitationMedecin::query()
            ->with('doctor:id,nom')
            ->where('id_patient_utilisateur', $userId)
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
                    'doctor_name' => $latestDoctorObservation?->doctor?->nom,
                    'doctor_email' => $latestDoctorObservation?->doctor?->compte?->email,
                ],
            ],
        ]);
    }

    // Lister tous les signes vitaux
    public function indexVitals(Request $request): JsonResponse
    {
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        $days = max(1, min((int) $request->query('days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1);

        $rows = SignesVitaux::query()
            ->where('id_utilisateur', $userId)
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'message' => 'Signes vitaux recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Enregistrer un nouveau signe vital
    public function storeVital(StoreSignesVitauxRequest $request): JsonResponse
    {
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        $payload = $request->validated();
        $measuredAt = isset($payload['measured_at']) ? Carbon::parse($payload['measured_at']) : now();
        $measuredDate = $measuredAt->toDateString();

        $existing = SignesVitaux::query()
            ->where('id_utilisateur', $userId)
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

        $vital = SignesVitaux::create([
            'id_utilisateur' => $userId,
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
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        $rows = ResultatAnalyse::query()
            ->where('id_utilisateur', $userId)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Resultats de laboratoire recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Enregistrer un nouveau résultat de laboratoire
    public function storeLabResult(StoreResultatAnalyseRequest $request): JsonResponse
    {
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        $payload = $request->validated();
        $payload['id_utilisateur'] = $userId;

        $row = ResultatAnalyse::create($payload);

        return response()->json([
            'message' => 'Resultat de laboratoire enregistre avec succes.',
            'data' => $row,
        ], 201);
    }

    // Mettre à jour un résultat de laboratoire
    public function updateLabResult(UpdateResultatAnalyseRequest $request, ResultatAnalyse $resultatAnalyse): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du résultat
        if ($error = $this->authorizeLabResult($resultatAnalyse, $request)) return $error;

        $resultatAnalyse->update($request->validated());

        return response()->json([
            'message' => 'Resultat de laboratoire mis a jour avec succes.',
            'data' => $resultatAnalyse->fresh(),
        ]);
    }

    // Supprimer un résultat de laboratoire
    public function destroyLabResult(Request $request, ResultatAnalyse $resultatAnalyse): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du résultat
        if ($error = $this->authorizeLabResult($resultatAnalyse, $request)) return $error;

        $resultatAnalyse->delete();

        return response()->json([
            'message' => 'Resultat de laboratoire supprime avec succes.',
        ]);
    }

    // Lister tous les contrôles de traitement
    public function indexTreatmentChecks(Request $request): JsonResponse
    {
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        $days = max(1, min((int) $request->query('days', 14), 90));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        $rows = SuiviTraitement::query()
            ->where('id_utilisateur', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        return response()->json([
            'message' => 'Controles de traitement recuperes avec succes.',
            'data' => $rows,
        ]);
    }

    // Synchroniser les contrôles de traitement
    public function syncTreatmentChecks(SyncSuiviTraitementRequest $request): JsonResponse
    {
        $compte = $request->user();
        $userId = $compte->utilisateur->id;

        foreach ($request->validated('checks') as $check) {
            SuiviTraitement::updateOrCreate(
                [
                    'id_utilisateur' => $userId,
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
    private function authorizeLabResult(ResultatAnalyse $labResult, Request $request): ?JsonResponse
    {
        // Vérifier que le résultat appartient à l'utilisateur
        $compte = $request->user();
        $userId = $compte->utilisateur->id;
        if ($labResult->id_utilisateur !== $userId) {
            return response()->json(['message' => 'Acces non autorise a ce resultat de laboratoire.'], Response::HTTP_FORBIDDEN);
        }
        return null;
    }
}
