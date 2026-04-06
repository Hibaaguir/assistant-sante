<?php

namespace App\Console\Commands;

use App\Models\TreatmentCheck;
use App\Models\User;
use App\Notifications\DailyTreatmentNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NotifyDailyTreatments extends Command
{
    protected $signature = 'treatments:notify
                            {--mode=all : reminder|missed|all}
                            {--date= : Target date in YYYY-MM-DD format}';

    protected $description = 'Send reminder and missed-dose notifications for today\'s treatments';

    public function __construct(private readonly HealthDataService $healthDataService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $mode = strtolower((string) $this->option('mode'));

        if (! in_array($mode, ['all', 'reminder', 'missed'], true)) {
            $this->error('Invalid --mode option. Allowed values: all, reminder, missed.');
            return self::FAILURE;
        }

        $date = $this->resolveDate();
        $counts = ['reminder' => 0, 'missed' => 0];

        User::whereHas('healthProfile')->get()->each(function (User $user) use ($mode, $date, &$counts) {
            $medicines = collect($this->healthDataService->resolveTreatmentMedicines($user->id));

            if ($medicines->isEmpty()) return;

            $stats = $this->buildStats($user->id, $date, $medicines);

            $this->maybeNotify($user, 'reminder', $mode, $date, $stats, $counts);
            $this->maybeNotify($user, 'missed',   $mode, $date, $stats, $counts);
        });

        $this->info("Reminder notifications sent: {$counts['reminder']}");
        $this->info("Missed-dose notifications sent: {$counts['missed']}");

        return self::SUCCESS;
    }

    private function maybeNotify(User $user, string $type, string $mode, Carbon $date, array $stats, array &$counts): void
    {
        $guard = $type === 'reminder' ? $stats['expected_total'] > 0 : $stats['missing_total'] > 0;

        if (! in_array($mode, ['all', $type], true) || ! $guard || $this->alreadyNotified($user, $type, $date)) {
            return;
        }

        $user->notify(new DailyTreatmentNotification(
            notificationType: $type,
            targetDate:       $date,
            items:            $stats['items'],
            expectedTotal:    $stats['expected_total'],
            takenTotal:       $stats['taken_total'],
            missingTotal:     $stats['missing_total'],
        ));

        $counts[$type]++;
    }

    private function resolveDate(): Carbon
    {
        $raw = $this->option('date');
        return is_string($raw) && trim($raw) !== '' ? Carbon::parse($raw)->startOfDay() : Carbon::today();
    }

    private function buildStats(int $userId, Carbon $date, Collection $medicines): array
    {
        $checks = TreatmentCheck::query()
            ->where('user_id', $userId)
            ->whereDate('check_date', $date->toDateString())
            ->get();

        $items = $medicines->map(function (array $med) use ($checks) {
            $id       = (string) ($med['id'] ?? '');
            $expected = max(1, (int) ($med['doses_per_day'] ?? 1));
            $taken    = min($expected, $checks->filter(
                fn (TreatmentCheck $c) => str_starts_with((string) $c->medication_key, $id.'__dose_') && (bool) $c->taken
            )->count());

            return [
                'medication_id'   => $id,
                'medication_name' => (string) ($med['name'] ?? 'Treatment'),
                'expected'        => $expected,
                'taken'           => $taken,
                'missing'         => $expected - $taken,
            ];
        })->values()->all();

        $expectedTotal = array_sum(array_column($items, 'expected'));
        $takenTotal    = array_sum(array_column($items, 'taken'));

        return [
            'items'          => $items,
            'expected_total' => $expectedTotal,
            'taken_total'    => $takenTotal,
            'missing_total'  => $expectedTotal - $takenTotal,
        ];
    }

    private function alreadyNotified(User $user, string $type, Carbon $date): bool
    {
        return $user->notifications()
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}
