<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilUtilisateurController extends Controller
{
    // Récupérer le profil de l'utilisateur
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_photo' => $user->profile_photo,
                'date_of_birth' => $user->date_of_birth,
            ],
        ]);
    }

    // Mettre à jour le nom de l'utilisateur
    public function updateName(Request $request): JsonResponse
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

    // Changer le mot de passe
    public function changePassword(Request $request): JsonResponse
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

        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($request->input('mot_de_passe_actuel'), $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->input('nouveau_mot_de_passe'))]);

        return response()->json(['message' => 'Mot de passe changé avec succès.']);
    }

    // Mettre à jour la photo de profil
    public function updatePhoto(Request $request): JsonResponse
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

    // Supprimer la photo de profil
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['profile_photo' => null]);

        return response()->json([
            'message' => 'Photo de profil supprimée avec succès.',
            'data' => ['photo_profil' => null],
        ]);
    }
}
