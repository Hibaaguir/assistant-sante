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
    public function __construct(private readonly HealthDataService $healthDataService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $invitations = DoctorInvitation::query()
            ->with(['patient:id,name,email,date_of_birth,created_at', 'patient.profilSante'])
            ->where('doctor_user_id', $user->id)
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('id')
            ->get()
            ->map(fn (DoctorInvitation $invitation) => $this->serializeInvitation($invitation))
            ->values();

        return response()->json([
            'message' => 'Invitations fetched successfully.',
            'data' => $invitations,
        ]);
    }

    public function accept(Request $request, DoctorInvitation $doctorInvitation): JsonResponse
    {
        if ($error = $this->authorizeInvitation($doctorInvitation, $request)) return $error;
        $user = $request->user();

        if ($doctorInvitation->status !== 'accepted') {
            $doctorInvitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'rejected_at' => null,
                'revoked_at' => null,
            ]);
        }

        if ($user->role !== 'medecin') {
            $user->update(['role' => 'medecin']);
        }

        return response()->json([
            'message' => 'Invitation accepted.',
            'data' => $this->serializeInvitation($doctorInvitation->fresh(['patient:id,name,email,date_of_birth,created_at', 'patient.profilSante'])),
        ]);
    }

    public function reject(Request $request, DoctorInvitation $doctorInvitation): JsonResponse
    {
        if ($error = $this->authorizeInvitation($doctorInvitation, $request)) return $error;

        $doctorInvitation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation rejected.',
            'data' => $this->serializeInvitation($doctorInvitation->fresh(['patient:id,name,email,date_of_birth,created_at', 'patient.profilSante'])),
        ]);
    }

    public function patients(Request $request): JsonResponse
    {
        $user = $request->user();

        $patients = DoctorInvitation::query()
            ->with(['patient:id,name,email,date_of_birth,created_at', 'patient.profilSante'])
            ->where('doctor_user_id', $user->id)
            ->where('status', 'accepted')
            ->orderByDesc('accepted_at')
            ->get()
            ->map(function (DoctorInvitation $invitation) {
                $patient = $invitation->patient;
                if (! $patient) {
                    return null;
                }

                $latestVitals = HealthVital::query()
                    ->where('user_id', $patient->id)
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

                $latestLabResults = HealthLabResult::query()
                    ->where('user_id', $patient->id)
                    ->orderByDesc('analysis_date')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get();

                return [
                    'invitation_id' => $invitation->id,
                    'accepted_at' => $invitation->accepted_at?->toISOString(),
                    'patient' => $patient,
                    'profile' => $patient->profilSante,
                    'latest_vitals' => $latestVitals,
                    'alerts' => $this->buildPatientAlerts($patient, $latestVitals, $latestLabResults),
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'message' => 'Doctor patients fetched successfully.',
            'data' => $patients,
        ]);
    }

    public function patientDetail(Request $request, User $patient): JsonResponse
    {
        $invitation = $this->findAuthorizedInvitation($request, $patient);
        if (! $invitation) {
            return response()->json([
                'message' => 'Unauthorized patient access.',
            ], 403);
        }

        $days = max(1, min((int) $request->query('days', 7), 30));
        $vitalsDays = max(1, min((int) $request->query('vitals_days', 30), 90));
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $vitalsStart = Carbon::today()->subDays($vitalsDays - 1)->startOfDay();

        $vitals = HealthVital::query()
            ->where('user_id', $patient->id)
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get();

        $vitalsHistory = HealthVital::query()
            ->where('user_id', $patient->id)
            ->where('measured_at', '>=', $vitalsStart)
            ->orderByDesc('measured_at')
            ->get();

        $latestVitals = HealthVital::query()
            ->where('user_id', $patient->id)
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
            ->where('user_id', $patient->id)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        $treatmentChecks = HealthTreatmentCheck::query()
            ->where('user_id', $patient->id)
            ->where('check_date', '>=', Carbon::today()->subDays(29)->toDateString())
            ->orderBy('check_date')
            ->orderBy('medication_name')
            ->get();

        $profile = $patient->profilSante;

        return response()->json([
            'message' => 'Doctor patient detail fetched successfully.',
            'data' => [
                'invitation_id' => $invitation->id,
                'accepted_at' => optional($invitation->accepted_at)?->toISOString(),
                'patient' => $patient,
                'profile' => $profile,
                'latest_vitals' => $latestVitals,
                'vitals' => $vitalsHistory,
                'vitals_chart' => $this->healthDataService->buildVitalsChartSeries($vitals, $days),
                'lab_results' => $labResults,
                'treatment_medicines' => $this->healthDataService->resolveTreatmentMedicines($patient->id),
                'treatment_checks' => $treatmentChecks,
                'alerts' => $this->buildPatientAlerts($patient, $latestVitals, $labResults),
            ],
        ]);
    }

    private function serializeInvitation(DoctorInvitation $invitation): array
    {
        return [
            'id' => $invitation->id,
            'status' => $invitation->status,
            'doctor_email' => $invitation->doctor_email,
            'created_at' => $invitation->created_at?->toISOString(),
            'accepted_at' => $invitation->accepted_at?->toISOString(),
            'rejected_at' => $invitation->rejected_at?->toISOString(),
            'patient' => $invitation->patient,
            'profile' => $invitation->patient?->profilSante,
        ];
    }

    private function findAuthorizedInvitation(Request $request, User $patient): ?DoctorInvitation
    {
        return DoctorInvitation::query()
            ->where('doctor_user_id', $request->user()->id)
            ->where('patient_user_id', $patient->id)
            ->where('status', 'accepted')
            ->latest('accepted_at')
            ->latest('id')
            ->first();
    }

    private function buildPatientAlerts(User $patient, ?HealthVital $latestVitals, Collection $labResults): array
    {
        $alerts = [];

        if ($latestVitals?->systolic_pressure !== null && $latestVitals->systolic_pressure >= 140) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => 'Alerte',
                'message' => 'Tension arterielle elevee : '.(int) $latestVitals->systolic_pressure.'/'.(int) ($latestVitals->diastolic_pressure ?? 0).' mmHg',
                'recommendation' => 'Surveiller la tension et contacter le patient si la hausse persiste.',
                'measured_at' => $latestVitals->measured_at?->toISOString(),
            ];
        }

        $glucose = $labResults->first(
            fn (HealthLabResult $result) => str_contains(strtolower((string) $result->analysis_type), 'glucose')
        );

        if ($glucose && is_numeric($glucose->value) && $glucose->value < 3.9) {
            $alerts[] = [
                'severity' => 'critical',
                'title' => 'Alerte critique',
                'message' => 'Glycemie tres basse detectee : '.rtrim(rtrim((string) $glucose->value, '0'), '.').' '.($glucose->unit ?: 'mmol/L'),
                'recommendation' => 'Contacter immediatement le patient. Resucrage urgent recommande.',
                'measured_at' => $glucose->analysis_date?->toISOString(),
            ];
        }

        return $alerts;
    }

    private function authorizeInvitation(DoctorInvitation $invitation, Request $request): ?JsonResponse
    {
        if ($invitation->doctor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized invitation access.'], 403);
        }
        return null;
    }
}