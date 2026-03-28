<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChronicDiseaseCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChronicDiseaseCatalogController extends Controller
{
    public function __construct(
        private readonly ChronicDiseaseCatalogService $chronicDiseaseCatalogService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->chronicDiseaseCatalogService->buildCatalog(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $this->chronicDiseaseCatalogService->saveEntry(
            $validated['name'],
            Auth::id(),
        );

        return response()->json([
            'message' => 'Catalogue maladies chroniques mis a jour.',
            'data' => $this->chronicDiseaseCatalogService->buildCatalog(),
        ]);
    }
}
