<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tobacco;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TobaccoController extends Controller
{
    // Get all tobacco usage for a journal entry
    public function index(JournalEntry $journalEntry)
    {
        $tobacco = $journalEntry->tobacco()->get();
        return response()->json(['data' => $tobacco], Response::HTTP_OK);
    }

    // Create new tobacco usage
    public function store(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'tobacco_type' => 'required|in:cigarette,vape',
            'cigarettes_per_day' => 'nullable|integer|min:0',
            'vape_frequency' => 'nullable|integer|min:0',
            'puffs_per_day' => 'nullable|integer|min:0',
        ]);

        $tobacco = $journalEntry->tobacco()->create($validated);

        return response()->json(['data' => $tobacco], Response::HTTP_CREATED);
    }

    // Afficher une consommation de tabac spécifique
    public function show(JournalEntry $entreeJournal, Tobacco $tobacco)
    {
        return response()->json(['data' => $tobacco], Response::HTTP_OK);
    }

    // Mettre à jour une consommation de tabac
    public function update(Request $request, JournalEntry $entreeJournal, Tobacco $tobacco)
    {
        $validated = $request->validate([
            'tobacco_type' => 'sometimes|in:cigarette,vape',
            'cigarettes_per_day' => 'nullable|integer|min:0',
            'vape_frequency' => 'nullable|integer|min:0',
            'puffs_per_day' => 'nullable|integer|min:0',
        ]);

        $tobacco->update($validated);

        return response()->json(['data' => $tobacco], Response::HTTP_OK);
    }

    // Delete tobacco usage
    public function destroy(JournalEntry $journalEntry, Tobacco $tobacco)
    {
        $tobacco->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
