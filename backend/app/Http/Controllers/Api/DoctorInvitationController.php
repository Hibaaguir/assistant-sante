<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Models\HealthLabResult;
use App\Models\HealthTreatmentCheck;
use App\Models\HealthVital;
use App\Models\User;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class DoctorInvitationController extends Controller
{
    public function __construct(private readonly HealthDataService $serviceDonneesSante) {}

    // Lister toutes les invitations médicales
    public function index(Request $request): JsonResponse
    {
        $invitations = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $request->user()->id)
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('id')
            ->get()
            ->map(fn (DoctorInvitation $inv) => $this->serializeInvitation($inv))
            ->values();

        return response()->json(['message' => 'Invitations recuperees avec succes.', 'data' => $invitations]);
    }

    // Accepter une invitation médicale
    public function accept(Request $request, DoctorInvitation $doctorInvitation): JsonResponse
    {
        // Vérifier que le médecin est propriétaire de l'invitation
        if ($error = $this->authorizeInvitation($doctorInvitation, $request)) return $error;

        // Mettre à jour le statut seulement s'il n'est pas déjà accepté
        if ($doctorInvitation->status !== 'accepted') {
            $doctorInvitation->update(['status' => 'accepted', 'accepted_at' => now(), 'rejected_at' => null, 'revoked_at' => null]);
        }

        // Synchroniser le rôle de l'utilisateur en médecin
        if ($request->user()->role !== 'medecin') {
            $request->user()->update(['role' => 'medecin']);
        }

        return response()->json([
            'message' => 'Invitation acceptee.',
            'data'    => $this->serializeInvitation($doctorInvitation->fresh($this->withPatient())),
        ]);
    }

    // Refuser une invitation médicale
    public function reject(Request $request, DoctorInvitation $doctorInvitation): JsonResponse
    {
        // Vérifier que le médecin est propriétaire de l'invitation
        if ($error = $this->authorizeInvitation($doctorInvitation, $request)) return $error;

        $doctorInvitation->update(['status' => 'rejected', 'rejected_at' => now()]);

        return response()->json([
            'message' => 'Invitation refusee.',
            'data'    => $this->serializeInvitation($doctorInvitation->fresh($this->withPatient())),
        ]);
    }

    // Lister tous les patients du médecin
    public function indexPatients(Request $request): JsonResponse
    {
        $patients = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->orderByDesc('accepted_at')
            ->get()
            ->map(function (DoctorInvitation $invitation) {
                $patient = $invitation->patient;
                // Filtrer les invitations sans patient valide
                if (! $patient) return null;

                $latestVitals  = $this->latestVitals($patient->id);
                $labResults    = HealthLabResult::where('user_id', $patient->id)->orderByDesc('analysis_date')->orderByDesc('id')->limit(5)->get();

                return [
                    'invitation_id' => $invitation->id,
                    'accepted_at'   => $invitation->accepted_at?->toISOString(),
                    'patient'       => $patient,
                    'profile'       => $patient->profilSante,
                    'latest_vitals' => $latestVitals,
                    'alerts'        => $this->buildPatientAlerts($patient, $latestVitals, $labResults),
                ];
            })
            ->filter()
            ->values();

        return response()->json(['message' => 'Patients du medecin recuperes avec succes.', 'data' => $patients]);
    }

    // Afficher détail complet d'un patient
    public function showPatient(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->findAuthorizedInvitation($request, $patient);
        // Vérifier que le médecin a accès à ce patient
        if (! $invitation) {
            return response()->json(['message' => 'Acces non autorise a ce patient.'], 403);
        }

        $vitalsDays  = max(1, min((int) $request->query('vitals_days', 30), 90));
        $vitalsStart = Carbon::today()->subDays($vitalsDays - 1)->startOfDay();

        return response()->json([
            'message' => 'Detail du patient recupere avec succes.',
            'data'    => [
                'invitation_id'       => $invitation->id,
                'accepted_at'         => $invitation->accepted_at?->toISOString(),
                'patient'             => $patient,
                'profile'             => $patient->profilSante,
                'latest_vitals'       => $this->latestVitals($patient->id),
                'vitals'              => HealthVital::where('user_id', $patient->id)->where('measured_at', '>=', $vitalsStart)->orderByDesc('measured_at')->get(),
                'lab_results'         => HealthLabResult::where('user_id', $patient->id)->orderByDesc('analysis_date')->orderByDesc('id')->get(),
                'treatment_medicines' => $this->serviceDonneesSante->resoudreMedicamentsTraitement($patient->id),
                'treatment_checks'    => HealthTreatmentCheck::where('user_id', $patient->id)->where('check_date', '>=', Carbon::today()->subDays(29)->toDateString())->orderBy('check_date')->orderBy('medication_name')->get(),
                'general_observation' => [
                    'text'       => $invitation->general_observation,
                    'updated_at' => $invitation->general_observation_updated_at?->toISOString(),
                ],
            ],
        ]);
    }

    // Enregistrer observation générale sur un patient
    public function storeObservation(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->findAuthorizedInvitation($request, $patient);
        // Vérifier que le médecin a accès à ce patient
        if (! $invitation) {
            return response()->json(['message' => 'Acces non autorise a ce patient.'], 403);
        }

        $validated   = $request->validate(
            ['observation' => ['nullable', 'string', 'max:3000']],
            ['observation.max' => "L'observation generale ne doit pas depasser 3000 caracteres."],
        );
        $observation = trim((string) ($validated['observation'] ?? ''));
        // Vérifier s'il y a du texte pour déterminer la date mise à jour
        $hasText     = $observation !== '';

        $invitation->update([
            'general_observation'            => $hasText ? $observation : null,
            'general_observation_updated_at' => $hasText ? now() : null,
        ]);

        $invitation->refresh();

        return response()->json([
            'message' => $hasText ? 'Observation generale enregistree.' : 'Observation generale effacee.',
            'data'    => [
                'general_observation' => [
                    'text'       => $invitation->general_observation,
                    'updated_at' => $invitation->general_observation_updated_at?->toISOString(),
                ],
            ],
        ]);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    // Récupérer les colonnes des patients
    private function withPatient(): array
    {
        return ['patient:id,name,email,date_of_birth,created_at', 'patient.profilSante'];
    }

    // Récupérer les derniers signes vitaux du patient
    private function latestVitals(int $userId): ?HealthVital
    {
        return HealthVital::where('user_id', $userId)
            ->where(fn ($q) => $q->whereNotNull('heart_rate')->orWhereNotNull('systolic_pressure')->orWhereNotNull('diastolic_pressure')->orWhereNotNull('oxygen_saturation'))
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();
    }

    // Sérialiser une invitation médicale
    private function serializeInvitation(DoctorInvitation $invitation): array
    {
        return [
            'id'           => $invitation->id,
            'status'       => $invitation->status,
            'doctor_email' => $invitation->doctor_email,
            'created_at'   => $invitation->created_at?->toISOString(),
            'accepted_at'  => $invitation->accepted_at?->toISOString(),
            'rejected_at'  => $invitation->rejected_at?->toISOString(),
            'patient'      => $invitation->patient,
            'profile'      => $invitation->patient?->profilSante,
        ];
    }

    // Trouver invitation autorisée pour un patient
    private function findAuthorizedInvitation(Request $request, User $patient): ?DoctorInvitation
    {
        return DoctorInvitation::where('doctor_user_id', $request->user()->id)
            ->where('patient_user_id', $patient->id)
            ->where('status', 'accepted')
            ->latest('accepted_at')
            ->latest('id')
            ->first();
    }

    // Construire alertes pour un patient
    private function buildPatientAlerts(User $patient, ?HealthVital $latestVitals, Collection $labResults): array
    {
        $alerts = [];

        // Détecter la tension artérielle élevée
        if ($latestVitals?->systolic_pressure >= 140) {
            $alerts[] = [
                'severity'       => 'warning',
                'title'          => 'Alerte',
                'message'        => 'Tension arterielle elevee : ' . (int) $latestVitals->systolic_pressure . '/' . (int) ($latestVitals->diastolic_pressure ?? 0) . ' mmHg',
                'recommendation' => 'Surveiller la tension et contacter le patient si la hausse persiste.',
                'measured_at'    => $latestVitals->measured_at?->toISOString(),
            ];
        }

        $glucose = $labResults->first(fn (HealthLabResult $r) => str_contains(strtolower((string) $r->analysis_type), 'glucose'));

        // Détecter une glycémie dangeureusement basse (critique)
        if ($glucose && is_numeric($glucose->value) && $glucose->value < 3.9) {
            $alerts[] = [
                'severity'       => 'critical',
                'title'          => 'Alerte critique',
                'message'        => 'Glycemie tres basse detectee : ' . rtrim(rtrim((string) $glucose->value, '0'), '.') . ' ' . ($glucose->unit ?: 'mmol/L'),
                'recommendation' => 'Contacter immediatement le patient. Resucrage urgent recommande.',
                'measured_at'    => $glucose->analysis_date?->toISOString(),
            ];
        }

        return $alerts;
    }

    // Vérifier authorization pour une invitation
    private function authorizeInvitation(DoctorInvitation $invitation, Request $request): ?JsonResponse
    {
        // Vérifier que l'utilisateur est le propriétaire de l'invitation
        if ($invitation->doctor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Acces non autorise a cette invitation.'], 403);
        }
        return null;
    }
}