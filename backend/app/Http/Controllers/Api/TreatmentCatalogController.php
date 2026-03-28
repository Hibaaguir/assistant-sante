<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TreatmentCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentCatalogController extends Controller
{
    public function __construct(
        private readonly TreatmentCatalogService $treatmentCatalogService,
    ) {}

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
            Auth::id(),
        );

        return response()->json([
            'message' => 'Catalogue de traitements mis a jour.',
            'data' => $this->treatmentCatalogService->buildCatalog(),
        ]);
    }
}
