<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJournalEntryRequest;
use App\Http\Requests\Api\UpdateJournalEntryRequest;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gère les entrées du journal de santé de l'utilisateur
 * 
 * Responsabilités:
 * - CRUD complet des entrées du journal
 * - Fusion automatique des entrées du même jour
 * - Vérification des droits d'accès
 */
class JournalEntryController extends Controller
{
    // Récupérer toutes les entrées du journal
    public function index(Request $request): JsonResponse
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
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        // Récupérer les données validées
        $payload = $this->normaliserPayload($request->validated());
        $payload['user_id'] = $request->user()->id;

        // Créer ou mettre à jour l'entrée du journal
        $entry = JournalEntry::updateOrCreate(
            [
                'user_id' => $payload['user_id'],
                'entry_date' => $payload['entry_date'],
            ],
            $payload
        );

        $isNew = $entry->wasRecentlyCreated;

        return response()->json([
            'message' => 'Entree du journal ' . ($isNew ? 'enregistree' : 'mise a jour') . ' avec succes.',
            'data' => $entry,
        ], $isNew ? 201 : 200);
    }

    // Afficher une entrée spécifique du journal
    public function show(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->authorizeEntry($journalEntry, $request);
        // Vérifier que l'entrée appartient à l'utilisateur
        if ($error) {
            return $error;
        }

        return response()->json([
            'message' => 'Entree du journal recuperee avec succes.',
            'data' => $journalEntry,
        ]);
    }

    // Mettre à jour une entrée existante du journal
    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->authorizeEntry($journalEntry, $request);
        // Vérifier que l'entrée appartient à l'utilisateur
        if ($error) {
            return $error;
        }

        $journalEntry->update($this->normaliserPayload($request->validated()));

        return response()->json([
            'message' => 'Entree du journal mise a jour avec succes.',
            'data' => $journalEntry->fresh(),
        ]);
    }

    // Supprimer une entrée du journal
    public function destroy(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        $error = $this->authorizeEntry($journalEntry, $request);
        // Vérifier que l'entrée appartient à l'utilisateur
        if ($error) {
            return $error;
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Entree du journal supprimee avec succes.',
        ]);
    }

    // Vérifier que l'entrée appartient bien à l'utilisateur connecté
    private function authorizeEntry(JournalEntry $entry, Request $request): ?JsonResponse
    {
        // Empêcher l'accès non autorisé aux entrées d'autres utilisateurs
        if ($entry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acces non autorise a cette entree du journal.',
            ], 403);
        }

        return null;
    }

    private function normaliserPayload(array $payload): array
    {
        $caloriesDepuisMeals = null;

        if (array_key_exists('meals', $payload)) {
            [$payload['meals'], $caloriesDepuisMeals] = $this->retirerCaloriesDesMeals($payload['meals']);
        }

        if (array_key_exists('calories', $payload)) {
            $payload['calories'] = $payload['calories'] === null
                ? null
                : $this->bornerCalories($payload['calories']);

            return $payload;
        }

        if ($caloriesDepuisMeals !== null) {
            $payload['calories'] = $caloriesDepuisMeals;
        }

        return $payload;
    }

    private function retirerCaloriesDesMeals(mixed $meals): array
    {
        if (! is_array($meals)) {
            return [$meals, 0];
        }

        $total = 0;
        $sanitizedMeals = [];

        foreach ($meals as $meal) {
            if (! is_array($meal)) {
                $sanitizedMeals[] = $meal;
                continue;
            }

            $rawCalories = $meal['calories'] ?? null;
            if ($rawCalories === null || $rawCalories === '') {
                unset($meal['calories']);
                $sanitizedMeals[] = $meal;
                continue;
            }

            $total += $this->bornerCalories($rawCalories);
            if ($total >= 65535) {
                $total = 65535;
            }

            unset($meal['calories']);
            $sanitizedMeals[] = $meal;
        }

        return [$sanitizedMeals, $total];
    }

    private function bornerCalories(mixed $value): int
    {
        $parsed = is_numeric($value) ? (int) round((float) $value) : 0;
        if ($parsed < 0) {
            return 0;
        }

        return min($parsed, 65535);
    }
}