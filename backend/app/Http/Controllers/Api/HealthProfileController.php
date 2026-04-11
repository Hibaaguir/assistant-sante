<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthProfile;
use App\Models\Treatment;
use App\Models\TreatmentCatalog;
use App\Services\DoctorInvitationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthProfileController extends Controller
{
    public function __construct(
        private readonly DoctorInvitationService $invitationService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Save or update the health profile of the current user
    //
    // 1. Normalize and validate the form data
    // 2. Save the previous doctor email (needed for invitation sync)
    // 3. Save the health profile
    // 4. Replace all treatments with the new list
    // 5. Sync doctor invitations
    // 6. Return the saved profile with treatments
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        // Normalize gender to lowercase before validation
        if ($request->has('gender')) {
            $request->merge(['gender' => strtolower(trim($request->gender))]);
        }

        $data = $request->validate([
            'gender'                       => 'required|in:male,female',
            'height'                       => 'required|numeric|min:30|max:250',
            'weight'                       => 'required|numeric|min:1|max:300',
            'blood_type'                   => 'required|string|max:5',
            'goals'                        => 'nullable|array',
            'goals.*'                      => 'string|max:120',
            'allergies'                    => 'nullable|array',
            'allergies.*'                  => 'string|max:100',
            'chronic_diseases'             => 'nullable|array',
            'chronic_diseases.*'           => 'string|max:120',
            'treatments'                   => 'nullable|array',
            'treatments.*.type'            => 'required_with:treatments|string|max:120',
            'treatments.*.name'            => 'nullable|string|max:255',
            'treatments.*.dose'            => 'nullable|string|max:120',
            'treatments.*.frequency_unit'  => 'nullable|in:day,week,month',
            'treatments.*.frequency_count' => 'nullable|integer|min:1',
            'treatments.*.duration'        => 'nullable|string|max:120',
            'treatments.*.start_date'      => 'nullable|date',
            'treatments.*.end_date'        => 'nullable|date',
            'doctor_invited'               => 'nullable|boolean',
            'doctor_email'                 => [
                'nullable',
                'email',
                'required_if:doctor_invited,1',
                function ($_attribute, $value, $fail) use ($request) {
                    $myEmail = strtolower($request->user()->account?->email ?? '');
                    if (strtolower($value) === $myEmail) {
                        $fail("The doctor's email must be different from your email.");
                    }
                },
            ],
            'smoker'                       => 'required|boolean',
            'alcoholic'                    => 'required|boolean',
        ]);

        $user = $request->user();

        // Normalize doctor email to lowercase
        if (!empty($data['doctor_email'])) {
            $data['doctor_email'] = strtolower(trim($data['doctor_email']));
        }

        // Save the old doctor email before updating (used for invitation sync below)
        $existingProfile     = HealthProfile::where('user_id', $user->id)->first();
        $previousDoctorEmail = $existingProfile ? strtolower(trim((string) $existingProfile->doctor_email)) : null;

        // Separate treatments from the rest of the profile data
        $treatments = $data['treatments'] ?? [];
        unset($data['treatments']);

        $data['user_id'] = $user->id;

        // Save the health profile (create if new, update if exists)
        $profile = HealthProfile::updateOrCreate(['user_id' => $user->id], $data);

        // Replace all existing treatments with the new list
        if (!empty($treatments)) {
            Treatment::where('user_id', $user->id)->delete();

            foreach ($treatments as $treatment) {
                $startDate = $treatment['start_date'] ?? null;
                $endDate   = $treatment['end_date']   ?? null;

                // If start date is after end date, swap them automatically
                if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }

                $type = trim($treatment['type'] ?? '');
                $name = trim($treatment['name'] ?? '');

                // Find or create the medication in the catalog
                $catalog = $type !== '' ? TreatmentCatalog::firstOrCreate([
                    'medication_type' => $type,
                    'medication_name' => $name,
                ]) : null;

                Treatment::create([
                    'user_id'              => $user->id,
                    'treatment_catalog_id' => $catalog?->id,
                    'dose'                 => $treatment['dose']            ?? null,
                    'frequency'            => $treatment['frequency_unit']  ?? null,
                    'daily_doses'          => $treatment['frequency_count'] ?? null,
                    'start_date'           => $startDate,
                    'end_date'             => $endDate,
                ]);
            }
        }

        // Sync doctor invitations if the doctor email changed
        $this->invitationService->sync($profile, $previousDoctorEmail, $user);

        // Load and return the saved treatments
        $profileData               = $profile->toArray();
        $profileData['treatments'] = $user->treatments()->with('treatmentCatalog')->get()
            ->map(fn($t) => $this->formatTreatment($t))
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Health profile saved successfully.',
            'data'    => $profileData,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Return the health profile of the current user
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->healthProfile()->first();

        if (!$profile) {
            return response()->json(['data' => null, 'user' => $user]);
        }

        $profileData               = $profile->toArray();
        $profileData['treatments'] = $user->treatments()->with('treatmentCatalog')->get()
            ->map(fn($t) => $this->formatTreatment($t, withFormattedDates: true))
            ->values()
            ->toArray();

        return response()->json([
            'data' => $profileData,
            'user' => $user,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Format a treatment record for the API response
    // Pass withFormattedDates: true to also include human-readable date strings
    // ─────────────────────────────────────────────────────────────────────────
    private function formatTreatment($treatment, bool $withFormattedDates = false): array
    {
        $startDate = $treatment->start_date?->toDateString();
        $endDate   = $treatment->end_date?->toDateString();

        $result = [
            'id'              => $treatment->id,
            'type'            => $treatment->treatmentCatalog?->medication_type ?? '',
            'name'            => $treatment->treatmentCatalog?->medication_name  ?? '',
            'dose'            => $treatment->dose,
            'frequency_unit'  => $treatment->frequency,
            'frequency_count' => $treatment->daily_doses,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
        ];

        // Only show formatted dates when requested (used in show(), not store())
        if ($withFormattedDates) {
            $result['formatted_start_date'] = $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '';
            $result['formatted_end_date']   = $endDate   ? Carbon::parse($endDate)->format('d/m/Y')   : '';
        }

        return $result;
    }
}
