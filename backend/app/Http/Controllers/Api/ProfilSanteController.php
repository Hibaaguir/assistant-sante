<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DoctorInvitationMail;
use App\Models\DoctorInvitation;
use App\Models\ProfilSante;
use App\Models\User;
use App\Services\AllergyCatalogService;
use App\Services\ChronicDiseaseCatalogService;
use App\Services\TreatmentCatalogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Gère le profil de santé de l'utilisateur
 * 
 * Responsabilités:
 * - Création et mise à jour du profil de santé complet
 * - Consultation du profil de santé
 * - Synchronisation automatique des invitations médicales
 * - Gestion des consentements médicaux
 */
class ProfilSanteController extends Controller
{
    public function __construct(
        private readonly AllergyCatalogService $allergyCatalogService,
        private readonly ChronicDiseaseCatalogService $chronicDiseaseCatalogService,
        private readonly TreatmentCatalogService $treatmentCatalogService,
    ) {}

    // Enregistrer ou mettre à jour le profil de santé
    public function store(Request $request)
    {
        // Normaliser le champ sexe en minuscules
        if (is_string($request->input('sexe'))) {
            $request->merge([
                'sexe' => strtolower(trim($request->input('sexe'))),
            ]);
        }

        $validated = $request->validate([
            'sexe' => ['required', Rule::in(['homme', 'femme'])],
            'taille' => ['required', 'numeric', 'min:30', 'max:250'],
            'poids' => ['required', 'numeric', 'min:1', 'max:300'],
            'groupe_sanguin' => ['required', 'string', 'max:5'],
            'objectifs' => ['nullable', 'array'],
            'objectifs.*' => ['string', 'max:120'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:100'],
            'maladies_chroniques' => ['nullable', 'array'],
            'maladies_chroniques.*' => ['string', 'max:120'],
            'traitements' => ['nullable', 'array'],
            'traitements.*.type' => ['required_with:traitements', 'string', 'max:120'],
            'traitements.*.name' => ['nullable', 'string', 'max:255'],
            'traitements.*.dose' => ['nullable', 'string', 'max:120'],
            'traitements.*.frequency_unit' => ['nullable', Rule::in(['jour', 'semaine', 'mois'])],
            'traitements.*.frequency_count' => ['nullable', 'integer', 'min:1'],
            'traitements.*.duration' => ['nullable', 'string', 'max:120'],
            'consulte_medecin' => ['required', 'boolean'],
            'medecin_peut_consulter' => ['required_if:consulte_medecin,1', 'boolean'],
            'medecin_email' => [
                'nullable',
                'email',
                'required_if:medecin_peut_consulter,1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $currentEmail = strtolower((string) Auth::user()?->email);
                    if (strtolower((string) $value) === $currentEmail) {
                        $fail("L'email du medecin doit etre different de votre email.");
                    }
                },
            ],
            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $validated['user_id'] = Auth::id();
        $validated['medecin_email'] = $validated['medecin_email'] !== null
            ? strtolower(trim($validated['medecin_email']))
            : null;

        $existingProfil = ProfilSante::query()
            ->where('user_id', Auth::id())
            ->first();

        $previousDoctorEmail = $existingProfil?->medecin_email !== null
            ? strtolower(trim((string) $existingProfil->medecin_email))
            : null;

        try {
            $profil = DB::transaction(function () use ($validated) {
                $savedProfil = ProfilSante::updateOrCreate(
                    ['user_id' => Auth::id()],
                    $validated
                );

                $this->treatmentCatalogService->saveFromTreatments(
                    is_array($validated['traitements'] ?? null) ? $validated['traitements'] : [],
                    (int) Auth::id(),
                );

                $this->allergyCatalogService->saveFromList(
                    is_array($validated['allergies'] ?? null) ? $validated['allergies'] : [],
                    (int) Auth::id(),
                );

                $this->chronicDiseaseCatalogService->saveFromList(
                    is_array($validated['maladies_chroniques'] ?? null) ? $validated['maladies_chroniques'] : [],
                    (int) Auth::id(),
                );

                return $savedProfil;
            });
        } catch (\Throwable $exception) {
            Log::error('Profil sante persistence failed during treatment catalog sync: '.$exception->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => "Erreur lors de l'enregistrement du profil.",
            ], 500);
        }

        $this->synchroniserInvitationMedecin($profil, $previousDoctorEmail);

        return response()->json([
            'message' => 'Profil sante enregistre avec succes.',
            'data' => $profil,
        ]);
    }

    // Afficher le profil de santé de l'utilisateur
    public function show()
    {
        $user = Auth::user();
        $profil = $user->profilSante;

        if ($profil && is_array($profil->traitements)) {
            try {
                $this->treatmentCatalogService->saveFromTreatments(
                    $profil->traitements,
                    (int) $user->id,
                );
            } catch (\Throwable $exception) {
                Log::warning('Profil sante show catalog sync skipped: '.$exception->getMessage(), [
                    'user_id' => $user->id,
                ]);
            }
        }

        if ($profil && is_array($profil->allergies)) {
            try {
                $this->allergyCatalogService->saveFromList(
                    $profil->allergies,
                    (int) $user->id,
                );
            } catch (\Throwable $exception) {
                Log::warning('Profil sante show allergy sync skipped: '.$exception->getMessage(), [
                    'user_id' => $user->id,
                ]);
            }
        }

        if ($profil && is_array($profil->maladies_chroniques)) {
            try {
                $this->chronicDiseaseCatalogService->saveFromList(
                    $profil->maladies_chroniques,
                    (int) $user->id,
                );
            } catch (\Throwable $exception) {
                Log::warning('Profil sante show chronic disease sync skipped: '.$exception->getMessage(), [
                    'user_id' => $user->id,
                ]);
            }
        }

        return response()->json([
            'data' => $profil,
            'user' => $user,
        ]);
    }

    // Synchroniser invitation médical pour ce profil
    private function synchroniserInvitationMedecin(ProfilSante $profil, ?string $previousDoctorEmail = null): void
    {
        $patient = Auth::user();
        // Vérifier que l'utilisateur est connecté
        if (! $patient) {
            return;
        }

        $shouldInvite = (bool) $profil->consulte_medecin
            && (bool) $profil->medecin_peut_consulter
            && ! empty($profil->medecin_email);

        // Révoquer les invitations si les conditions ne sont pas réunies
        if (! $shouldInvite) {
            DoctorInvitation::query()
                ->where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
            return;
        }

        $doctorEmail = strtolower(trim((string) $profil->medecin_email));
        $doctorEmailChanged = $doctorEmail !== ($previousDoctorEmail !== null ? strtolower(trim($previousDoctorEmail)) : null);

        // Révoquer les invitations si l'email du médecin a changé
        if ($doctorEmailChanged) {
            DoctorInvitation::query()
                ->where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->where('doctor_email', '!=', $doctorEmail)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
        }

        $doctorAccount = User::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->where('role', 'medecin')
            ->latest('id')
            ->first();

        $existingAccount = $doctorAccount ?: User::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->latest('id')
            ->first();

        // Vérifier que l'utilisateur ne s'invite pas lui-même comme médecin
        if ($existingAccount && $existingAccount->id === $patient->id) {
            return;
        }

        $doctor = $doctorAccount;

        // Mettre à jour le rôle d'un utilisateur existant à 'médecin'
        if ($existingAccount && ! $doctorAccount && $existingAccount->role !== 'medecin') {
            try {
                $existingAccount->update([
                    'role' => 'medecin',
                ]);
                $doctor = $existingAccount->fresh();
            } catch (QueryException $exception) {
                Log::warning('Doctor invitation role sync skipped: '.$exception->getMessage(), [
                    'doctor_email' => $doctorEmail,
                    'existing_account_id' => $existingAccount->id,
                ]);

                $doctor = User::query()
                    ->whereRaw('LOWER(email) = ?', [$doctorEmail])
                    ->where('role', 'medecin')
                    ->latest('id')
                    ->first();
            }
        }

        $existing = DoctorInvitation::query()
            ->where('patient_user_id', $patient->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        // Gérer la création ou la mise à jour de l'invitation
        if ($existing) {
            // Mettre à jour si l'invitation est déjà acceptée
            if ($existing->status === 'accepted') {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            } elseif ($doctorEmailChanged) {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                    'status' => 'pending',
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            } else {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            }
        } else {
            // Créer une nouvelle invitation si elle n'existe pas
            DoctorInvitation::query()->create([
                'patient_user_id' => $patient->id,
                'doctor_user_id' => $doctor?->id,
                'doctor_email' => $doctorEmail,
                'status' => 'pending',
                'token' => Str::random(64),
            ]);
        }

        // Envoyer l'email d'invitation seulement si l'email a changé et le médecin n'existe pas
        if (! $doctorEmailChanged || $doctor) {
            return;
        }

        try {
            Mail::to($doctorEmail)->send(new DoctorInvitationMail(
                $patient,
                $doctorEmail,
            ));
        } catch (\Throwable $e) {
            Log::warning('Doctor invitation email failed: '.$e->getMessage());
        }
    }
}
