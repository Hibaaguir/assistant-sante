<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetMotDePasseMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class MotDePasseOubliController extends Controller
{
    public function demanderReinit(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        // Ne pas révéler si l'email existe ou non (sécurité)
        if (!$user) {
            return response()->json([
                'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
            ]);
        }

        // Créer un token de réinitialisation
        $token = Str::random(64);

        // Supprimer les tokens existants pour cet email
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Insérer le nouveau token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Construire l'URL du frontend pour réinitialiser
        $frontendUrl = config('app.frontend_url', config('app.url')) . '/reinitialiser-mot-de-passe';
        $resetUrl = $frontendUrl . '?email=' . urlencode($user->email) . '&token=' . $token;

        // Envoyer l'email
        Mail::send(new ResetMotDePasseMail($user->email, $token, $resetUrl));

        return response()->json([
            'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
        ]);
    }

    public function reinitialiserMotDePasse(Request $request): JsonResponse
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

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email ou token invalide.',
            ], 422);
        }

        // Vérifier le token
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->latest('created_at')
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Le lien de réinitialisation a expiré ou est invalide.',
            ], 422);
        }

        // Vérifier que le token n'a pas expiré (60 minutes par défaut)
        if ($resetToken->created_at < now()->subMinutes(60)) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            return response()->json([
                'message' => 'Le lien de réinitialisation a expiré.',
            ], 422);
        }

        // Vérifier le token fourni
        if (!Hash::check($request->input('token'), $resetToken->token)) {
            return response()->json([
                'message' => 'Token invalide.',
            ], 422);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->input('mot_de_passe')),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'data' => [
                'role' => $user->role,
            ],
        ]);
    }
}
