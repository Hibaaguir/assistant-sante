<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DoctorInvitationMail;
use App\Models\InvitationMedecin;
use App\Models\ProfilSante;
use App\Models\Utilisateur;

use App\Services\TraitementCatalogueService;
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
            'traitements.*.date_debut' => ['nullable', 'date_format:Y-m-d'],
            'traitements.*.date_fin' => ['nullable', 'date_format:Y-m-d'],
            'consulte_medecin' => ['required', 'boolean'],
            'medecin_peut_consulter' => ['required_if:consulte_medecin,1', 'boolean'],
            'medecin_email' => [
                'nullable',
                'email',
                'required_if:medecin_peut_consulter,1',
                'email:rfc,dns',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $utilisateur = Auth::user();
                    $currentEmail = strtolower((string) $utilisateur?->compte?->email);
                    if (strtolower((string) $value) === $currentEmail) {
                        $fail("L'email du medecin doit etre different de votre email.");
                    }
                },
            ],
            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $utilisateur = Auth::user();
        
        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_UNAUTHORIZED);
        }
        
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

        // Extraire les traitements avant de faire updateOrCreate
        $traitementsData = $validated['traitements'] ?? [];
        unset($validated['traitements']);

        try {
            $profil = DB::transaction(function () use ($validated, $utilisateur, $traitementsData) {
                $userId = $utilisateur->id;
                $savedProfil = ProfilSante::updateOrCreate(
                    ['id_utilisateur' => $userId],
                    $validated
                );

                // Sauvegarder les traitements dans la table traitements
                if (is_array($traitementsData) && count($traitementsData) > 0) {
                    // Supprimer les anciens traitements
                    $savedProfil->traitements()->delete();
                    
                    // Ajouter les nouveaux traitements
                    foreach ($traitementsData as $traitement) {
                        if (! is_array($traitement)) {
                            continue;
                        }

                        // D'abord, créer/trouver l'entrée dans le catalogue
                        $catalogueEntry = null;
                        $type = $traitement['type'] ?? null;
                        $name = $traitement['name'] ?? null;
                        
                        if (!empty($type)) {
                            $normalizedType = mb_strtolower(trim($type), 'UTF-8');
                            $normalizedName = mb_strtolower(trim($name ?? ''), 'UTF-8');
                            
                            $catalogueEntry = \App\Models\CatalogueTraitement::query()
                                ->whereRaw('LOWER(type) = ?', [$normalizedType])
                                ->whereRaw('LOWER(nom) = ?', [$normalizedName])
                                ->first();
                            
                            if (!$catalogueEntry) {
                                $catalogueEntry = \App\Models\CatalogueTraitement::query()->create([
                                    'type' => $type,
                                    'nom' => $name ?? '',
                                    'created_by_id_utilisateur' => $userId,
                                ]);
                            }
                        }
                        
                        // Créer le traitement avec la FK vers le catalogue
                        $savedProfil->traitements()->create([
                            'catalogue_traitement_id' => $catalogueEntry?->id,
                            'dose' => $traitement['dose'] ?? null,
                            'frequence' => $traitement['frequency_unit'] ?? null,
                            'nombre_prises' => $traitement['frequency_count'] ?? null,
                            'date_debut' => !empty($traitement['date_debut']) ? $traitement['date_debut'] : null,
                            'date_fin' => !empty($traitement['date_fin']) ? $traitement['date_fin'] : null,
                        ]);
                    }
                }

                return $savedProfil;
            });
        } catch (\Throwable $exception) {
            Log::error('Profil sante persistence failed: '.$exception->getMessage(), [
                'id_utilisateur' => $utilisateur->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => "Erreur lors de l'enregistrement du profil.",
            ], 500);
        }

        $this->synchroniserInvitationMedecin($profil, $previousDoctorEmail, $utilisateur);

        // Recharger le profil avec les traitements pour la réponse
        $profil->load('traitements');

        return response()->json([
            'message' => 'Profil sante enregistre avec succes.',
            'data' => $profil,
        ]);
    }

    // Afficher le profil de santé de l'utilisateur
    public function show()
    {
        $utilisateur = Auth::user();
        
        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_UNAUTHORIZED);
        }
        
        $profil = $utilisateur->profilSante()->with('traitements')->first();

        return response()->json([
            'data' => $profil,
            'utilisateur' => $utilisateur,
        ]);
    }

    // Synchroniser invitation médical pour ce profil
    private function synchroniserInvitationMedecin(ProfilSante $profil, ?string $previousDoctorEmail = null, $utilisateur = null): void
    {
        if (!$utilisateur) {
            $utilisateur = Auth::user();
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
                ->where('id_utilisateur_patient', $utilisateur->id)
                ->where('statut', 'en_attente')
                ->update([
                    'statut' => 'revoque',
                    'revoked_at' => now(),
                ]);
            return;
        }

        $doctorEmail = strtolower(trim((string) $profil->medecin_email));
        $doctorEmailChanged = $doctorEmail !== ($previousDoctorEmail !== null ? strtolower(trim($previousDoctorEmail)) : null);

        // Révoquer les invitations si l'email du médecin a changé
        if ($doctorEmailChanged) {
            InvitationMedecin::query()
                ->where('id_utilisateur_patient', $utilisateur->id)
                ->where('statut', 'en_attente')
                ->where('email_medecin', '!=', $doctorEmail)
                ->update([
                    'statut' => 'revoque',
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
                    'email_medecin' => $doctorEmail,
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
            ->where('id_utilisateur_patient', $utilisateur->id)
            ->where('email_medecin', $doctorEmail)
            ->first();

        // Gérer la création ou la mise à jour de l'invitation
        if ($existing) {
            // Mettre à jour si l'invitation est déjà acceptée
            if ($existing->statut === 'accepte') {
                $existing->update([
                    'id_utilisateur_medecin' => $doctor?->id,
                    'email_medecin' => $doctorEmail,
                ]);
            } elseif ($doctorEmailChanged) {
                $existing->update([
                    'id_utilisateur_medecin' => $doctor?->id,
                    'email_medecin' => $doctorEmail,
                    'statut' => 'en_attente',
                    'jeton' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            } else {
                $existing->update([
                    'id_utilisateur_medecin' => $doctor?->id,
                    'email_medecin' => $doctorEmail,
                ]);
            }
        } else {
            // Créer une nouvelle invitation si elle n'existe pas
            InvitationMedecin::query()->create([
                'id_utilisateur_patient' => $utilisateur->id,
                'id_utilisateur_medecin' => $doctor?->id,
                'email_medecin' => $doctorEmail,
                'statut' => 'en_attente',
                'jeton' => Str::random(64),
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
                    'email_medecin' => $doctorEmail,
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
