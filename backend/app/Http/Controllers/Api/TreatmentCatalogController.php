<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCatalog;
use App\Services\TreatmentCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreatmentCatalogController extends Controller
{
    public function __construct(
        private readonly TreatmentCatalogService $treatmentCatalogService,
    ) {}

    // Returns all distinct medication types as a flat array of strings
    public function medicationTypes(): JsonResponse
    {
        $types = TreatmentCatalog::query()
            ->pluck('medication_type')
            ->filter(fn($type) => !is_null($type) && $type !== '')
            ->unique()
            ->sort()
            ->values();

        return response()->json($types);
    }

    // Returns all medication names for a given type as a flat array of strings
    public function medicationNames(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $names = TreatmentCatalog::query()
            ->where('medication_type', $type)
            ->pluck('medication_name')
            ->filter(fn($name) => !is_null($name) && $name !== '')
            ->unique()
            ->sort()
            ->values();

        return response()->json($names);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->treatmentCatalogService->buildCatalog(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->treatmentCatalogService->saveEntry(
            $validated['type'],
            $validated['name'] ?? null,
        );

        return response()->json([
            'message' => 'Treatment catalog updated.',
            'data' => $this->treatmentCatalogService->buildCatalog(),
        ]);
    }
}
