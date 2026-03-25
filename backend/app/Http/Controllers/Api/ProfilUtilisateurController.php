<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilUtilisateurController extends Controller
{
    public function mettreAJourNom(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'required|string|min:2|max:120|not_regex:/^\s+$/',
        ], [
            'nom.required' => 'Le nom est requis.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 120 caractères.',
            'nom.not_regex' => 'Le nom ne peut pas contenir seulement des espaces.',
        ]);

        $user = $request->user();
        $user->update(['name' => trim($request->input('nom'))]);

        return response()->json([
            'message' => 'Nom mis à jour avec succès.',
            'data' => ['nom' => $user->name],
        ]);
    }

    public function changerMotDePasse(Request $request): JsonResponse
    {
        $request->validate([
            'mot_de_passe_actuel' => 'required|string',
            'nouveau_mot_de_passe' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'mot_de_passe_actuel.required' => 'Le mot de passe actuel est requis.',
            'nouveau_mot_de_passe.required' => 'Le nouveau mot de passe est requis.',
            'nouveau_mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('mot_de_passe_actuel'), $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->input('nouveau_mot_de_passe'))]);

        return response()->json(['message' => 'Mot de passe changé avec succès.']);
    }

    public function mettreAJourPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => [
                'required',
                'string',
                'max:5000000',
                'regex:/^data:image\/(png|jpe?g|webp);base64,/i',
            ],
        ], [
            'photo.required' => 'La photo est requise.',
            'photo.max' => 'La photo est trop volumineuse (max 5MB).',
            'photo.regex' => 'Format de photo non supporté.',
        ]);

        $user = $request->user();
        $user->update(['profile_photo' => $request->input('photo')]);

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'data' => ['photo_profil' => $user->profile_photo],
        ]);
    }

    public function supprimerPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['profile_photo' => null]);

        return response()->json([
            'message' => 'Photo de profil supprimée avec succès.',
            'data' => ['photo_profil' => null],
        ]);
    }
}
