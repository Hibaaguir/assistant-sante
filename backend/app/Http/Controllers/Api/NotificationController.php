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

    // ─────────────────────────────────────────────────────────────────────────
    // Return the last 100 notifications for the current user
    // Also triggers new notifications if needed before returning the list
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if any new notifications need to be created before returning
        $this->triggerMedicationNotifications($user);

        // Get all treatment IDs that belong to this user
        $treatmentIds = $user->treatments()->pluck('id');

        // Load and format the notifications
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
            'message' => 'Notifications retrieved successfully.',
            'data'    => $notifications,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    public function markAllAsRead(Request $request): JsonResponse
    {
        $treatmentIds = $request->user()->treatments()->pluck('id');

        Notification::whereIn('treatment_id', $treatmentIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications have been marked as read.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Check if any medication notifications need to be created for today
    //
    // - Morning (5h–20h): send a reminder for each medicine
    // - Night (20h+):     send a missed alert if any dose was not taken
    // ─────────────────────────────────────────────────────────────────────────
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

            // Morning: send a simple reminder if not already sent today
            if ($isMorningWindow && !$this->notificationAlreadySent($treatment->id, 'reminder', $today)) {
                $this->createNotification($treatment->id, 'reminder', $today, $medicineName);
            }

            // Night: check if the user missed any dose for this medicine today
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

    // ─────────────────────────────────────────────────────────────────────────
    // Save a notification in the database
    // ─────────────────────────────────────────────────────────────────────────
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
                'title'             => $isMissed ? 'Forgotten treatment today' : 'Treatment reminder for today',
                'message'           => $isMissed
                    ? "You forgot to take {$medicineName} today."
                    : "Don't forget to take {$medicineName} today.",
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Check if a notification of this type was already sent today for a treatment
    // ─────────────────────────────────────────────────────────────────────────
    private function notificationAlreadySent(int $treatmentId, string $type, Carbon $date): bool
    {
        return Notification::where('treatment_id', $treatmentId)
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}
