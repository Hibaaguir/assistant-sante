<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TacheBienEtre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TacheBienEtreController extends Controller
{
    public function lister(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:120'],
            'categorie' => ['nullable', Rule::in(['toutes', 'bien-etre', 'sante', 'fitness', 'nutrition'])],
        ]);

        $requete = TacheBienEtre::query()->where('user_id', $userId);

        $recherche = trim((string) ($filtres['recherche'] ?? ''));
        if ($recherche !== '') {
            $requete->where('titre', 'like', "%{$recherche}%");
        }

        $categorie = $filtres['categorie'] ?? 'toutes';
        if ($categorie !== 'toutes') {
            $requete->where('categorie', $categorie);
        }

        $taches = $requete
            ->orderBy('est_complete')
            ->orderByDesc('id')
            ->get();

        $statsGlobales = TacheBienEtre::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN est_complete = 1 THEN 1 ELSE 0 END) as completes')
            ->first();

        $compteursParCategorie = TacheBienEtre::query()
            ->where('user_id', $userId)
            ->selectRaw('categorie, COUNT(*) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie');

        return response()->json([
            'message' => 'Taches recuperees avec succes.',
            'data' => $taches,
            'meta' => [
                'total' => (int) ($statsGlobales?->total ?? 0),
                'completes' => (int) ($statsGlobales?->completes ?? 0),
                'categories' => [
                    'bien-etre' => (int) ($compteursParCategorie['bien-etre'] ?? 0),
                    'sante' => (int) ($compteursParCategorie['sante'] ?? 0),
                    'fitness' => (int) ($compteursParCategorie['fitness'] ?? 0),
                    'nutrition' => (int) ($compteursParCategorie['nutrition'] ?? 0),
                ],
            ],
        ]);
    }

    public function creer(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'titre' => ['required', 'string', 'min:2', 'max:180'],
            'categorie' => ['required', Rule::in(['bien-etre', 'sante', 'fitness', 'nutrition'])],
            'date_echeance' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $payload['user_id'] = $request->user()->id;
        $payload['est_complete'] = false;
        $payload['terminee_le'] = null;

        $tache = TacheBienEtre::query()->create($payload);

        return response()->json([
            'message' => 'Tache creee avec succes.',
            'data' => $tache,
        ], 201);
    }

    public function mettreAJour(Request $request, TacheBienEtre $tacheBienEtre): JsonResponse
    {
        $erreur = $this->autoriserTache($request, $tacheBienEtre);
        if ($erreur) {
            return $erreur;
        }

        $payload = $request->validate([
            'titre' => ['required', 'string', 'min:2', 'max:180'],
            'categorie' => ['required', Rule::in(['bien-etre', 'sante', 'fitness', 'nutrition'])],
            'date_echeance' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $tacheBienEtre->update($payload);

        return response()->json([
            'message' => 'Tache mise a jour avec succes.',
            'data' => $tacheBienEtre->fresh(),
        ]);
    }

    public function basculerStatut(Request $request, TacheBienEtre $tacheBienEtre): JsonResponse
    {
        $erreur = $this->autoriserTache($request, $tacheBienEtre);
        if ($erreur) {
            return $erreur;
        }

        $nouveauStatut = ! $tacheBienEtre->est_complete;

        $tacheBienEtre->update([
            'est_complete' => $nouveauStatut,
            'terminee_le' => $nouveauStatut ? now() : null,
        ]);

        return response()->json([
            'message' => $nouveauStatut ? 'Tache marquee comme complete.' : 'Tache marquee comme non complete.',
            'data' => $tacheBienEtre->fresh(),
        ]);
    }

    public function supprimer(Request $request, TacheBienEtre $tacheBienEtre): JsonResponse
    {
        $erreur = $this->autoriserTache($request, $tacheBienEtre);
        if ($erreur) {
            return $erreur;
        }

        $tacheBienEtre->delete();

        return response()->json([
            'message' => 'Tache supprimee avec succes.',
        ]);
    }

    private function autoriserTache(Request $request, TacheBienEtre $tache): ?JsonResponse
    {
        if ($tache->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acces non autorise a cette tache.',
            ], 403);
        }

        return null;
    }
}
