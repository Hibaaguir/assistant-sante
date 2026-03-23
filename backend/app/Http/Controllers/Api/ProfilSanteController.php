<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilSante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfilSanteController extends Controller
{
    public function enregistrer(Request $request)
    {
        try {
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
                'traitements.*.frequency_count' => ['nullable', 'integer', 'min:1'],
                'traitements.*.duration' => ['nullable', 'string', 'max:120'],
                'prend_medicament' => ['required', 'boolean'],
                'nom_medicament' => ['nullable', 'string', 'max:255', 'required_if:prend_medicament,1'],
                'fumeur' => ['required', 'boolean'],
                'alcool' => ['required', 'boolean'],
                'activite_physique' => ['required', 'boolean'],
                'activites_physiques' => ['nullable', 'array'],
                'activites_physiques.*' => ['string', 'max:120'],
                'frequence_activite_physique' => ['nullable', Rule::in(['1-2 fois', '3-4 fois', '5+ fois'])],
            ]);

            if (! ($validated['activite_physique'] ?? false)) {
                $validated['activites_physiques'] = [];
                $validated['frequence_activite_physique'] = null;
            }

            if (($validated['activite_physique'] ?? false) && empty($validated['activites_physiques'])) {
                $validated['frequence_activite_physique'] = null;
            }

            if (($validated['activite_physique'] ?? false)
                && ! empty($validated['activites_physiques'])
                && empty($validated['frequence_activite_physique'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'frequence_activite_physique' => ['Veuillez sélectionner la fréquence d\'activité physique.'],
                ]);
            }

            $validated['user_id'] = Auth::id();

            $profil = ProfilSante::updateOrCreate(
                ['user_id' => Auth::id()],
                $validated
            );

            return response()->json([
                'message' => 'Profil sante enregistre avec succes.',
                'data' => $profil,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Profil sante registration error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du profil.'], 500);
        }
    }

    public function afficher()
    {
        $user = Auth::user();
        $profil = $user->profilSante;

        return response()->json([
            'data' => $profil,
            'user' => $user,
        ]);
    }


}
