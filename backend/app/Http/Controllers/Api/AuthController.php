<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Models\User;
use App\Services\DoctorInvitationLinker;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
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
                'name'          => ['required', 'string', 'min:3', 'max:50'],
                'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($q) => $q->where('role', 'user'))],
                'date_of_birth' => ['required', 'date_format:Y-m-d', $this->regleDate()],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ], $this->messagesDeBase());

            $user = User::create([
                'name'          => $validated['name'],
                'email'         => strtolower(trim($validated['email'])),
                'date_of_birth' => $validated['date_of_birth'],
                'password'      => Hash::make($validated['password']),
                'role'          => 'user',
            ]);

            return $this->reponseAuthentifiee($user, $user->createToken('auth_token')->plainTextToken, false, '/profil-sante', false, 201, 'Compte cree avec succes');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire.');
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte'], 500);
        }
    }

    // Enregistrer un nouveau médecin
    public function registerDoctor(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($q) => $q->where('role', 'medecin'))],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'specialite'    => ['required', 'string', 'min:2', 'max:120'],
                'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            ], array_merge($this->messagesDeBase(), [
                'specialite.required'      => 'La specialite est obligatoire.',
                'date_of_birth.date_format' => 'La date doit etre au format YYYY-MM-DD.',
            ]));

            $email = strtolower(trim($validated['email']));

            // Vérifier que le médecin a une invitation en attente
            if (! $this->hasInvitationForEmail($email)) {
                return response()->json([
                    'message' => "Inscription medecin non autorisee sans invitation en attente.",
                    'errors'  => ['email' => ["Aucune invitation en attente n'a ete trouvee pour cet email."]],
                ], 403);
            }

            $user = User::create([
                'name'          => trim($request->input('name') ?: 'Medecin'),
                'email'         => $email,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'password'      => Hash::make($validated['password']),
                'role'          => 'medecin',
                'specialite'    => trim($validated['specialite']),
            ]);

            $hasPendingInvitations = $this->doctorInvitationLinker->linkForUser($user);

            return $this->reponseAuthentifiee($user, $user->createToken('doctor_auth_token')->plainTextToken, false, '/main/dashboard', $hasPendingInvitations, 201, 'Compte medecin cree avec succes');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire medecin.');
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Doctor registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte medecin'], 500);
        }
    }

    // Connecter un utilisateur
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
                'role'     => ['nullable', Rule::in(['user', 'medecin', 'admin', 'administrateur'])],
            ]);

            $email = strtolower(trim($credentials['email']));
            $role  = $credentials['role'] ?? null;

            $user = $role !== null
                ? $this->findUserForLogin($email, $credentials['password'], $role)
                : $this->findUserWithoutRole($email, $credentials['password']);

            // Vérifier que l'utilisateur existe et le mot de passe est correct
            if (! $user) {
                return response()->json(['message' => 'Email ou mot de passe invalide.'], 401);
            }

            $user->tokens()->delete();

            $isDoctor    = $user->role === 'medecin';
            $isAdmin     = in_array($user->role, ['admin', 'administrateur'], true);
            $hasProfil   = $user->profilSante()->exists();
            // Link pending doctor invitations if user is a doctor
            $hasPending  = $isDoctor && ($this->doctorInvitationLinker->linkForUser($user) || $this->hasPendingDoctorInvitations($user));
            $redirectTo  = $isAdmin || $isDoctor ? '/main/dashboard' : ($hasProfil ? '/main' : '/profil-sante');
            $tokenName   = $isDoctor ? 'doctor_auth_token' : 'auth_token';

            return $this->reponseAuthentifiee($user, $user->createToken($tokenName)->plainTextToken, $hasProfil, $redirectTo, $hasPending, 200, 'Connexion reussie.');

        } catch (ValidationException $e) {
            return $this->erreurValidation($e, 'Veuillez corriger les erreurs du formulaire.');
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la connexion.'], 500);
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
    public function getCurrentUser(Request $request): JsonResponse
    {
        /** @var User $user */
        $user      = $request->user();
        $isDoctor  = $user->role === 'medecin';
        $isAdmin   = in_array($user->role, ['admin', 'administrateur'], true);
        $hasProfil = $user->profilSante()->exists();

        // Link pending doctor invitations if user is a doctor
        if ($isDoctor) {
            $this->doctorInvitationLinker->linkForUser($user);
        }

        return response()->json([
            'user'                          => $this->donneesUtilisateur($user),
            'has_profil_sante'              => $hasProfil,
            'has_pending_doctor_invitations' => $isDoctor && $this->hasPendingDoctorInvitations($user),
            'redirect_to'                   => $isAdmin || $isDoctor ? '/main/dashboard' : ($hasProfil ? '/main' : '/profil-sante'),
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
    private function reponseAuthentifiee(User $user, string $token, bool $hasProfil, string $redirectTo, bool $hasPendingInvitations, int $status, string $message): JsonResponse
    {
        return response()->json([
            'message'                        => $message,
            'token'                          => $token,
            'has_profil_sante'               => $hasProfil,
            'redirect_to'                    => $redirectTo,
            'user'                           => $this->donneesUtilisateur($user),
            'has_pending_doctor_invitations' => $hasPendingInvitations,
        ], $status);
    }

    // Retourner une erreur de validation formatée
    private function erreurValidation(ValidationException $e, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => $e->errors()], 422);
    }

    // Récupérer les données publiques de l'utilisateur
    private function donneesUtilisateur(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'role'          => $user->role,
            'specialite'    => $user->specialite,
            'profile_photo' => $user->profile_photo,
        ];
    }

    // Vérifier si utilisateur a invitations médecin en attente
    private function hasPendingDoctorInvitations(User $user): bool
    {
        return DoctorInvitation::where('doctor_user_id', $user->id)->where('status', 'pending')->exists();
    }

    // Vérifier si invitation en attente existe pour email
    private function hasInvitationForEmail(string $email): bool
    {
        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])->where('status', 'pending')->exists();
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
    private function findUserForLogin(string $email, string $password, string $role): ?User
    {
        $user = User::where('email', $email)->where('role', $role)->latest('id')->first();
        return $user && Hash::check($password, $user->password) ? $user : null;
    }

    // Trouver utilisateur sans rôle spécifié
    private function findUserWithoutRole(string $email, string $password): ?User
    {
        foreach (['admin', 'administrateur', 'medecin', 'user'] as $role) {
            if ($user = $this->findUserForLogin($email, $password, $role)) {
                return $user;
            }
        }
        return null;
    }

    // Retourner les messages de validation de base
    private function messagesDeBase(): array
    {
        return [
            'name.required'          => "Le nom d'utilisateur est obligatoire.",
            'email.required'         => "L'adresse email est obligatoire.",
            'email.unique'           => 'Cet email est deja utilise pour ce role.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.date_format' => 'Format de date invalide. Utilisez YYYY-MM-DD.',
            'password.required'      => 'Le mot de passe est obligatoire.',
        ];
    }

    // Créer règle validation personnalisée pour date de naissance
    private function regleDate(): \Closure
    {
        return function ($attribute, $value, $fail) {
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

            // Vérifier l'âge minimum (18 ans)
            if ($birthDate->diffInYears(now()) < 18) {
                $fail('Vous devez avoir au minimum 18 ans pour creer un compte.');
            }
        };
    }
}