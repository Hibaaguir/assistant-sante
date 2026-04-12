<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    // Récupérer le profil de l'utilisateur
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->account?->email,
                'role'          => $user->role,
                'profile_photo' => $user->profile_photo,
                'date_of_birth' => $user->date_of_birth,
                'age'           => $user->age,
                'specialty'     => $user->specialty,
            ],
        ]);
    }

    // Mettre à jour le nom de l'utilisateur
    public function updateName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:120|not_regex:/^\s+$/',
        ]);

        $user = $request->user();
        $user->update(['name' => trim($request->input('name'))]);

        return response()->json([
            'message' => 'Nom mis à jour avec succès.',
            'data'    => ['name' => $user->name],
        ]);
    }

    // Changer le mot de passe
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $account = $request->user()->account;

        // Vérifier que le mot de passe courant est correct
        if (!Hash::check($request->input('current_password'), $account->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
        }

        $account->update(['password' => Hash::make($request->input('new_password'))]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
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
        ]);

        $user = $request->user();
        $user->update(['profile_photo' => $request->input('photo')]);

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'data'    => ['profile_photo' => $user->profile_photo],
        ]);
    }

    // Supprimer la photo de profil
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['profile_photo' => null]);

        return response()->json([
            'message' => 'Photo de profil supprimée avec succès.',
            'data' => ['profile_photo' => null],
        ]);
    }
}
