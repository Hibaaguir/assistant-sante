<?php

namespace App\Http\Controllers;

use App\Models\ActivitePhysique;
use App\Models\JournalQuotidien;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActivitePhysiqueController extends Controller
{
    // Récupérer toutes les activités physiques d'une entrée du journal
    public function index(JournalEntry $entreeJournal)
    {
        $activites = $entreeJournal->activitesPhysiques()->get();
        return response()->json(['data' => $activites], Response::HTTP_OK);
    }

    // Créer une nouvelle activité physique
    public function store(Request $request, JournalEntry $entreeJournal)
    {
        $validated = $request->validate([
            'type_activite' => 'required|string|max:120',
            'duree_activite' => 'nullable|integer|min:0',
            'intensite' => 'required|in:faible,moyenne,elevee',
        ]);

        $activite = $entreeJournal->activitesPhysiques()->create($validated);

        return response()->json(['data' => $activite], Response::HTTP_CREATED);
    }

    // Afficher une activité physique spécifique
    public function show(JournalEntry $entreeJournal, ActivitePhysique $activitePhysique)
    {
        return response()->json(['data' => $activitePhysique], Response::HTTP_OK);
    }

    // Mettre à jour une activité physique
    public function update(Request $request, JournalEntry $entreeJournal, ActivitePhysique $activitePhysique)
    {
        $validated = $request->validate([
            'type_activite' => 'sometimes|string|max:120',
            'duree_activite' => 'nullable|integer|min:0',
            'intensite' => 'sometimes|in:faible,moyenne,elevee',
        ]);

        $activitePhysique->update($validated);

        return response()->json(['data' => $activitePhysique], Response::HTTP_OK);
    }

    // Supprimer une activité physique
    public function destroy(JournalEntry $entreeJournal, ActivitePhysique $activitePhysique)
    {
        $activitePhysique->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
