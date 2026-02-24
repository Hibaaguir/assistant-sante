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
            return response()->json(['message' => 'Utilisateur non authentifie.'], 401);
        }

        if (is_string($request->input('sexe'))) {
            $request->merge([
                'sexe' => strtolower(trim($request->input('sexe'))),
            ]);
        }

        $validated = $request->validate([
            'sexe' => ['required', Rule::in(['homme', 'femme'])],
            'taille' => ['required', 'numeric', 'min:30', 'max:250'],
            'poids' => ['required', 'numeric', 'min:1', 'max:300'],
            'groupe_sanguin' => ['required', 'string', 'max:5'],
            'objectif' => ['nullable', 'string', 'max:255'],

            'objectifs' => ['nullable', 'array'],
            'objectifs.*' => ['string', 'max:120'],

            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:100'],

            'maladies_chroniques' => ['nullable', 'array'],
            'maladies_chroniques.*' => ['string', 'max:120'],

            'traitements' => ['nullable', 'array'],
            'traitements.*.type' => ['required_with:traitements', 'string', 'max:120'],
            'traitements.*.name' => ['nullable', 'string', 'max:255'],
            'traitements.*.dose' => ['nullable', 'string', 'max:120'],
            'traitements.*.frequency_unit' => ['nullable', Rule::in(['jour', 'semaine', 'mois'])],
            'traitements.*.frequency_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            'traitements.*.duration' => ['nullable', 'string', 'max:120'],

            'prend_medicament' => ['required', 'boolean'],
            'nom_medicament' => ['nullable', 'string', 'max:255', 'required_if:prend_medicament,1'],

            'consulte_medecin' => ['required', 'boolean'],
            'medecin_peut_consulter' => ['required', 'boolean'],
            'medecin_email' => ['nullable', 'email', 'required_if:medecin_peut_consulter,1'],

            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $validated['user_id'] = Auth::id();

        $profil = ProfilSante::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json([
            'message' => 'Profil sante enregistre avec succes.',
            'data' => $profil,
        ], 200);
    }

    public function show()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Utilisateur non authentifie.'], 401);
        }

        $profil = ProfilSante::where('user_id', Auth::id())->first();
        $user = Auth::user();

        return response()->json([
            'data' => $profil,
            'user' => $user,
        ], 200);
    }
}
