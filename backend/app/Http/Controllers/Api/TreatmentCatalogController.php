<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TraitementCatalogueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TreatmentCatalogController extends Controller
{
    public function __construct(
        private readonly TraitementCatalogueService $traitementCatalogueService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->traitementCatalogueService->buildCatalog(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->traitementCatalogueService->saveEntry(
            $validated['type'],
            $validated['name'] ?? null,
            Auth::id(),
        );

        return response()->json([
            'message' => 'Catalogue de traitements mis a jour.',
            'data' => $this->traitementCatalogueService->buildCatalog(),
        ]);
    }
}
