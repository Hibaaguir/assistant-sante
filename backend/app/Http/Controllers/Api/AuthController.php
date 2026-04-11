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

    // ─────────────────────────────────────────────────────────────────────────
    // Register a new patient account
    //
    // 1. Validate the form fields
    // 2. Create an Account (email + password)
    // 3. Create a User linked to that account
    // 4. Create an empty HealthProfile for the user
    // 5. Return a token so the user is logged in right away
    // ─────────────────────────────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|min:2|max:255',
            'email'         => 'required|email|unique:accounts,email',
            'password'      => 'required|confirmed|min:8',
            'date_of_birth' => 'required|date_format:Y-m-d|before_or_equal:today|before:' . Carbon::today()->subYears(18)->toDateString(),
            'profile_photo' => 'nullable|string',
        ]);

        // Save the email in lowercase so login always works
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

        // Every new patient starts with an empty health profile
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

    // ─────────────────────────────────────────────────────────────────────────
    // Register a new doctor account
    //
    // Doctors can only register if they already have a pending invitation.
    // 1. Validate the form
    // 2. Check that a pending invitation exists for this email
    // 3. Create Account + User (role = doctor)
    // 4. Link any pending invitations to this doctor
    // 5. Return a token
    // ─────────────────────────────────────────────────────────────────────────
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

        // Block registration if no invitation was sent to this email
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

        // Attach any invitations that were sent to this email before the doctor signed up
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

    // ─────────────────────────────────────────────────────────────────────────
    // Log in any user (patient, doctor, or admin)
    //
    // 1. Check email and password
    // 2. Decide where to redirect based on role and profile status
    // 3. Return a token
    // ─────────────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email   = strtolower(trim($data['email']));
        $account = Account::where('email', $email)->first();

        // Wrong email or wrong password → same generic message (security best practice)
        if (!$account || !Hash::check($data['password'], $account->password)) {
            return response()->json(['message' => 'E-mail ou mot de passe invalide.'], 401);
        }

        $user = $account->user;

        if (!$user) {
            return response()->json(['message' => 'Aucun utilisateur lié à ce compte.'], 404);
        }

        // When a doctor logs in, link any new invitations that arrived since last login
        if ($user->role === 'doctor') {
            $this->invitationService->linkToDoctor($user);
        }

        // Decide where to send the user after login
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

    // ─────────────────────────────────────────────────────────────────────────
    // Return the currently logged-in user
    // Used by the frontend on page load to check who is connected
    // ─────────────────────────────────────────────────────────────────────────
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
    // Log out — delete the current token so it can no longer be used
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout successful.']);
    }
}