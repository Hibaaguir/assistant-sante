<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // The user forgot their password and asks for a reset link
    //
    // 1. Validate the email
    // 2. Check that the email exists in the database
    // 3. Create a reset token and save it
    // 4. Send the reset link by email
    // ─────────────────────────────────────────────────────────────────────────
    public function requestReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find the account with this email
        $account = Account::where('email', $request->email)->first();

        if (!$account) {
            return response()->json(['message' => 'Aucun compte trouvé avec cet email.'], 404);
        }

        // Create a simple random token
        $token = Str::random(64);

        // Delete any old token for this email, then save the new one
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $account->email,
            'token'      => $token,
            'created_at' => now(),
        ]);

        // Build the reset link and send it by email
        $frontendUrl = config('app.frontend_url', config('app.url')) . '/reinitialiser-mot-de-passe';
        $resetUrl    = $frontendUrl . '?email=' . urlencode($account->email) . '&token=' . $token;

        Mail::send(new ResetPasswordMail($account->email, $token, $resetUrl));

        return response()->json(['message' => 'Un lien de réinitialisation a été envoyé à votre email.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // The user clicked the link and wants to set a new password
    //
    // 1. Validate email, token, and new password
    // 2. Find the account
    // 3. Check that the token is correct
    // 4. Save the new password and delete the used token
    // ─────────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        // Find the account
        $account = Account::where('email', $request->email)->first();

        if (!$account) {
            return response()->json(['message' => 'Email invalide.'], 422);
        }

        // Find the reset token saved in the database
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $account->email)
            ->first();

        if (!$resetToken) {
            return response()->json(['message' => 'Token invalide ou déjà utilisé.'], 422);
        }

        // Check that the token from the URL matches the one in the database
        if ($request->token !== $resetToken->token) {
            return response()->json(['message' => 'Token invalide.'], 422);
        }

        // Save the new password
        $account->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete the token so it cannot be used again
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'data'    => ['role' => $account->user->role],
        ]);
    }
}
