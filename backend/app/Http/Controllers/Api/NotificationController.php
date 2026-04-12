<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Notifications\DailyTreatmentNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Récupérer les 100 dernières notifications de l'utilisateur
    // Déclenche également les nouvelles notifications si nécessaire avant de retourner la liste
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Déclencher les nouvelles notifications
        $this->triggerMedicationNotifications($user);

        // Récupérer les identifiants de traitement de l'utilisateur
        $treatmentIds = $user->treatments()->pluck('id');

        // Charger et formater les notifications
        $notifications = Notification::whereIn('treatment_id', $treatmentIds)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at?->toISOString(),
            ])
            ->values();

        return response()->json([
            'message' => 'Notifications récupérées avec succès.',
            'data'    => $notifications,
        ]);
    }

    // Marquer toutes les notifications comme lues
    public function markAllAsRead(Request $request): JsonResponse
    {
        $treatmentIds = $request->user()->treatments()->pluck('id');

        Notification::whereIn('treatment_id', $treatmentIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    // Vérifier si des notifications de médicaments doivent être créées pour aujourd'hui
    // - Matin (5h–20h): envoyer un rappel pour chaque médicament
    // - Nuit (20h+): envoyer une alerte pour dose non prise
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

        foreach ($medicines as $medicine) {
            $treatment = Treatment::find($medicine['id'] ?? null);
            if (!$treatment) {
                continue;
            }

            $medicineName = $medicine['name'] ?? 'Treatment';

            // Envoyer un rappel le matin
            if ($isMorningWindow && !$this->notificationAlreadySent($treatment->id, 'reminder', $today)) {
                $this->createNotification($treatment->id, 'reminder', $today, $medicineName);
            }

            // Vérifier les doses manquées la nuit
            if ($isNightWindow) {
                $missedAny = TreatmentCheck::whereHas('healthData', fn ($q) => $q->where('user_id', $user->id))
                    ->where('treatment_id', $treatment->id)
                    ->whereDate('check_date', $today->toDateString())
                    ->where('taken', false)
                    ->exists();

                if ($missedAny && !$this->notificationAlreadySent($treatment->id, 'missed', $today)) {
                    $this->createNotification($treatment->id, 'missed', $today, $medicineName);
                }
            }
        }
    }

    // Créer une notification pour le traitement du jour
    private function createNotification(int $treatmentId, string $type, Carbon $date, string $medicineName): void
    {
        $isMissed = $type === 'missed';

        Notification::create([
            'id'           => Str::uuid()->toString(),
            'type'         => DailyTreatmentNotification::class,
            'treatment_id' => $treatmentId,
            'data'         => [
                'notification_kind' => $type,
                'target_date'       => $date->toDateString(),
                'title'             => $isMissed ? 'Traitement oublié aujourd\'hui' : 'Rappel de traitement',
                'message'           => $isMissed
                    ? "Vous avez oublié de prendre {$medicineName} aujourd'hui."
                    : "N'oubliez pas de prendre {$medicineName} aujourd'hui.",
            ],
        ]);
    }

    // Vérifier si la notification a déjà été envoyeé aujourd'hui
    private function notificationAlreadySent(int $treatmentId, string $type, Carbon $date): bool
    {
        return Notification::where('treatment_id', $treatmentId)
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}
