<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AllergyCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllergyCatalogController extends Controller
{
    public function __construct(
        private readonly AllergyCatalogService $allergyCatalogService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->allergyCatalogService->buildCatalog(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $this->allergyCatalogService->saveEntry(
            $validated['name'],
            Auth::id(),
        );

        return response()->json([
            'message' => 'Catalogue allergies mis a jour.',
            'data' => $this->allergyCatalogService->buildCatalog(),
        ]);
    }
}
