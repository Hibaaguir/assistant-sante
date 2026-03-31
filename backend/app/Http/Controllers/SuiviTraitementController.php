<?php

namespace App\Http\Controllers;

use App\Models\SuiviTraitement;
use App\Models\Traitement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuiviTraitementController extends Controller
{
    // Récupérer tous les suivis d'un traitement
    public function index(Traitement $traitement)
    {
        $suivis = $traitement->suivis()->get();
        return response()->json(['data' => $suivis], Response::HTTP_OK);
    }

    // Créer un nouveau suivi
    public function store(Request $request, Traitement $traitement)
    {
        $validated = $request->validate([
            'date_controle' => 'required|date',
            'pris' => 'required|boolean',
            'verifie_a' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $suivi = $traitement->suivis()->create($validated);

        return response()->json(['data' => $suivi], Response::HTTP_CREATED);
    }

    // Afficher un suivi spécifique
    public function show(Traitement $traitement, SuiviTraitement $suiviTraitement)
    {
        return response()->json(['data' => $suiviTraitement], Response::HTTP_OK);
    }

    // Mettre à jour un suivi
    public function update(Request $request, Traitement $traitement, SuiviTraitement $suiviTraitement)
    {
        $validated = $request->validate([
            'date_controle' => 'sometimes|date',
            'pris' => 'sometimes|boolean',
            'verifie_a' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $suiviTraitement->update($validated);

        return response()->json(['data' => $suiviTraitement], Response::HTTP_OK);
    }

    // Supprimer un suivi
    public function destroy(Traitement $traitement, SuiviTraitement $suiviTraitement)
    {
        $suiviTraitement->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
