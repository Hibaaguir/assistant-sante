<?php

namespace App\Http\Controllers;

use App\Models\Repas;
use App\Models\JournalQuotidien;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RepasController extends Controller
{
    // Récupérer tous les repas d'une entrée du journal
    public function index(JournalQuotidien $entreeJournal)
    {
        $repas = $entreeJournal->repas()->get();
        return response()->json(['data' => $repas], Response::HTTP_OK);
    }

    // Créer un nouveau repas
    public function store(Request $request, JournalQuotidien $entreeJournal)
    {
        $validated = $request->validate([
            'type_repas' => 'required|in:petit_dejeuner,dejeuner,diner,collation',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer|min:0',
            'apport_sucre' => 'nullable|string',
        ]);

        $repas = $entreeJournal->repas()->create($validated);

        return response()->json(['data' => $repas], Response::HTTP_CREATED);
    }

    // Afficher un repas spécifique
    public function show(JournalQuotidien $entreeJournal, Repas $repas)
    {
        return response()->json(['data' => $repas], Response::HTTP_OK);
    }

    // Mettre à jour un repas
    public function update(Request $request, JournalQuotidien $entreeJournal, Repas $repas)
    {
        $validated = $request->validate([
            'type_repas' => 'sometimes|in:petit_dejeuner,dejeuner,diner,collation',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer|min:0',
            'apport_sucre' => 'nullable|string',
        ]);

        $repas->update($validated);

        return response()->json(['data' => $repas], Response::HTTP_OK);
    }

    // Supprimer un repas
    public function destroy(JournalQuotidien $entreeJournal, Repas $repas)
    {
        $repas->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
