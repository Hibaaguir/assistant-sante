<?php 

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller; 
use App\Models\User; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password; 

class AuthController extends Controller 
{ 
    public function register(Request $request) 
    { 
        try {
            $validated = $request->validate([ 
                'name' => ['required', 'string', 'min:3', 'max:50'], 
                'email' => ['required', 'email', 'max:255', 'unique:users,email'], 
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()], 
            ]); 

            $user = User::create([ 
                'name' => $validated['name'], 
                'email' => $validated['email'], 
                'password' => Hash::make($validated['password']), 
            ]); 

            $token = $user->createToken('auth_token')->plainTextToken; 

            return response()->json([ 
                'message' => 'Compte créé avec succès', 
                'token' => $token, 
                'user' => [ 
                    'id' => $user->id, 
                    'name' => $user->name, 
                    'email' => $user->email, 
                ], 
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    } 
}