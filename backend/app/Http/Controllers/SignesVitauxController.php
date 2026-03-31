<?php

namespace App\Http\Controllers;

use App\Models\SignesVitaux;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SignesVitauxController extends Controller
{
    // Récupérer tous les signes vitaux d'un utilisateur
    public function index(Utilisateur $utilisateur)
    {
        $signesVitaux = $utilisateur->signesVitaux()->latest('mesure_a')->get();
        return response()->json(['data' => $signesVitaux], Response::HTTP_OK);
    }

    // Créer un nouveau signe vital
    public function store(Request $request, Utilisateur $utilisateur)
    {
        $validated = $request->validate([
            'mesure_a' => 'required|date_format:Y-m-d H:i:s',
            'frequence_cardiaque' => 'nullable|integer|min:0|max:300',
            'pression_systolique' => 'nullable|integer|min:0|max:300',
            'pression_diastolique' => 'nullable|integer|min:0|max:300',
            'saturation_oxygene' => 'nullable|numeric|min:0|max:100',
        ]);

        $signesVitaux= $utilisateur->signesVitaux()->create($validated);

        return response()->json(['data' => $signesVitaux], Response::HTTP_CREATED);
    }

    // Afficher un signe vital spécifique
    public function show(Utilisateur $utilisateur, SignesVitaux $signesVitaux)
    {
        return response()->json(['data' => $signesVitaux], Response::HTTP_OK);
    }

    // Mettre à jour un signe vital
    public function update(Request $request, Utilisateur $utilisateur, SignesVitaux $signesVitaux)
    {
        $validated = $request->validate([
            'mesure_a' => 'sometimes|date_format:Y-m-d H:i:s',
            'frequence_cardiaque' => 'nullable|integer|min:0|max:300',
            'pression_systolique' => 'nullable|integer|min:0|max:300',
            'pression_diastolique' => 'nullable|integer|min:0|max:300',
            'saturation_oxygene' => 'nullable|numeric|min:0|max:100',
        ]);

        $signesVitaux->update($validated);

        return response()->json(['data' => $signesVitaux], Response::HTTP_OK);
    }

    // Supprimer un signe vital
    public function destroy(Utilisateur $utilisateur, SignesVitaux $signesVitaux)
    {
        $signesVitaux->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
