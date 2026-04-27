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
    // HealthChart — séries de signes vitaux pour le graphe en courbe
    public function vitalsChart(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = (int) $request->query('days', 7);
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $fields    = ['heart_rate', 'systolic_pressure', 'diastolic_pressure', 'oxygen_saturation'];

        $vitals = VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->whereDate('measured_at', '>=', $startDate)
            ->orderBy('measured_at')
            ->get()
            ->groupBy(fn ($v) => $v->measured_at?->toDateString());

        $dates = collect(range(0, $days - 1))
            ->map(fn ($i) => Carbon::today()->subDays($days - 1 - $i)->toDateString());

        $chart = ['labels' => $dates->values()];

        foreach ($fields as $field) {
            $chart[$field] = $dates->map(function ($date) use ($vitals, $field) {
                $row = ($vitals[$date] ?? collect())->sortByDesc('id')->first(fn ($v) => $v->{$field} !== null);
                return $row ? round((float) $row->{$field}, 1) : null;
            })->values();
        }

        return response()->json(['data' => $chart]);
    }

    // TreatmentPieChart — traitements actifs aujourd'hui regroupés par type
    public function treatments(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $today  = Carbon::today()->toDateString();

        $data = Treatment::with('treatmentCatalog')
            ->whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->get()
            ->map(fn ($t) => [
                'type' => ucfirst(trim($t->treatmentCatalog?->treatment_type ?? 'Autre')),
            ]);

        return response()->json(['data' => $data]);
    }

    // LastVitalsRow + VitalSignsComparisonChart + VitalSignsProgressiveLine
    public function vitals(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $days      = (int) $request->query('days', 30);
        $startDate = Carbon::today()->subDays($days - 1);

        $vitals = VitalSigns::whereHas('healthData', fn ($q) => $q->where('user_id', $userId))
            ->where('measured_at', '>=', $startDate)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json(['data' => $vitals]);
    }

    // LabsDistributionChart — analyses biologiques
    public function labs(Request $request): JsonResponse
    {
        $startDate = Carbon::today()->subMonths(12)->toDateString();

        $labs = AnalysisResult::whereHas('healthData', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('analysis_date', '>=', $startDate)
            ->orderByDesc('analysis_date')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $labs]);
    }

    // TreatmentMonthlyChart — prises et oublis de médicaments
    public function treatmentChecks(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $startDate = Carbon::today()->subDays(89)->toDateString();

        $data = TreatmentCheck::with('treatment.treatmentCatalog')
            ->where('user_id', $userId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date')
            ->get()
            ->map(fn ($c) => [
                'check_date'      => $c->check_date?->toDateString(),
                'medication_name' => $c->treatment?->treatmentCatalog?->treatment_name,
                'taken'           => (bool) $c->taken,
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    // ActivityDistributionChart + Top3ActivitiesChart + HydrationChart + SleepTrendsChart
    // + LastVitalsRow (uniquement pour la dernière activité physique)
    // Limité à 12 mois (période max utilisée par SleepTrendsChart)
    // Seule la relation physicalActivities est chargée (meals et tobacco non utilisés dans le dashboard)
    public function journalEntries(Request $request): JsonResponse
    {
        $startDate = Carbon::today()->subMonths(12)->toDateString();

        $entries = JournalEntry::where('user_id', $request->user()->id)
            ->where('entry_date', '>=', $startDate)
            ->with(['physicalActivities'])
            ->orderByDesc('entry_date')
            ->get();

        return response()->json(['data' => $entries]);
    }

    // WeightComparisonChart — poids initial et poids actuel
    public function weight(Request $request): JsonResponse
    {
        $profile = HealthProfile::where('user_id', $request->user()->id)->first();

        return response()->json([
            'data' => [
                'initial_weight' => $profile?->initial_weight,
                'current_weight' => $profile?->current_weight,
            ],
        ]);
    }
}
