<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Traitement;
use App\Models\ProfilSante;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TraitementController extends Controller
{
    // Récupérer tous les traitements d'un profil de santé
    public function index(ProfilSante $profilSante)
    {
        $traitements = $profilSante->traitements()->with('catalogueTraitement')->get();
        return response()->json(['data' => $traitements], Response::HTTP_OK);
    }

    // Créer un nouveau traitement
    public function store(Request $request, ProfilSante $profilSante)
    {
        $validated = $request->validate([
            'article_catalogue_id' => 'required|exists:catalogue_traitements,id',
            'dose' => 'nullable|string|max:120',
            'frequence' => 'nullable|string|max:120',
            'nombre_prises' => 'nullable|integer|min:1',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $traitement = $profilSante->traitements()->create($validated);

        return response()->json(['data' => $traitement->load('catalogueTraitement')], Response::HTTP_CREATED);
    }

    // Afficher un traitement spécifique
    public function show(ProfilSante $profilSante, Traitement $traitement)
    {
        return response()->json(['data' => $traitement->load('catalogueTraitement')], Response::HTTP_OK);
    }

    // Mettre à jour un traitement
    public function update(Request $request, ProfilSante $profilSante, Traitement $traitement)
    {
        $validated = $request->validate([
            'article_catalogue_id' => 'sometimes|exists:catalogue_traitements,id',
            'dose' => 'nullable|string|max:120',
            'frequence' => 'nullable|string|max:120',
            'nombre_prises' => 'nullable|integer|min:1',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $traitement->update($validated);

        return response()->json(['data' => $traitement->load('catalogueTraitement')], Response::HTTP_OK);
    }

    // Supprimer un traitement
    public function destroy(ProfilSante $profilSante, Traitement $traitement)
    {
        $traitement->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
