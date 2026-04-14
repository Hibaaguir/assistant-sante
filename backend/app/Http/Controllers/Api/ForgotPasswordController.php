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
    // Demander un lien de réinitialisation de mot de passe
    public function requestReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Chercher le compte avec cet email
        $account = Account::where('email', $request->email)->first();

        if (!$account) {
            return response()->json(['message' => 'Aucun compte trouvé avec cet email.'], 404);
        }

        // Créer un token de réinitialisation aléatoire
        $token = Str::random(64);

        // Supprimer les anciens tokens et enregistrer le nouveau
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $account->email,
            'token'      => $token,
            'created_at' => now(),
        ]);

        // Construire le lien de réinitialisation et l'envoyer par email
        $frontendUrl = config('app.frontend_url', config('app.url')) . '/reinitialiser-mot-de-passe';
        $resetUrl    = $frontendUrl . '?email=' . urlencode($account->email) . '&token=' . $token;

        Mail::send(new ResetPasswordMail($account->email, $token, $resetUrl));

        return response()->json(['message' => 'Un lien de réinitialisation a été envoyé à votre email.']);
    }

    // Réinitialiser le mot de passe avec le token
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        // Chercher le compte par email
        $account = Account::where('email', $request->email)->first();

        if (!$account) {
            return response()->json(['message' => 'Email invalide.'], 422);
        }

        // Récupérer le token de réinitialisation depuis la base de données
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $account->email)
            ->first();

        if (!$resetToken || $request->token !== $resetToken->token) {
            return response()->json(['message' => 'Token invalide ou déjà utilisé.'], 422);
        }

        // Vérifier que le token n'a pas expiré (60 minutes)
        if (now()->diffInMinutes($resetToken->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $account->email)->delete();
            return response()->json(['message' => 'Ce lien a expiré. Veuillez refaire une demande.'], 422);
        }

        // Enregistrer le nouveau mot de passe
        $account->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token pour qu'il ne soit pas réutilisé
        DB::table('password_reset_tokens')->where('email', $account->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'data'    => ['role' => $account->user->role],
        ]);
    }
}
