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
    public function __construct(private readonly HealthDataService $healthDataService) {}

    // Récupérer les 100 dernières notifications de l'utilisateur
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->triggerMedicationNotifications($user);

        $treatmentIds = $user->treatments()->pluck('treatments.id');

        $notifications = Notification::whereIn('treatment_id', $treatmentIds)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'id'          => $n->id,
                'kind'        => $n->kind,
                'message'     => $n->message,
                'target_date' => $n->target_date?->toDateString(),
                'read_at'     => $n->read_at?->toISOString(),
                'created_at'  => $n->created_at?->toISOString(),
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
        $treatmentIds = $request->user()->treatments()->pluck('treatments.id');

        Notification::whereIn('treatment_id', $treatmentIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    // Déclencher les notifications si nécessaire pour aujourd'hui
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

            $medicineName = $medicine['name'] ?? 'Traitement';

            if ($isMorningWindow && !$this->alreadyExists($treatment->id, 'reminder', $today)) {
                $this->createNotification($treatment->id, 'reminder', $today, $medicineName);
            }

            if ($isNightWindow) {
                $missedAny = TreatmentCheck::where('user_id', $user->id)
                    ->where('treatment_id', $treatment->id)
                    ->whereDate('check_date', $today->toDateString())
                    ->where('taken', false)
                    ->exists();

                if ($missedAny && !$this->alreadyExists($treatment->id, 'missed', $today)) {
                    $this->createNotification($treatment->id, 'missed', $today, $medicineName);
                }
            }
        }
    }

    // Créer une notification
    private function createNotification(int $treatmentId, string $kind, Carbon $date, string $medicineName): void
    {
        Notification::create([
            'treatment_id' => $treatmentId,
            'kind'         => $kind,
            'target_date'  => $date->toDateString(),
            'message'      => $kind === 'missed'
                ? "Vous avez oublié de prendre {$medicineName} aujourd'hui."
                : "N'oubliez pas de prendre {$medicineName} aujourd'hui.",
        ]);
    }

    // Vérifier si la notification existe déjà pour aujourd'hui
    private function alreadyExists(int $treatmentId, string $kind, Carbon $date): bool
    {
        return Notification::where('treatment_id', $treatmentId)
            ->where('kind', $kind)
            ->where('target_date', $date->toDateString())
            ->exists();
    }
}
