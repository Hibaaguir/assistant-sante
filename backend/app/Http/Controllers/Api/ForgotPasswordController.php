<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Manages forgotten password reset
 * 
 * Responsibilities:
 * - Password reset request
 * - Secure link sending by email
 * - Validation and password reset
 */
class ForgotPasswordController extends Controller
{
    // Request password reset
    public function requestReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be valid.',
        ]);

        $account = Account::where('email', $request->input('email'))->first();

        // Ne pas révéler si l'email existe (sécurité : prévient l'énumération)
        if (!$account) {
            return response()->json([
                'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
            ]);
        }

        // Créer un token de réinitialisation
        $token = Str::random(64);

        // Supprimer les tokens existants pour cet email
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();

        // Insérer le nouveau token
        DB::table('password_reset_tokens')->insert([
            'email' => $account->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Construire l'URL du frontend pour réinitialiser
        $frontendUrl = config('app.frontend_url', config('app.url')) . '/reinitialiser-mot-de-passe';
        $resetUrl = $frontendUrl . '?email=' . urlencode($account->email) . '&token=' . $token;

        // Envoyer l'email
        Mail::send(new ResetPasswordMail($account->email, $token, $resetUrl));

        return response()->json([
            'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
        ]);
    }

    // Réinitialiser le mot de passe
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'token.required' => 'Le token est requis.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
        ]);

        $account = Account::where('email', $request->input('email'))->first();

        // Vérifier que le compte existe
        if (!$account) {
            return response()->json([
                'message' => 'Email ou token invalide.',
            ], 422);
        }

        // Vérifier que le token de réinitialisation existe
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $account->email)
            ->latest('created_at')
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Le lien de réinitialisation a expiré ou est invalide.',
            ], 422);
        }

        // Vérifier que le token n'a pas expiré
        if ($resetToken->created_at < now()->subMinutes(60)) {
            DB::table('password_reset_tokens')->where('email', $account->email)->delete();
            return response()->json([
                'message' => 'Le lien de réinitialisation a expiré.',
            ], 422);
        }

        // Vérifier que le token fourni est valide
        if (!Hash::check($request->input('token'), $resetToken->token)) {
            return response()->json([
                'message' => 'Token invalide.',
            ], 422);
        }

        // Mettre à jour le mot de passe
        $account->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'data' => [
                'role' => $account->user->role,
            ],
        ]);
    }
}
