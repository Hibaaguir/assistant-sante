<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilUtilisateurController extends Controller
{
    public function obtenirProfil(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Profil obtenu avec succès.',
            'data' => [
                'id' => $user->id,
                'nom' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'photo_profil' => $user->profile_photo,
            ],
        ]);
    }

    public function mettreAJourNom(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'required|string|min:2|max:120',
        ], [
            'nom.required' => 'Le nom est requis.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 120 caractères.',
        ]);

        $user = $request->user();
        $user->update(['name' => $request->input('nom')]);

        return response()->json([
            'message' => 'Nom mis à jour avec succès.',
            'data' => [
                'nom' => $user->name,
            ],
        ]);
    }

    public function changerMotDePasse(Request $request): JsonResponse
    {
        $request->validate([
            'mot_de_passe_actuel' => 'required|string',
            'nouveau_mot_de_passe' => 'required|string|min:8|confirmed',
        ], [
            'mot_de_passe_actuel.required' => 'Le mot de passe actuel est requis.',
            'nouveau_mot_de_passe.required' => 'Le nouveau mot de passe est requis.',
            'nouveau_mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'nouveau_mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('mot_de_passe_actuel'), $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->input('nouveau_mot_de_passe'))]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès.',
        ]);
    }

    public function mettreAJourPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => [
                'required',
                'string',
                'max:3000000',
                'regex:/^data:image\/(png|jpe?g|webp);base64,/i',
            ],
        ], [
            'photo.required' => 'La photo est requise.',
            'photo.max' => 'La photo est trop volumineuse.',
            'photo.regex' => 'Format de photo non supporte.',
        ]);

        $user = $request->user();
        $user->update([
            'profile_photo' => $request->input('photo'),
        ]);

        return response()->json([
            'message' => 'Photo de profil mise a jour avec succes.',
            'data' => [
                'photo_profil' => $user->profile_photo,
            ],
        ]);
    }

    public function supprimerPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'profile_photo' => null,
        ]);

        return response()->json([
            'message' => 'Photo de profil supprimee avec succes.',
            'data' => [
                'photo_profil' => null,
            ],
        ]);
    }
}
