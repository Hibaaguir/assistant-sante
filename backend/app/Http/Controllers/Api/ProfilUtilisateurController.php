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
        $compte = $request->user();
        $utilisateur = $compte->utilisateur;

        return response()->json([
            'data' => [
                'id'             => $utilisateur->id,
                'nom'            => $utilisateur->nom,
                'email'          => $compte->email,
                'role'           => $utilisateur->role,
                'photo_profil'   => $utilisateur->photo_profil,
                'date_naissance' => $utilisateur->date_naissance,
                'age'            => $utilisateur->age,
                'specialite'     => $utilisateur->specialite,
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

        $compte = $request->user();
        $utilisateur = $compte->utilisateur;
        $utilisateur->update(['nom' => trim($request->input('nom'))]);

        return response()->json([
            'message' => 'Nom mis à jour avec succès.',
            'data' => ['nom' => $utilisateur->nom],
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

        $compte = $request->user();

        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($request->input('mot_de_passe_actuel'), $compte->motdepasse)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
        }

        $compte->update(['motdepasse' => Hash::make($request->input('nouveau_mot_de_passe'))]);

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

        $compte = $request->user();
        $utilisateur = $compte->utilisateur;
        $utilisateur->update(['photo_profil' => $request->input('photo')]);

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'data' => ['photo_profil' => $utilisateur->photo_profil],
        ]);
    }

    // Supprimer la photo de profil
    public function deletePhoto(Request $request): JsonResponse
    {
        $compte = $request->user();
        $utilisateur = $compte->utilisateur;
        $utilisateur->update(['photo_profil' => null]);

        return response()->json([
            'message' => 'Photo de profil supprimée avec succès.',
            'data' => ['photo_profil' => null],
        ]);
    }
}
