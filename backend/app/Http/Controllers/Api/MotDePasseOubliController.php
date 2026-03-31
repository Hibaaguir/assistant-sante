<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ReinisialiserMotDePasse;
use App\Models\Compte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Gère la réinitialisation du mot de passe oublié
 * 
 * Responsabilités:
 * - Demande de réinitialisation de mot de passe
 * - Envoi de lien sécurisé par email
 * - Validation et réinitialisation du mot de passe
 */
class MotDePasseOubliController extends Controller
{
    // Demander la réinitialisation du mot de passe
    public function requestReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
        ]);

        $compte = Compte::where('email', $request->input('email'))->first();

        // Ne pas révéler si l'email existe (sécurité : prévient l'énumération)
        if (!$compte) {
            return response()->json([
                'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
            ]);
        }

        // Créer un token de réinitialisation
        $token = Str::random(64);

        // Supprimer les tokens existants pour cet email
        DB::table('password_reset_tokens')->where('email', $compte->email)->delete();

        // Insérer le nouveau token
        DB::table('password_reset_tokens')->insert([
            'email' => $compte->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Construire l'URL du frontend pour réinitialiser
        $frontendUrl = config('app.frontend_url', config('app.url')) . '/reinitialiser-mot-de-passe';
        $resetUrl = $frontendUrl . '?email=' . urlencode($compte->email) . '&token=' . $token;

        // Envoyer l'email
        Mail::send(new ReinisialiserMotDePasse($compte->email, $token, $resetUrl));

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
            'mot_de_passe' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'token.required' => 'Le token est requis.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe.confirmed' => 'La confirmation ne correspond pas.',
        ]);

        $compte = Compte::where('email', $request->input('email'))->first();

        // Vérifier que le compte existe
        if (!$compte) {
            return response()->json([
                'message' => 'Email ou token invalide.',
            ], 422);
        }

        // Vérifier que le token de réinitialisation existe
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $compte->email)
            ->latest('created_at')
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Le lien de réinitialisation a expiré ou est invalide.',
            ], 422);
        }

        // Vérifier que le token n'a pas expiré
        if ($resetToken->created_at < now()->subMinutes(60)) {
            DB::table('password_reset_tokens')->where('email', $compte->email)->delete();
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
        $compte->update([
            'motdepasse' => Hash::make($request->input('mot_de_passe')),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $compte->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'data' => [
                'role' => $compte->utilisateur->role,
            ],
        ]);
    }
}
