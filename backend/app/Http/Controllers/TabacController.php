<?php

namespace App\Http\Controllers;

use App\Models\Tabac;
use App\Models\JournalQuotidien;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TabacController extends Controller
{
    // Récupérer toutes les consommations de tabac d'une entrée du journal
    public function index(JournalEntry $entreeJournal)
    {
        $tabacs = $entreeJournal->tabacs()->get();
        return response()->json(['data' => $tabacs], Response::HTTP_OK);
    }

    // Créer une nouvelle consommation de tabac
    public function store(Request $request, JournalEntry $entreeJournal)
    {
        $validated = $request->validate([
            'type_tabac' => 'required|in:cigarette,vape',
            'cigarettes_par_jour' => 'nullable|integer|min:0',
            'frequence_vape' => 'nullable|integer|min:0',
            'bouffees_par_jour' => 'nullable|integer|min:0',
        ]);

        $tabac = $entreeJournal->tabacs()->create($validated);

        return response()->json(['data' => $tabac], Response::HTTP_CREATED);
    }

    // Afficher une consommation de tabac spécifique
    public function show(JournalEntry $entreeJournal, Tabac $tabac)
    {
        return response()->json(['data' => $tabac], Response::HTTP_OK);
    }

    // Mettre à jour une consommation de tabac
    public function update(Request $request, JournalEntry $entreeJournal, Tabac $tabac)
    {
        $validated = $request->validate([
            'type_tabac' => 'sometimes|in:cigarette,vape',
            'cigarettes_par_jour' => 'nullable|integer|min:0',
            'frequence_vape' => 'nullable|integer|min:0',
            'bouffees_par_jour' => 'nullable|integer|min:0',
        ]);

        $tabac->update($validated);

        return response()->json(['data' => $tabac], Response::HTTP_OK);
    }

    // Supprimer une consommation de tabac
    public function destroy(JournalEntry $entreeJournal, Tabac $tabac)
    {
        $tabac->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
