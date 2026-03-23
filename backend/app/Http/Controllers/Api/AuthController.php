<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function connecter(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'role' => ['nullable', Rule::in(['user', 'admin', 'administrateur'])],
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
            $isAdmin = in_array($user->role, ['admin', 'administrateur'], true);
            $hasProfil = $user->profilSante()->exists();

            return $this->reponseAuthentifiee(
                $user,
                $user->createToken('auth_token')->plainTextToken,
                $hasProfil,
                $isAdmin ? '/main/dashboard' : ($hasProfil ? '/main' : '/profil-sante'),
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

    public function utilisateurConnecte(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $hasProfil = $user->profilSante()->exists();
        $isAdmin = in_array($user->role, ['admin', 'administrateur'], true);

        return response()->json([
            'user' => $this->donneesUtilisateur($user),
            'has_profil_sante' => $hasProfil,
            'redirect_to' => $isAdmin ? '/main/dashboard' : ($hasProfil ? '/main' : '/profil-sante'),
        ]);
    }

    public function deconnexion(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    private function reponseAuthentifiee(User $user, string $token, bool $hasProfil, string $redirectTo, int $status, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'token' => $token,
            'has_profil_sante' => $hasProfil,
            'redirect_to' => $redirectTo,
            'user' => $this->donneesUtilisateur($user),
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
        foreach (['admin', 'administrateur', 'user'] as $role) {
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
