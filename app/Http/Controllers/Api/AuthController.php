<?php 

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller; 
use App\Models\User; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password; 
use Carbon\Carbon;

class AuthController extends Controller 
{ 
    public function register(Request $request) 
    { 
        try {
            $validated = $request->validate([ 
                'name' => ['required', 'string', 'min:3', 'max:50'], 
                'email' => ['required', 'email', 'max:255', 'unique:users,email'], 
                'date_of_birth' => [
                    'required', 
                    'date',
                    'before:today',
                    function ($attribute, $value, $fail) {
                        $birthDate = Carbon::parse($value);
                        $age = $birthDate->diffInYears(Carbon::now());
                        
                        if ($age < 18) {
                            $fail('Vous devez avoir au minimum 18 ans pour créer un compte.');
                        }
                    }
                ],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()], 
            ]); 

            $user = User::create([ 
                'name' => $validated['name'], 
                'email' => $validated['email'], 
                'date_of_birth' => $validated['date_of_birth'],
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
                    'date_of_birth' => $user->date_of_birth,
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