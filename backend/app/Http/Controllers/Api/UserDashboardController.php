<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\HealthProfile;
use App\Models\JournalEntry;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    // Retourne les signes vitaux jour par jour pour afficher le graphe en courbe
    public function vitalsChart(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = (int) $request->query('days', 7);
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();

        // Les 4 indicateurs que l'on veut afficher sur le graphe
        $fields = ['heart_rate', 'systolic_pressure', 'diastolic_pressure', 'oxygen_saturation'];

        // Récupérer toutes les mesures de l'utilisateur à partir de la date de début
        $rawVitals = VitalSigns::whereHas('healthData', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereDate('measured_at', '>=', $startDate)
        ->orderBy('measured_at')
        ->get();

        // Regrouper les mesures dans un tableau indexé par date
        // Résultat : ['2025-04-20' => [mesure1, mesure2], '2025-04-21' => [mesure3], ...]
        $vitalsByDate = [];
        foreach ($rawVitals as $vital) {
            if ($vital->measured_at) {
                $date                  = $vital->measured_at->toDateString();
                $vitalsByDate[$date][] = $vital;
            }
        }

        // Générer la liste de tous les jours de la période (du plus ancien au plus récent)
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = Carbon::today()->subDays($days - 1 - $i)->toDateString();
        }

        // Initialiser le résultat avec les dates comme étiquettes de l'axe X
        $chart = ['labels' => $dates];

        // Pour chaque indicateur, chercher la dernière valeur connue pour chaque jour
        foreach ($fields as $field) {
            $valeurs = [];

            foreach ($dates as $date) {
                $mesuresDuJour = $vitalsByDate[$date] ?? [];
                $valeur        = null;

                // Parcourir les mesures du jour du plus récent au plus ancien
                // et prendre la première valeur non nulle trouvée
                for ($j = count($mesuresDuJour) - 1; $j >= 0; $j--) {
                    if ($mesuresDuJour[$j]->{$field} !== null) {
                        $valeur = round((float) $mesuresDuJour[$j]->{$field}, 1);
                        break; // trouvé, on arrête la boucle
                    }
                }

                $valeurs[] = $valeur; // null si aucune mesure ce jour-là
            }

            $chart[$field] = $valeurs;
        }

        return response()->json(['data' => $chart]);
    }

    // Retourne les traitements actifs aujourd'hui pour le graphe camembert
    public function treatments(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $today  = Carbon::today()->toDateString();

        // Récupérer les traitements dont la période est en cours
        // (date début vide ou passée) ET (date fin vide ou future)
        $traitements = Treatment::with('treatmentCatalog')
            ->whereHas('healthData', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        // Extraire uniquement le type de chaque traitement
        $data = [];
        foreach ($traitements as $traitement) {
            $catalog = $traitement->treatmentCatalog;
            $type    = $catalog ? $catalog->treatment_type : 'Autre';
            $data[]  = ['type' => ucfirst(trim($type))];
        }

        return response()->json(['data' => $data]);
    }

    // Retourne tous les signes vitaux des N derniers jours (pour les graphes de comparaison)
    public function vitals(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = (int) $request->query('days', 30);
        $startDate = Carbon::today()->subDays($days - 1);

        $vitals = VitalSigns::whereHas('healthData', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('measured_at', '>=', $startDate)
        ->orderByDesc('measured_at')
        ->get();

        return response()->json(['data' => $vitals]);
    }

    // Retourne les analyses biologiques des 12 derniers mois
    public function labs(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $startDate = Carbon::today()->subMonths(12)->toDateString();

        $labs = AnalysisResult::whereHas('healthData', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('analysis_date', '>=', $startDate)
        ->orderByDesc('analysis_date')
        ->orderByDesc('id')
        ->get();

        return response()->json(['data' => $labs]);
    }

    // Retourne l'historique des prises de médicaments sur les 90 derniers jours
    public function treatmentChecks(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $startDate = Carbon::today()->subDays(89)->toDateString();

        $prises = TreatmentCheck::with('treatment.treatmentCatalog')
            ->where('user_id', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get();

        // Formater chaque prise pour le frontend
        $data = [];
        foreach ($prises as $prise) {
            $catalog = $prise->treatment ? $prise->treatment->treatmentCatalog : null;

            $data[] = [
                'check_date'      => $prise->check_date ? $prise->check_date->toDateString() : null,
                'medication_name' => $catalog ? $catalog->treatment_name : null,
                'taken'           => (bool) $prise->taken,
            ];
        }

        return response()->json(['data' => $data]);
    }

    // Retourne les entrées du journal sur 12 mois avec les activités physiques
    public function journalEntries(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $startDate = Carbon::today()->subMonths(12)->toDateString();

        $entries = JournalEntry::where('user_id', $userId)
            ->where('entry_date', '>=', $startDate)
            ->with(['physicalActivities'])
            ->orderByDesc('entry_date')
            ->get();

        return response()->json(['data' => $entries]);
    }

    // Retourne le poids initial et le poids actuel depuis le profil de santé
    public function weight(Request $request): JsonResponse
    {
        $userId  = $request->user()->id;
        $profile = HealthProfile::where('user_id', $userId)->first();

        // Si aucun profil trouvé, retourner null pour les deux valeurs
        $poidInitial = $profile ? $profile->initial_weight : null;
        $poidActuel  = $profile ? $profile->current_weight : null;

        return response()->json([
            'data' => [
                'initial_weight' => $poidInitial,
                'current_weight' => $poidActuel,
            ],
        ]);
    }
}
