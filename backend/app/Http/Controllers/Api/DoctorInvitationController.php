<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\DoctorInvitation;
use App\Models\HealthData;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Models\VitalSigns;
use App\Services\DoctorInvitationService;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorInvitationController extends Controller
{
    public function __construct(
        private readonly DoctorInvitationService $invitationService,
        private readonly HealthDataService $healthDataService,
    ) {}

    // Récupérer toutes les invitations du médecin
    public function index(Request $request): JsonResponse
    {
        $invitations = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $request->user()->id)
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('id')
            ->get()
            ->map(fn(DoctorInvitation $inv) => $this->serializeInvitation($inv))
            ->values();

        return response()->json(['message' => 'Invitations récupérées avec succès.', 'data' => $invitations]);
    }

    // Accepter une invitation médecin
    public function accept(Request $request, DoctorInvitation $invitation): JsonResponse
    {
        $user = $request->user();

        if (!$this->invitationService->authorizeAndLink($invitation, $user)) {
            return response()->json(['message' => 'Accès non autorisé à cette invitation.'], 403);
        }

        if ($invitation->status !== 'accepted') {
            $invitation->update(['status' => 'accepted', 'accepted_at' => now(), 'rejected_at' => null, 'revoked_at' => null]);
        }

        if ($user->role !== 'doctor') {
            $user->update(['role' => 'doctor']);
        }

        return response()->json([
            'message' => 'Invitation acceptée.',
            'data'    => $this->serializeInvitation($invitation->fresh($this->withPatient())),
        ]);
    }

    // Rejeter une invitation médecin
    public function reject(Request $request, DoctorInvitation $invitation): JsonResponse
    {
        if (!$this->invitationService->authorizeAndLink($invitation, $request->user())) {
            return response()->json(['message' => 'Accès non autorisé à cette invitation.'], 403);
        }

        $invitation->update(['status' => 'rejected', 'rejected_at' => now()]);

        return response()->json([
            'message' => 'Invitation rejetée.',
            'data'    => $this->serializeInvitation($invitation->fresh($this->withPatient())),
        ]);
    }

    // Récupérer tous les patients du médecin avec un résumé de santé
    public function indexPatients(Request $request): JsonResponse
    {
        $patients = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->orderByDesc('accepted_at')
            ->get()
            ->map(function (DoctorInvitation $invitation) {
                $patient = $invitation->patient;
                if (!$patient) return null;

                $latestVitals = $this->healthDataService->latestVitals($patient->id);
                $labResults   = AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $patient->id))
                    ->orderByDesc('analysis_date')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get();

                return [
                    'invitation_id' => $invitation->id,
                    'accepted_at'   => $invitation->accepted_at?->toISOString(),
                    'patient'       => [
                        'id'            => $patient->id,
                        'name'          => $patient->name,
                        'date_of_birth' => $patient->date_of_birth,
                        'profile_photo' => $patient->profile_photo,
                        'email'         => $patient->account?->email,
                    ],
                    'profile'       => $patient->healthProfile,
                    'latest_vitals' => $latestVitals,
                    'alerts'        => $this->healthDataService->buildPatientAlerts($latestVitals, $labResults),
                ];
            })
            ->filter()
            ->values();

        return response()->json(['message' => 'Patients du médecin récupérés avec succès.', 'data' => $patients]);
    }

    // Afficher les détails complets du patient
    public function showPatient(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->authorizePatient($request, $patient);
        if ($invitation instanceof JsonResponse) return $invitation;

        $vitalsDays  = max(1, min((int) $request->query('vitals_days', 30), 90));
        $vitalsStart = Carbon::today()->subDays($vitalsDays - 1)->startOfDay();

        $treatmentChecks = TreatmentCheck::with('treatment.treatmentCatalog')
            ->where('user_id', $patient->id)
            ->where('check_date', '>=', Carbon::today()->subDays(29)->toDateString())
            ->orderBy('check_date')
            ->get();

        return response()->json([
            'message' => 'Détails du patient récupérés avec succès.',
            'data'    => [
                'invitation_id'      => $invitation->id,
                'accepted_at'        => $invitation->accepted_at?->toISOString(),
                'patient'            => $this->serializePatient($patient),
                'profile'            => $patient->healthProfile,
                'latest_vitals'      => $this->healthDataService->latestVitals($patient->id),
                'vitals'             => VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $patient->id))->where('measured_at', '>=', $vitalsStart)->orderByDesc('measured_at')->get(),
                'lab_results'        => AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $patient->id))->orderByDesc('analysis_date')->orderByDesc('id')->get(),
                'treatment_medicines'=> $this->healthDataService->resolveTreatmentMedicines($patient->id),
                'treatment_checks'   => $this->healthDataService->serializeTreatmentChecks($treatmentChecks),
                'health_data'        => HealthData::where('user_id', $patient->id)
                                            ->orderByDesc('date')
                                            ->limit(60)
                                            ->get(['id', 'date', 'doctor_observation', 'updated_at']),
            ],
        ]);
    }

    // Ajouter une observation du médecin sur un patient à n'importe quelle date
    // Crée automatiquement le dossier health_data s'il n'existe pas
    public function storeObservation(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->authorizePatient($request, $patient);
        if ($invitation instanceof JsonResponse) return $invitation;

        $validated = $request->validate([
            'date'        => ['required', 'date_format:Y-m-d'],
            'observation' => ['required', 'string', 'max:2000'],
        ]);

        $healthData = HealthData::firstOrCreate(
            ['user_id' => $patient->id, 'date' => $validated['date']],
        );
        $healthData->update(['doctor_observation' => trim($validated['observation'])]);

        return response()->json(['message' => 'Observation enregistrée.', 'data' => [
            'id'                 => $healthData->id,
            'date'               => $healthData->date->toDateString(),
            'doctor_observation' => $healthData->doctor_observation,
        ]]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    // Authorize that the current doctor has an accepted invitation for this patient.
    // Returns the invitation on success, or a 403 JsonResponse on failure.
    // Autoriser le médecin courant pour accéder au patient
    // Retourne l'invitation si succès, ou une réponse 403 sinon
    private function authorizePatient(Request $request, User $patient): DoctorInvitation|JsonResponse
    {
        $invitation = $this->invitationService->findAccepted($request->user()->id, $patient->id);

        if (!$invitation) {
            return response()->json(['message' => 'Accès non autorisé à ce patient.'], 403);
        }

        return $invitation;
    }

    // Retourner seulement les champs du patient nécessaires au frontend
    private function serializePatient(User $patient): array
    {
        return [
            'id'            => $patient->id,
            'name'          => $patient->name,
            'date_of_birth' => $patient->date_of_birth,
            'profile_photo' => $patient->profile_photo,
            'age'           => $patient->age,
            'role'          => $patient->role,
            'email'         => $patient->account?->email,
        ];
    }

    // Charger les relations pour l'affichage du patient
    private function withPatient():array
    {
        return ['patient:id,account_id,name,date_of_birth,profile_photo,created_at', 'patient.account:id,email', 'patient.healthProfile'];
    }

    // Sérialiser une invitation médecin pour les réponses API
    private function serializeInvitation(DoctorInvitation $invitation): array
    {
        $patient = $invitation->patient;

        return [
            'id'          => $invitation->id,
            'status'      => $invitation->status,
            'doctor_email'=> $invitation->doctor_email,
            'created_at'  => $invitation->created_at?->toISOString(),
            'accepted_at' => $invitation->accepted_at?->toISOString(),
            'rejected_at' => $invitation->rejected_at?->toISOString(),
            'patient'     => $patient ? [
                'id'            => $patient->id,
                'name'          => $patient->name,
                'date_of_birth' => $patient->date_of_birth,
                'profile_photo' => $patient->profile_photo,
                'email'         => $patient->account?->email,
            ] : null,
            'profile'     => $patient?->healthProfile,
        ];
    }
}
