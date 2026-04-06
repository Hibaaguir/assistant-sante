<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use App\Models\HealthProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TreatmentController extends Controller
{
    // Get all treatments for a health profile
    public function index(HealthProfile $healthProfile)
    {
        // Treatments are now associated with users, not health profiles
        $treatments = $healthProfile->user->treatments()->with('treatmentCatalog')->get();
        return response()->json(['data' => $treatments], Response::HTTP_OK);
    }

    // Create a new treatment
    public function store(Request $request, HealthProfile $healthProfile)
    {
        $validated = $request->validate([
            'treatment_catalog_id' => 'required|exists:treatment_catalogs,id',
            'dose' => 'nullable|string|max:120',
            'frequency' => 'nullable|string|max:120',
            'doses_per_day' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Add user_id to the validated data
        $validated['user_id'] = $healthProfile->user_id;

        $treatment = Treatment::create($validated);

        return response()->json(['data' => $treatment->load('treatmentCatalog')], Response::HTTP_CREATED);
    }

    // Show a specific treatment
    public function show(HealthProfile $healthProfile, Treatment $treatment)
    {
        return response()->json(['data' => $treatment->load('treatmentCatalog')], Response::HTTP_OK);
    }

    // Update a treatment
    public function update(Request $request, HealthProfile $healthProfile, Treatment $treatment)
    {
        $validated = $request->validate([
            'treatment_catalog_id' => 'sometimes|exists:treatment_catalogs,id',
            'dose' => 'nullable|string|max:120',
            'frequency' => 'nullable|string|max:120',
            'doses_per_day' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $treatment->update($validated);

        return response()->json(['data' => $treatment->load('treatmentCatalog')], Response::HTTP_OK);
    }

    // Delete a treatment
    public function destroy(HealthProfile $healthProfile, Treatment $treatment)
    {
        $treatment->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
