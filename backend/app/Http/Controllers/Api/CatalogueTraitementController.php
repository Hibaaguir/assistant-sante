<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogueTraitement;
use Illuminate\Http\Request;

class CatalogueTraitementController extends Controller
{
    // GET /api/catalogue-traitements/types
    public function types()
    {
        $types = CatalogueTraitement::query()->distinct()->pluck('type');
        return response()->json($types);
    }

    // GET /api/catalogue-traitements/noms?type=XXX
    public function noms(Request $request)
    {
        $type = $request->query('type');
        $noms = CatalogueTraitement::where('type', $type)->get(['id', 'nom']);
        return response()->json($noms);
    }
}
