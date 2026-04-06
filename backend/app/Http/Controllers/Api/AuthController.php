<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Models\HealthProfile;
use App\Models\User;
use App\Models\Account;
use App\Services\DoctorInvitationLinker;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly DoctorInvitationLinker $doctorInvitationLinker,
    ) {}

    // Register a new user
    public function register(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'name'          => ['required', 'string', 'min:2', 'max:255'],
                'email'         => ['required', 'email', 'max:255', 'unique:accounts,email'],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'date_of_birth'=> ['required', 'date_format:Y-m-d', $this->birthDateRule()],
                'profile_photo'  => ['nullable', 'string'],
                'age'           => ['nullable', 'integer', 'min:0'],
            ], $this->baseValidationMessages());

            // Create the account
            $account = Account::create([
                'email'        => strtolower(trim($validated['email'])),
                'password'   => Hash::make($validated['password']),
                'account_status'=> 'active',
            ]);

            // Create the user linked to the account
            $user = User::create([
                'account_id'      => $account->id,
                'name'            => $validated['name'],
                'date_of_birth' => $validated['date_of_birth'],
                'profile_photo'   => $validated['profile_photo'] ?? null,
                'age'            => $this->calculateAge($validated['date_of_birth']),
                'role'           => 'user',
            ]);

            // Automatically create an empty health profile
            HealthProfile::create([
                'user_id' => $user->id,
            ]);

            return $this->authenticatedResponse($user, $user->createToken('auth_token')->plainTextToken, false, '/health-profile', false, 201, 'Compte créé avec succès');

        } catch (ValidationException $e) {
            return $this->validationError($e, 'Please correct the form errors.');
        } catch (QueryException $e) {
            return $this->handleRegistrationQueryException($e);
        } catch (\Throwable $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du compte'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Register a new doctor
    public function registerDoctor(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'         => ['required', 'email', 'max:255', 'unique:accounts,email'],
                'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'name'           => ['required', 'string', 'min:2', 'max:120'],
                'specialty'    => ['required', 'string', 'min:2', 'max:120'],
                'date_of_birth' => ['nullable', 'date_format:Y-m-d', $this->birthDateRule(25, 'A doctor must be at least 25 years old to create an account.')],
            ], array_merge($this->baseValidationMessages(), [
                'specialty.required'      => 'La spécialité est obligatoire.',
                'date_of_birth.date_format' => 'Le format de la date doit être AAAA-MM-JJ.',
            ]));

            $email = strtolower(trim($validated['email']));

            // Check that the doctor has a pending invitation
            if (! $this->hasInvitationForEmail($email)) {
                return response()->json([
                    'message' => "L'inscription du médecin n'est pas autorisée sans une invitation en attente.",
                    'errors'  => ['email' => ["Aucune invitation en attente trouvée pour cet e-mail."]],
                ], 403);
            }

            // Create the account
            $account = Account::create([
                'email'        => $email,
                'password'   => Hash::make($validated['password']),
                'account_status'=> 'active',
            ]);

            // Create the doctor user
            $user = User::create([
                'account_id'      => $account->id,
                'name'           => trim($validated['name']),
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'age'            => $validated['date_of_birth'] ? $this->calculateAge($validated['date_of_birth']) : null,
                'role'          => 'doctor',
                'specialty'    => trim($validated['specialty']),
            ]);

            $hasPendingInvitations = $this->doctorInvitationLinker->linkForUser($user);

            return $this->authenticatedResponse($user, $user->createToken('doctor_auth_token')->plainTextToken, false, '/main/dashboard', $hasPendingInvitations, 201, 'Compte médecin créé avec succès');

        } catch (ValidationException $e) {
            return $this->validationError($e, 'Veuillez corriger les erreurs du formulaire médecin.');
        } catch (QueryException $e) {
            return $this->handleRegistrationQueryException($e);
        } catch (\Throwable $e) {
            Log::error('Doctor registration error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du compte médecin'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Log in a user
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $email = strtolower(trim($credentials['email']));
            $account = Account::where('email', $email)->first();
            if (! $account || !Hash::check($credentials['password'], $account->password)) {
                return response()->json(['message' => 'E-mail ou mot de passe invalide.'], Response::HTTP_UNAUTHORIZED);
            }

            $user = $account->user;
            if (! $user) {
                return response()->json(['message' => 'Aucun utilisateur lié à ce compte.'], Response::HTTP_NOT_FOUND);
            }

            // Delete old tokens if needed (according to your security logic)
            // $account->tokens()->delete();

            // For doctors: link any pending invitations that match their email
            if ($user->role === 'doctor') {
                $this->doctorInvitationLinker->linkForUser($user);
            }

            $healthProfile = $user->healthProfile;
            $hasProfile = $healthProfile && $healthProfile->isComplete();

            if ($user->role === 'admin') {
                $redirectTo = '/main/dashboard';
            } else {
                $redirectTo = $hasProfile ? '/main' : '/health-profile';
            }

            return $this->authenticatedResponse($user, $user->createToken('auth_token')->plainTextToken, $hasProfile, $redirectTo, false, 200, 'Connexion réussie.');

        } catch (ValidationException $e) {
            return $this->validationError($e, 'Veuillez corriger les erreurs du formulaire.');
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur de connexion.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Log in a doctor (alias for login)
    public function loginDoctor(Request $request): JsonResponse
    {
        // Force the role to 'doctor' for doctor-specific login
        $request->merge(['role' => 'doctor']);
        return $this->login($request);
    }

    // Get the current authenticated user
    public function getCurrentUser(Request $request): JsonResponse
    {
        // Get the user via the token (Auth::user() returns User according to config/auth.php)
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['user' => null, 'has_health_profile' => false], Response::HTTP_UNAUTHORIZED);
        }
        
        $account = $user->account;
        $healthProfile = $user->healthProfile;
        $hasProfile = $healthProfile && $healthProfile->isComplete();

        return response()->json([
            'user'      => [
                'id'             => $user->id,
                'account_id'      => $user->account_id,
                'name'            => $user->name,
                'date_of_birth' => $user->date_of_birth,
                'profile_photo'   => $user->profile_photo,
                'age'            => $user->age,
                'role'           => $user->role,
                'specialty'     => $user->specialty,
                'email'          => $account?->email,
                'status'         => $account?->account_status,
            ],
            'has_health_profile' => $hasProfile,
            'redirect_to'      => $hasProfile ? '/main' : '/health-profile',
        ]);
    }

    // Log out the user
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout successful.']);
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    // Format the authentication response
    private function authenticatedResponse(User $user, string $token, bool $hasProfile, string $redirectTo, bool $hasPendingInvitations, int $status, string $message): JsonResponse
    {
        return response()->json([
            'message'                        => $message,
            'token'                          => $token,
            'has_health_profile'               => $hasProfile,
            'redirect_to'                    => $redirectTo,
            'user'                    => $this->userData($user),
            'has_pending_doctor_invitations' => $hasPendingInvitations,
        ], $status);
    }

    // Return a formatted validation error
    private function validationError(ValidationException $e, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Get user public data
    private function userData(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'            => $user->name,
            'date_of_birth' => $user->date_of_birth,
            'profile_photo'   => $user->profile_photo,
            'age'            => $user->age,
            'role'           => $user->role,
            'specialty'     => $user->specialty,
        ];
    }

    // Check if pending invitation exists for email
    private function hasInvitationForEmail(string $email): bool
    {
        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])->where('status', 'pending')->exists();
    }

    // Handle database errors during registration
    private function handleRegistrationQueryException(QueryException $e): JsonResponse
    {
        $sqlState   = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        if ($sqlState === '23000' || $driverCode === 1062) {
            return response()->json([
                'message' => 'This email is already in use.',
                'errors'  => ['email' => ['This email is already in use.']],
            ], 409);
        }

        Log::error('Registration database error: ' . $e->getMessage());
        return response()->json(['message' => 'Error creating account'], 500);
    }

    // Return base validation messages
    private function baseValidationMessages(): array
    {
        return [
            'name.required'               => "Le nom d'utilisateur est obligatoire.",
            'email.required'             => "L'adresse e-mail est obligatoire.",
            'email.unique'               => "Cet e-mail est déjà utilisé.",
            'date_of_birth.required'    => "La date de naissance est obligatoire.",
            'date_of_birth.date_format' => "Format de date invalide. Utilisez AAAA-MM-JJ.",
            'password.required'          => "Le mot de passe est obligatoire.",
            'password.confirmed'         => "Les mots de passe ne correspondent pas.",
        ];
    }

    // Create custom validation rule for birth date
    private function birthDateRule(int $minimumAge = 18, ?string $minimumAgeMessage = null): \Closure
    {
        return function ($_attribute, $value, $fail) use ($minimumAge, $minimumAgeMessage) {
            try {
                $birthDate = Carbon::createFromFormat('Y-m-d', (string) $value);
            } catch (\Throwable) {
                $fail('Date de naissance invalide.');
                return;
            }

            // Check that the date format is valid
            if (! $birthDate || $birthDate->format('Y-m-d') !== $value) {
                $fail('Date de naissance invalide.');
                return;
            }

            // Check that the birth date is not in the future
            if ($birthDate->isFuture()) {
                $fail('La date de naissance ne peut pas être dans le futur.');
                return;
            }

            // Check minimum age
            if ($birthDate->diffInYears(now()) < $minimumAge) {
                $fail($minimumAgeMessage ?? "Vous devez avoir au moins {$minimumAge} ans pour créer un compte.");
            }
        };
    }

    // Calculate age from birth date
    private function calculateAge(string $dateOfBirth): int
    {
        return Carbon::createFromFormat('Y-m-d', $dateOfBirth)->diffInYears(now());
    }
}