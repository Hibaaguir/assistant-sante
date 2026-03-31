<?php

namespace App\Http\Controllers;

use App\Models\ResultatAnalyse;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResultatAnalyseController extends Controller
{
    // Récupérer tous les résultats d'analyses d'un utilisateur
    public function index(Utilisateur $utilisateur)
    {
        $resultats = $utilisateur->resultatsAnalyses()->latest('date_analyse')->get();
        return response()->json(['data' => $resultats], Response::HTTP_OK);
    }

    // Créer un nouveau résultat d'analyse
    public function store(Request $request, Utilisateur $utilisateur)
    {
        $validated = $request->validate([
            'type_analyse' => 'required|string|max:120',
            'resultat_analyse' => 'nullable|string|max:120',
            'valeur' => 'required|numeric',
            'unite' => 'nullable|string|max:30',
            'date_analyse' => 'required|date',
        ]);

        $resultat = $utilisateur->resultatsAnalyses()->create($validated);

        return response()->json(['data' => $resultat], Response::HTTP_CREATED);
    }

    // Afficher un résultat d'analyse spécifique
    public function show(Utilisateur $utilisateur, ResultatAnalyse $resultatAnalyse)
    {
        return response()->json(['data' => $resultatAnalyse], Response::HTTP_OK);
    }

    // Mettre à jour un résultat d'analyse
    public function update(Request $request, Utilisateur $utilisateur, ResultatAnalyse $resultatAnalyse)
    {
        $validated = $request->validate([
            'type_analyse' => 'sometimes|string|max:120',
            'resultat_analyse' => 'nullable|string|max:120',
            'valeur' => 'sometimes|numeric',
            'unite' => 'nullable|string|max:30',
            'date_analyse' => 'sometimes|date',
        ]);

        $resultatAnalyse->update($validated);

        return response()->json(['data' => $resultatAnalyse], Response::HTTP_OK);
    }

    // Supprimer un résultat d'analyse
    public function destroy(Utilisateur $utilisateur, ResultatAnalyse $resultatAnalyse)
    {
        $resultatAnalyse->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
