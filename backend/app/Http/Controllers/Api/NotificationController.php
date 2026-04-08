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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function __construct(private readonly HealthDataService $healthDataService)
    {
    }

    // List all notifications for the user (fetched via their treatments)
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $this->triggerMedicationNotificationsBySchedule($user);
        }

        $treatmentIds = $user->treatments()->pluck('id');

        $notificationsList = Notification::query()
            ->whereIn('treatment_id', $treatmentIds)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => optional($n->read_at)?->toISOString(),
                'created_at' => optional($n->created_at)?->toISOString(),
            ])
            ->values();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data'    => $notificationsList,
        ]);
    }

    // Mark a single notification as read
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $treatmentIds = $request->user()->treatments()->pluck('id');

        $notification = Notification::query()
            ->where('id', $notificationId)
            ->whereIn('treatment_id', $treatmentIds)
            ->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request): JsonResponse
    {
        $treatmentIds = $request->user()->treatments()->pluck('id');

        Notification::query()
            ->whereIn('treatment_id', $treatmentIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications have been marked as read.']);
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    // Trigger medication notifications per treatment according to schedule
    private function triggerMedicationNotificationsBySchedule(User $user): void
    {
        $now         = Carbon::now(config('app.timezone'));
        $targetDate  = $now->copy()->startOfDay();
        $currentHour = (int) $now->format('H');
        $dateKey     = $targetDate->toDateString();

        $medicines = collect($this->healthDataService->resolveTreatmentMedicines($user->id));
        if ($medicines->isEmpty()) {
            return;
        }

        $isMorningReminderWindow = $currentHour >= 5 && $currentHour < 20;
        $isNightMissedWindow     = $currentHour >= 20;

        // Load all today's checks once
        $todayChecks = TreatmentCheck::where('user_id', $user->id)
            ->whereDate('check_date', $dateKey)
            ->get();

        foreach ($medicines as $medicine) {
            $treatment = Treatment::find($medicine['id'] ?? null);
            if (! $treatment) {
                continue;
            }

            $stats = $this->buildStatisticsForMedicine($medicine, $todayChecks);

            if ($isMorningReminderWindow
                && $stats['expected_total'] > 0
                && ! $this->hasNotificationBeenSent($treatment->id, 'reminder', $targetDate)) {
                $this->createNotification($treatment->id, 'reminder', $targetDate, $stats);
            }

            if ($isNightMissedWindow
                && $stats['missing_total'] > 0
                && ! $this->hasNotificationBeenSent($treatment->id, 'missed', $targetDate)) {
                $this->createNotification($treatment->id, 'missed', $targetDate, $stats);
            }
        }
    }

    // Create a notification directly in the database
    private function createNotification(int $traitementId, string $type, Carbon $targetDate, array $stats): void
    {
        $isMissed = $type === 'missed';

        Notification::create([
            'id'            => Str::uuid()->toString(),
            'type'          => DailyTreatmentNotification::class,
            'treatment_id' => $traitementId,
            'data'          => [
                'notification_kind' => $type,
                'target_date'       => $targetDate->toDateString(),
                'title'             => $isMissed ? 'Forgotten treatments today' : 'Treatment reminders for today',
                'message'           => $isMissed
                    ? "You missed {$stats['missing_total']} dose(s) out of {$stats['expected_total']} expected."
                    : "You have {$stats['expected_total']} dose(s) expected today.",
                'expected_total'    => $stats['expected_total'],
                'taken_total'       => $stats['taken_total'],
                'missing_total'     => $stats['missing_total'],
                'items'             => $stats['items'],
            ],
        ]);
    }

    // Build statistics for a single medicine
    private function buildStatisticsForMedicine(array $medicine, Collection $checks): array
    {
        $medicineId   = (string) ($medicine['id'] ?? '');
        $medicineName = (string) ($medicine['name'] ?? 'Treatment');
        $expected     = max(1, (int) ($medicine['doses_per_day'] ?? 1));

        $taken = min($expected, $checks->filter(
            fn (TreatmentCheck $c) => str_starts_with((string) $c->medication_key, $medicineId . '__dose_') && (bool) $c->taken
        )->count());

        $missing = max(0, $expected - $taken);

        return [
            'items'          => [[
                'medication_id'   => $medicineId,
                'medication_name' => $medicineName,
                'expected'        => $expected,
                'taken'           => $taken,
                'missing'         => $missing,
            ]],
            'expected_total' => $expected,
            'taken_total'    => $taken,
            'missing_total'  => $missing,
        ];
    }

    // Check if a notification was already sent for this treatment/type/date
    private function hasNotificationBeenSent(int $traitementId, string $type, Carbon $date): bool
    {
        return Notification::query()
            ->where('treatment_id', $traitementId)
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}
