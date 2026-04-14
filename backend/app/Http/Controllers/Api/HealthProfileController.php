<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthData;
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

    // Enregistrer ou mettre à jour le profil de santé
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gender'                       => 'required|string|max:30',
            'height'                       => 'required|numeric|min:30|max:250',
            'current_weight'               => 'required|numeric|min:1|max:300',
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
            'treatments.*.frequency_unit'  => 'nullable|string|max:30',
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
                        $fail("L'email du médecin doit être différent de votre email.");
                    }
                },
            ],
            'smoker'                       => 'required|boolean',
            'alcoholic'                    => 'required|boolean',
        ]);

        $user = $request->user();

        // Normaliser l'email du médecin en minuscules
        if (!empty($data['doctor_email'])) {
            $data['doctor_email'] = strtolower(trim($data['doctor_email']));
        }

        // Enregistrer l'ancien email du médecin pour la synchronisation
        $existingProfile     = HealthProfile::where('user_id', $user->id)->first();
        $previousDoctorEmail = $existingProfile ? strtolower(trim((string) $existingProfile->doctor_email)) : null;

        // Séparer les traitements des autres données de profil
        $treatments = $data['treatments'] ?? [];
        unset($data['treatments']);

        $data['user_id'] = $user->id;

        // initial_weight ne se remplit qu'à la création — jamais modifié ensuite
        if (!$existingProfile) {
            $data['initial_weight'] = $data['current_weight'];
        }

        // Enregistrer ou mettre à jour le profil de santé
        $profile = HealthProfile::updateOrCreate(['user_id' => $user->id], $data);

        // Remplacer tous les traitements existants
        if (!empty($treatments)) {
            $profileHealthData = HealthData::firstOrCreate([
                'user_id' => $user->id,
                'date'    => Carbon::today()->toDateString(),
            ]);

            Treatment::whereHas('healthData', fn ($q) => $q->where('user_id', $user->id))->delete();

            foreach ($treatments as $treatment) {
                $startDate = $treatment['start_date'] ?? null;
                $endDate   = $treatment['end_date']   ?? null;

                // Inverser les dates si nécessaire
                if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }

                $type = trim($treatment['type'] ?? '');
                $name = trim($treatment['name'] ?? '');

                // Chercher ou créer le médicament
                $catalog = $type !== '' ? TreatmentCatalog::firstOrCreate([
                    'treatment_type' => $type,
                    'treatment_name' => $name,
                ]) : null;

                Treatment::create([
                    'health_data_id'       => $profileHealthData->id,
                    'treatment_catalog_id' => $catalog?->id,
                    'dose'                 => $treatment['dose']            ?? null,
                    'frequency'            => $treatment['frequency_unit']  ?? null,
                    'daily_doses'          => $treatment['frequency_count'] ?? null,
                    'start_date'           => $startDate,
                    'end_date'             => $endDate,
                ]);
            }
        }

        // Synchroniser les invitations du médecin
        $this->invitationService->sync($profile, $previousDoctorEmail, $user);

        // Charger et retourner les données du profil
        $profileData               = $profile->toArray();
        $profileData['treatments'] = $user->treatments()->with('treatmentCatalog')->get()
            ->map(fn($t) => $this->formatTreatment($t))
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Profil de santé enregistré avec succès.',
            'data'    => $profileData,
        ]);
    }

    // Récupérer le profil de santé de l'utilisateur
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

    // Formater un traitement pour l'API
    private function formatTreatment($treatment, bool $withFormattedDates = false): array
    {
        $startDate = $treatment->start_date?->toDateString();
        $endDate   = $treatment->end_date?->toDateString();

        $result = [
            'id'              => $treatment->id,
            'type'            => $treatment->treatmentCatalog?->treatment_type ?? '',
            'name'            => $treatment->treatmentCatalog?->treatment_name  ?? '',
            'dose'            => $treatment->dose,
            'frequency_unit'  => $treatment->frequency,
            'frequency_count' => $treatment->daily_doses,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
        ];

        // Ajouter les dates formatées uniquement si demandé
        if ($withFormattedDates) {
            $result['formatted_start_date'] = $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '';
            $result['formatted_end_date']   = $endDate   ? Carbon::parse($endDate)->format('d/m/Y')   : '';
        }

        return $result;
    }
}
