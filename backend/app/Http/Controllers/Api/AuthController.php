<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\DoctorInvitationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly DoctorInvitationService $invitationService,
    ) {}

    // Enregistrer un nouveau compte patient
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|min:2|max:255',
            'email'         => 'required|email|unique:accounts,email',
            'password'      => 'required|confirmed|min:8',
            'date_of_birth' => 'required|date_format:Y-m-d|before_or_equal:today|before:' . Carbon::today()->subYears(18)->toDateString(),
            'profile_photo' => 'nullable|string',
        ]);

        // Normaliser l'email en minuscules
        $data['email'] = strtolower(trim($data['email']));

        $account = Account::create([
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'account_status' => 'active',
        ]);

        $user = User::create([
            'account_id'    => $account->id,
            'name'          => $data['name'],
            'date_of_birth' => $data['date_of_birth'],
            'profile_photo' => $data['profile_photo'] ?? null,
            'age'           => Carbon::parse($data['date_of_birth'])->age,
            'role'          => 'user',
        ]);

        // Créer un profil de santé vide
        HealthProfile::create(['user_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'                        => 'Compte créé avec succès',
            'token'                          => $token,
            'has_health_profile'             => false,
            'redirect_to'                    => '/health-profile',
            'has_pending_doctor_invitations' => false,
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'date_of_birth' => $user->date_of_birth,
                'profile_photo' => $user->profile_photo,
                'age'           => $user->age,
                'role'          => $user->role,
                'specialty'     => $user->specialty,
            ],
        ], 201);
    }

    // Enregistrer un nouveau compte médecin
    public function registerDoctor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'         => 'required|email|unique:accounts,email',
            'password'      => 'required|confirmed|min:8',
            'name'          => 'required|string|min:2|max:120',
            'specialty'     => 'required|string|min:2|max:120',
            'date_of_birth' => 'nullable|date_format:Y-m-d|before_or_equal:today|before:' . Carbon::today()->subYears(25)->toDateString(),
        ]);

        $email = strtolower(trim($data['email']));

        // Vérifier l'invitation en attente
        if (!$this->invitationService->existsForEmail($email)) {
            return response()->json([
                'message' => "L'inscription du médecin n'est pas autorisée sans une invitation en attente.",
                'errors'  => ['email' => ["Aucune invitation en attente trouvée pour cet e-mail."]],
            ], 403);
        }

        $account = Account::create([
            'email'          => $email,
            'password'       => Hash::make($data['password']),
            'account_status' => 'active',
        ]);

        $user = User::create([
            'account_id'    => $account->id,
            'name'          => trim($data['name']),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'age'           => isset($data['date_of_birth']) ? Carbon::parse($data['date_of_birth'])->age : null,
            'role'          => 'doctor',
            'specialty'     => trim($data['specialty']),
        ]);

        // Lier les invitations en attente
        $hasPendingInvitations = $this->invitationService->linkToDoctor($user);

        $token = $user->createToken('doctor_auth_token')->plainTextToken;

        return response()->json([
            'message'                        => 'Compte médecin créé avec succès',
            'token'                          => $token,
            'has_health_profile'             => false,
            'redirect_to'                    => '/main/dashboard',
            'has_pending_doctor_invitations' => $hasPendingInvitations,
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'date_of_birth' => $user->date_of_birth,
                'profile_photo' => $user->profile_photo,
                'age'           => $user->age,
                'role'          => $user->role,
                'specialty'     => $user->specialty,
            ],
        ], 201);
    }

    // Authentifier l'utilisateur
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email   = strtolower(trim($data['email']));
        $account = Account::where('email', $email)->first();

        // Messages d'erreur génériques pour la sécurité
        if (!$account || !Hash::check($data['password'], $account->password)) {
            return response()->json(['message' => 'E-mail ou mot de passe invalide.'], 401);
        }

        $user = $account->user;

        if (!$user) {
            return response()->json(['message' => 'Aucun utilisateur lié à ce compte.'], 404);
        }

        // Lier les nouvelles invitations du médecin
        if ($user->role === 'doctor') {
            $this->invitationService->linkToDoctor($user);
        }

        // D\u00e9terminer la redirection
        $hasProfile = $user->healthProfile?->isComplete() ?? false;

        if ($user->role === 'admin') {
            $redirectTo = '/main/dashboard';
        } elseif ($hasProfile) {
            $redirectTo = '/main';
        } else {
            $redirectTo = '/health-profile';
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'                        => 'Connexion réussie.',
            'token'                          => $token,
            'has_health_profile'             => $hasProfile,
            'redirect_to'                    => $redirectTo,
            'has_pending_doctor_invitations' => false,
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'date_of_birth' => $user->date_of_birth,
                'profile_photo' => $user->profile_photo,
                'age'           => $user->age,
                'role'          => $user->role,
                'specialty'     => $user->specialty,
            ],
        ]);
    }

    // Récupérer l'utilisateur courant
    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['user' => null, 'has_health_profile' => false], 401);
        }

        $hasProfile = $user->healthProfile?->isComplete() ?? false;

        return response()->json([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'date_of_birth' => $user->date_of_birth,
                'profile_photo' => $user->profile_photo,
                'age'           => $user->age,
                'role'          => $user->role,
                'specialty'     => $user->specialty,
                'account_id'    => $user->account_id,
                'email'         => $user->account?->email,
                'status'        => $user->account?->account_status,
            ],
            'has_health_profile' => $hasProfile,
            'redirect_to'        => $hasProfile ? '/main' : '/health-profile',
        ]);
    }

    // Déconnecter l'utilisateur en supprimant son token
    public function logout(Request $request): JsonResponse
    {
        // Supprimer le token actuel
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}