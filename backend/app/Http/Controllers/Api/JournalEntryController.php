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
        $entries = JournalEntry::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Journal entries fetched successfully.',
            'data' => $entries,
        ]);
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'entry_date' => $payload['entry_date'],
            ],
            $payload
        );

        return response()->json([
            'message' => 'Journal entry saved successfully.',
            'data' => $entry,
        ], 201);
    }

    public function show(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this journal entry.',
            ], 403);
        }

        return response()->json([
            'message' => 'Journal entry fetched successfully.',
            'data' => $journalEntry,
        ]);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this journal entry.',
            ], 403);
        }

        $journalEntry->update($request->validated());

        return response()->json([
            'message' => 'Journal entry updated successfully.',
            'data' => $journalEntry->fresh(),
        ]);
    }

    public function destroy(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this journal entry.',
            ], 403);
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Journal entry deleted successfully.',
        ]);
    }
}

