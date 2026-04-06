<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MealController extends Controller
{
    // Get all meals for a journal entry
    public function index(JournalEntry $journalEntry)
    {
        $meals = $journalEntry->meals()->get();
        return response()->json(['data' => $meals], Response::HTTP_OK);
    }

    // Create a new meal
    public function store(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer|min:0',
            'sugar_intake' => 'nullable|string',
        ]);

        $meal = $journalEntry->meals()->create($validated);

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

        return response()->json(['data' => $meal], Response::HTTP_OK);
    }

    // Delete a meal
    public function destroy(JournalEntry $journalEntry, Meal $meal)
    {
        $meal->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
