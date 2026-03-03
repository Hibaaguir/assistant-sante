<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:3', 'max:50'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'date_of_birth' => [
                    'required',
                    'date',
                    'before:today',
                    function ($attribute, $value, $fail) {
                        $birthDate = Carbon::parse($value);
                        $age = $birthDate->diffInYears(Carbon::now());

                        if ($age < 18) {
                            $fail('Vous devez avoir au minimum 18 ans pour creer un compte.');
                        }
                    },
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->numbers(),
                ],
            ], [
                'name.required' => "Le nom d'utilisateur est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.unique' => 'Cet email est déjà utilisé.',
                'date_of_birth.required' => 'La date de naissance est obligatoire.',
                'password.required' => 'Le mot de passe est obligatoire.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'date_of_birth' => $validated['date_of_birth'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Compte cree avec succes',
                'token' => $token,
                'has_profil_sante' => false,
                'redirect_to' => '/profil-sante',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'date_of_birth' => $user->date_of_birth,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            $driverCode = $e->errorInfo[1] ?? null;

            // Duplicate key error (MySQL/MariaDB: 1062, SQLSTATE: 23000)
            if ($sqlState === '23000' || $driverCode === 1062) {
                return response()->json([
                    'message' => 'Cet email est déjà utilisé.',
                    'errors' => [
                        'email' => ['Cet email est déjà utilisé.'],
                    ],
                ], 409);
            }

            Log::error('Registration database error: '.$e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la creation du compte',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Registration error: '.$e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la creation du compte',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (! Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Email ou mot de passe invalide.',
                ], 401);
            }

            /** @var User $user */
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            $hasProfil = $user->profilSante()->exists();

            return response()->json([
                'message' => 'Connexion reussie.',
                'token' => $token,
                'has_profil_sante' => $hasProfil,
                'redirect_to' => $hasProfil ? '/main' : '/profil-sante',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'date_of_birth' => $user->date_of_birth,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Login error: '.$e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la connexion.',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $hasProfil = $user->profilSante()->exists();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'date_of_birth' => $user->date_of_birth,
            ],
            'has_profil_sante' => $hasProfil,
            'redirect_to' => $hasProfil ? '/main' : '/profil-sante',
        ]);
    }
}
