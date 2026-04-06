<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DoctorInvitationMail;
use App\Models\DoctorInvitation;
use App\Models\HealthProfile;
use App\Models\User;

use App\Services\TreatmentCatalogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Manages user health profile
 * 
 * Responsibilities:
 * - Creation and update of complete health profile
 * - Health profile consultation
 * - Automatic synchronization of doctor invitations
 * - Management of medical consents
 */
class HealthProfileController extends Controller
{
    public function __construct(
        private readonly TreatmentCatalogService $treatmentCatalogService,
    ) {}

    // Record or update health profile
    public function store(Request $request)
    {
        // Normalize gender field to lowercase
        if (is_string($request->input('gender'))) {
            $request->merge([
                'gender' => strtolower(trim($request->input('gender'))),
            ]);
        }

        $validated = $request->validate([
            'gender' => ['required', Rule::in(['male', 'female'])],
            'height' => ['required', 'numeric', 'min:30', 'max:250'],
            'weight' => ['required', 'numeric', 'min:1', 'max:300'],
            'blood_type' => ['required', 'string', 'max:5'],
            'goals' => ['nullable', 'array'],
            'goals.*' => ['string', 'max:120'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:100'],
            'chronic_diseases' => ['nullable', 'array'],
            'chronic_diseases.*' => ['string', 'max:120'],
            'treatments' => ['nullable', 'array'],
            'treatments.*.type' => ['required_with:treatments', 'string', 'max:120'],
            'treatments.*.name' => ['nullable', 'string', 'max:255'],
            'treatments.*.dose' => ['nullable', 'string', 'max:120'],
            'treatments.*.frequency_unit' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'treatments.*.frequency_count' => ['nullable', 'integer', 'min:1'],
            'treatments.*.duration' => ['nullable', 'string', 'max:120'],
            'treatments.*.start_date' => ['nullable', 'date'],
            'treatments.*.end_date' => ['nullable', 'date'],
            'consults_doctor' => ['nullable', 'boolean'],
            'doctor_can_consult' => ['nullable', 'boolean'],
            'doctor_email' => [
                'nullable',
                'email',
                'required_if:doctor_can_consult,1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = Auth::user();
                    $currentEmail = strtolower((string) $user?->account?->email);
                    if (strtolower((string) $value) === $currentEmail) {
                        $fail("The doctor's email must be different from your email.");
                    }
                },
            ],
            'smoker' => ['required', 'boolean'],
            'alcoholic' => ['required', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated['user_id'] = $user->id;
        $validated['doctor_email'] = $validated['doctor_email'] !== null
            ? strtolower(trim($validated['doctor_email']))
            : null;

        $existingProfile = HealthProfile::query()
            ->where('user_id', $user->id)
            ->first();

        $previousDoctorEmail = $existingProfile?->doctor_email !== null
            ? strtolower(trim((string) $existingProfile->doctor_email))
            : null;

        // Extract treatments before updateOrCreate
        $treatmentsData = $validated['treatments'] ?? [];
        unset($validated['treatments']);

        try {
            $profile = DB::transaction(function () use ($validated, $user, $treatmentsData) {
                $userId = $user->id;
                $savedProfile = HealthProfile::updateOrCreate(
                    ['user_id' => $userId],
                    $validated
                );

                // Save treatments in the treatments table
                if (is_array($treatmentsData) && count($treatmentsData) > 0) {
                    // Delete old treatments
                    \App\Models\Treatment::where('user_id', $userId)->delete();
                    
                    // Add new treatments
                    foreach ($treatmentsData as $idx => $treatment) {
                        if (! is_array($treatment)) {
                            continue;
                        }

                        // Validate dates
                        $startDate = !empty($treatment['start_date']) ? $treatment['start_date'] : null;
                        $endDate = !empty($treatment['end_date']) ? $treatment['end_date'] : null;

                        if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
                            Log::warning("Treatment date validation: start_date > end_date for user {$userId}", [
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                            ]);
                            // Swap dates if reversed
                            [$startDate, $endDate] = [$endDate, $startDate];
                        }

                        // Find or create catalog entry
                        $catalogEntry = null;
                        $type = trim($treatment['type'] ?? '');
                        $name = trim($treatment['name'] ?? '');

                        if ($type !== '') {
                            $catalogEntry = \App\Models\TreatmentCatalog::firstOrCreate(
                                ['medication_type' => $type, 'medication_name' => $name],
                                ['created_by_user_id' => $userId]
                            );
                        }
                        
                        // Log treatment creation
                        Log::info("Creating treatment for user {$userId}", [
                            'type' => $type,
                            'name' => $name,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]);

                        // Create treatment with FK to user
                        \App\Models\Treatment::create([
                            'user_id' => $userId,
                            'treatment_catalog_id' => $catalogEntry ? $catalogEntry->id : null,
                            'dose' => $treatment['dose'] ?? null,
                            'frequency' => $treatment['frequency_unit'] ?? null,
                            'daily_doses' => $treatment['frequency_count'] ?? null,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]);
                    }
                }

                return $savedProfile;
            });
        } catch (\Throwable $exception) {
            Log::error('Health profile persistence failed: '.$exception->getMessage(), [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => "Error saving health profile.",
            ], 500);
        }

        $this->syncDoctorInvitation($profile, $previousDoctorEmail, $user);

        // Reload profile with treatments for response
        $treatments = $user->treatments()->with('treatmentCatalog')->get()->map(function ($t) {
            $startDate = $t->start_date?->toDateString();
            $endDate   = $t->end_date?->toDateString();
            return [
                'id'              => $t->id,
                'type'            => $t->treatmentCatalog?->medication_type ?? '',
                'name'            => $t->treatmentCatalog?->medication_name  ?? '',
                'dose'            => $t->dose,
                'frequency_unit'  => $t->frequency,
                'frequency_count' => $t->daily_doses,
                'start_date'      => $startDate,
                'end_date'        => $endDate,
            ];
        })->values()->toArray();

        $profileData = $profile->toArray();
        $profileData['treatments'] = $treatments;

        return response()->json([
            'message' => 'Health profile saved successfully.',
            'data' => $profileData,
        ]);
    }

    // Display user's health profile
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_UNAUTHORIZED);
        }

