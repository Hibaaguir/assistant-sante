<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VitalSigns;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VitalSignsController extends Controller
{
    // Get all vital signs for a user
    public function index(User $user)
    {
        $vitalSigns = $user->vitalSigns()->latest('measured_at')->get();
        return response()->json(['data' => $vitalSigns], Response::HTTP_OK);
    }

    // Create a new vital sign
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'measured_at' => 'required|date_format:Y-m-d H:i:s',
            'heart_rate' => 'nullable|integer|min:0|max:300',
            'systolic_pressure' => 'nullable|integer|min:0|max:300',
            'diastolic_pressure' => 'nullable|integer|min:0|max:300',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
        ]);

        $vitalSigns = $user->vitalSigns()->create($validated);

        return response()->json(['data' => $vitalSigns], Response::HTTP_CREATED);
    }

    // Show a specific vital sign
    public function show(User $user, VitalSigns $vitalSigns)
    {
        return response()->json(['data' => $vitalSigns], Response::HTTP_OK);
    }

    // Update a vital sign
    public function update(Request $request, User $user, VitalSigns $vitalSigns)
    {
        $validated = $request->validate([
            'measured_at' => 'sometimes|date_format:Y-m-d H:i:s',
            'heart_rate' => 'nullable|integer|min:0|max:300',
            'systolic_pressure' => 'nullable|integer|min:0|max:300',
            'diastolic_pressure' => 'nullable|integer|min:0|max:300',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
        ]);

        $vitalSigns->update($validated);

        return response()->json(['data' => $vitalSigns], Response::HTTP_OK);
    }

    // Delete a vital sign
    public function destroy(User $user, VitalSigns $vitalSigns)
    {
        $vitalSigns->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

