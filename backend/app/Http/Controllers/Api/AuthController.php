<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationMedecin;
use App\Models\ProfilSante;
use App\Models\Utilisateur;
use App\Models\Compte;
use App\Services\DoctorInvitationLinker;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly DoctorInvitationLinker $doctorInvitationLinker,
    ) {}

    // Enregistrer un nouvel utilisateur
    public function register(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'name'          => ['required', 'string', 'min:2', 'max:255'],
                'email'         => ['required', 'email', 'max:255', 'unique:comptes,email'],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'date_naissance'=> ['required', 'date_format:Y-m-d', $this->regleDate()],
                'photo_profil'  => ['nullable', 'string'],
                'age'           => ['nullable', 'integer', 'min:0'],
            ], $this->messagesDeBase());

            // Création du compte
            $compte = Compte::create([
                'email'        => strtolower(trim($validated['email'])),
                'motdepasse'   => Hash::make($validated['password']),
                'statut_compte'=> 'actif',
            ]);

            // Création de l'utilisateur lié au compte
            $utilisateur = Utilisateur::create([
                'compte_id'      => $compte->id,
                'nom'            => $validated['name'],
                'date_naissance' => $validated['date_naissance'],
                'photo_profil'   => $validated['photo_profil'] ?? null,
                'age'            => $this->calculerAge($validated['date_naissance']),
                'role'           => 'usager',
            ]);

            // Création automatique d'un profil santé vide
            ProfilSante::create([
                'id_utilisateur' => $utilisateur->id,
            ]);

            return $this->reponseAuthentifiee($utilisateur, $utilisateur->createToken('auth_token')->plainTextToken, false, '/profil-sante', false, 201, 'Compte cree avec succes');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire.');
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Enregistrer un nouveau médecin
    public function registerDoctor(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'         => ['required', 'email', 'max:255', 'unique:comptes,email'],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'nom'           => ['required', 'string', 'min:2', 'max:120'],
                'specialite'    => ['required', 'string', 'min:2', 'max:120'],
                'date_naissance' => ['nullable', 'date_format:Y-m-d', $this->regleDate(25, 'Un medecin doit avoir au minimum 25 ans pour creer un compte medecin.')],
            ], array_merge($this->messagesDeBase(), [
                'specialite.required'      => 'La specialite est obligatoire.',
                'date_naissance.date_format' => 'La date doit etre au format YYYY-MM-DD.',
            ]));

            $email = strtolower(trim($validated['email']));

            // Vérifier que le médecin a une invitation en attente
            if (! $this->hasInvitationForEmail($email)) {
                return response()->json([
                    'message' => "Inscription medecin non autorisee sans invitation en attente.",
                    'errors'  => ['email' => ["Aucune invitation en attente n'a ete trouvee pour cet email."]],
                ], 403);
            }

            // Création du compte
            $compte = Compte::create([
                'email'        => $email,
                'motdepasse'   => Hash::make($validated['password']),
                'statut_compte'=> 'actif',
            ]);

            // Création de l'utilisateur médecin
            $utilisateur = Utilisateur::create([
                'compte_id'      => $compte->id,
                'nom'           => trim($validated['nom']),
                'date_naissance' => $validated['date_naissance'] ?? null,
                'age'            => $validated['date_naissance'] ? $this->calculerAge($validated['date_naissance']) : null,
                'role'          => 'medecin',
                'specialite'    => trim($validated['specialite']),
            ]);

            $hasPendingInvitations = $this->doctorInvitationLinker->linkForUser($utilisateur);

            return $this->reponseAuthentifiee($utilisateur, $utilisateur->createToken('doctor_auth_token')->plainTextToken, false, '/main/dashboard', $hasPendingInvitations, 201, 'Compte medecin cree avec succes');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire medecin.');
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Doctor registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte medecin'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Connecter un utilisateur
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $email = strtolower(trim($credentials['email']));
            $compte = Compte::where('email', $email)->first();
            if (! $compte || !Hash::check($credentials['password'], $compte->motdepasse)) {
                return response()->json(['message' => 'Email ou mot de passe invalide.'], Response::HTTP_UNAUTHORIZED);
            }

            $utilisateur = $compte->utilisateur;
            if (! $utilisateur) {
                return response()->json(['message' => 'Aucun utilisateur lié à ce compte.'], Response::HTTP_NOT_FOUND);
            }

            // Suppression des anciens tokens si besoin (selon votre logique de sécurité)
            // $compte->tokens()->delete();

            $profilSante = $utilisateur->profilSante;
            $hasProfil = $profilSante && $profilSante->isComplete();
            $redirectTo = $hasProfil ? '/main' : '/profil-sante';

            return $this->reponseAuthentifiee($utilisateur, $utilisateur->createToken('auth_token')->plainTextToken, $hasProfil, $redirectTo, false, 200, 'Connexion reussie.');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire.');
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la connexion.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Connecter un médecin (alias de login)
    public function loginDoctor(Request $request): JsonResponse
    {
        // Force the role to 'medecin' for doctor-specific login
        $request->merge(['role' => 'medecin']);
        return $this->login($request);
    }

    // Récupérer l'utilisateur connecté
    // Récupérer l'utilisateur connecté
    public function getCurrentUser(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur via le token (Auth::user() retourne Utilisateur selon config/auth.php)
        $utilisateur = $request->user();
        
        if (!$utilisateur) {
            return response()->json(['utilisateur' => null, 'has_profil_sante' => false], Response::HTTP_UNAUTHORIZED);
        }
        
        $compte = $utilisateur->compte;
        $profilSante = $utilisateur->profilSante;
        $hasProfil = $profilSante && $profilSante->isComplete();

        return response()->json([
            'utilisateur'      => [
                'id'             => $utilisateur->id,
                'compte_id'      => $utilisateur->compte_id,
                'nom'            => $utilisateur->nom,
                'date_naissance' => $utilisateur->date_naissance,
                'photo_profil'   => $utilisateur->photo_profil,
                'age'            => $utilisateur->age,
                'role'           => $utilisateur->role,
                'specialite'     => $utilisateur->specialite,
                'email'          => $compte?->email,
                'statut'         => $compte?->statut_compte,
            ],
            'has_profil_sante' => $hasProfil,
            'redirect_to'      => $hasProfil ? '/main' : '/profil-sante',
        ]);
    }

    // Déconnecter l'utilisateur
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Deconnexion reussie.']);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    // Formater la réponse d'authentification
    private function reponseAuthentifiee(Utilisateur $utilisateur, string $token, bool $hasProfil, string $redirectTo, bool $hasPendingInvitations, int $status, string $message): JsonResponse
    {
        return response()->json([
            'message'                        => $message,
            'token'                          => $token,
            'has_profil_sante'               => $hasProfil,
            'redirect_to'                    => $redirectTo,
            'utilisateur'                    => $this->donneesUtilisateur($utilisateur),
            'has_pending_doctor_invitations' => $hasPendingInvitations,
        ], $status);
    }

    // Retourner une erreur de validation formatée
    private function erreurValidation(ValidationException $e, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Récupérer les données publiques de l'utilisateur
    private function donneesUtilisateur(Utilisateur $utilisateur): array
    {
        return [
            'id'             => $utilisateur->id,
            'nom'            => $utilisateur->nom,
            'date_naissance' => $utilisateur->date_naissance,
            'photo_profil'   => $utilisateur->photo_profil,
            'age'            => $utilisateur->age,
            'role'           => $utilisateur->role,
            'specialite'     => $utilisateur->specialite,
        ];
    }

    // Vérifier si utilisateur a invitations médecin en attente
    private function hasPendingDoctorInvitations(Utilisateur $user): bool
    {
        return InvitationMedecin::where('id_medecin_utilisateur', $user->id)->where('status', 'pending')->exists();
    }

    // Vérifier si invitation en attente existe pour email
    private function hasInvitationForEmail(string $email): bool
    {
        return InvitationMedecin::whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])->where('status', 'pending')->exists();
    }

    // Gérer les erreurs de base de données lors de l'inscription
    private function gererExceptionRequeteInscription(QueryException $e): JsonResponse
    {
        $sqlState   = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        if ($sqlState === '23000' || $driverCode === 1062) {
            return response()->json([
                'message' => 'Cet email est deja utilise.',
                'errors'  => ['email' => ['Cet email est deja utilise.']],
            ], 409);
        }

        Log::error('Registration database error: ' . $e->getMessage());
        return response()->json(['message' => 'Erreur lors de la creation du compte'], 500);
    }

    // Trouver utilisateur pour connexion avec rôle
    private function findUserForLogin(string $email, string $password, string $role): ?Utilisateur
    {
        $compte = Compte::where('email', $email)->first();
        if (!$compte || !Hash::check($password, $compte->motdepasse)) {
            return null;
        }
        
        $utilisateur = $compte->utilisateur;
        return ($utilisateur && $utilisateur->role === $role) ? $utilisateur : null;
    }

    // Trouver utilisateur sans rôle spécifié
    private function findUserWithoutRole(string $email, string $password): ?Utilisateur
    {
        $compte = Compte::where('email', $email)->first();
        return ($compte && Hash::check($password, $compte->motdepasse)) ? $compte->utilisateur : null;
    }

    // Retourner les messages de validation de base
    private function messagesDeBase(): array
    {
        return [
            'nom.required'               => "Le nom d'utilisateur est obligatoire.",
            'email.required'             => "L'adresse email est obligatoire.",
            'email.unique'               => 'Cet email est deja utilise.',
            'date_naissance.required'    => 'La date de naissance est obligatoire.',
            'date_naissance.date_format' => 'Format de date invalide. Utilisez YYYY-MM-DD.',
            'password.required'          => 'Le mot de passe est obligatoire.',
            'password.confirmed'         => 'Les mots de passe ne correspondent pas.',
        ];
    }

    // Créer règle validation personnalisée pour date de naissance
    private function regleDate(int $ageMinimum = 18, ?string $messageAgeMinimum = null): \Closure
    {
        return function ($attribute, $value, $fail) use ($ageMinimum, $messageAgeMinimum) {
            try {
                $birthDate = Carbon::createFromFormat('Y-m-d', (string) $value);
            } catch (\Throwable) {
                $fail('Date de naissance invalide.');
                return;
            }

            // Vérifier que le format de la date est valide
            if (! $birthDate || $birthDate->format('Y-m-d') !== $value) {
                $fail('Date de naissance invalide.');
                return;
            }

            // Vérifier que la date de naissance n'est pas dans le futur
            if ($birthDate->isFuture()) {
                $fail('La date de naissance ne peut pas etre dans le futur.');
                return;
            }

            // Vérifier l'âge minimum
            if ($birthDate->diffInYears(now()) < $ageMinimum) {
                $fail($messageAgeMinimum ?? "Vous devez avoir au minimum {$ageMinimum} ans pour creer un compte.");
            }
        };
    }

    // Calculer l'âge à partir de la date de naissance
    private function calculerAge(string $dateNaissance): int
    {
        return Carbon::createFromFormat('Y-m-d', $dateNaissance)->diffInYears(now());
    }
}