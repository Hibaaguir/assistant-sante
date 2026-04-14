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
    // Récupérer toutes les entrées du journal de l'utilisateur (plus récentes en premier)
    public function index(Request $request): JsonResponse
    {
        $entries = JournalEntry::where('user_id', $request->user()->id)
            ->with(['meals', 'physicalActivities', 'tobacco'])
            ->orderByDesc('entry_date')
            ->get();

        return response()->json([
            'message' => 'Entrées du journal récupérées avec succès.',
            'data'    => $entries,
        ]);
    }

    // Enregistrer l'entrée du journal
    // Vérifier l'entrée existante si elle doit être mise à jour
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Vérifier si une entrée existe déjà pour cette date
        $existingEntry = JournalEntry::where('user_id', $user->id)
            ->where('entry_date', $data['entry_date'])
            ->first();

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id'    => $user->id,
                'entry_date' => $data['entry_date'],
            ],
            [
                'sleep'          => $data['sleep']           ?? null,
                'stress'         => $data['stress']          ?? null,
                'energy'         => $data['energy']          ?? null,
                'caffeine'       => $data['caffeine']        ?? null,
                'hydration'      => $data['hydration']       ?? null,
                'sugar_intake'   => $this->getSugarIntake($data, $existingEntry?->sugar_intake),
                'alcohol'        => $data['alcohol']         ?? false,
                'alcohol_glasses'=> $data['alcohol_glasses'] ?? null,
            ]
        );

        $this->syncEntryData($entry, $data);

        return response()->json([
            'message' => 'Entrée du journal enregistrée avec succès.',
            'data'    => $entry->load(['meals', 'physicalActivities', 'tobacco']),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    // Récupérer une seule entrée du journal
    public function show(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        return response()->json([
            'message' => 'Entrée du journal récupérée avec succès.',
            'data'    => $journalEntry->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    // Mettre à jour une entrée du journal existante
    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $data = $request->validated();

        $updatableFields = [
            'entry_date',
            'sleep',
            'stress',
            'energy',
            'caffeine',
            'hydration',
            'alcohol',
            'alcohol_glasses',
        ];

        $payload = [];
        foreach ($updatableFields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('sugar_intake', $data)) {
            $payload['sugar_intake'] = $this->getSugarIntake($data, $journalEntry->sugar_intake);
        }

        if (!empty($payload)) {
            $journalEntry->update($payload);
        }

        $this->syncEntryData($journalEntry, $data);

        return response()->json([
            'message' => 'Entrée du journal mise à jour avec succès.',
            'data'    => $journalEntry->fresh()->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    // Supprimer une entrée du journal
    public function destroy(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $journalEntry->delete();

        return response()->json(['message' => 'Entrée du journal supprimée avec succès.']);
    }

    // Déterminer la valeur de sugar_intake à enregistrer
    // Utiliser array_key_exists pour permettre l'envoi de null
    private function getSugarIntake(array $data, ?string $oldValue): ?string
    {
        // Le champ n'a pas été envoyé, garder l'ancienne valeur
        if (!array_key_exists('sugar_intake', $data)) {
            return $oldValue;
        }

        // Le champ a été envoyé, utiliser la nouvelle valeur
        $value = trim((string) $data['sugar_intake']);
        return $value !== '' ? $value : null;
    }

    // Synchroniser les données d'entrée (repas, activité, tabac)
    private function syncEntryData(JournalEntry $entry, array $data): void
    {
        // Enregistrer les repas
        if (isset($data['meals']) && is_array($data['meals'])) {
            $entry->meals()->delete();
            foreach ($data['meals'] as $meal) {
                $mealType = trim((string) ($meal['meal_type'] ?? ''));
                $entry->meals()->create([
                    'meal_type'   => $mealType !== '' ? $mealType : 'lunch',
                    'description' => $meal['description'] ?? null,
                    'calories'    => $meal['calories']    ?? null,
                ]);
            }
        }

        // Enregistrer l'activité physique (une seule par entrée)
        $entry->physicalActivities()->delete();
        if (!empty($data['activity_type'])) {
            $intensity = trim((string) ($data['intensity'] ?? ''));
            $entry->physicalActivities()->create([
                'activity_type'    => $data['activity_type'],
                'duration_minutes' => $data['activity_duration'] ?? null,
                'intensity'        => $intensity !== '' ? $intensity : 'medium',
            ]);
        }

        // Enregistrer le tabac
        $entry->tobacco()->delete();
        if ($data['tobacco'] ?? false) {
            $types = $data['tobacco_types'] ?? [];

            if (!empty($types['cigarette'])) {
                $entry->tobacco()->create([
                    'tobacco_type'       => 'cigarette',
                    'cigarettes_per_day' => $data['cigarettes_per_day'] ?? null,
                ]);
            }

            if (!empty($types['vape'])) {
                $entry->tobacco()->create([
                    'tobacco_type'  => 'vape',
                    'puffs_per_day' => $data['vape_liquid_ml'] ?? null,
                ]);
            }
        }
    }
}