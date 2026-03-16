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
    // Récupérer toutes les entrées du journal de l'utilisateur connecté
    public function lister(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $entries = JournalEntry::where('user_id', $userId)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Entrees du journal recuperees avec succes.',
            'data' => $entries,
        ]);
    }

    // Enregistrer une nouvelle entrée ou mettre à jour celle du même jour
    public function enregistrer(StoreJournalEntryRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id' => $payload['user_id'],
                'entry_date' => $payload['entry_date'],
            ],
            $payload
        );

        return response()->json([
            'message' => 'Entree du journal enregistree avec succes.',
            'data' => $entry,
        ], 201);
    }

    // Afficher une entrée spécifique du journal
    public function afficher(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->autoriserEntree($journalEntry, $request);
        if ($error) {
            return $error;
        }

        return response()->json([
            'message' => 'Entree du journal recuperee avec succes.',
            'data' => $journalEntry,
        ]);
    }

    // Mettre à jour une entrée existante du journal
    public function mettreAJour(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->autoriserEntree($journalEntry, $request);
        if ($error) {
            return $error;
        }

        $journalEntry->update($request->validated());

        return response()->json([
            'message' => 'Entree du journal mise a jour avec succes.',
            'data' => $journalEntry->fresh(),
        ]);
    }

    // Supprimer une entrée du journal
    public function supprimer(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->autoriserEntree($journalEntry, $request);
        if ($error) {
            return $error;
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Entree du journal supprimee avec succes.',
        ]);
    }

    // Vérifier que l'entrée appartient bien à l'utilisateur connecté
    private function autoriserEntree(JournalEntry $entry, Request $request): ?JsonResponse
    {
        if ($entry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acces non autorise a cette entree du journal.',
            ], 403);
        }

        return null;
    }
}