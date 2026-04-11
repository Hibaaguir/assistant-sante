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
    // ─────────────────────────────────────────────────────────────────────────
    // Return all journal entries for the current user, newest first
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $entries = JournalEntry::where('user_id', $request->user()->id)
            ->with(['meals', 'physicalActivities', 'tobacco'])
            ->orderByDesc('entry_date')
            ->get();

        return response()->json([
            'message' => 'Journal entries retrieved successfully.',
            'data'    => $entries,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create or update a journal entry for a given date
    //
    // If an entry already exists for that date, it gets updated.
    // If not, a new one is created.
    // ─────────────────────────────────────────────────────────────────────────
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Check if an entry already exists for this date (needed to preserve sugar_intake)
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
            'message' => 'Journal entry saved successfully.',
            'data'    => $entry->load(['meals', 'physicalActivities', 'tobacco']),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Return a single journal entry
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Journal entry retrieved successfully.',
            'data'    => $journalEntry->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update an existing journal entry
    // ─────────────────────────────────────────────────────────────────────────
    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
            'message' => 'Journal entry updated successfully.',
            'data'    => $journalEntry->fresh()->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete a journal entry
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $journalEntry->delete();

        return response()->json(['message' => 'Journal entry deleted successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Determine the final sugar_intake value to save
    //
    // We use array_key_exists (not isset) because the user may intentionally
    // send null to clear the value. If the field was not sent at all, we keep
    // the old value.
    // ─────────────────────────────────────────────────────────────────────────
    private function getSugarIntake(array $data, ?string $oldValue): ?string
    {
        // Field was not sent → keep the existing value
        if (!array_key_exists('sugar_intake', $data)) {
            return $oldValue;
        }

        // Field was sent → use the new value (trim it, return null if empty)
        $value = trim((string) $data['sugar_intake']);
        return $value !== '' ? $value : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Save meals, physical activity, and tobacco linked to a journal entry
    //
    // Each time this runs, the old records are deleted and replaced with
    // the new ones sent from the frontend.
    // ─────────────────────────────────────────────────────────────────────────
    private function syncEntryData(JournalEntry $entry, array $data): void
    {
        // Save meals
        if (isset($data['meals']) && is_array($data['meals'])) {
            $entry->meals()->delete();
            foreach ($data['meals'] as $meal) {
                $entry->meals()->create([
                    'meal_type'   => $meal['meal_type']   ?? null,
                    'description' => $meal['description'] ?? null,
                    'calories'    => $meal['calories']    ?? null,
                ]);
            }
        }

        // Save physical activity (only one per entry)
        $entry->physicalActivities()->delete();
        if (!empty($data['activity_type'])) {
            $entry->physicalActivities()->create([
                'activity_type'    => $data['activity_type'],
                'duration_minutes' => $data['activity_duration'] ?? null,
                'intensity'        => $data['intensity']         ?? 'medium',
            ]);
        }

        // Save tobacco records (one per type: cigarette, vape)
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