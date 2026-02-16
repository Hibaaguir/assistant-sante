<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilSante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfilSanteController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $validated = $request->validate([
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'sexe' => ['required', Rule::in(['homme', 'femme'])],
            'taille' => ['required', 'numeric', 'min:30', 'max:250'],
            'poids' => ['required', 'numeric', 'min:1', 'max:300'],
            'groupe_sanguin' => ['required', 'string', 'max:5'],
            'objectif' => ['required', 'string', 'max:255'],

            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:100'],

            'maladies_chroniques' => ['nullable', 'array'],
            'maladies_chroniques.*' => ['string', 'max:120'],

            'traitements' => ['nullable', 'array'],
            'traitements.*' => ['string', 'max:120'],

            'prend_medicament' => ['required', 'boolean'],
            'nom_medicament' => ['nullable', 'string', 'max:255', 'required_if:prend_medicament,1'],

            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $validated['user_id'] = Auth::id();

        $profil = ProfilSante::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json([
            'message' => 'Profil santé enregistré avec succès.',
            'data' => $profil,
        ], 200);
    }

    public function show()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $profil = ProfilSante::where('user_id', Auth::id())->first();

        return response()->json(['data' => $profil], 200);
    }
}
