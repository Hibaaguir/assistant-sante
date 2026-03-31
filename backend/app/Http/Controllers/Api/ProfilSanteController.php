<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DoctorInvitationMail;
use App\Models\InvitationMedecin;
use App\Models\ProfilSante;
use App\Models\Utilisateur;

use App\Services\TraitementCatalogueService;
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
        private readonly TraitementCatalogueService $traitementCatalogueService,
    ) {}

    // Enregistrer ou mettre à jour le profil de santé
    public function store(Request $request)
    {
        // Normaliser le champ sexe en minuscules
        if (is_string($request->input('sexe'))) {
            $request->merge([
                'genre' => strtolower(trim($request->input('sexe'))),
            ]);
        }

        $validated = $request->validate([
            'genre' => ['required', Rule::in(['homme', 'femme'])],
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
                'email:rfc,dns',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $compte = Auth::user();
                    $currentEmail = strtolower((string) $compte?->email);
                    if (strtolower((string) $value) === $currentEmail) {
                        $fail("L'email du medecin doit etre different de votre email.");
                    }
                },
            ],
            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $compte = Auth::user();
        $utilisateur = $compte->utilisateur;
        $validated['id_utilisateur'] = $utilisateur->id;
        $validated['medecin_email'] = $validated['medecin_email'] !== null
            ? strtolower(trim($validated['medecin_email']))
            : null;

        $existingProfil = ProfilSante::query()
            ->where('id_utilisateur', $utilisateur->id)
            ->first();

        $previousDoctorEmail = $existingProfil?->medecin_email !== null
            ? strtolower(trim((string) $existingProfil->medecin_email))
            : null;

        try {
            $profil = DB::transaction(function () use ($validated, $utilisateur) {
                $userId = $utilisateur->id;
                $savedProfil = ProfilSante::updateOrCreate(
                    ['id_utilisateur' => $userId],
                    $validated
                );

                $this->traitementCatalogueService->saveFromTreatments(
                    is_array($validated['traitements'] ?? null) ? $validated['traitements'] : [],
                    (int) $userId,
                );

                

                return $savedProfil;
            });
        } catch (\Throwable $exception) {
            Log::error('Profil sante persistence failed during treatment catalog sync: '.$exception->getMessage(), [
                'id_utilisateur' => $utilisateur->id,
            ]);

            return response()->json([
                'message' => "Erreur lors de l'enregistrement du profil.",
            ], 500);
        }

        $this->synchroniserInvitationMedecin($profil, $previousDoctorEmail, $utilisateur);

        return response()->json([
            'message' => 'Profil sante enregistre avec succes.',
            'data' => $profil,
        ]);
    }

    // Afficher le profil de santé de l'utilisateur
    public function show()
    {
        $compte = Auth::user();
        $utilisateur = $compte->utilisateur;
        $profil = $utilisateur->profilSante;

        $userId = $utilisateur->id;
        if ($profil && is_array($profil->traitements)) {
            try {
                $this->traitementCatalogueService->saveFromTreatments(
                    $profil->traitements,
                    (int) $userId,
                );
            } catch (\Throwable $exception) {
                Log::warning('Profil sante show catalog sync skipped: '.$exception->getMessage(), [
                    'id_utilisateur' => $userId,
                ]);
            }
        }


        return response()->json([
            'data' => $profil,
            'utilisateur' => $utilisateur,
        ]);
    }

    // Synchroniser invitation médical pour ce profil
    private function synchroniserInvitationMedecin(ProfilSante $profil, ?string $previousDoctorEmail = null, $utilisateur = null): void
    {
        if (!$utilisateur) {
            $compte = Auth::user();
            $utilisateur = $compte->utilisateur;
        }
        
        // Vérifier que l'utilisateur existe
        if (! $utilisateur) {
            return;
        }

        $shouldInvite = (bool) $profil->consulte_medecin
            && (bool) $profil->medecin_peut_consulter
            && ! empty($profil->medecin_email);

        // Révoquer les invitations si les conditions ne sont pas réunies
        if (! $shouldInvite) {
            InvitationMedecin::query()
                ->where('id_patient_utilisateur', $utilisateur->id)
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
            InvitationMedecin::query()
                ->where('id_patient_utilisateur', $utilisateur->id)
                ->where('status', 'pending')
                ->where('doctor_email', '!=', $doctorEmail)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
        }

        // Search for doctor account by email in Compte table
        $doctorCompte = \App\Models\Compte::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->first();
        
        $doctorAccount = $doctorCompte?->utilisateur;
        
        // If not found as doctor, search for any account with that email
        if (!$doctorAccount) {
            $doctorAccount = \App\Models\Utilisateur::query()
                ->whereHas('compte', function ($query) use ($doctorEmail) {
                    $query->whereRaw('LOWER(email) = ?', [$doctorEmail]);
                })
                ->latest('id')
                ->first();
        }

        $existingAccount = $doctorAccount;

        // Vérifier que l'utilisateur ne s'invite pas lui-même comme médecin
        if ($existingAccount && $existingAccount->id === $utilisateur->id) {
            return;
        }

        $doctor = $doctorAccount;

        // Mettre à jour le rôle d'un utilisateur existant à 'médecin'
        if ($existingAccount && ! ($doctorAccount?->role === 'medecin') && $existingAccount->role !== 'medecin') {
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

                $doctorCompte = \App\Models\Compte::query()
                    ->whereRaw('LOWER(email) = ?', [$doctorEmail])
                    ->first();
                
                $doctor = $doctorCompte?->utilisateur;
                if ($doctor && $doctor->role !== 'medecin') {
                    $doctor = null;
                }
            }
        }

        $existing = InvitationMedecin::query()
            ->where('id_patient_utilisateur', $utilisateur->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        // Gérer la création ou la mise à jour de l'invitation
        if ($existing) {
            // Mettre à jour si l'invitation est déjà acceptée
            if ($existing->status === 'accepted') {
                $existing->update([
                    'id_medecin_utilisateur' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            } elseif ($doctorEmailChanged) {
                $existing->update([
                    'id_medecin_utilisateur' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                    'status' => 'pending',
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            } else {
                $existing->update([
                    'id_medecin_utilisateur' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            }
        } else {
            // Créer une nouvelle invitation si elle n'existe pas
            InvitationMedecin::query()->create([
                'id_patient_utilisateur' => $utilisateur->id,
                'id_medecin_utilisateur' => $doctor?->id,
                'doctor_email' => $doctorEmail,
                'status' => 'pending',
                'token' => Str::random(64),
            ]);
        }

        // Envoyer l'email d'invitation seulement si:
        // 1. L'email a changé OU c'est la première fois
        // 2. Le médecin n'existe pas (pas de compte doctor existant)
        $shouldSendEmail = $doctorEmailChanged && ! $doctor;

        if ($shouldSendEmail) {
            try {
                Mail::to($doctorEmail)->queue(new DoctorInvitationMail(
                    $utilisateur,
                    $doctorEmail,
                ));
                Log::info('Doctor invitation email queued successfully', [
                    'doctor_email' => $doctorEmail,
                    'patient_id' => $utilisateur->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Doctor invitation email failed', [
                    'doctor_email' => $doctorEmail,
                    'patient_id' => $utilisateur->id,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
            }
        }
    }
}
