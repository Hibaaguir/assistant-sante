<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    // Get user profile
    public function getProfile(Request $request): JsonResponse
    {
        $account = $request->user();
        $user = $account->user;

        return response()->json([
            'data' => [
                'id'             => $user->id,
                'name'            => $user->name,
                'email'          => $account->email,
                'role'           => $user->role,
                'profile_photo'   => $user->profile_photo,
                'date_of_birth' => $user->date_of_birth,
                'age'            => $user->age,
                'specialty'     => $user->specialty,
            ],
        ]);
    }

    // Update user name
    public function updateName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:120|not_regex:/^\s+$/',
        ], [
            'name.required' => 'The name is required.',
            'name.min' => 'The name must contain at least 2 characters.',
            'name.max' => 'The name cannot exceed 120 characters.',
            'name.not_regex' => 'The name cannot contain only spaces.',
        ]);

        $account = $request->user();
        $user = $account->user;
        $user->update(['name' => trim($request->input('name'))]);

        return response()->json([
            'message' => 'Name updated successfully.',
            'data' => ['name' => $user->name],
        ]);
    }

    // Change password
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'The current password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);

        $account = $request->user()->account;

        // Check that current password is correct
        if (!Hash::check($request->input('current_password'), $account->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 422);
        }

        $account->update(['password' => Hash::make($request->input('new_password'))]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    // Update profile photo
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
            'photo.required' => 'The photo is required.',
            'photo.max' => 'The photo is too large (max 5MB).',
            'photo.regex' => 'Unsupported photo format.',
        ]);

        $account = $request->user();
        $user = $account->user;
        $user->update(['profile_photo' => $request->input('photo')]);

        return response()->json([
            'message' => 'Profile photo updated successfully.',
            'data' => ['profile_photo' => $user->profile_photo],
        ]);
    }

    // Delete profile photo
    public function deletePhoto(Request $request): JsonResponse
    {
        $account = $request->user();
        $user = $account->user;
        $user->update(['profile_photo' => null]);

        return response()->json([
            'message' => 'Profile photo deleted successfully.',
            'data' => ['profile_photo' => null],
        ]);
    }
}
