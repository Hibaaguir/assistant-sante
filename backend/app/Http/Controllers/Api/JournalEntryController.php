<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJournalEntryRequest;
use App\Http\Requests\Api\UpdateJournalEntryRequest;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    public function lister(Request $request): JsonResponse
    {
        $entries = JournalEntry::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Entrees du journal recuperees avec succes.',
            'data' => $entries,
        ]);
    }

    public function enregistrer(StoreJournalEntryRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $entry = JournalEntry::updateOrCreate(
            ['user_id' => $payload['user_id'], 'entry_date' => $payload['entry_date']],
            $payload
        );

        return response()->json([
            'message' => 'Entree du journal enregistree avec succes.',
            'data' => $entry,
        ], 201);
    }

    public function afficher(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($error = $this->autoriserEntree($journalEntry, $request)) return $error;

        return response()->json([
            'message' => 'Entree du journal recuperee avec succes.',
            'data' => $journalEntry,
        ]);
    }

    public function mettreAJour(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($error = $this->autoriserEntree($journalEntry, $request)) return $error;

        $journalEntry->update($request->validated());

        return response()->json([
            'message' => 'Entree du journal mise a jour avec succes.',
            'data' => $journalEntry->fresh(),
        ]);
    }

    public function supprimer(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($error = $this->autoriserEntree($journalEntry, $request)) return $error;

        $journalEntry->delete();

        return response()->json([
            'message' => 'Entree du journal supprimee avec succes.',
        ]);
    }

    private function autoriserEntree(JournalEntry $entry, Request $request): ?JsonResponse
    {
        if ($entry->user_id !== $request->user()->id) {
            return response()->json(['message' => "Acces non autorise a cette entree du journal."], 403);
        }
        return null;
    }
}
