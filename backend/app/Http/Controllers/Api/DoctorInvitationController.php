<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Models\HealthLabResult;
use App\Models\HealthTreatmentCheck;
use App\Models\HealthVital;
use App\Models\ProfilSante;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DoctorInvitationController extends Controller
{
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
        $user = $request->user();
        if ($doctorInvitation->doctor_user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized invitation access.'], 403);
        }

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
        $user = $request->user();
        if ($doctorInvitation->doctor_user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized invitation access.'], 403);
        }

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
                    'accepted_at' => optional($invitation->accepted_at)?->toISOString(),
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

        $profile = ProfilSante::query()
            ->where('user_id', $patient->id)
            ->first();

        return response()->json([
            'message' => 'Doctor patient detail fetched successfully.',
            'data' => [
                'invitation_id' => $invitation->id,
                'accepted_at' => optional($invitation->accepted_at)?->toISOString(),
                'patient' => $patient,
                'profile' => $profile,
                'latest_vitals' => $latestVitals,
                'vitals' => $vitalsHistory,
                'vitals_chart' => $this->buildVitalsChartSeries($vitals, $days),
                'lab_results' => $labResults,
                'treatment_medicines' => $this->resolveTreatmentMedicines($patient->id),
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
            'created_at' => optional($invitation->created_at)?->toISOString(),
            'accepted_at' => optional($invitation->accepted_at)?->toISOString(),
            'rejected_at' => optional($invitation->rejected_at)?->toISOString(),
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

        if ($latestVitals?->systolic_pressure !== null && (float) $latestVitals->systolic_pressure >= 140) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => 'Alerte',
                'message' => 'Tension arterielle elevee : '.(int) $latestVitals->systolic_pressure.'/'.(int) ($latestVitals->diastolic_pressure ?? 0).' mmHg',
                'recommendation' => 'Surveiller la tension et contacter le patient si la hausse persiste.',
                'measured_at' => optional($latestVitals->measured_at)?->toISOString(),
            ];
        }

        $glucose = $labResults->first(function (HealthLabResult $result) {
            return str_contains(strtolower((string) $result->analysis_type), 'glucose');
        });

        if ($glucose && is_numeric($glucose->value) && (float) $glucose->value < 3.9) {
            $alerts[] = [
                'severity' => 'critical',
                'title' => 'Alerte critique',
                'message' => 'Glycemie tres basse detectee : '.rtrim(rtrim((string) $glucose->value, '0'), '.').' '.($glucose->unit ?: 'mmol/L'),
                'recommendation' => 'Contacter immediatement le patient. Resucrage urgent recommande.',
                'measured_at' => optional($glucose->analysis_date)?->toISOString(),
            ];
        }

        return $alerts;
    }

    private function buildVitalsChartSeries(Collection $vitals, int $days): array
    {
        $dates = collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset)->toDateString())
            ->values();

        $grouped = $vitals
            ->groupBy(fn (HealthVital $vital) => optional($vital->measured_at)->toDateString())
            ->map(function (Collection $items): array {
                $sorted = $items->sortByDesc(function (HealthVital $vital): string {
                    $timestamp = optional($vital->measured_at)?->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00';
                    return $timestamp.'#'.str_pad((string) $vital->id, 10, '0', STR_PAD_LEFT);
                });

                $latestMeasuredValue = function (string $field) use ($sorted): ?float {
                    $row = $sorted->first(fn (HealthVital $vital) => $vital->{$field} !== null);
                    if (! $row) {
                        return null;
                    }

                    return round((float) $row->{$field}, 1);
                };

                return [
                    'heart_rate' => $latestMeasuredValue('heart_rate'),
                    'systolic_pressure' => $latestMeasuredValue('systolic_pressure'),
                    'diastolic_pressure' => $latestMeasuredValue('diastolic_pressure'),
                    'oxygen_saturation' => $latestMeasuredValue('oxygen_saturation'),
                ];
            });

        return [
            'labels' => $dates,
            'heart_rate' => $dates->map(fn (string $date) => $grouped[$date]['heart_rate'] ?? null)->all(),
            'systolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['systolic_pressure'] ?? null)->all(),
            'diastolic_pressure' => $dates->map(fn (string $date) => $grouped[$date]['diastolic_pressure'] ?? null)->all(),
            'oxygen_saturation' => $dates->map(fn (string $date) => $grouped[$date]['oxygen_saturation'] ?? null)->all(),
        ];
    }

    private function resolveTreatmentMedicines(int $userId): array
    {
        $profil = ProfilSante::query()
            ->where('user_id', $userId)
            ->first();

        $rawTreatments = is_array($profil?->traitements) ? $profil->traitements : [];
        $medicines = [];

        foreach ($rawTreatments as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            $type = trim((string) ($item['type'] ?? ''));
            if ($name === '' && $type === '') {
                continue;
            }

            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name !== '' ? $name : $type) ?? 'traitement');
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'traitement';
            }

            $frequencyCount = (int) ($item['frequency_count'] ?? 0);
            $frequencyUnit = trim((string) ($item['frequency_unit'] ?? ''));
            $dosesPerDay = 1;
            if ($frequencyCount > 0) {
                $dosesPerDay = $frequencyUnit === 'jour' ? $frequencyCount : 1;
            }

            $frequency = $frequencyCount > 0 && $frequencyUnit !== ''
                ? $frequencyCount.' fois / '.$frequencyUnit
                : 'Non precise';

            $medicines[] = [
                'id' => $base.'-'.($index + 1),
                'name' => $name !== '' ? $name : ucfirst($type),
                'dose' => trim((string) ($item['dose'] ?? '')) ?: 'Dose non precisee',
                'freq' => $frequency,
                'doses_per_day' => max(1, min($dosesPerDay, 12)),
                'note' => trim((string) ($item['duration'] ?? '')) ?: ($type !== '' ? ucfirst($type) : ''),
            ];
        }

        if (empty($medicines) && ($profil?->prend_medicament) && ! empty($profil?->nom_medicament)) {
            $nom = trim((string) $profil->nom_medicament);
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nom) ?? 'medicament');
            $base = trim($base, '-');
            if ($base === '') {
                $base = 'medicament';
            }

            $medicines[] = [
                'id' => $base.'-1',
                'name' => $nom,
                'dose' => 'Dose non precisee',
                'freq' => 'Non precise',
                'doses_per_day' => 1,
                'note' => '',
            ];
        }

        return $medicines;
    }
}
