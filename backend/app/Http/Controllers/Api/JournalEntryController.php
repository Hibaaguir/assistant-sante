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
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $entries = JournalEntry::where('user_id', $user->id)
            ->with(['meals', 'physicalActivities', 'tobacco'])
            ->orderByDesc('entry_date')
            ->get();

        return response()->json([
            'message' => 'Journal entries retrieved successfully.',
            'data' => $entries,
        ]);
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id' => $user->id,
                'entry_date' => $validated['entry_date'],
            ],
            [
                'user_id' => $user->id,
                'entry_date' => $validated['entry_date'],
                'sleep' => $validated['sleep'] ?? null,
                'stress' => $validated['stress'] ?? null,
                'energy' => $validated['energy'] ?? null,
                'caffeine' => $validated['caffeine'] ?? null,
                'hydration' => $validated['hydration'] ?? null,
                'alcohol' => $validated['alcohol'] ?? false,
                'alcohol_glasses' => $validated['alcohol_glasses'] ?? null,
            ]
        );

        $this->syncRelations($entry, $validated, $request);

        return response()->json([
            'message' => 'Journal entry saved successfully.',
            'data' => $entry->load(['meals', 'physicalActivities', 'tobacco']),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Journal entry retrieved successfully.',
            'data' => $journalEntry->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $journalEntry->update([
            'entry_date' => $validated['entry_date'],
            'sleep' => $validated['sleep'] ?? null,
            'stress' => $validated['stress'] ?? null,
            'energy' => $validated['energy'] ?? null,
            'caffeine' => $validated['caffeine'] ?? null,
            'hydration' => $validated['hydration'] ?? null,
            'alcohol' => $validated['alcohol'] ?? false,
            'alcohol_glasses' => $validated['alcohol_glasses'] ?? null,
        ]);

        $this->syncRelations($journalEntry, $validated, $request);

        return response()->json([
            'message' => 'Journal entry updated successfully.',
            'data' => $journalEntry->fresh()->load(['meals', 'physicalActivities', 'tobacco']),
        ]);
    }

    public function destroy(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Journal entry deleted successfully.',
        ]);
    }

    private function syncRelations(JournalEntry $entry, array $validated, Request $request): void
    {
        // Sync meals
        if (isset($validated['meals']) && is_array($validated['meals'])) {
            $entry->meals()->delete();
            foreach ($validated['meals'] as $meal) {
                $entry->meals()->create([
                    'meal_type'   => $meal['meal_type'] ?? null,
                    'description' => $meal['description'] ?? null,
                    'calories'    => $meal['calories'] ?? null,
                ]);
            }
        }

        // Sync physical activity (one record per entry)
        $entry->physicalActivities()->delete();
        if (!empty($validated['activity_type'])) {
            $entry->physicalActivities()->create([
                'activity_type'    => $validated['activity_type'],
                'duration_minutes' => $validated['activity_duration'] ?? null,
                'intensity'        => $validated['intensity'] ?? 'medium',
            ]);
        }

        // Sync tobacco (one record per active type)
        $entry->tobacco()->delete();
        if ($validated['tobacco'] ?? false) {
            $tobaccoTypes = $validated['tobacco_types'] ?? [];
            if (!empty($tobaccoTypes['cigarette'])) {
                $entry->tobacco()->create([
                    'tobacco_type'       => 'cigarette',
                    'cigarettes_per_day' => $validated['cigarettes_per_day'] ?? null,
                ]);
            }
            if (!empty($tobaccoTypes['vape'])) {
                $entry->tobacco()->create([
                    'tobacco_type'  => 'vape',
                    'puffs_per_day' => $validated['vape_liquid_ml'] ?? null,
                ]);
            }
        }
    }
}