        $profile = $user->healthProfile()->first();

        if (!$profile) {
            return response()->json(['data' => null, 'user' => $user]);
        }

        // Load treatments directly from the user
        $treatments = $user->treatments()->with('treatmentCatalog')->get();

        $profileData = $profile->toArray();
        $profileData['treatments'] = $treatments->map(function ($t) {
            $startDate = $t->start_date?->toDateString();
            $endDate   = $t->end_date?->toDateString();
            return [
                'id'              => $t->id,
                'type'            => $t->treatmentCatalog?->medication_type ?? '',
                'name'            => $t->treatmentCatalog?->medication_name  ?? '',
                'dose'            => $t->dose,
                'frequency_unit'  => $t->frequency,
                'frequency_count' => $t->daily_doses,
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'formatted_start_date'      => $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '',
                'formatted_end_date'        => $endDate   ? \Carbon\Carbon::parse($endDate)->format('d/m/Y')   : '',
            ];
        })->values()->toArray();

        return response()->json([
            'data'        => $profileData,
            'user' => $user,
        ]);
    }

    // Synchronize doctor invitation for this profile
    private function syncDoctorInvitation(HealthProfile $profile, ?string $previousDoctorEmail = null, $user = null): void
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        // Check that user exists
        if (! $user) {
            return;
        }

        $shouldInvite = (bool) $profile->consults_doctor
            && (bool) $profile->doctor_can_consult
            && ! empty($profile->doctor_email);

        // Revoke invitations if conditions are not met
        if (! $shouldInvite) {
            DoctorInvitation::query()
                ->where('patient_user_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
            return;
        }

        $doctorEmail = strtolower(trim((string) $profile->doctor_email));
        $doctorEmailChanged = $doctorEmail !== ($previousDoctorEmail !== null ? strtolower(trim($previousDoctorEmail)) : null);

        // Revoke all previous invitations (pending OR accepted) if doctor email has changed
        if ($doctorEmailChanged) {
            DoctorInvitation::query()
                ->where('patient_user_id', $user->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->where('doctor_email', '!=', $doctorEmail)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
        }

        // Search for doctor account by email in Account table
        $doctorAccount = \App\Models\Account::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->first();
        
        $doctor = $doctorAccount?->user;
        
        // If not found as doctor, search for any account with that email
        if (!$doctor) {
            $doctor = \App\Models\User::query()
                ->whereHas('account', function ($query) use ($doctorEmail) {
                    $query->whereRaw('LOWER(email) = ?', [$doctorEmail]);
                })
                ->latest('id')
                ->first();
        }

        $existingAccount = $doctor;

        // Check that user is not inviting themselves as doctor
        if ($existingAccount && $existingAccount->id === $user->id) {
            return;
        }

        $doctor = $existingAccount;

        // Update role of existing user to 'doctor'
        if ($existingAccount && ! ($doctor?->role === 'doctor') && $existingAccount->role !== 'doctor') {
            try {
                $existingAccount->update([
                    'role' => 'doctor',
                ]);
                $doctor = $existingAccount->fresh();
            } catch (QueryException $exception) {
                Log::warning('Doctor invitation role sync skipped: '.$exception->getMessage(), [
                    'doctor_email' => $doctorEmail,
                    'existing_account_id' => $existingAccount->id,
                ]);

                $doctorAccount = \App\Models\Account::query()
                    ->whereRaw('LOWER(email) = ?', [$doctorEmail])
                    ->first();
                
                $doctor = $doctorAccount?->user;
                if ($doctor && $doctor->role !== 'doctor') {
                    $doctor = null;
                }
            }
        }

        $existing = DoctorInvitation::query()
            ->where('patient_user_id', $user->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        // Handle creation or update of invitation
        if ($existing) {
            if ($doctorEmailChanged || $existing->status === 'revoked' || $existing->status === 'rejected') {
                // Always reset to pending so the new doctor must explicitly accept
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email'   => $doctorEmail,
                    'status'         => 'pending',
                    'token'          => Str::random(64),
                    'accepted_at'    => null,
                    'rejected_at'    => null,
                    'revoked_at'     => null,
                ]);
            } else {
                // Same email, still pending/accepted — just sync doctor_user_id
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                ]);
            }
        } else {
            // No existing invitation — create a new pending one
            DoctorInvitation::query()->create([
                'patient_user_id' => $user->id,
                'doctor_user_id'  => $doctor?->id,
                'doctor_email'    => $doctorEmail,
                'status'          => 'pending',
                'token'           => Str::random(64),
            ]);
        }

        // Send invitation email only if:
        // 1. Email has changed OR it's the first time
        // 2. Doctor doesn't exist (no existing doctor account)
        $shouldSendEmail = $doctorEmailChanged && ! $doctor;

        if ($shouldSendEmail) {
            try {
                Mail::to($doctorEmail)->queue(new DoctorInvitationMail(
                    $user,
                    $doctorEmail,
                ));
                Log::info('Doctor invitation email queued successfully', [
                    'doctor_email' => $doctorEmail,
                    'patient_id' => $user->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Doctor invitation email failed', [
                    'doctor_email' => $doctorEmail,
                    'patient_id' => $user->id,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
            }
        }
    }
}
