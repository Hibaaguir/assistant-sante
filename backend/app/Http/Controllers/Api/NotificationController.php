<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Notifications\DailyTreatmentNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService)
    {
    }

    // List all notifications for a user
    public function index(Request $request): JsonResponse
    {
        // Get user and trigger medication notifications
        $user = $request->user();
        // Check that user is a User instance
        if ($user instanceof User) {
            $this->triggerMedicationNotificationsBySchedule($user);
        }

        $notificationsList = $request->user()
            ->notifications()
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($notificationItem) {
                return [
                    'id' => $notificationItem->id,
                    'type' => $notificationItem->type,
                    'data' => $notificationItem->data,
                    'read_at' => optional($notificationItem->read_at)?->toISOString(),
                    'created_at' => optional($notificationItem->created_at)?->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data' => $notificationsList,
        ]);
    }

    // Mark a notification as read
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $foundNotification = $request->user()->notifications()->whereKey($notificationId)->first();
        // Check that notification exists
        if (! $foundNotification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        // Mark as read only if not already read
        if ($foundNotification->read_at === null) {
            $foundNotification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications have been marked as read.',
        ]);
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    // Trigger medication notifications according to schedule
    private function triggerMedicationNotificationsBySchedule(User $user): void
    {
        // Get current date and time
        $now = Carbon::now(config('app.timezone'));
        $targetDate = $now->copy()->startOfDay();
        $currentHour = (int) $now->format('H');

        $medications = collect($this->healthDataService->resolveTreatmentMedicines($user->id));
        // Return if user has no medications to process
        if ($medications->isEmpty()) {
            return;
        }

        $statistics = $this->buildStatisticsForDate($user->id, $targetDate, $medications->all());

        // Send reminder any time before evening if not yet sent today
        $isMorningReminderWindow = $currentHour >= 5 && $currentHour < 20;
        if ($isMorningReminderWindow
            && $statistics['expected_total'] > 0
            && ! $this->hasNotificationBeenSent($user, 'reminder', $targetDate)) {
            $user->notify(new DailyTreatmentNotification(
                notificationType: 'reminder',
                targetDate: $targetDate,
                items: $statistics['items'],
                expectedTotal: $statistics['expected_total'],
                takenTotal: $statistics['taken_total'],
                missingTotal: $statistics['missing_total'],
            ));
        }

        $isNightMissedWindow = $currentHour >= 20;
        if ($isNightMissedWindow
            && $statistics['missing_total'] > 0
            && ! $this->hasNotificationBeenSent($user, 'missed', $targetDate)) {
            $user->notify(new DailyTreatmentNotification(
                notificationType: 'missed',
                targetDate: $targetDate,
                items: $statistics['items'],
                expectedTotal: $statistics['expected_total'],
                takenTotal: $statistics['taken_total'],
                missingTotal: $statistics['missing_total'],
            ));
        }
    }

    // Build treatment statistics for a date
    private function buildStatisticsForDate(int $userId, Carbon $targetDate, array $medications): array
    {
        $dateKey = $targetDate->toDateString();
        $checks = TreatmentCheck::query()
            ->where('user_id', $userId)
            ->whereDate('check_date', $dateKey)
            ->get();

        $items = [];
        $expectedTotal = 0;
        $takenTotal = 0;

        foreach ($medications as $medication) {
            $medicationId = (string) ($medication['id'] ?? '');
            $medicationName = (string) ($medication['name'] ?? 'Treatment');
            $expectedDoses = max(1, (int) ($medication['doses_per_day'] ?? 1));

            $takenDosesForMedication = $checks
                ->filter(function (TreatmentCheck $check) use ($medicationId) {
                    return str_starts_with((string) $check->medication_key, $medicationId.'__dose_') && (bool) $check->taken;
                })
                ->count();

            $takenDosesForMedication = min($takenDosesForMedication, $expectedDoses);
            $missingDosesForMedication = max(0, $expectedDoses - $takenDosesForMedication);

            $items[] = [
                'medication_id' => $medicationId,
                'medication_name' => $medicationName,
                'expected' => $expectedDoses,
                'taken' => $takenDosesForMedication,
                'missing' => $missingDosesForMedication,
            ];

            $expectedTotal += $expectedDoses;
            $takenTotal += $takenDosesForMedication;
        }

        return [
            'items' => $items,
            'expected_total' => $expectedTotal,
            'taken_total' => $takenTotal,
            'missing_total' => max(0, $expectedTotal - $takenTotal),
        ];
    }

    // Check if notification has already been sent
    private function hasNotificationBeenSent(User $user, string $notificationType, Carbon $targetDate): bool
    {
        return $user->notifications()
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $notificationType)
            ->where('data->target_date', $targetDate->toDateString())
            ->exists();
    }
}
