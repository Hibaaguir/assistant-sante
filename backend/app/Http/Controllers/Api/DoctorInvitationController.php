<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Models\AnalysisResult;
use App\Models\HealthObservation;
use App\Models\TreatmentCheck;
use App\Models\VitalSigns;
use App\Models\User;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;


class DoctorInvitationController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Create an invitation for a doctor who wants to register
    public function createInvitation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'doctor_email' => ['required', 'email', 'max:255'],
            ]);

            $doctorEmail = strtolower(trim($validated['doctor_email']));

            // Check if invitation already exists for this email
            $existing = DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [$doctorEmail])->first();

            if ($existing) {
                return response()->json([
                    'message' => 'An invitation already exists for this email.',
                    'data'    => [
                        'id'            => $existing->id,
                        'doctor_email'  => $existing->doctor_email,
                        'status'        => $existing->status,
                        'created_at'    => $existing->created_at?->toISOString(),
                    ],
                ], 200);
            }

            // Create new invitation for a doctor without patient (registration phase)
            $invitation = DoctorInvitation::create([
                'patient_user_id' => null,
                'doctor_user_id'  => null,
                'doctor_email'    => $doctorEmail,
                'status'          => 'pending',
                'token'           => \Illuminate\Support\Str::uuid(),
            ]);

            return response()->json([
                'message' => 'Invitation created successfully.',
                'data'    => [
                    'id'            => $invitation->id,
                    'doctor_email'  => $invitation->doctor_email,
                    'status'        => $invitation->status,
                    'created_at'    => $invitation->created_at?->toISOString(),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Doctor invitation error: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating invitation.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // List all doctor invitations
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $invitations = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $user->id)
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('id')
            ->get()
            ->map(fn (DoctorInvitation $inv) => $this->serializeInvitation($inv))
            ->values();

        return response()->json(['message' => 'Invitations retrieved successfully.', 'data' => $invitations]);
    }

    // Accept a doctor invitation
    public function accept(Request $request, DoctorInvitation $invitation): JsonResponse
    {
        // Check that doctor owns the invitation
        if ($error = $this->authorizeInvitation($invitation, $request)) return $error;

        // Update status only if not already accepted
        if ($invitation->status !== 'accepted') {
            $invitation->update(['status' => 'accepted', 'accepted_at' => now(), 'rejected_at' => null, 'revoked_at' => null]);
        }

        // Synchronize user role as doctor
        $user = $request->user();
        if ($user->role !== 'doctor') {
            $user->update(['role' => 'doctor']);
        }

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => $this->serializeInvitation($invitation->fresh($this->withPatient())),
        ]);
    }

    // Reject a doctor invitation
    public function reject(Request $request, DoctorInvitation $invitation): JsonResponse
    {
        // Check that doctor owns the invitation
        if ($error = $this->authorizeInvitation($invitation, $request)) return $error;

        $invitation->update(['status' => 'rejected', 'rejected_at' => now()]);

        return response()->json([
            'message' => 'Invitation rejected.',
            'data'    => $this->serializeInvitation($invitation->fresh($this->withPatient())),
        ]);
    }

    // List all doctor's patients
    public function indexPatients(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $patients = DoctorInvitation::with($this->withPatient())
            ->where('doctor_user_id', $user->id)
            ->where('status', 'accepted')
            ->orderByDesc('accepted_at')
            ->get()
            ->map(function (DoctorInvitation $invitation) {
                $patient = $invitation->patient;
                // Filter out invitations without valid patient
                if (! $patient) return null;

                $latestVitals  = $this->latestVitals($patient->id);
                $labResults    = AnalysisResult::where('user_id', $patient->id)->orderByDesc('analysis_date')->orderByDesc('id')->limit(5)->get();

                return [
                    'invitation_id' => $invitation->id,
                    'accepted_at'   => $invitation->accepted_at?->toISOString(),
                    'patient'       => array_merge($patient->toArray(), ['email' => $patient->account?->email]),
                    'profile'       => $patient->healthProfile,
                    'latest_vitals' => $latestVitals,
                    'alerts'        => $this->buildPatientAlerts($latestVitals, $labResults),
                ];
            })
            ->filter()
            ->values();

        return response()->json(['message' => 'Doctor\'s patients retrieved successfully.', 'data' => $patients]);
    }

    // Show complete patient details
    public function showPatient(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->findAuthorizedInvitation($request, $patient);
        // Check that doctor has access to this patient
        if (! $invitation) {
            return response()->json(['message' => 'Unauthorized access to this patient.'], Response::HTTP_FORBIDDEN);
        }

        $vitalsDays  = max(1, min((int) $request->query('vitals_days', 30), 90));
        $vitalsStart = Carbon::today()->subDays($vitalsDays - 1)->startOfDay();

        $doctor = $request->user();
        $observations = HealthObservation::where('doctor_user_id', $doctor->id)
            ->where('patient_user_id', $patient->id)
            ->orderByDesc('observation_date')
            ->get()
            ->map(fn ($o) => [
                'observation_date' => $o->observation_date->toDateString(),
                'note'             => $o->note,
                'updated_at'       => $o->updated_at?->toISOString(),
            ]);

        return response()->json([
            'message' => 'Patient details retrieved successfully.',
            'data'    => [
                'invitation_id'       => $invitation->id,
                'accepted_at'         => $invitation->accepted_at?->toISOString(),
                'patient'             => $patient,
                'profile'             => $patient->healthProfile,
                'latest_vitals'       => $this->latestVitals($patient->id),
                'vitals'              => VitalSigns::where('user_id', $patient->id)->where('measured_at', '>=', $vitalsStart)->orderByDesc('measured_at')->get(),
                'lab_results'         => AnalysisResult::where('user_id', $patient->id)->orderByDesc('analysis_date')->orderByDesc('id')->get(),
                'treatment_medicines' => $this->healthDataService->resolveTreatmentMedicines($patient->id),
                'treatment_checks'    => TreatmentCheck::where('user_id', $patient->id)->where('check_date', '>=', Carbon::today()->subDays(29)->toDateString())->orderBy('check_date')->get(),
                'observations'        => $observations,
            ],
        ]);
    }

    // Create or update an observation for a specific date
    public function upsertObservation(Request $request, User $patient): JsonResponse
    {
        if (! $this->findAuthorizedInvitation($request, $patient)) {
            return response()->json(['message' => 'Unauthorized access to this patient.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'observation_date' => ['required', 'date_format:Y-m-d'],
            'note'             => ['required', 'string', 'max:3000'],
        ]);

        $obs = HealthObservation::updateOrCreate(
            [
                'doctor_user_id'   => $request->user()->id,
                'patient_user_id'  => $patient->id,
                'observation_date' => $validated['observation_date'],
            ],
            ['note' => trim($validated['note'])],
        );

        return response()->json([
            'message' => 'Observation saved.',
            'data'    => [
                'observation_date' => $obs->observation_date->toDateString(),
                'note'             => $obs->note,
                'updated_at'       => $obs->updated_at?->toISOString(),
            ],
        ]);
    }

    // Delete an observation for a specific date
    public function destroyObservation(Request $request, User $patient, string $date): JsonResponse
    {
        if (! $this->findAuthorizedInvitation($request, $patient)) {
            return response()->json(['message' => 'Unauthorized access to this patient.'], Response::HTTP_FORBIDDEN);
        }

        HealthObservation::where([
            'doctor_user_id'   => $request->user()->id,
            'patient_user_id'  => $patient->id,
            'observation_date' => $date,
        ])->delete();

        return response()->json(['message' => 'Observation deleted.']);
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    // Get patient columns
    private function withPatient(): array
    {
        return ['patient:id,account_id,name,date_of_birth,created_at', 'patient.account:id,email', 'patient.healthProfile'];
    }

    // Get latest vital signs for patient
    private function latestVitals(int $userId): ?VitalSigns
    {
        return VitalSigns::where('user_id', $userId)
            ->where(fn ($q) => $q->whereNotNull('heart_rate')->orWhereNotNull('systolic_pressure')->orWhereNotNull('diastolic_pressure')->orWhereNotNull('oxygen_saturation'))
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();
    }

    // Serialize doctor invitation
    private function serializeInvitation(DoctorInvitation $invitation): array
    {
        $patient = $invitation->patient;
        return [
            'id'           => $invitation->id,
            'status'       => $invitation->status,
            'doctor_email' => $invitation->doctor_email,
            'created_at'   => $invitation->created_at?->toISOString(),
            'accepted_at'  => $invitation->accepted_at?->toISOString(),
            'rejected_at'  => $invitation->rejected_at?->toISOString(),
            'patient'      => $patient ? array_merge($patient->toArray(), ['email' => $patient->account?->email]) : null,
            'profile'      => $patient?->healthProfile,
        ];
    }

    // Find authorized invitation for a patient
    private function findAuthorizedInvitation(Request $request, User $patient): ?DoctorInvitation
    {
        $user = $request->user();
        return DoctorInvitation::where('doctor_user_id', $user->id)
            ->where('patient_user_id', $patient->id)
            ->where('status', 'accepted')
            ->latest('accepted_at')
            ->latest('id')
            ->first();
    }

    // Build alerts for a patient
    private function buildPatientAlerts(?VitalSigns $latestVitals, Collection $labResults): array
    {
        $alerts = [];

        // Detect high blood pressure
        if ($latestVitals?->systolic_pressure >= 140) {
            $alerts[] = [
                'severity'       => 'warning',
                'title'          => 'Alert',
                'message'        => 'Elevated blood pressure: ' . (int) $latestVitals->systolic_pressure . '/' . (int) ($latestVitals->diastolic_pressure ?? 0) . ' mmHg',
                'recommendation' => 'Monitor blood pressure and contact patient if elevation persists.',
                'measured_at'    => $latestVitals->measured_at?->toISOString(),
            ];
        }

        $glucose = $labResults->first(fn (AnalysisResult $r) => str_contains(strtolower((string) $r->analysis_type), 'glucose'));

        // Detect dangerously low blood glucose (critical)
        if ($glucose && is_numeric($glucose->analysis_result) && $glucose->analysis_result < 3.9) {
            $alerts[] = [
                'severity'       => 'critical',
                'title'          => 'Critical Alert',
                'message'        => 'Very low blood glucose detected: ' . rtrim(rtrim((string) $glucose->analysis_result, '0'), '.') . ' ' . ($glucose->normal_range ?: 'mmol/L'),
                'recommendation' => 'Contact patient immediately. Emergency sugar intake recommended.',
                'measured_at'    => $glucose->analysis_date?->toISOString(),
            ];
        }

        return $alerts;
    }

    // Check authorization for an invitation
    private function authorizeInvitation(DoctorInvitation $invitation, Request $request): ?JsonResponse
    {
        $user = $request->user();

        // Primary check: doctor_user_id already linked to this user
        if ($invitation->doctor_user_id === $user->id) {
            return null;
        }

        // Fallback: doctor_user_id not yet set — check by email
        $doctorEmail = strtolower((string) ($user->account?->email ?? ''));
        if (
            $doctorEmail !== '' &&
            strtolower((string) $invitation->doctor_email) === $doctorEmail &&
            is_null($invitation->doctor_user_id)
        ) {
            // Link now so subsequent checks work
            $invitation->update(['doctor_user_id' => $user->id]);
            return null;
        }

        return response()->json(['message' => 'Unauthorized access to this invitation.'], Response::HTTP_FORBIDDEN);
    }
}