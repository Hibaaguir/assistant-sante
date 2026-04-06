<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhysicalActivity;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PhysicalActivityController extends Controller
{
    // Get all physical activities for a journal entry
    public function index(JournalEntry $journalEntry)
    {
        $activities = $journalEntry->physicalActivities()->get();
        return response()->json(['data' => $activities], Response::HTTP_OK);
    }

    // Create a new physical activity
    public function store(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'activity_type' => 'required|string|max:120',
            'activity_duration' => 'nullable|integer|min:0',
            'intensity' => 'required|in:low,medium,high',
        ]);

        // Add the foreign key expected by the model
        $validated['journal_entry_id'] = $journalEntry->id;

        $activity = PhysicalActivity::create($validated);

        return response()->json(['data' => $activity], Response::HTTP_CREATED);
    }

    // Show a specific physical activity
    public function show(JournalEntry $journalEntry, PhysicalActivity $physicalActivity)
    {
        return response()->json(['data' => $physicalActivity], Response::HTTP_OK);
    }

    // Update a physical activity
    public function update(Request $request, JournalEntry $journalEntry, PhysicalActivity $physicalActivity)
    {
        $validated = $request->validate([
            'activity_type' => 'sometimes|string|max:120',
            'activity_duration' => 'nullable|integer|min:0',
            'intensity' => 'sometimes|in:low,medium,high',
        ]);

        $physicalActivity->update($validated);

        return response()->json(['data' => $physicalActivity], Response::HTTP_OK);
    }

    // Delete a physical activity
    public function destroy(JournalEntry $journalEntry, PhysicalActivity $physicalActivity)
    {
        $physicalActivity->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
