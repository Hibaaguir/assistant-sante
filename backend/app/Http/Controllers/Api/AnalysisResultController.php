<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalysisResultController extends Controller
{
    // Get all analysis results for a user
    public function index(User $user)
    {
        $results = $user->analysisResults()->latest('analysis_date')->get();
        return response()->json(['data' => $results], Response::HTTP_OK);
    }

    // Create a new analysis result
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'analysis_type' => 'required|string|max:120',
            'analysis_result' => 'nullable|string|max:120',
            'value' => 'required|numeric',
            'normal_range' => 'nullable|string|max:30',
            'analysis_date' => 'required|date',
        ]);

        $result = $user->analysisResults()->create($validated);

        return response()->json(['data' => $result], Response::HTTP_CREATED);
    }

    // Show a specific analysis result
    public function show(User $user, AnalysisResult $analysisResult)
    {
        return response()->json(['data' => $analysisResult], Response::HTTP_OK);
    }

    // Update an analysis result
    public function update(Request $request, User $user, AnalysisResult $analysisResult)
    {
        $validated = $request->validate([
            'analysis_type' => 'sometimes|string|max:120',
            'analysis_result' => 'nullable|string|max:120',
            'value' => 'sometimes|numeric',
            'normal_range' => 'nullable|string|max:30',
            'analysis_date' => 'sometimes|date',
        ]);

        $analysisResult->update($validated);

        return response()->json(['data' => $analysisResult], Response::HTTP_OK);
    }

    // Delete an analysis result
    public function destroy(User $user, AnalysisResult $analysisResult)
    {
        $analysisResult->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
