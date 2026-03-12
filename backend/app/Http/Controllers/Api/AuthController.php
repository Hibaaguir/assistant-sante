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
        private readonly DoctorInvitationLinker $lieurInvitationMedecin,
    ) {
    }

    public function inscrire(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:3', 'max:50'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', 'user')),
                ],
                'date_of_birth' => ['required', 'date_format:Y-m-d', $this->regleDate()],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ], $this->messagesDeBase());

            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'date_of_birth' => $validated['date_of_birth'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            return $this->reponseAuthentifiee(
                $user,
                $user->createToken('auth_token')->plainTextToken,
                false,
                '/profil-sante',
                false,
                201,
                'Compte cree avec succes'
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Registration error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte'], 500);
        }
    }

    public function inscrireMedecin(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', 'medecin')),
                ],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'specialite' => ['required', 'string', 'min:2', 'max:120'],
            ], array_merge($this->messagesDeBase(), [
                'specialite.required' => 'La specialite est obligatoire.',
            ]));

            $user = User::create([
                'name' => trim($request->input('name') ?: 'Medecin'),
                'email' => strtolower(trim($validated['email'])),
                'date_of_birth' => null,
                'password' => Hash::make($validated['password']),
                'role' => 'medecin',
                'specialite' => trim($validated['specialite']),
            ]);

            $hasPendingDoctorInvitations = $this->lieurInvitationMedecin->lierPourUtilisateur($user);
            return $this->reponseAuthentifiee(
                $user,
                $user->createToken('doctor_auth_token')->plainTextToken,
                false,
                '/main/dashboard',
                $hasPendingDoctorInvitations,
                201,
                'Compte medecin cree avec succes'
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire medecin.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return $this->gererExceptionRequeteInscription($e);
        } catch (\Throwable $e) {
            Log::error('Doctor registration error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de la creation du compte medecin'], 500);
        }
    }

    public function connecter(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'role' => ['nullable', Rule::in(['user', 'medecin', 'admin'])],
            ]);

            $email = strtolower(trim($credentials['email']));
            $role = $credentials['role'] ?? null;
            $user = $role !== null
                ? $this->trouverUtilisateurPourConnexion($email, $credentials['password'], $role)
                : $this->trouverUtilisateurSansRole($email, $credentials['password']);

            if (! $user) {
                return response()->json(['message' => 'Email ou mot de passe invalide.'], 401);
            }

            $user->tokens()->delete();
            $isDoctor = $user->role === 'medecin';
            $linkedPendingInvitations = $isDoctor ? $this->lieurInvitationMedecin->lierPourUtilisateur($user) : false;
            $hasProfil = $user->profilSante()->exists();
            $hasPendingDoctorInvitations = $isDoctor && ($linkedPendingInvitations || $this->aInvitationsMedecinEnAttente($user));

            return $this->reponseAuthentifiee(
                $user,
                $user->createToken($isDoctor ? 'doctor_auth_token' : 'auth_token')->plainTextToken,
                $hasProfil,
                $isDoctor ? '/choix-espace' : ($hasProfil ? '/main' : '/profil-sante'),
                $hasPendingDoctorInvitations,
                200,
                'Connexion reussie.'
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Login error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de la connexion.'], 500);
        }
    }

    public function connecterMedecin(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => "L'adresse email est obligatoire.",
                'password.required' => 'Le mot de passe est obligatoire.',
            ]);

            $credentials = [
                'email' => strtolower(trim($validated['email'])),
                'password' => $validated['password'],
            ];

            $user = $this->trouverUtilisateurPourConnexion($credentials['email'], $credentials['password'], 'medecin');

            if (! $user) {
                return response()->json(['message' => 'Email ou mot de passe invalide.'], 401);
            }

            $user->tokens()->delete();
            $linkedPendingInvitations = $this->lieurInvitationMedecin->lierPourUtilisateur($user);
            $hasPendingDoctorInvitations = $linkedPendingInvitations || $this->aInvitationsMedecinEnAttente($user);

            $hasProfil = $user->profilSante()->exists();

            return $this->reponseAuthentifiee(
                $user,
                $user->createToken('doctor_auth_token')->plainTextToken,
                $hasProfil,
                '/main/dashboard',
                $hasPendingDoctorInvitations,
                200,
                'Connexion medecin reussie.'
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire medecin.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Doctor login error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de la connexion medecin.'], 500);
        }
    }

    public function utilisateurConnecte(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $hasProfil = $user->profilSante()->exists();

        if ($user->role === 'medecin') {
            $this->lieurInvitationMedecin->lierPourUtilisateur($user);
        }

        return response()->json([
            'user' => $this->donneesUtilisateur($user),
            'has_profil_sante' => $hasProfil,
            'has_pending_doctor_invitations' => $user->role === 'medecin' ? $this->aInvitationsMedecinEnAttente($user) : false,
            'redirect_to' => $user->role === 'medecin' ? '/main/dashboard' : ($hasProfil ? '/main' : '/profil-sante'),
        ]);
    }

    public function deconnexion(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    private function reponseAuthentifiee(User $user, string $token, bool $hasProfil, string $redirectTo, bool $hasPendingDoctorInvitations, int $status, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'token' => $token,
            'has_profil_sante' => $hasProfil,
            'redirect_to' => $redirectTo,
            'user' => $this->donneesUtilisateur($user),
            'has_pending_doctor_invitations' => $hasPendingDoctorInvitations,
        ], $status);
    }

    private function donneesUtilisateur(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'role' => $user->role,
            'specialite' => $user->specialite,
        ];
    }

    private function aInvitationsMedecinEnAttente(User $user): bool
    {
        return DoctorInvitation::query()
            ->where('doctor_user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    private function gererExceptionRequeteInscription(QueryException $e): JsonResponse
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        if ($sqlState === '23000' || $driverCode === 1062) {
            return response()->json([
                'message' => 'Cet email est deja utilise.',
                'errors' => [
                    'email' => ['Cet email est deja utilise.'],
                ],
            ], 409);
        }

        Log::error('Registration database error: '.$e->getMessage());
        return response()->json(['message' => 'Erreur lors de la creation du compte'], 500);
    }

    private function trouverUtilisateurPourConnexion(string $email, string $password, string $role): ?User
    {
        $user = User::query()
            ->where('email', $email)
            ->where('role', $role)
            ->latest('id')
            ->first();

        return $user && Hash::check($password, $user->password) ? $user : null;
    }

    private function trouverUtilisateurSansRole(string $email, string $password): ?User
    {
        foreach (['medecin', 'user', 'admin'] as $role) {
            $user = $this->trouverUtilisateurPourConnexion($email, $password, $role);

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    private function messagesDeBase(): array
    {
        return [
            'name.required' => "Le nom d'utilisateur est obligatoire.",
            'email.required' => "L'adresse email est obligatoire.",
            'email.unique' => 'Cet email est deja utilise pour ce role.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.date_format' => 'Format de date invalide. Utilisez YYYY-MM-DD.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    private function regleDate(): \Closure
    {
        return function ($attribute, $value, $fail) {
            try {
                $birthDate = Carbon::createFromFormat('Y-m-d', (string) $value);
            } catch (\Throwable) {
                $fail('Date de naissance invalide.');
                return;
            }

            if (! $birthDate || $birthDate->format('Y-m-d') !== $value) {
                $fail('Date de naissance invalide.');
                return;
            }

            if ($birthDate->isFuture()) {
                $fail('La date de naissance ne peut pas etre dans le futur.');
                return;
            }

            $age = $birthDate->diffInYears(now());

            if ($age < 18) {
                $fail('Vous devez avoir au minimum 18 ans pour creer un compte.');
            }
        };
    }
}
