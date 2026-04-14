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

    // Retourner tous les types de médicaments distincts sous forme de tableau plat
    public function medicationTypes(): JsonResponse
    {
        $types = TreatmentCatalog::query()
            ->pluck('treatment_type')
            ->filter(fn($type) => !is_null($type) && $type !== '')
            ->unique()
            ->sort()
            ->values();

        return response()->json($types);
    }

    // Retourner tous les noms de médicaments pour un type donné
    public function medicationNames(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $names = TreatmentCatalog::query()
            ->where('treatment_type', $type)
            ->pluck('treatment_name')
            ->filter(fn($name) => !is_null($name) && $name !== '')
            ->unique()
            ->sort()
            ->values();

        return response()->json($names);
    }

    // Récupérer le catalogue complet
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->treatmentCatalogService->buildCatalog(),
        ]);
    }

    // Ajouter une entrée au catalogue de traitement
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
            'message' => 'Catalogue de traitement mis à jour.',
            'data' => $this->treatmentCatalogService->buildCatalog(),
        ]);
    }
}
