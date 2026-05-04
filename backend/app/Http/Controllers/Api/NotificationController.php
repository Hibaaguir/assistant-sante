<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Injection automatique du service qui récupère les médicaments actifs
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Récupérer les 100 dernières notifications de l'utilisateur
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Créer les notifications du jour si elles n'existent pas encore
        $this->triggerMedicationNotifications($user);

        // Récupérer les IDs des traitements de l'utilisateur
        $treatmentIds = $user->treatments()->pluck('treatments.id');

        // Récupérer les 100 dernières notifications et les formater
        $result = [];
        $notifications = Notification::whereIn('treatment_id', $treatmentIds)
            ->latest()
            ->limit(100)
            ->get();

        foreach ($notifications as $n) {
            $result[] = [
                'id'          => $n->id,
                'kind'        => $n->kind,
                'message'     => $n->message,
                'target_date' => $n->target_date?->toDateString(),
                'read_at'     => $n->read_at?->toISOString(),
                'created_at'  => $n->created_at?->toISOString(),
            ];
        }

        return response()->json([
            'message' => 'Notifications récupérées avec succès.',
            'data'    => $result,
        ]);
    }

    // Marquer toutes les notifications comme lues
    public function markAllAsRead(Request $request): JsonResponse
    {
        $treatmentIds = $request->user()->treatments()->pluck('treatments.id');

        Notification::whereIn('treatment_id', $treatmentIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    // Déclencher les notifications du jour si elles n'existent pas encore
    private function triggerMedicationNotifications(User $user): void
    {
        $now         = Carbon::now(config('app.timezone'));
        $today       = Carbon::today(config('app.timezone'));
        $currentHour = (int) $now->format('H');

        $medicines = collect($this->healthDataService->resolveTreatmentMedicines($user->id));

        if ($medicines->isEmpty()) {
            return;
        }

        $isMorningWindow = $currentHour >= 5 && $currentHour < 20;
        $isNightWindow   = $currentHour >= 20;

        // Chargement de tous les checks de l'utilisateur aujourd'hui en une seule requête
        $checks = TreatmentCheck::where('user_id', $user->id)
            ->whereDate('check_date', $today->toDateString())
            ->where('taken', false)
            ->get();

        foreach ($medicines as $medicine) {
            $treatment = Treatment::find($medicine['id'] ?? null);

            if (!$treatment) {
                continue;
            }

            $medicineName = $medicine['name'] ?? 'Traitement';

            // Fenêtre du matin : créer un rappel s'il n'existe pas encore
            if ($isMorningWindow && !$this->alreadyExists($treatment->id, 'reminder', $today)) {
                $this->createNotification($treatment->id, 'reminder', $today, $medicineName);
            }

            // Fenêtre du soir : créer une notification si des doses ont été oubliées
            if ($isNightWindow) {
                $missedAny = $checks->where('treatment_id', $treatment->id)->isNotEmpty();

                if ($missedAny && !$this->alreadyExists($treatment->id, 'missed', $today)) {
                    $this->createNotification($treatment->id, 'missed', $today, $medicineName);
                }
            }
        }
    }

    // Créer et sauvegarder une notification en base de données
    private function createNotification(int $treatmentId, string $kind, Carbon $date, string $medicineName): void
    {
        if ($kind === 'missed') {
            $message = "Vous avez manqué au moins une prise de {$medicineName} aujourd'hui.";
        } else {
            $message = "N'oubliez pas de prendre {$medicineName} aujourd'hui.";
        }

        Notification::create([
            'treatment_id' => $treatmentId,
            'kind'         => $kind,
            'target_date'  => $date->toDateString(),
            'message'      => $message,
        ]);
    }

    // Vérifier si une notification identique existe déjà pour aujourd'hui
    private function alreadyExists(int $treatmentId, string $kind, Carbon $date): bool
    {
        return Notification::where('treatment_id', $treatmentId)
            ->where('kind', $kind)
            ->where('target_date', $date->toDateString())
            ->exists();
    }
}
