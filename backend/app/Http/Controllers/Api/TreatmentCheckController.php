<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCheck;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TreatmentCheckController extends Controller
{
    // Get all treatment checks for a treatment
    public function index(Treatment $treatment)
    {
        $checks = $treatment->checks()->get();
        return response()->json(['data' => $checks], Response::HTTP_OK);
    }

    // Create a treatment check
    public function store(Request $request, Treatment $treatment)
    {
        $validated = $request->validate([
            'check_date'     => 'required|date',
            'status'         => 'sometimes|in:taken,not_taken,pending',
            'medication_key' => 'nullable|string|max:120',
            'notes'          => 'nullable|string',
        ]);

        $check = $treatment->checks()->create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $check], Response::HTTP_CREATED);
    }

    // Show a specific treatment check
    public function show(Treatment $treatment, TreatmentCheck $treatmentCheck)
    {
        return response()->json(['data' => $treatmentCheck], Response::HTTP_OK);
    }

    // Update a treatment check
    public function update(Request $request, Treatment $treatment, TreatmentCheck $treatmentCheck)
    {
        $validated = $request->validate([
            'check_date'     => 'sometimes|date',
            'status'         => 'sometimes|in:taken,not_taken,pending',
            'medication_key' => 'nullable|string|max:120',
            'notes'          => 'nullable|string',
        ]);

        $treatmentCheck->update($validated);

        return response()->json(['data' => $treatmentCheck], Response::HTTP_OK);
    }

    // Delete a treatment check
    public function destroy(Treatment $treatment, TreatmentCheck $treatmentCheck)
    {
        $treatmentCheck->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
