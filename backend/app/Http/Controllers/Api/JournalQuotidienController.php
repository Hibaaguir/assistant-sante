<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJournalQuotidienRequest;
use App\Http\Requests\Api\UpdateJournalQuotidienRequest;
use App\Models\JournalQuotidien;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JournalQuotidienController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $utilisateur = $request->user();
        $entries = JournalQuotidien::where('id_utilisateur', $utilisateur->id)
            ->orderByDesc('date_entree')
            ->get();

        return response()->json([
            'message' => 'Journal entries retrieved successfully.',
            'data' => $entries,
        ]);
    }

    public function store(StoreJournalQuotidienRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $utilisateur = $request->user();

        $entry = JournalQuotidien::updateOrCreate(
            [
                'id_utilisateur' => $utilisateur->id,
                'date_entree' => $validated['entry_date'],
            ],
            [
                'id_utilisateur' => $utilisateur->id,
                'date_entree' => $validated['entry_date'],
                'sommeil' => $validated['sleep'] ?? null,
                'stress' => $validated['stress'] ?? null,
                'energie' => $validated['energy'] ?? null,
                'cafeine' => $validated['caffeine'] ?? null,
                'hydratation' => $validated['hydration'] ?? null,
                'alcool' => $validated['alcohol'] ?? false,
                'nb_verres_alcool' => $validated['alcohol_drinks'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Journal entry saved successfully.',
            'data' => $entry,
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, JournalQuotidien $journalQuotidien): JsonResponse
    {
        if ($journalQuotidien->id_utilisateur !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Journal entry retrieved successfully.',
            'data' => $journalQuotidien,
        ]);
    }

    public function update(UpdateJournalQuotidienRequest $request, JournalQuotidien $journalQuotidien): JsonResponse
    {
        if ($journalQuotidien->id_utilisateur !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $journalQuotidien->update([
            'date_entree' => $validated['entry_date'],
            'sommeil' => $validated['sleep'] ?? null,
            'stress' => $validated['stress'] ?? null,
            'energie' => $validated['energy'] ?? null,
            'cafeine' => $validated['caffeine'] ?? null,
            'hydratation' => $validated['hydration'] ?? null,
            'alcool' => $validated['alcohol'] ?? false,
            'nb_verres_alcool' => $validated['alcohol_drinks'] ?? null,
        ]);

        return response()->json([
            'message' => 'Journal entry updated successfully.',
            'data' => $journalQuotidien->fresh(),
        ]);
    }

    public function destroy(Request $request, JournalQuotidien $journalQuotidien): JsonResponse
    {
        if ($journalQuotidien->id_utilisateur !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $journalQuotidien->delete();

        return response()->json([
            'message' => 'Journal entry deleted successfully.',
        ]);
    }
}